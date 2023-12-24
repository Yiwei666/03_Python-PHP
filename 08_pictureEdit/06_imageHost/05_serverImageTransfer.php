<?php
// 添加获取POST参数的检查
$imageName = isset($_POST['imageName']) ? $_POST['imageName'] : '';

// 指定文本文件路径
$filePath = '/home/01_html/05_imageTransferName.txt';

// 获取当前时间（北京时间），使用 Y-m-d H:i:s 格式
$currentTime = date('Y-m-d H:i:s', strtotime('+8 hours'));

// 将内容写入文本文件（以追加的方式）
if (!empty($imageName)) {
    // 构建要写入文本文件的内容，包括文件名和格式化的时间
    $contentToWrite = $imageName . ',' . $currentTime . PHP_EOL;

    // 写入内容到文本文件
    file_put_contents($filePath, $contentToWrite, FILE_APPEND);

    // 返回成功响应
    echo 'success';
} else {
    // 如果没有传递 imageName 参数，返回错误响应
    echo 'error: imageName parameter is missing';
}
?>
