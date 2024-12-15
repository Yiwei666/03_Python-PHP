// ==UserScript==
// @name         X
// @namespace    http://tampermonkey.net/
// @version      0.4
// @description  try to take over the web with new download features
// @author       You
// @match        https://x.com/*
// @grant        none
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

        function generateFilename(date) {
            // 获取当前页面的 URL
            var currentUrl = window.location.href;
            var parts = currentUrl.split('/');

            // 检查斜杠数量是否>=4（即 parts.length >= 5）
            // parts数组下标：0:"https:",1:"",2:"x.com",3:"可能的账号字符串",...
            // 若满足条件则账号字符串= parts[3]；否则为空
            var accountString = '';
            if (parts.length >= 5) {
                accountString = parts[3];
            }

            var randomString = Math.random().toString(36).substr(2, 6);
            var year = date.getFullYear();
            var month = (date.getMonth() + 1).toString().padStart(2, '0');
            var day = date.getDate().toString().padStart(2, '0');
            var hours = date.getHours().toString().padStart(2, '0');
            var minutes = date.getMinutes().toString().padStart(2, '0');
            var seconds = date.getSeconds().toString().padStart(2, '0');

            // 如果有账号字符串，则在 时分秒 和 随机字符串之间加入 账号字符串-
            if (accountString) {
                return `${year}${month}${day}-${hours}${minutes}${seconds}-${accountString}-${randomString}.png`;
            } else {
                return `${year}${month}${day}-${hours}${minutes}${seconds}-${randomString}.png`;
            }
        }
    }, 3000); // Adjust the delay here based on the typical load time of your target pages
})();
