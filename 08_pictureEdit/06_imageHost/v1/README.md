


```php
        function displayUploadInfo(response) {
            var uploadInfoDiv = document.getElementById('uploadInfo');
            uploadInfoDiv.innerHTML = 'File Size: ' + response.size + ' MB<br>';
            uploadInfoDiv.innerHTML += 'File Name: ' + response.fileName + '<br>';
            uploadInfoDiv.innerHTML += 'File Path: ' + response.filePath;
        }
```











