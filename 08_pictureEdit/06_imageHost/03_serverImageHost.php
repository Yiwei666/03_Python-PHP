<?php
// 设置上传目录
$uploadDirectory = '/home/01_html/02_LAS1109/35_imageHost/';

// 获取上传的文件
$uploadedFile = $_FILES['image'];

// 检查文件是否上传成功
if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
    die('Upload failed with error code ' . $uploadedFile['error']);
}

// 构建目标文件路径，使用当前的年月日-时分秒时间戳作为文件名
$timestamp = date('Ymd-His');
$targetFileName = $timestamp . '.png';
$targetFilePath = $uploadDirectory . $targetFileName;

// 移动文件到目标路径
if (move_uploaded_file($uploadedFile['tmp_name'], $targetFilePath)) {
    echo 'File has been uploaded successfully.';
} else {
    echo 'Upload failed.';
}
?>
