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
