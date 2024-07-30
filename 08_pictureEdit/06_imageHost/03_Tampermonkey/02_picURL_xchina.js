// ==UserScript==
// @name         Image Link Cleaner with Download Function for Specific Container
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  Get and download image links from a specific container
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
            var containers = document.querySelectorAll('div.container');
            containers.forEach(function(container) {
                var images = container.querySelectorAll('img');
                images.forEach(function(img) {
                    var cleanedSrc = img.src.replace(/amp;/g, '');
                    if (!displayedLinks.has(cleanedSrc)) {
                        displayedLinks.add(cleanedSrc);
                        var p = document.createElement('p');
                        var linkText = document.createTextNode(cleanedSrc);
                        var downloadButton = document.createElement('button');

                        downloadButton.textContent = '下载';
                        downloadButton.style.marginLeft = '10px';
                        downloadButton.style.fontSize = '12px';
                        downloadButton.style.padding = '2px 5px';
                        downloadButton.style.cursor = 'pointer';
                        downloadButton.onclick = function() {
                            downloadImage(cleanedSrc);
                        };

                        p.appendChild(linkText);
                        p.appendChild(downloadButton);
                        p.style.fontSize = '12px';
                        linksContainer.appendChild(p);
                    }
                });
            });
        });

        // 下载图片函数
        function downloadImage(url) {
            var img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                var canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                canvas.toBlob(function(blob) {
                    var date = new Date();
                    var filename = generateFilename(date);
                    var link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }, 'image/png');
            };
            img.onerror = function() {
                console.error('图片加载失败:', url);
            };
            img.src = url;
        }

        // 生成文件名函数
        function generateFilename(date) {
            var randomString = Math.random().toString(36).substr(2, 6);
            return `${date.getFullYear()}${(date.getMonth() + 1).toString().padStart(2, '0')}${date.getDate().toString().padStart(2, '0')}-${date.getHours().toString().padStart(2, '0')}${date.getMinutes().toString().padStart(2, '0')}${date.getSeconds().toString().padStart(2, '0')}-${randomString}.png`;
        }
    }, 3000); // 调整延迟时间以适应目标页面的典型加载时间
})();
