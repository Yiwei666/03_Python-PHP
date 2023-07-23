<!DOCTYPE html>
<html>
<head>
    <title>Upload_MP4_VIEDEOS</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-bottom: 20px;
        }

        #progressContainer {
            width: 400px;
            height: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        #progressBar {
            width: 0;
            height: 100%;
            background-color: #4CAF50;
            border-radius: 5px 0 0 5px;
            transition: width 0.2s ease;
        }

        #progressText {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #333;
            font-size: 14px;
        }

        /* Add margin to the upload button */
        button {
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Optional: Add margin to the file input button */
        input[type="file"] {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h3>Upload MP4 VIEDEOS</h3>
    <input type="file" name="videos[]" id="videos" multiple accept=".mp4"><br><br>
    <button onclick="uploadVideos()">上传</button>

    <div id="progressContainer" style="margin-top: 10px;">
        <div id="progressBar"></div>
        <div id="progressText">0%</div>
    </div>

    <script>
        function uploadVideos() {
            var fileInput = document.getElementById('videos');
            var files = fileInput.files;
            var formData = new FormData();

            for (var i = 0; i < files.length; i++) {
                formData.append('videos[]', files[i]);
            }

            var progressBar = document.getElementById('progressBar');
            var progressText = document.getElementById('progressText');
            progressBar.style.width = '0%';

            $('#progressContainer').show();

            $.ajax({
                url: '06_upload.php',
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            progressBar.style.width = percentComplete.toFixed(2) + '%';
                            
                            // Determine the appropriate unit for upload speed
                            var uploadSpeed = (evt.loaded / 1024) / (evt.timeStamp / 1000);
                            var unit = 'KB/s';
                            if (uploadSpeed > 1024) {
                                uploadSpeed /= 1024;
                                unit = 'MB/s';
                            }
                            
                            progressText.textContent = percentComplete.toFixed(2) + '%';
                            progressText.textContent += ' (' + uploadSpeed.toFixed(2) + ' ' + unit + ')';
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#progressContainer').hide();
                    alert(response);
                }
            });
        }
    </script>
</body>
</html>

