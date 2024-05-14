<?php
include '18_db_config.php';  // 引入数据库配置文件，建立 $mysqli 数据库连接对象

// 从数据库中读取最后两条 URL 写入信息
$query = "SELECT video_url, url_write_time FROM douyin_videos ORDER BY id DESC LIMIT 2";
$result = $mysqli->query($query);

// 生成日志内容的字符串
$logContent = "";
while ($row = $result->fetch_assoc()) {
    $logContent .= $row['video_url'] . ", " . $row['url_write_time'] . "\n";
}

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
        <pre>" . htmlspecialchars($logContent) . "</pre>
      </div>";

// 输出 HTML 尾部
echo "</body>
</html>";
?>
