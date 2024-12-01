// ==UserScript==
// @name         Extract Citation Data with DOI Lookup
// @namespace    http://tampermonkey.net/
// @version      1.2
// @description  Extract citation strings from GB/T 7714 and APA rows on Google Scholar and query DOI via CrossRef API
// @author       Ayo
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest
// @connect      api.crossref.org
// ==/UserScript==

(function () {
    'use strict';

    window.addEventListener('load', () => {
        // 创建按钮
        const button = document.createElement('button');
        button.textContent = '提取内容并查询 DOI';
        button.style.position = 'fixed';
        button.style.top = '10px';
        button.style.right = '10px';
        button.style.zIndex = 9999;
        button.style.backgroundColor = '#4CAF50';
        button.style.color = 'white';
        button.style.border = 'none';
        button.style.padding = '10px';
        button.style.cursor = 'pointer';
        document.body.appendChild(button);

        // 点击按钮事件
        button.addEventListener('click', function () {
            try {
                const gbElement = document.evaluate(
                    "//th[text()='GB/T 7714']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;
                const gbText = gbElement ? gbElement.textContent.trim() : '未找到 GB/T 7714 内容';

                const apaElement = document.evaluate(
                    "//th[text()='APA']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;
                const apaText = apaElement ? apaElement.textContent.trim() : '未找到 APA 内容';

                displayResult({ gbText, apaText });

                queryDOI(gbText);
            } catch (e) {
                console.error("提取时出错:", e);
                alert('提取过程中发生错误，请检查脚本或页面内容');
            }
        });
    });

    // 动态创建弹窗显示结果
    function displayResult({ gbText, apaText, doi = '查询中...', title = '查询中...' }) {
        let container = document.getElementById('result-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'result-container';
            container.style.position = 'fixed';
            container.style.top = '50px';
            container.style.right = '10px';
            container.style.width = '300px';
            container.style.padding = '10px';
            container.style.border = '1px solid #ccc';
            container.style.backgroundColor = '#fff';
            container.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
            container.style.zIndex = 9999;
            container.style.overflowY = 'auto';
            document.body.appendChild(container);
        }

        container.innerHTML = `
            <h3>提取结果</h3>
            <p><strong>GB/T 7714:</strong> ${gbText}</p>
            <p><strong>APA:</strong> ${apaText}</p>
            <h3>DOI 查询结果</h3>
            <p><strong>DOI:</strong> ${doi}</p>
            <p><strong>标题:</strong> ${title}</p>
        `;
    }

    // 查询 CrossRef API 并更新弹窗
    function queryDOI(reference) {
        const apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(reference)}`;

        GM_xmlhttpRequest({
            method: 'GET',
            url: apiUrl,
            onload: (response) => {
                try {
                    const data = JSON.parse(response.responseText);
                    if (data.message && data.message.items && data.message.items.length > 0) {
                        const firstResult = data.message.items[0];
                        const doi = firstResult.DOI || '未找到 DOI';
                        const title = firstResult.title ? firstResult.title.join(' ') : '未找到标题';

                        // 更新弹窗内容
                        displayResult({ gbText: reference, apaText: reference, doi, title });
                    } else {
                        displayResult({ gbText: reference, apaText: reference, doi: '未找到 DOI', title: '未找到标题' });
                    }
                } catch (e) {
                    console.error("解析 API 响应时出错:", e);
                    displayResult({ gbText: reference, apaText: reference, doi: '查询失败', title: '查询失败' });
                }
            },
            onerror: () => {
                displayResult({ gbText: reference, apaText: reference, doi: '查询失败', title: '查询失败' });
            }
        });
    }
})();
