// ==UserScript==
// @name         Merge Citation Formats with Precise Extraction
// @namespace    http://tampermonkey.net/
// @version      1.4
// @description  Extract and merge GB/T 7714 and APA citation formats into a new reference style with debugging and precise extraction
// @author       Ayo
// @match        https://scholar.google.com.hk/*
// @grant        none
// ==/UserScript==

(function () {
    'use strict';

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

    // 在页面添加按钮
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

    // 添加调试信息的函数
    function appendDebugInfo(label, content) {
        const debugLine = document.createElement('div');
        debugLine.innerHTML = `<strong>${label}:</strong> ${content}`;
        debugContainer.appendChild(debugLine);
    }

    button.addEventListener('click', function () {
        try {
            // 清空调试信息容器
            debugContainer.innerHTML = '';

            // 提取 GB/T 7714 和 APA 格式引用
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

            // 提取文章标题（更精确地定位第一个和第二个 "."）
            const firstDot = gbText.indexOf('.');
            const secondDot = gbText.indexOf('.', firstDot + 1);
            const string1 = gbText.substring(firstDot + 1, secondDot).trim();
            const string2 = string1.replace('[J]', '').trim(); // 去掉 [J]
            appendDebugInfo('文章标题 (string2)', string2);

            // 改进卷、出版年和页码范围提取逻辑
            const lastPeriod = gbText.lastIndexOf('.');
            const firstLastComma = gbText.lastIndexOf(',', lastPeriod - 1);
            const secondLastComma = gbText.lastIndexOf(',', firstLastComma - 1);
            const string3 = gbText.substring(secondLastComma + 1, lastPeriod).trim();
            appendDebugInfo('卷、出版年和页码范围 (string3)', string3);


            // 解析卷、出版年和页码范围
            const parts = string3.split(/[,:\(\)]+/).map((s) => s.trim());
            let s1, s2, s3 = "NULL", s4;
            // 判断 string3 是否包含括号
            if (string3.includes('(')) {
                // 包含括号的情况
                [s1, s2, s3, s4] = parts;
            } else {
                // 不包含括号的情况
                [s1, s2, s4] = parts;
                s3 = "NULL"; // 设置默认值
            }
            // 格式化输出
            const string4 = `${s2} (${s1}) ${s4}.`;
            appendDebugInfo('格式化的出版信息 (string4)', string4);


            // 提取期刊全称（更精确的倒数第二个 "." 和 "," 之间的部分）
            const secondLastDot = gbText.lastIndexOf('.', lastPeriod - 1);
            const string5 = gbText.substring(secondLastDot + 1, secondLastComma).trim();
            appendDebugInfo('期刊全称 (string5)', string5);



            // 硬编码期刊映射表
            const journalAbbreviations = {
                "ACS Sustainable Chemistry & Engineering": "ACS Sustain. Chem. Eng.",
                "Angewandte Chemie International Edition in English": "Angew. Chem. Int. Ed.",
                "Chemical Reviews": "Chem. Rev.",
                "Chemical Geology": "Chem. Geol.",
                "Chemical Engineering Science": "Chem. Eng. Sci.",
                "Chemical Engineering Journal": "Chem. Eng. J.",
                "Computer Physics Communications": "Comput. Phys. Commun.",
                "Chemical Physics Letters": "Chem. Phys. Lett.",
                "Energy & Environmental Science": "Energy Environ. Sci.",
                "Earth and Planetary Science Letters": "Earth Planet. Sci. Lett.",
                "Geophysical research letters": "Geophys. Res. Lett.",
                "International Journal of Minerals, Metallurgy and Materials": "Int. J. Miner. Metall. Mater.",
                "Journal of Cleaner Production": "J. Clean. Prod.",
                "Journal of Molecular Liquids": "J. Mol. Liq.",
                "Journal of Hazardous Materials": "J. Hazard. Mater.",
                "Journal of Alloys and Compounds": "J. Alloys Compd.",
                "Journal of Non-crystalline Solids": "J. Non Cryst. Solids",
                "Journal of the American Chemical Society": "J. Am. Chem. Soc.",
                "Journal of applied crystallography": "J. Appl. Crystallogr.",
                "Journal of computational chemistry": "J. Comput. Chem.",
                "JOM": "JOM",
                "Metallurgical and Materials Transactions B": "Metall. Mater. Trans. B",
                "Molecular Physics": "Mol. Phys.",
                "Modelling and simulation in materials science and engineering": "Model. Simul. Mat. Sci. Eng.",
                "Nature": "Nature",
                "Nature Communications": "Nat. Commun.",
                "Nature Reviews Materials": "Nat. Rev. Mater.",
                "Nature materials": "Nat. Mater.",
                "Proceedings of the National Academy of Sciences": "Proc. Natl. Acad. Sci. U.S.A.",
                "Physical review letters": "Phys. Rev. Lett.",
                "Physical Review B": "Phys. Rev. B",
                "Physics and Chemistry of Minerals": "Phys. Chem. Miner.",
                "Resources, Conservation and Recycling": "Resour. Conserv. Recycl.",
                "Science": "Science",
                "Science Advances": "Sci. Adv.",
                "Solar energy materials and solar cells": "Sol. Energy Mater Sol. Cells",
                "Science and Technology of Advanced Materials": "Sci. Technol. Adv. Mater.",
                "Theoretical Chemistry Accounts": "Theor. Chem. Acc.",
                "The Journal of chemical physics": "J. Chem. Phys.",
                "The Journal of Physical Chemistry C": "J. Phys. Chem. C",
                "Wiley Interdisciplinary Reviews: Computational Molecular Science": "Wiley Interdiscip. Rev. Comput. Mol. Sci."
            };

            // 忽略大小写匹配
            const matchedKey = Object.keys(journalAbbreviations).find(key => {
                return key.toLowerCase() === string5.toLowerCase();
            });
            const string6 = matchedKey ? journalAbbreviations[matchedKey] : string5;
            appendDebugInfo('期刊简称或全称 (string6)', string6);



            // 提取 APA 作者名
            const apaAuthors = apaText.split('(')[0].trim(); // 提取括号前的内容
            let authorParts = apaAuthors.split(',').map((s) => s.trim()); // 按逗号分割并去除空格

            // 移除 "&" 并调整格式
            authorParts = authorParts.map((part) => part.replace('&', '').trim());

            // 检查长度是否为偶数，如果不是，则添加默认值或报错
            if (authorParts.length % 2 !== 0) {
                appendDebugInfo('警告', '作者部分格式异常，长度不是偶数，将最后一项处理为单独的作者');
                authorParts.push('Unknown'); // 添加默认名值以确保偶数
            }

            appendDebugInfo('APA 作者部分 (authorParts)', authorParts);

            // 重排作者名
            let reorderedAuthors = [];
            for (let i = 0; i < authorParts.length; i += 2) {
                if (authorParts[i + 1]) {
                    reorderedAuthors.push(`${authorParts[i + 1]} ${authorParts[i]}`); // 名 姓
                } else {
                    reorderedAuthors.push(authorParts[i]); // 仅有姓时保留原样
                }
            }

            // 合并为最终字符串并添加连接符
            const string7 = reorderedAuthors.join(', ') + ', ';
            appendDebugInfo('重排后的作者名 (string7)', string7);



            // 拼接最终结果
            const result3 = `${string7}${string2}, ${string6} ${string4}`;
            appendDebugInfo('最终合并的新格式参考文献 (result3)', result3);
        } catch (e) {
            appendDebugInfo('脚本执行出错', e.message);
        }
    });
})();
