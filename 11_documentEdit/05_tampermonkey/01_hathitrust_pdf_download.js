// ==UserScript==
// @name         HathiTrust 批量下载（可配置版）
// @namespace    https://example.com/
// @version      2.0
// @description  先点第一个Download再点第二个Download；支持自定义 id、起止页、等待时间与文件名
// @match        https://babel.hathitrust.org/cgi/pt*
// @match        https://*.hathitrust.org/cgi/pt*
// @run-at       document-idle
// @grant        GM_setValue
// @grant        GM_getValue
// @grant        GM_notification
// @grant        GM_download
// @connect      babel.hathitrust.org
// ==/UserScript==

(function () {
  'use strict';

  // ====== 可配置参数 ======
  const CONFIG = {
    // 指定书籍 id；为 null 表示使用当前 URL 的 id 参数
    bookId: "hvd.hc54vb",                     // 例: "hvd.hc54vb"
    // 下载页码范围
    pageStart: 121,
    pageEnd: 127,
    // 第二次 Download 点击后停留等待（避免请求被取消）
    waitAfterSecondMs: 3500,
    // 翻页之间的基础间隔
    gapBetweenPagesMs: 1500,
    // 优先用 GM_download 后台下载；若为 false 则只模拟点击
    useGMDownload: true,
    // 文件名模板（仅 GM_download 生效）
    filenamePattern: '{id}_seq{seq}.pdf',
    // 显示调试日志
    debug: false,
  };
  // ========================

  const STORE_KEY = 'ht_batch_dl_job';
  let runningLock = false;

  // ---------- 小工具 ----------
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));
  const qs  = (s, root=document) => root.querySelector(s);
  const qsa = (s, root=document) => Array.from(root.querySelectorAll(s));
  const getParam = (k, s = location.search) => new URLSearchParams(s).get(k);
  const log = (...a)=>CONFIG.debug && console.log('[HT]', ...a);
  const notify = (t)=>{ try{ GM_notification({text:t,title:'HathiTrust 批量下载',timeout:2000}); }catch{ console.log('[HT]', t); } };
  const saveJob = (j)=>GM_setValue(STORE_KEY, j);
  const loadJob = ()=>GM_getValue(STORE_KEY);
  const clearJob = ()=>GM_setValue(STORE_KEY, null);

  const sanitize = s => (s||'').replace(/[^\w.-]+/g,'_');
  const formatName = (pattern, vars) =>
    pattern.replace(/\{(\w+)\}/g, (_,k)=> (vars[k] != null ? String(vars[k]) : ''));

  function idToUse() {
    return CONFIG.bookId || getParam('id');
  }

  function buildSeqUrl(id, seq) {
    const u = new URL(location.href);
    u.searchParams.set('id', id);
    u.searchParams.set('seq', String(seq));
    return u.toString();
  }

  function isVisible(el){
    if(!el) return false;
    const st = getComputedStyle(el);
    if (st.display === 'none' || st.visibility === 'hidden' || st.opacity === '0') return false;
    const r = el.getBoundingClientRect();
    return r.width > 0 && r.height > 0;
  }

  function findVisibleDownloadButtons() {
    return qsa('a,button,[role="button"]').filter(isVisible).filter(el=>{
      const t=(el.textContent||'').trim().toLowerCase();
      const title=(el.getAttribute('title')||'').toLowerCase();
      const aria=(el.getAttribute('aria-label')||'').toLowerCase();
      return t.includes('download') || title.includes('download') || aria.includes('download');
    });
  }

  async function waitFirstDownload(maxWait=20000, step=300){
    const t0=Date.now(); let btn=findVisibleDownloadButtons()[0];
    while(!btn && Date.now()-t0<maxWait){ await sleep(step); btn=findVisibleDownloadButtons()[0]; }
    log('first btn:', btn);
    return btn||null;
  }

  async function waitSecondDownload(firstBtn, maxWait=20000, step=300){
    const t0=Date.now();
    while(Date.now()-t0<maxWait){
      const all=findVisibleDownloadButtons().filter(el=>el!==firstBtn);
      if(all.length){
        const top1=firstBtn.getBoundingClientRect().top;
        const below=all.filter(el=>el.getBoundingClientRect().top >= top1 - 1);
        const second = below[0] || all[0];
        log('second btn:', second);
        return second;
      }
      await sleep(step);
    }
    return null;
  }

  function fireClickSeries(el){
    const types=['pointerdown','mousedown','mouseup','click'];
    for(const t of types){
      try{
        const Ctor = t.startsWith('pointer') ? PointerEvent : MouseEvent;
        el.dispatchEvent(new Ctor(t, {bubbles:true, cancelable:true}));
      }catch{}
    }
    try{ el.click(); }catch{}
  }

  function extractDownloadUrlNear(el, seq){
    const aSelf = (el.tagName === 'A' && el.getAttribute('href')) ? el : null;
    const aClosest = el.closest && el.closest('a[href]');
    const href1 = aSelf ? aSelf.getAttribute('href') : (aClosest ? aClosest.getAttribute('href') : null);

    let href2 = null;
    const container = el.closest('.dropdown, .menu, .popover, .modal, div, section') || document;
    const candidates = qsa('a[href]', container);
    for (const a of candidates){
      const h = a.getAttribute('href'); if (!h) continue;
      const H = h.toLowerCase();
      if ((H.includes('download') || H.includes('deliver') || H.includes('.pdf') || H.includes('format=pdf')) &&
          (H.includes('seq=') || H.includes('%3fseq%3d') || /[\?;&]seq=\d+/.test(H))) {
        href2 = h; break;
      }
    }

    const href = href1 || href2;
    if (!href) return null;
    try{ return new URL(href, location.href).href; }catch{ return null; }
  }

  function gmDownload(url, id, seq){
    if(!url) return false;
    const name = formatName(CONFIG.filenamePattern, {id: sanitize(id), seq});
    try{ GM_download({ url, name, saveAs: false, onerror: ()=>{} }); log('GM_download:', url, name); return true; }
    catch{ return false; }
  }

  // ---------- 主流程 ----------
  async function processIfJobActive(){
    if (runningLock) return;
    const job = loadJob();
    if (!job || !job.active) return;
    runningLock = true;

    try{
      const curId  = getParam('id');
      const curSeq = parseInt(getParam('seq') || CONFIG.pageStart, 10);

      if (curId !== job.id){ location.href = buildSeqUrl(job.id, job.nextSeq); return; }
      if (curSeq !== job.nextSeq){ location.href = buildSeqUrl(job.id, job.nextSeq); return; }

      notify(`第 ${job.nextSeq} 页：等待第一个 Download…`);
      const first = await waitFirstDownload();
      if (!first){ notify(`未找到第一个 Download（seq=${job.nextSeq}）`); clearJob(); return; }
      fireClickSeries(first);

      notify(`第 ${job.nextSeq} 页：等待第二个 Download…`);
      const second = await waitSecondDownload(first);
      if (!second){ notify(`未找到第二个 Download（seq=${job.nextSeq}）`); clearJob(); return; }

      let usedGM = false;
      if (CONFIG.useGMDownload){
        const u = extractDownloadUrlNear(second, job.nextSeq);
        if (u) usedGM = gmDownload(u, job.id, job.nextSeq);
      }
      if (!usedGM) fireClickSeries(second);

      await sleep(job.waitAfterSecondMs);

      const next = job.nextSeq + 1;
      if (next <= job.endSeq){
        saveJob({ ...job, nextSeq: next });
        await sleep(job.gapBetweenPagesMs);
        location.href = buildSeqUrl(job.id, next);
      } else {
        notify(`完成（${job.startSeq}–${job.endSeq}）`);
        clearJob();
      }
    } finally {
      runningLock = false;
    }
  }

  // ---------- UI ----------
  function addButton(){
    if (qs('#ht-auto-download-btn')) return;
    const btn = document.createElement('button');
    btn.id = 'ht-auto-download-btn';
    btn.textContent = `自动下载 ${CONFIG.pageStart}–${CONFIG.pageEnd}`;
    Object.assign(btn.style, {
      position:'fixed', right:'18px', bottom:'18px', zIndex: 999999,
      padding:'10px 14px', borderRadius:'10px', border:'1px solid #999',
      background:'#1f6feb', color:'#fff', fontSize:'14px', cursor:'pointer',
      boxShadow:'0 4px 12px rgba(0,0,0,0.2)',
    });
    btn.title = '两次 Download；可配置起止页/等待时间/文件名/是否GM_download';
    btn.addEventListener('click', ()=>{
      const id = idToUse();
      if (!id){ notify('未检测到 id 参数，且未在 CONFIG.bookId 指定'); return; }

      const prev = loadJob();
      if (prev && prev.active){ clearJob(); notify('已停止当前任务'); return; }

      // 写入任务（把配置拷贝进去，便于过程中读取）
      saveJob({
        active: true,
        id,
        nextSeq: CONFIG.pageStart,
        startSeq: CONFIG.pageStart,
        endSeq: CONFIG.pageEnd,
        waitAfterSecondMs: CONFIG.waitAfterSecondMs,
        gapBetweenPagesMs: CONFIG.gapBetweenPagesMs,
        startedAt: Date.now()
      });
      notify(`开始批量下载：seq=${CONFIG.pageStart} → ${CONFIG.pageEnd}`);
      location.href = buildSeqUrl(id, CONFIG.pageStart);
    });
    document.body.appendChild(btn);
  }

  // 初始
  addButton();
  processIfJobActive();

  // 监听 URL 变化
  let last = location.href;
  setInterval(()=>{
    if (location.href !== last){
      last = location.href;
      addButton();
      processIfJobActive();
    }
  }, 500);
})();
