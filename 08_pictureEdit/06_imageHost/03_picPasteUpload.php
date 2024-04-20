<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clipboard Image Uploader</title>
    <style>

        #imageContainer {
            border: 1px solid #ccc;
            padding: 10px;
            width: auto;
            height: auto;
            min-width: 500px;
            min-height: 80px;
            max-width: 500px;
            max-height: 300px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #242729; /* 设置为与页面背景色一致的深灰色 */
            border-radius: 5px; /* 添加倒角 */
            
        }

        #uploadButton {
            margin-top: 10px;
            cursor: pointer;
            padding: 5px 10px;
            background-color: #008080;
            color: white;
            border: none;
            border-radius: 5px;
            width: auto; /* 设置按钮宽度自适应内容 */
            max-width: 200px; /* 最大宽度限制 */
            margin-left: auto; /* 水平居中 */
            margin-right: auto; /* 水平居中 */
        }

        body {
            display: flex;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #242729;
            color: #ffffff;
            padding-top: 20px; /* 设置顶部间距 */
        }

        #container {
            text-align: center;
            width: 550px;
            padding: 20px;
            box-sizing: border-box;
        }

        #uploadInfo {
            text-align: left;
            color: #ffffff;
            margin-top: 10px;
        }

    </style>
</head>
<body>
    <div id="container">
        <p>Paste your image here:</p>
        <div contenteditable="true" id="imageContainer" style="text-align: left; justify-content: flex-start;"></div> <!-- 调整光标位置和文本对齐 -->
        <button id="uploadButton">Upload Image</button>
        <div id="uploadInfo"></div>
    </div>

    <script>
        document.getElementById('imageContainer').addEventListener('paste', function (event) {
            event.preventDefault();
            var items = (event.clipboardData || window.clipboardData).items;
            for (var index in items) {
                var item = items[index];
                if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
                    var blob = item.getAsFile();
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        var img = new Image();
                        img.src = event.target.result;
                        document.getElementById('imageContainer').innerHTML = '';
                        document.getElementById('imageContainer').appendChild(img);
                    };
                    reader.readAsDataURL(blob);
                }
            }
        });

        document.getElementById('uploadButton').addEventListener('click', function () {
            var img = document.querySelector('#imageContainer img');
            if (img) {
                var canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                canvas.toBlob(function(blob) {
                    var formData = new FormData();
                    formData.append('image', blob, 'image.png');

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/upload', true);
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            document.getElementById('uploadInfo').innerHTML = 'File Size (MB): ' + response.sizeMB + ' MB<br>' +
                                'File Size (KB): ' + response.sizeKB + ' KB<br>' +
                                'File Name: ' + response.fileName + '<br>' +
                                'File Path: ' + response.filePath + '<br>' +
                                '<a href="' + response.adjustedPath + '" target="_blank">Adjusted Path</a>';
                            document.getElementById('imageContainer').innerHTML = ''; // Clear the image container
                        } else {
                            document.getElementById('uploadInfo').textContent = 'Failed to upload image.';
                        }
                    };
                    xhr.send(formData);
                }, 'image/png');
            }
        });
    </script>
</body>
</html>
