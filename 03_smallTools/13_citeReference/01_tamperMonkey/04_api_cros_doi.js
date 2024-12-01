// ==UserScript==
// @name         Merge Citation Formats with DOI Lookup
// @namespace    http://tampermonkey.net/
// @version      1.8
// @description  Extract and merge GB/T 7714 and APA citation formats into a new reference style with dynamic journal abbreviation fetching and DOI lookup
// @author       Ayo
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest
// @connect      api.crossref.org
// @connect      *
// ==/UserScript==

(function () {
    'use strict';

    const journalAbbreviationURL = 'http://39.105.186.182/06_journal_Abbreviation.txt';
    let journalAbbreviations = {}; // 动态加载的期刊简称字典

    // 在页面顶部添加调试信息容器
    const debugContainer = document.createElement('div');
    debugContainer.style.position = 'fixed';
    debugContainer.style.top = '0';
    debugContainer.style.right = '0';
    debugContainer.style.width = '40%';
    debugContainer.style.height = 'auto';
    debugContainer.style.maxHeight = '30%';
    debugContainer.style.overflowY = 'auto';
    debugContainer.style.backgroundColor = '#f9f9f9';
    debugContainer.style.color = '#333';
    debugContainer.style.padding = '10px';
    debugContainer.style.zIndex = '9998';
    debugContainer.style.border = '1px solid #ccc';
    debugContainer.style.fontFamily = 'Arial, sans-serif';
    debugContainer.style.fontSize = '12px';
    debugContainer.style.lineHeight = '1.5';
    debugContainer.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
    debugContainer.id = 'debug-container';
    document.body.appendChild(debugContainer);

    const generateButton = document.createElement('button');
    generateButton.textContent = '生成新格式参考文献';
    generateButton.style.position = 'fixed';
    generateButton.style.bottom = '50px';
    generateButton.style.right = '10px';
    generateButton.style.zIndex = '9999';
    generateButton.style.backgroundColor = '#4CAF50';
    generateButton.style.color = 'white';
    generateButton.style.border = 'none';
    generateButton.style.padding = '10px';
    generateButton.style.cursor = 'pointer';
    generateButton.style.borderRadius = '5px';
    generateButton.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
    document.body.appendChild(generateButton);

    const copyButton = document.createElement('button');
    copyButton.textContent = '复制参考文献';
    copyButton.style.position = 'fixed';
    copyButton.style.bottom = '10px';
    copyButton.style.right = '10px';
    copyButton.style.zIndex = '9999';
    copyButton.style.backgroundColor = '#2196F3';
    copyButton.style.color = 'white';
    copyButton.style.border = 'none';
    copyButton.style.padding = '10px';
    copyButton.style.cursor = 'pointer';
    copyButton.style.borderRadius = '5px';
    copyButton.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
    copyButton.style.display = 'none'; // 初始状态隐藏
    document.body.appendChild(copyButton);

    let result3 = ''; // 用于存储初始合并的参考文献

    function appendDebugInfo(label, content) {
        const debugLine = document.createElement('div');
        debugLine.innerHTML = `<strong>${label}:</strong> ${content}`;
        debugContainer.appendChild(debugLine);
    }

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
                                acc[parts[0].trim()] = parts[1].trim();
                            }
                            return acc;
                        }, {});
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

    generateButton.addEventListener('click', async function () {
        try {
            debugContainer.innerHTML = ''; // 清空调试信息
            if (Object.keys(journalAbbreviations).length === 0) {
                appendDebugInfo('信息', '正在加载期刊简称...');
                try {
                    journalAbbreviations = await loadJournalAbbreviations();
                } catch (error) {
                    appendDebugInfo('加载期刊简称失败', error.message);
                    return;
                }
            }

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
                appendDebugInfo('错误', '未找到目标内容，请检查页面是否正确加载');
                return;
            }

            const gbText = gbElement.textContent.trim();
            const apaText = apaElement.textContent.trim();

            appendDebugInfo('GB/T 7714 引用', gbText);
            appendDebugInfo('APA 引用', apaText);

            const firstDot = gbText.indexOf('.');
            const secondDot = gbText.indexOf('.', firstDot + 1);
            const string1 = gbText.substring(firstDot + 1, secondDot).trim();
            const string2 = string1.replace('[J]', '').trim();
            appendDebugInfo('文章标题 (string2)', string2);

            const lastPeriod = gbText.lastIndexOf('.');
            const firstLastComma = gbText.lastIndexOf(',', lastPeriod - 1);
            const secondLastComma = gbText.lastIndexOf(',', firstLastComma - 1);
            const string3 = gbText.substring(secondLastComma + 1, lastPeriod).trim();
            appendDebugInfo('卷、出版年和页码范围 (string3)', string3);

            const parts = string3.split(/[,:\(\)]+/).map((s) => s.trim());
            let s1, s2, s3 = "NULL", s4;
            if (string3.includes('(')) {
                [s1, s2, s3, s4] = parts;
            } else {
                [s1, s2, s4] = parts;
                s3 = "NULL";
            }
            const string4 = `${s2} (${s1}) ${s4}.`;
            appendDebugInfo('格式化的出版信息 (string4)', string4);

            const secondLastDot = gbText.lastIndexOf('.', lastPeriod - 1);
            const string5 = gbText.substring(secondLastDot + 1, secondLastComma).trim();
            appendDebugInfo('期刊全称 (string5)', string5);

            const matchedKey = Object.keys(journalAbbreviations).find(key => {
                return key.toLowerCase() === string5.toLowerCase();
            });
            const string6 = matchedKey ? journalAbbreviations[matchedKey] : string5;
            appendDebugInfo('期刊简称或全称 (string6)', string6);

            const apaAuthors = apaText.split('(')[0].trim();
            let authorParts = apaAuthors.split(',').map((s) => s.trim());
            authorParts = authorParts.map((part) => part.replace('&', '').trim());
            if (authorParts.length % 2 !== 0) {
                appendDebugInfo('警告', '作者部分格式异常，长度不是偶数，将最后一项处理为单独的作者');
                authorParts.push('Unknown');
            }
            appendDebugInfo('APA 作者部分 (authorParts)', authorParts);

            let reorderedAuthors = [];
            for (let i = 0; i < authorParts.length; i += 2) {
                if (authorParts[i + 1]) {
                    reorderedAuthors.push(`${authorParts[i + 1]} ${authorParts[i]}`);
                } else {
                    reorderedAuthors.push(authorParts[i]);
                }
            }
            const string7 = reorderedAuthors.join(', ') + ', ';
            appendDebugInfo('重排后的作者名 (string7)', string7);

            result3 = `${string7}${string2}, ${string6} ${string4}`;
            appendDebugInfo('最终合并的新格式参考文献 (result3)', result3);

            // 显示复制按钮
            copyButton.style.display = 'block';

            // 设置初始复制内容为 result3
            copyButton.dataset.resultText = result3;

            // 现在查询 DOI
            appendDebugInfo('信息', '正在查询 DOI...');
            queryDOI(gbText, string2);

        } catch (e) {
            appendDebugInfo('脚本执行出错', e.message);
        }
    });

    copyButton.addEventListener('click', function () {
        const textToCopy = copyButton.dataset.resultText || result3;
        if (textToCopy) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                alert('参考文献已复制到剪贴板！');
            }).catch(err => {
                alert('复制失败，请手动复制。');
            });
        } else {
            alert('没有可复制的内容，请先生成参考文献。');
        }
    });

    // 查询 CrossRef API 并处理结果
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
                        const doi = firstResult.DOI || '';
                        const apiTitle = firstResult.title ? firstResult.title.join(' ') : '';

                        // 比较标题
                        const isMatch = compareTitles(apiTitle, extractedTitle);

                        if (isMatch && doi) {
                            appendDebugInfo('DOI 查询结果', `找到匹配的 DOI: ${doi}`);

                            // 创建 doiLink
                            const doiLink = `https://doi.org/${doi}.`;

                            // 去掉 result3 末尾的句号
                            const trimmedResult3 = result3.endsWith('.') ? result3.slice(0, -1) : result3;

                            // 拼接 result3 和 doiLink
                            const result4 = `${trimmedResult3}, ${doiLink}`;
                            appendDebugInfo('最终合成的新格式参考文献 (result4)', result4);

                            // 更新复制按钮的内容为 result4
                            copyButton.dataset.resultText = result4;
                        } else {
                            appendDebugInfo('DOI 查询结果', '标题不匹配，未能获取准确的 DOI。');
                            // 复制按钮内容保持为 result3
                            copyButton.dataset.resultText = result3;
                        }
                    } else {
                        appendDebugInfo('DOI 查询结果', '未找到 DOI。');
                        // 复制按钮内容保持为 result3
                        copyButton.dataset.resultText = result3;
                    }
                } catch (e) {
                    appendDebugInfo('DOI 查询错误', '解析 API 响应时出错。');
                    // 复制按钮内容保持为 result3
                    copyButton.dataset.resultText = result3;
                }
            },
            onerror: () => {
                appendDebugInfo('DOI 查询错误', '查询 DOI 时出错。');
                // 复制按钮内容保持为 result3
                copyButton.dataset.resultText = result3;
            }
        });
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
                dp[i][j] = Math.min(
                    dp[i - 1][j] + 1,      // 删除
                    dp[i][j - 1] + 1,      // 插入
                    dp[i - 1][j - 1] + cost // 替换
                );
            }
        }
        const distance = dp[len1][len2];
        return 1 - distance / Math.max(len1, len2); // 相似度计算
    }

})();
