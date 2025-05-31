// ==UserScript==
// @name         X-format
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

        /**
         * downloadImage：使用 fetch 拉取远程图片为 Blob，
         * 然后再通过 URL.createObjectURL 生成本地下载链接，
         * 最终再动态点击 <a download> 来下载。
         */
        function downloadImage(url) {
            // 1. 先拼接出一个带 ".png" 的 base 文件名
            var date = new Date();
            var baseFilename = generateFilename(date);

            // 2. 提取 URL 中的 format 字段：如 format=jpg、format=png
            var extMatch = url.match(/format=([^&]+)/);
            var ext = extMatch ? extMatch[1] : 'png';
            // 把 baseFilename 末尾的 .png 换成真正要的后缀
            var filename = baseFilename.replace(/\.png$/, '.' + ext);

            // 3. 使用 fetch 把远程资源拉取为二进制 Blob
            fetch(url, { mode: 'cors' })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.blob();
                })
                .then(function(blob) {
                    // 4. 把 Blob 转为本地 URL
                    var blobUrl = URL.createObjectURL(blob);
                    // 5. 创建一个 <a>，让浏览器把这个 Blob 以 download 方式保存
                    var link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    // 6. 释放掉这个临时 Blob URL
                    setTimeout(function() {
                        URL.revokeObjectURL(blobUrl);
                    }, 1000);
                })
                .catch(function(err) {
                    console.error('下载失败:', err);
                });
        }

        function generateFilename(date) {
            // 获取当前页面的 URL
            var currentUrl = window.location.href;
            var parts = currentUrl.split('/');

            // parts 数组下标：0:"https:",1:"",2:"x.com",3:"可能的账号字符串",...
            // 如果 parts.length >= 5，就把 parts[3] 当作账号字符串，否则为空
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

            // 如果存在账号字符串，则在时分秒与随机串之间加上账号名
            if (accountString) {
                return `${year}${month}${day}-${hours}${minutes}${seconds}-${accountString}-${randomString}.png`;
            } else {
                return `${year}${month}${day}-${hours}${minutes}${seconds}-${randomString}.png`;
            }
        }
    }, 3000); // 根据页面加载时间适当调整
})();
