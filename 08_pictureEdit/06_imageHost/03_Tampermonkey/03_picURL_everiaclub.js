// ==UserScript==
// @name         everiaclub
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  Get and display image links from a specific container
// @author       You
// @match        *://*/*
// @grant        none
// ==/UserScript==

(function() {
    'use strict';

    setTimeout(function() {
        // 创建按钮
        var button = document.createElement('button');
        button.textContent = '获取图片链接';
        button.style.position = 'fixed';
        button.style.top = '10px';
        button.style.left = '10px';
        button.style.backgroundColor = 'blue';
        button.style.color = 'white';
        button.style.padding = '10px 20px';
        button.style.border = 'none';
        button.style.borderRadius = '5px';
        button.style.cursor = 'pointer';
        document.body.appendChild(button);

        // 创建链接容器
        var linksContainer = document.createElement('div');
        linksContainer.style.position = 'fixed';
        linksContainer.style.top = '50px';
        linksContainer.style.left = '10px';
        linksContainer.style.backgroundColor = 'white';
        linksContainer.style.padding = '10px';
        linksContainer.style.border = '1px solid #ccc';
        linksContainer.style.maxHeight = '300px';
        linksContainer.style.overflow = 'auto';
        document.body.appendChild(linksContainer);

        var displayedLinks = new Set();

        // 按钮点击事件
        button.addEventListener('click', function() {
            linksContainer.innerHTML = '';
            var containers = document.querySelectorAll('body .mainleft');
            containers.forEach(function(container) {
                var images = container.querySelectorAll('img');
                images.forEach(function(img) {
                    var cleanedSrc = img.src.replace(/amp;/g, '');
                    if (!displayedLinks.has(cleanedSrc)) {
                        displayedLinks.add(cleanedSrc);
                        var p = document.createElement('p');
                        var linkText = document.createTextNode(cleanedSrc);
                        p.appendChild(linkText);
                        p.style.fontSize = '12px';
                        linksContainer.appendChild(p);
                    }
                });
            });
        });
    }, 3000); // 调整延迟时间以适应目标页面的典型加载时间
})();
