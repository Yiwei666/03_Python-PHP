// ==UserScript==
// @name         Douyin Link Saver
// @namespace    http://tampermonkey.net/
// @version      1.0
// @description  在页面右下角显示一个按钮，一键获取剪贴板内容并将其中的Douyin链接提交到服务器
// @match        https://www.douyin.com/*
// @grant        none
// ==/UserScript==

(function() {
    'use strict';

    /******************************************************************
     * 1. 创建并样式化按钮
     ******************************************************************/
    const btn = document.createElement('button');
    btn.innerText = '提取剪贴板并写入数据库';
    btn.style.position = 'fixed';
    btn.style.bottom = '20px';
    btn.style.right = '20px';
    btn.style.zIndex = 9999;
    btn.style.padding = '10px 15px';
    btn.style.backgroundColor = '#007bff';
    btn.style.color = '#fff';
    btn.style.border = 'none';
    btn.style.borderRadius = '4px';
    btn.style.cursor = 'pointer';
    btn.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
    btn.style.fontSize = '14px';
    document.body.appendChild(btn);

    /******************************************************************
     * 2. 按钮点击事件：读取剪贴板，确认后提交到后端
     ******************************************************************/
    btn.addEventListener('click', async () => {
        try {
            // 获取剪贴板内容（需 https 环境 或浏览器允许）
            const clipboardText = await navigator.clipboard.readText();
            if (!clipboardText) {
                alert('剪贴板中无内容，或浏览器不支持读取剪贴板。');
                return;
            }

            // 询问用户是否要提交
            const confirmSubmit = confirm(`剪贴板内容如下:\n\n${clipboardText}\n\n是否提取其中的链接并提交到数据库？`);
            if (!confirmSubmit) {
                return;
            }

            // 将剪贴板数据发送到后端
            const postUrl = 'https://domain.com/18_tm_url_api.php'; // 这里替换成你的后端脚本地址
            const formData = new FormData();
            formData.append('clipboardContent', clipboardText);

            const response = await fetch(postUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                alert(`请求失败，状态码：${response.status}`);
                return;
            }

            // 解析后端返回的 JSON
            const result = await response.json();
            console.log(result);

            // 给用户提示
            if (result.status === 'success') {
                // 有成功写入记录
                alert(`成功：${result.message}`);
            } else if (result.status === 'warning') {
                // 未新增记录
                alert(`提示：${result.message}`);
            } else {
                // 其他错误或异常
                alert(`错误：${result.message}`);
            }

            // 如果想查看每条链接的处理详情，也可在控制台查看:
            // console.log(result.detail);

        } catch (err) {
            console.error('读取或提交时出错:', err);
            alert(`读取或提交时出错: ${err}`);
        }
    });
})();
