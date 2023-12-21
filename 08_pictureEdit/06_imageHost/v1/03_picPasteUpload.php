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
            max-width: 300px;
            max-height: 300px;
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
    </style>
</head>
<body>
    <div>
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
            uploadInfoDiv.innerHTML = 'File Size: ' + response.size + ' MB<br>';
            uploadInfoDiv.innerHTML += 'File Name: ' + response.fileName + '<br>';
            uploadInfoDiv.innerHTML += 'File Path: ' + response.filePath;
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
