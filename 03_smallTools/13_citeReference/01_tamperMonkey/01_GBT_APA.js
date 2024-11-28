// ==UserScript==
// @name         Extract Citation Data
// @namespace    http://tampermonkey.net/
// @version      1.1
// @description  Extract citation strings from GB/T 7714 and APA rows on Google Scholar
// @author       Ayo
// @match        https://scholar.google.com.hk/*
// @grant        none
// ==/UserScript==

(function () {
    'use strict';

    // 等待页面加载完成
    window.addEventListener('load', () => {
        // 添加按钮到页面
        const button = document.createElement('button');
        button.textContent = '提取内容';
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

        // 按钮点击事件
        button.addEventListener('click', function () {
            try {
                // 提取 GB/T 7714 后的字符串
                const gbElement = document.evaluate(
                    "//th[text()='GB/T 7714']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;
                const gbText = gbElement ? gbElement.textContent.trim() : null;

                // 提取 APA 后的字符串
                const apaElement = document.evaluate(
                    "//th[text()='APA']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;
                const apaText = apaElement ? apaElement.textContent.trim() : null;

                // 检查是否成功提取
                if (!gbText || !apaText) {
                    alert('未找到目标内容，请检查页面是否正确加载');
                    return;
                }

                // 显示提取结果
                alert("GB/T 7714:\n" + gbText + "\n\nAPA:\n" + apaText);
                console.log("GB/T 7714:", gbText);
                console.log("APA:", apaText);
            } catch (e) {
                console.error("提取时出错:", e);
                alert('提取过程中发生错误，请检查脚本或页面内容');
            }
        });
    });
})();
