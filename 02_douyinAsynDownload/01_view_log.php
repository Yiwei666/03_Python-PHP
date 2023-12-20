<?php
$filePathLog = '/home/01_html/05_douyinAsynDload/2_addTotalLog.txt';

// 读取文件的最后两行
$logContent = shell_exec("tail -n 2 $filePathLog");

// 输出 HTML 头部
echo "<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>抖音日志最后两行</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    .centered-container {
      position: absolute;
      top: 20%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    h2 {
      font-size: 24px;
    }

    pre {
      text-align: center;
      font-size: 18px;
      line-height: 5em;
    }
  </style>
</head>
<body>";

// 输出内容
echo "<div class='centered-container'>
        <h2>日志最后两行</h2>
        <pre>$logContent</pre>
      </div>";

// 输出 HTML 尾部
echo "</body>
</html>";
?>
