// ==UserScript==
// @name         Extract Citation Data with DOI Lookup and Complete Reference Info
// @namespace    http://tampermonkey.net/
// @version      1.9
// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号、文章编号、出版商和 ISSN（标注类型）
// @author
// @match        https://scholar.google.com/*
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
    function displayResult({ gbText, apaText, doi = '查询中...', title = '查询中...', fullAuthors = '查询中...', abbreviatedAuthors = '查询中...', journal = '查询中...', publicationYear = '查询中...', volume = '查询中...', issue = '查询中...', pages = '查询中...', articleNumber = '查询中...', publisher = '查询中...', issnPrint = '查询中...', issnOnline = '查询中...', matchResult = '', extractedTitle = '' }) {
        let container = document.getElementById('result-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'result-container';
            container.style.position = 'fixed';
            container.style.top = '50px';
            container.style.right = '10px';
            container.style.width = '350px';
            container.style.maxHeight = '600px';
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
            <p><strong>期刊名:</strong> ${journal}</p>
            <p><strong>出版年:</strong> ${publicationYear}</p>
            <p><strong>卷:</strong> ${volume}</p>
            <p><strong>期:</strong> ${issue}</p>
            <p><strong>页码:</strong> ${pages}</p>
            <p><strong>文章号:</strong> ${articleNumber}</p>
            <p><strong>出版商:</strong> ${publisher}</p>
            <p><strong>ISSN (印刷版):</strong> ${issnPrint}</p>
            <p><strong>ISSN (电子版):</strong> ${issnOnline}</p>
            <p><strong>完整作者信息:</strong> ${fullAuthors}</p>
            <p><strong>缩写作者信息:</strong> ${abbreviatedAuthors}</p>
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

                        // 获取期刊名、出版年、卷、期、页码、文章号
                        const journal = firstResult['container-title'] ? firstResult['container-title'].join(' ') : '未找到期刊名';
                        const publicationYear = firstResult['published-print'] ? firstResult['published-print']['date-parts'][0][0] :
                                                (firstResult['published-online'] ? firstResult['published-online']['date-parts'][0][0] : '未找到出版年');
                        const volume = firstResult.volume || '未找到卷号';
                        const issue = firstResult.issue || '未找到期号';
                        const pages = firstResult.page || '未找到页码';
                        const articleNumber = firstResult['article-number'] || '未找到文章号';

                        // 获取出版商
                        const publisher = firstResult.publisher || '未找到出版商';

                        // 获取印刷版和电子版 ISSN
                        const issnPrint = firstResult['ISSN-type']?.find(item => item.type === 'print')?.value || '未找到印刷版 ISSN';
                        const issnOnline = firstResult['ISSN-type']?.find(item => item.type === 'electronic')?.value || '未找到电子版 ISSN';

                        // 获取完整作者信息
                        const authorsArray = firstResult.author || [];
                        const fullAuthors = formatFullAuthors(authorsArray) || '未找到作者信息';

                        // 获取并格式化缩写的作者信息
                        const abbreviatedAuthors = formatAbbreviatedAuthors(authorsArray) || '未找到作者信息';

                        // 校验标题是否匹配
                        const matchResult = compareTitles(title, extractedTitle)
                            ? '匹配成功'
                            : '标题不匹配，请检查引用或查询结果';

                        // 更新弹窗内容
                        displayResult({ gbText: reference, apaText: reference, doi, title, journal, publicationYear, volume, issue, pages, articleNumber, publisher, issnPrint, issnOnline, fullAuthors, abbreviatedAuthors, matchResult, extractedTitle });
                    } else {
                        displayResult({ gbText: reference, apaText: reference, doi: '未找到 DOI', title: '未找到标题', journal: '未找到期刊名', publicationYear: '未找到出版年', volume: '未找到卷号', issue: '未找到期号', pages: '未找到页码', articleNumber: '未找到文章号', publisher: '未找到出版商', issnPrint: '未找到印刷版 ISSN', issnOnline: '未找到电子版 ISSN', fullAuthors: '未找到作者信息', abbreviatedAuthors: '未找到作者信息', extractedTitle });
                    }
                } catch (e) {
                    console.error("解析 API 响应时出错:", e);
                    displayResult({ gbText: reference, apaText: reference, doi: '查询失败', title: '查询失败', journal: '查询失败', publicationYear: '查询失败', volume: '查询失败', issue: '查询失败', pages: '查询失败', articleNumber: '查询失败', publisher: '查询失败', issnPrint: '查询失败', issnOnline: '查询失败', fullAuthors: '查询失败', abbreviatedAuthors: '查询失败', extractedTitle });
                }
            },
            onerror: () => {
                displayResult({ gbText: reference, apaText: reference, doi: '查询失败', title: '查询失败', journal: '查询失败', publicationYear: '查询失败', volume: '查询失败', issue: '查询失败', pages: '查询失败', articleNumber: '查询失败', publisher: '查询失败', issnPrint: '查询失败', issnOnline: '查询失败', fullAuthors: '查询失败', abbreviatedAuthors: '查询失败', extractedTitle });
            }
        });
    }

    // 格式化完整作者信息
    function formatFullAuthors(authorsArray) {
        if (!authorsArray || authorsArray.length === 0) {
            return '';
        }

        const formattedAuthors = authorsArray.map(author => {
            const given = author.given || '';
            const family = author.family || '';
            return `${given} ${family}`.trim();
        });

        return formattedAuthors.join(', ');
    }

    // 格式化缩写后的作者信息
    function formatAbbreviatedAuthors(authorsArray) {
        if (!authorsArray || authorsArray.length === 0) {
            return '';
        }

        const formattedAuthors = authorsArray.map(author => {
            const given = author.given || '';
            const family = author.family || '';

            // 处理 given 名
            let abbreviatedGiven = '';
            if (given.includes(' ')) {
                const parts = given.split(' ');
                abbreviatedGiven = parts.map(part => part.charAt(0).toUpperCase() + '.').join('');
            } else {
                abbreviatedGiven = given.charAt(0).toUpperCase() + '.';
            }

            return `${abbreviatedGiven} ${family}`.trim();
        });

        const numAuthors = formattedAuthors.length;

        if (numAuthors === 1) {
            return formattedAuthors[0];
        } else if (numAuthors === 2) {
            return `${formattedAuthors[0]} and ${formattedAuthors[1]}`;
        } else {
            const allButLastTwo = formattedAuthors.slice(0, -2).join(', ');
            const lastTwo = formattedAuthors.slice(-2).join(', and ');
            return allButLastTwo ? `${allButLastTwo}, ${lastTwo}` : lastTwo;
        }
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
        return similarity > 0.5; // 阈值设置为 50%
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
                dp[i][j] = Math.min(
                    dp[i - 1][j] + 1,       // deletion
                    dp[i][j - 1] + 1,       // insertion
                    dp[i - 1][j - 1] + cost // substitution
                );
            }
        }
        const distance = dp[len1][len2];
        return 1 - distance / Math.max(len1, len2); // 相似度计算
    }
})();
