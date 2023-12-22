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

    // 计算文件大小（KB和MB）
    $fileSizeKB = round(filesize($targetFilePath) / 1024, 2); // 文件大小（KB）
    $fileSizeMB = round(filesize($targetFilePath) / (1024 * 1024), 3); // 文件大小（MB）

    // 替换路径中的 "/home/01_html" 为 "root"
    $adjustedFilePath = str_replace('/home/01_html', 'http://120.46.81.41', $targetFilePath);

    $response = [
        'sizeKB' => $fileSizeKB,
        'sizeMB' => $fileSizeMB,
        'fileName' => $targetFileName,
        'filePath' => $targetFilePath,
        'adjustedPath' => $adjustedFilePath
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    echo 'Upload failed.';
}
?>
