// ==UserScript==
// @name         Image Link Cleaner with Download Function
// @namespace    http://tampermonkey.net/
// @version      0.3
// @description  try to take over the web with new download features
// @author       You
// @match        *://*/*
// @grant        GM_download
// ==/UserScript==

(function() {
    'use strict';

    setTimeout(function() {
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

        button.addEventListener('click', function() {
            linksContainer.innerHTML = '';
            var images = document.querySelectorAll('img.css-9pa8cd');
            images.forEach(function(img) {
                var cleanedSrc = img.src.replace(/amp;/g, '');
                if (cleanedSrc.includes('https://pbs.twimg.com/media')) {
                    if (cleanedSrc.includes('format=jpg&name') && !displayedLinks.has(cleanedSrc)) {
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
                }
            });
        });

        function downloadImage(url) {
            var date = new Date();
            var filename = `${date.getFullYear()}${(date.getMonth()+1).toString().padStart(2, '0')}${date.getDate().toString().padStart(2, '0')}-${date.getHours().toString().padStart(2, '0')}${date.getMinutes().toString().padStart(2, '0')}${date.getSeconds().toString().padStart(2, '0')}.png`;

            // 使用 Tampermonkey 的 GM_download 功能来处理下载
            GM_download({
                url: url,
                name: filename,
                onerror: function(error) {
                    console.error('下载失败:', error);
                },
                onload: function() {
                    console.log('下载成功');
                }
            });
        }
    }, 3000); // Adjust the delay here based on the typical load time of your target pages
})();
