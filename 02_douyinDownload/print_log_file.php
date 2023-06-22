<?php
    $logFilePath = '/home/01_html/05_douyinDownload/douyin_log.txt';
    $logContent = file_get_contents($logFilePath);

    echo $logContent;
?>
