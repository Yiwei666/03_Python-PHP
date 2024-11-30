// ==UserScript==
// @name         Merge Citation Formats with Dynamic Journal Abbreviations
// @namespace    http://tampermonkey.net/
// @version      1.6
// @description  Extract and merge GB/T 7714 and APA citation formats into a new reference style with dynamic journal abbreviation fetching
// @author       Ayo
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest
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
    debugContainer.style.width = '50%';
    debugContainer.style.height = '30%';
    debugContainer.style.overflowY = 'scroll';
    debugContainer.style.backgroundColor = '#f4f4f4';
    debugContainer.style.color = '#333';
    debugContainer.style.padding = '10px';
    debugContainer.style.zIndex = '9998';
    debugContainer.style.border = '1px solid #ccc';
    debugContainer.style.fontFamily = 'monospace';
    debugContainer.style.whiteSpace = 'pre-wrap';
    debugContainer.style.boxSizing = 'border-box';
    debugContainer.id = 'debug-container';
    document.body.appendChild(debugContainer);

    const button = document.createElement('button');
    button.textContent = '生成新格式参考文献';
    button.style.position = 'fixed';
    button.style.bottom = '10px';
    button.style.right = '10px';
    button.style.zIndex = '9999';
    button.style.backgroundColor = '#4CAF50';
    button.style.color = 'white';
    button.style.border = 'none';
    button.style.padding = '10px';
    button.style.cursor = 'pointer';
    document.body.appendChild(button);

    function appendDebugInfo(label, content) {
        const debugLine = document.createElement('div');
        debugLine.innerHTML = `<strong>${label}:</strong> ${content}`;
        debugContainer.appendChild(debugLine);
    }

    function loadJournalAbbreviations() {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'GET',
                url: journalAbbreviationURL,
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
                        appendDebugInfo('期刊简称加载成功', JSON.stringify(abbreviations, null, 2));
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

    button.addEventListener('click', async function () {
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

            // 原有的脚本逻辑保持不变
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

            const result3 = `${string7}${string2}, ${string6} ${string4}`;
            appendDebugInfo('最终合并的新格式参考文献 (result3)', result3);
        } catch (e) {
            appendDebugInfo('脚本执行出错', e.message);
        }
    });
})();
