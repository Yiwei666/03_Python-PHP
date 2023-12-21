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
            max-width: 300px; /* 设置最大宽度 */
            max-height: 300px; /* 设置最大高度 */
            overflow: hidden; /* 超出尺寸时隐藏 */
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
    </style>
</head>
<body>
    <div>
        <p>Paste your image here:</p>
        <div contenteditable="true" id="imageContainer"></div>
        <button id="uploadButton">Upload Image</button>
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
                        document.getElementById('imageContainer').innerHTML = ''; // 清空容器
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

                // 将图像保存为 PNG 文件
                var dataURL = canvas.toDataURL('image/png');
                var blob = dataURItoBlob(dataURL);

                // 创建 FormData 对象并添加图像文件
                var formData = new FormData();
                formData.append('image', blob, 'image.png');

                // 发送图像到服务器
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/03_serverImageHost.php', true); // 请替换为你的服务器上传脚本
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        console.log('Image uploaded successfully.');
                    } else {
                        console.error('Image upload failed.');
                    }
                };

                xhr.send(formData);
            }
        });

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
