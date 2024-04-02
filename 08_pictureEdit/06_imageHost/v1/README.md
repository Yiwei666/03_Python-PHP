# 1. 项目功能

搭建图床，将剪贴板中的截图上传至云服务器，返回该截图图床链接

v1版本仅能返回文件大小（MB），文件名，以及文件的绝对路径

# 2. 文件结构

```
03_picPasteUpload.php             # 主脚本，获取剪贴板中的图像数据，点击`upload image`上传至云服务器，并返回图床链接和图片大小，需要指定服务器端处理图像的脚本

03_serverImageHost.php             # 服务器端处理图像的脚本
```


# 3. 环境配置


- web端主脚本 03_picPasteUpload.php

```php
function displayUploadInfo(response) {
    var uploadInfoDiv = document.getElementById('uploadInfo');
    uploadInfoDiv.innerHTML = 'File Size: ' + response.size + ' MB<br>';
    uploadInfoDiv.innerHTML += 'File Name: ' + response.fileName + '<br>';
    uploadInfoDiv.innerHTML += 'File Path: ' + response.filePath;
}
```


- 服务器端图像处理脚本 03_serverImageHost.php

```php
<?php
$uploadDirectory = '/home/01_html/02_LAS1109/35_imageHost/';

$uploadedFile = $_FILES['image'];

if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
    die('Upload failed with error code ' . $uploadedFile['error']);
}

$timestamp = date('Ymd-His');
$targetFileName = $timestamp . '.png';
$targetFilePath = $uploadDirectory . $targetFileName;

if (move_uploaded_file($uploadedFile['tmp_name'], $targetFilePath)) {
    $fileSizeMB = round(filesize($targetFilePath) / (1024 * 1024), 2);

    $response = [
        'size' => $fileSizeMB,
        'fileName' => $targetFileName,
        'filePath' => $targetFilePath
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    echo 'Upload failed.';
}
?>
```








