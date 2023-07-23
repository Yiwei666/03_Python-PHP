<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['videos'])) {
    $uploadDir = '/home/01_html/MuChaManor/';

    // Create the directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $totalFiles = count($_FILES['videos']['name']);
    $uploadSuccess = true;

    for ($i = 0; $i < $totalFiles; $i++) {
        $tmpFilePath = $_FILES['videos']['tmp_name'][$i];
        $newFilePath = $uploadDir . basename($_FILES['videos']['name'][$i]);
        $fileType = strtolower(pathinfo($newFilePath, PATHINFO_EXTENSION));

        // Check if the file type is MP4
        if ($fileType === 'mp4') {
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                echo "文件 " . $_FILES['videos']['name'][$i] . " 上传成功！<br>";
            } else {
                echo "文件 " . $_FILES['videos']['name'][$i] . " 上传失败！<br>";
                $uploadSuccess = false;
            }
        } else {
            echo "文件 " . $_FILES['videos']['name'][$i] . " 不是有效的MP4视频文件！<br>";
            $uploadSuccess = false;
        }
    }

    if ($uploadSuccess) {
        echo "所有文件上传成功！";
    }
}
?>
