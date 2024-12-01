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

                const apaElement = document.evaluate(
                    "//th[text()='APA']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;

                if (!gbElement || !apaElement) {
                    alert('未找到目标内容，请检查页面是否正确加载');
                    return;
                }

                const gbText = gbElement.textContent.trim();
                const apaText = apaElement.textContent.trim();

                // 提取 GB/T 7714 中的标题
                const extractedTitle = extractTitleFromReference(gbText);

                displayResult({ gbText, apaText, extractedTitle });

                queryDOI(gbText, extractedTitle);
            } catch (e) {
                console.error("提取时出错:", e);
                alert('提取过程中发生错误，请检查脚本或页面内容');
            }
        });
    });

    // 动态创建弹窗显示结果
    function displayResult({ gbText, apaText, doi = '查询中...', title = '查询中...', matchResult = '', extractedTitle = '' }) {
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
            <p><strong>提取的文章标题:</strong> ${extractedTitle}</p>
            <h3>DOI 查询结果</h3>
            <p><strong>DOI:</strong> ${doi}</p>
            <p><strong>标题:</strong> ${title}</p>
            <p><strong>匹配结果:</strong> ${matchResult}</p>
        `;
    }

    // 查询 CrossRef API 并更新弹窗
    function queryDOI(reference, extractedTitle) {
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

                        // 校验标题是否匹配
                        const matchResult = compareTitles(title, extractedTitle)
                            ? '匹配成功'
                            : '标题不匹配，请检查引用或查询结果';

                        // 更新弹窗内容
                        displayResult({ gbText: reference, apaText: reference, doi, title, matchResult, extractedTitle });
                    } else {
                        displayResult({ gbText: reference, apaText: reference, doi: '未找到 DOI', title: '未找到标题', extractedTitle });
                    }
                } catch (e) {
                    console.error("解析 API 响应时出错:", e);
                    displayResult({ gbText: reference, apaText: reference, doi: '查询失败', title: '查询失败', extractedTitle });
                }
            },
            onerror: () => {
                displayResult({ gbText: reference, apaText: reference, doi: '查询失败', title: '查询失败', extractedTitle });
            }
        });
    }

    // 从 GB/T 7714 格式中提取标题
    function extractTitleFromReference(reference) {
        // 定位第一个和第二个 "."，并去掉 [J]
        const firstDot = reference.indexOf('.');
        const secondDot = reference.indexOf('.', firstDot + 1);
        const string1 = reference.substring(firstDot + 1, secondDot).trim();
        const string2 = string1.replace('[J]', '').trim(); // 去掉 [J]
        return string2;
    }

    // 比较两个标题是否匹配
    function compareTitles(title1, title2) {
        if (!title1 || !title2) return false;

        // 使用字符串相似度算法（Levenshtein 距离）
        const similarity = calculateSimilarity(title1.toLowerCase(), title2.toLowerCase());
        return similarity > 0.8; // 阈值设置为 80%
    }

    // 计算字符串相似度（Levenshtein 距离）
    function calculateSimilarity(s1, s2) {
        const len1 = s1.length, len2 = s2.length;
        const dp = Array.from({ length: len1 + 1 }, () => Array(len2 + 1).fill(0));
        for (let i = 0; i <= len1; i++) dp[i][0] = i;
        for (let j = 0; j <= len2; j++) dp[0][j] = j;
        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                const cost = s1[i - 1] === s2[j - 1] ? 0 : 1;
                dp[i][j] = Math.min(dp[i - 1][j] + 1, dp[i][j - 1] + 1, dp[i - 1][j - 1] + cost);
            }
        }
        const distance = dp[len1][len2];
        return 1 - distance / Math.max(len1, len2); // 相似度计算
    }
})();
