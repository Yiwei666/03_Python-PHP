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
            width: auto; /* 初始宽度为自适应 */
            height: auto; /* 初始高度为自适应 */
            min-width: 500px; /* 最小宽度，根据需要调整 */
            min-height: 80px; /* 最小高度，根据需要调整 */
            max-width: 300px; /* 最大宽度为300px，你可以根据需要调整 */
            max-height: 300px; /* 最大高度为300px，你可以根据需要调整 */
            overflow: hidden;
        }

        #uploadButton {
            margin-top: 10px;
            cursor: pointer;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            margin: 100px 0 0 0; /* 顶部距离为20px，调整其他方向的边距为0 */
        }

        #container {
            text-align: left;
            width: 100%; /* 设置容器宽度，可以根据需要调整 */
            margin-top: 1px; /* 调整容器上边距 */
            display: flex;
            flex-direction: column;
            align-items: center; /* 调整竖直方向上的对齐方式 */
        }

    </style>
</head>
<body>
    <div id="container">
        <p>Paste your image here:</p>
        <div contenteditable="true" id="imageContainer"></div>
        <button id="uploadButton">Upload Image</button>
        <div id="uploadInfo"></div>
    </div>

    <script>
        document.getElementById('imageContainer').addEventListener('paste', function (event) {
            event.preventDefault();

            var items = (event.clipboardData || event.originalEvent.clipboardData).items;

            for (var index in items) {
                var item = items[index];

                if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
                    var blob = item.getAsFile();
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        var img = new Image();
                        img.src = e.target.result;
                        document.getElementById('imageContainer').innerHTML = '';
                        document.getElementById('imageContainer').appendChild(img);
                    };

                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });

        document.getElementById('uploadButton').addEventListener('click', function () {
            var imageContainer = document.getElementById('imageContainer');
            var image = imageContainer.querySelector('img');

            if (image) {
                var canvas = document.createElement('canvas');
                canvas.width = image.width;
                canvas.height = image.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(image, 0, 0, image.width, image.height);

                var dataURL = canvas.toDataURL('image/png');
                var blob = dataURItoBlob(dataURL);

                var formData = new FormData();
                formData.append('image', blob, 'image.png');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/03_serverImageHost.php', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        displayUploadInfo(response);
                        imageContainer.innerHTML = '';
                    } else {
                        console.error('Image upload failed.');
                    }
                };

                xhr.send(formData);
            }
        });

        function displayUploadInfo(response) {
            var uploadInfoDiv = document.getElementById('uploadInfo');
            uploadInfoDiv.innerHTML = 'File Size (MB): ' + response.sizeMB + ' MB<br>';
            uploadInfoDiv.innerHTML += 'File Size (KB): ' + response.sizeKB + ' KB<br>';
            uploadInfoDiv.innerHTML += 'File Name: ' + response.fileName + '<br>';
            uploadInfoDiv.innerHTML += 'File Path: ' + response.filePath + '<br>';
            
            // Make Adjusted Path a clickable link
            var adjustedPathLink = document.createElement('a');
            adjustedPathLink.href = response.adjustedPath;
            adjustedPathLink.textContent = 'Adjusted Path: ' + response.adjustedPath;
            adjustedPathLink.target = '_blank'; // Open in a new tab/window
            uploadInfoDiv.appendChild(document.createElement('br'));
            uploadInfoDiv.appendChild(adjustedPathLink);



            // 新增显示内容：显示代码块，添加复制代码块按钮
            var imageContainer = document.createElement('div');
            var imageCode = `<p align="center">
                              <img src="${response.adjustedPath}" alt="Image Description" width="700">
                             </p>`;
            imageContainer.innerHTML = imageCode;

            // 添加样式
            imageContainer.style.backgroundColor = 'black';
            imageContainer.style.color = 'white';
            imageContainer.style.marginTop = '20px'; // 设置距离顶部的距离为20px
            imageContainer.style.padding = '15px'; // 设置内边距为15px

            // 使用innerText而不是innerHTML
            imageContainer.innerText = imageCode;

            uploadInfoDiv.appendChild(document.createElement('br'));
            uploadInfoDiv.appendChild(imageContainer);

            // 添加复制按钮
            var copyButton = document.createElement('button');
            copyButton.innerText = '复制代码';
            copyButton.onclick = function() {
                // 创建一个文本域，并将代码块文本放入其中
                var textarea = document.createElement('textarea');
                textarea.value = imageCode.replace(/^[ \t]+/gm, ''); // 使用正则表达式去除行首空格

                // 将文本域添加到页面中
                document.body.appendChild(textarea);

                // 选中文本域中的内容
                textarea.select();
                
                // 执行复制命令
                document.execCommand('copy');

                // 移除文本域
                document.body.removeChild(textarea);

                alert('代码已复制到剪贴板！');
            };

            // 设置复制按钮的样式
            copyButton.style.marginTop = '15px'; // 设置距离顶部的距离为10px

            // 将复制按钮添加到uploadInfoDiv中
            uploadInfoDiv.appendChild(copyButton);



            // 新增代码段：显示图床中图片预览图
            var resultImageContainer = document.createElement('div');
            resultImageContainer.style.textAlign = 'center'; // 设置水平居中
            resultImageContainer.style.marginTop = '20px'; // 设置距离顶部的距离为20px
            resultImageContainer.style.backgroundColor = '#eee'; // 设置背景色为灰色
            resultImageContainer.style.padding = '10px'; // 设置内边距为10px

            var resultImage = new Image();
            resultImage.src = response.adjustedPath;
            resultImage.width = 300; // 设置图片宽度为500px
            resultImage.alt = 'Result Image';
            resultImageContainer.appendChild(resultImage);

            // 添加到uploadInfoDiv中
            uploadInfoDiv.appendChild(resultImageContainer);

        }


        function dataURItoBlob(dataURI) {
            var byteString = atob(dataURI.split(',')[1]);
            var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);

            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }

            return new Blob([ab], { type: mimeString });
        }
    </script>
</body>
</html>
