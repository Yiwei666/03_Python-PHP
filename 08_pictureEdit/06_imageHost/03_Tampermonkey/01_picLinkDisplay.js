// ==UserScript==
// @name         Image Link Cleaner
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  try to take over the web
// @author       You
// @match        *://*/*
// @grant        none
// ==/UserScript==

(function() {
    'use strict';

    // 确保在文档加载完毕后执行
    window.addEventListener('load', function() {
        // 添加按钮到页面
        var button = document.createElement('button');
        button.textContent = '获取图片链接';
        button.style.position = 'fixed';
        button.style.top = '10px';
        button.style.left = '10px';
        button.style.backgroundColor = 'blue'; // 设置按钮颜色为蓝色
        button.style.color = 'white'; // 设置按钮文字颜色为白色
        button.style.padding = '10px 20px'; // 设置按钮内边距
        button.style.border = 'none'; // 无边框
        button.style.borderRadius = '5px'; // 圆角按钮
        button.style.cursor = 'pointer'; // 鼠标悬停时为指针形状
        document.body.appendChild(button);

        // 添加显示链接的容器
        var linksContainer = document.createElement('div');
        linksContainer.style.position = 'fixed';
        linksContainer.style.top = '50px';
        linksContainer.style.left = '10px';
        linksContainer.style.backgroundColor = 'white';
        linksContainer.style.padding = '10px';
        linksContainer.style.border = '1px solid #ccc'; // 添加边框
        linksContainer.style.maxHeight = '300px'; // 最大高度
        linksContainer.style.overflow = 'auto'; // 内容超出时自动滚动
        document.body.appendChild(linksContainer);

        // 创建集合以存储已显示的链接
        var displayedLinks = new Set();

        button.addEventListener('click', function() {
            // 清空容器
            linksContainer.innerHTML = '';

            // 查找所有指定类名的图片
            var images = document.querySelectorAll('img.css-9pa8cd');
            images.forEach(function(img) {
                var cleanedSrc = img.src.replace(/amp;/g, '');
                // 检查链接是否包含指定字符串并且未被添加过
                if (cleanedSrc.includes('format=jpg&name') && !displayedLinks.has(cleanedSrc)) {
                    displayedLinks.add(cleanedSrc); // 添加到集合中，标记为已显示
                    var p = document.createElement('p');
                    p.textContent = cleanedSrc;
                    linksContainer.appendChild(p);
                }
            });
        });
    });
})();
