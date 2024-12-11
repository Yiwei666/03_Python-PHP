// ==UserScript==
// @name         Extract Citation Data with DOI Lookup and Complete Reference Info
// @namespace    http://tampermonkey.net/
// @version      1.8
// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号和文章编号
// @author      
// @match        https://scholar.google.com/*
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest
// @connect      api.crossref.org
// ==/UserScript==

(function () {
    'use strict';

    const journalAbbreviationURL = 'http://39.105.186.182/06_journal_Abbreviation.txt'; // 替换为实际的期刊简称数据源
    let journalAbbreviations = {}; // 缓存期刊简称字典
    let gbText = ''; // 全局变量，保存 GB/T 7714 引用
    let apaText = ''; // 全局变量，保存 APA 引用

    // 加载期刊简称字典
    function loadJournalAbbreviations() {
        return new Promise((resolve, reject) => {
            const urlWithTimestamp = `${journalAbbreviationURL}?_=${new Date().getTime()}`; // 添加时间戳绕过缓存
            GM_xmlhttpRequest({
                method: 'GET',
                url: urlWithTimestamp, // 使用带时间戳的 URL
                onload: function (response) {
                    if (response.status === 200) {
                        const text = response.responseText;
                        const lines = text.split('\n');
                        const abbreviations = lines.reduce((acc, line) => {
                            const parts = line.split('/');
                            if (parts.length === 2) {
                                acc[htmlDecode(parts[0].trim())] = parts[1].trim(); // 使用 HTML 解码
                            }
                            return acc;
                        }, {});
                        console.log('期刊简称加载成功:', abbreviations);
                        resolve(abbreviations);
                    } else {
                        reject(new Error(`HTTP 状态码: ${response.status}`));
                    }
                },
                onerror: function (error) {
                    reject(new Error('加载期刊简称失败: ' + error));
                }
            });
        });
    }

    window.addEventListener('load', async () => {
        try {
            journalAbbreviations = await loadJournalAbbreviations();
        } catch (error) {
            console.error('期刊简称加载失败:', error);
        }

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

                gbText = gbElement.textContent.trim(); // 全局变量存储 GB/T 7714 引用
                apaText = apaElement.textContent.trim(); // 全局变量存储 APA 引用

                const extractedTitle = extractTitleFromReference(gbText);

                displayResult({ gbText, apaText, extractedTitle });

                queryDOI(gbText, extractedTitle);
            } catch (e) {
                console.error("提取时出错:", e);
                alert('提取过程中发生错误，请检查脚本或页面内容');
            }
        });
    });

    function displayResult({
        gbText,
        apaText,
        doi = '查询中...',
        title = '查询中...',
        fullAuthors = '查询中...',
        abbreviatedAuthors = '查询中...',
        journal = '查询中...',
        journalAbbreviation = '查询中...',
        publicationYear = '查询中...',
        volume = '查询中...',
        issue = '',
        pages = '查询中...',
        articleNumber = '查询中...',
        matchResult = '',
        extractedTitle = ''
    }) {
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

        // 处理缩写作者信息，移除 "and "
        let cleanedAuthors = abbreviatedAuthors.replace(/and\s+/gi, '');

        // 如果页码不存在，使用文章号
        let finalPages = (pages && !pages.startsWith('未找到')) ? pages : articleNumber;

        // 如果期号不存在，则不包含 " (" + 期号 + ")"
        let issuePart = issue && !issue.startsWith('未找到') ? ` (${issue})` : '';

        // 合成引用
        let composedCitation = `${cleanedAuthors}, ${title}, ${journalAbbreviation} ${volume}${issuePart} (${publicationYear}) ${finalPages}, https://doi.org/${doi}`;

        container.innerHTML = `
            <h3>提取结果</h3>
            <p><strong>GB/T 7714:</strong> ${gbText}</p>
            <p><strong>APA:</strong> ${apaText}</p>
            <p><strong>提取的文章标题:</strong> ${extractedTitle}</p>
            <h3>DOI 查询结果</h3>
            <p><strong>DOI:</strong> ${doi}</p>
            <p><strong>标题:</strong> ${title}</p>
            <p><strong>期刊名:</strong> ${journal}</p>
            <p><strong>期刊简称:</strong> ${journalAbbreviation}</p>
            <p><strong>出版年:</strong> ${publicationYear}</p>
            <p><strong>卷:</strong> ${volume}</p>
            <p><strong>期:</strong> ${issue}</p>
            <p><strong>页码:</strong> ${pages}</p>
            <p><strong>文章号:</strong> ${articleNumber}</p>
            <p><strong>完整作者信息:</strong> ${fullAuthors}</p>
            <p><strong>缩写作者信息:</strong> ${abbreviatedAuthors}</p>
            <p><strong>匹配结果:</strong> ${matchResult}</p>
            <h3>合成引用</h3>
            <p>${composedCitation}</p>
        `;
    }

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

                        const journal = firstResult['container-title'] ? firstResult['container-title'].join(' ') : '未找到期刊名';
                        const publicationYear = firstResult['published-print'] ? firstResult['published-print']['date-parts'][0][0] :
                                                (firstResult['published-online'] ? firstResult['published-online']['date-parts'][0][0] : '未找到出版年');
                        const volume = firstResult.volume || '未找到卷号';
                        const issue = firstResult.issue || '未找到期号';
                        const pages = firstResult.page || '未找到页码';
                        const articleNumber = firstResult['article-number'] || '未找到文章号';

                        const authorsArray = firstResult.author || [];
                        const fullAuthors = formatFullAuthors(authorsArray) || '未找到作者信息';
                        const abbreviatedAuthors = formatAbbreviatedAuthors(authorsArray) || '未找到作者信息';

                        const journalAbbreviation = matchJournalAbbreviation(journal);

                        const matchResult = compareTitles(title, extractedTitle)
                            ? '匹配成功'
                            : '标题不匹配，请检查引用或查询结果';

                        displayResult({
                            gbText,
                            apaText,
                            doi, title, journal, journalAbbreviation,
                            publicationYear, volume, issue, pages,
                            articleNumber, fullAuthors, abbreviatedAuthors,
                            matchResult, extractedTitle
                        });
                    } else {
                        displayResult({
                            gbText,
                            apaText,
                            doi: '未找到 DOI', title: '未找到标题',
                            journal: '未找到期刊名', journalAbbreviation: '未找到期刊简称',
                            publicationYear: '未找到出版年', volume: '未找到卷号',
                            issue: '未找到期号', pages: '未找到页码',
                            articleNumber: '未找到文章号',
                            fullAuthors: '未找到作者信息',
                            abbreviatedAuthors: '未找到作者信息',
                            extractedTitle
                        });
                    }
                } catch (e) {
                    console.error("解析 API 响应时出错:", e);
                    displayResult({
                        gbText,
                        apaText,
                        doi: '查询失败', title: '查询失败',
                        journal: '查询失败', journalAbbreviation: '查询失败',
                        publicationYear: '查询失败', volume: '查询失败',
                        issue: '查询失败', pages: '查询失败',
                        articleNumber: '查询失败',
                        fullAuthors: '查询失败',
                        abbreviatedAuthors: '查询失败',
                        extractedTitle
                    });
                }
            },
            onerror: () => {
                displayResult({
                    gbText,
                    apaText,
                    doi: '查询失败', title: '查询失败',
                    journal: '查询失败', journalAbbreviation: '查询失败',
                    publicationYear: '查询失败', volume: '查询失败',
                    issue: '查询失败', pages: '查询失败',
                    articleNumber: '查询失败',
                    fullAuthors: '查询失败',
                    abbreviatedAuthors: '查询失败',
                    extractedTitle
                });
            }
        });
    }

    function extractTitleFromReference(reference) {
        const firstDot = reference.indexOf('.');
        const secondDot = reference.indexOf('.', firstDot + 1);
        const string1 = reference.substring(firstDot + 1, secondDot).trim();
        const string2 = string1.replace('[J]', '').trim();
        return string2;
    }

    function compareTitles(title1, title2) {
        if (!title1 || !title2) return false;

        const similarity = calculateSimilarity(title1.toLowerCase(), title2.toLowerCase());
        return similarity > 0.5;
    }

    function calculateSimilarity(s1, s2) {
        const len1 = s1.length, len2 = s2.length;
        const dp = Array.from({ length: len1 + 1 }, () => Array(len2 + 1).fill(0));
        for (let i = 0; i <= len1; i++) dp[i][0] = i;
        for (let j = 0; j <= len2; j++) dp[0][j] = j;
        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                const cost = s1[i - 1] === s2[j - 1] ? 0 : 1;
                dp[i][j] = Math.min(
                    dp[i - 1][j] + 1,
                    dp[i][j - 1] + 1,
                    dp[i - 1][j - 1] + cost
                );
            }
        }
        const distance = dp[len1][len2];
        return 1 - distance / Math.max(len1, len2);
    }

    function matchJournalAbbreviation(journalFullName) {
        if (!journalFullName) return '未找到期刊简称';

        const decodedJournalFullName = htmlDecode(journalFullName);

        const matchedKey = Object.keys(journalAbbreviations).find(key =>
            htmlDecode(key).toLowerCase() === decodedJournalFullName.toLowerCase()
        );

        return matchedKey ? journalAbbreviations[matchedKey] : '未找到期刊简称';
    }

    function htmlDecode(str) {
        const parser = new DOMParser();
        const decodedString = parser.parseFromString(str, 'text/html').body.textContent || '';
        return decodedString.trim();
    }

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

    function formatAbbreviatedAuthors(authorsArray) {
        if (!authorsArray || authorsArray.length === 0) {
            return '';
        }

        const formattedAuthors = authorsArray.map(author => {
            const given = author.given || '';
            const family = author.family || '';

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
})();
