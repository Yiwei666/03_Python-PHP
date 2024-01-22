<?php

// 读取 nameURL.txt 中的文件名和链接
$nameUrlLines = file('nameURL.txt', FILE_IGNORE_NEW_LINES);

// 读取 remote_filename.txt 中已下载的文件名（去除后缀）
$downloadedFiles = array_map(fn($line) => pathinfo($line)['filename'], file('remote_filename.txt', FILE_IGNORE_NEW_LINES));

// 遍历每一行，检查文件名是否在已下载文件列表中，如果不在则写入 undownload_mp3.txt
foreach ($nameUrlLines as $line) {
    list($filename, $url) = explode(',', $line);
    $filename = trim($filename);
    
    if (!in_array(pathinfo($filename)['filename'], $downloadedFiles)) {
        file_put_contents('undownload_mp3.txt', "$filename,$url\n", FILE_APPEND);
    }
}

echo "未下载的文件名和链接已写入 undownload_mp3.txt 文件。\n";

?>
