<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// If the user clicked the logout link, log them out and redirect to the login page
if (isset($_GET['logout'])) {
  session_destroy(); // destroy all session data
  header('Location: login.php');
  exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Website Display</title>
    <style>
        .container {
            width: 40%;
            margin: 60px auto 0; /* 设置容器顶部间距为 60px，其余间距自动 */
            border-left: 0.1px solid #ccc; /* 添加左侧细线 */
            border-right: 0.1px solid #ccc; /* 添加右侧细线 */
            padding-left: 30px; /* 添加内边距 */
            background-color: #f2f2f2; /* 将容器内的背景色设置为浅灰色 */
        }
        .website {
            clear: left;
            margin-bottom: 10px; /* 设置网站和介绍文本之间的距离 */
        }
        .description {
            font-size: 12px;
            margin-left: 20px; /* 添加此样式以在容器内部左对齐 */
            margin-bottom: 30px; /* 设置介绍文本和网站之间的距离 */
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    $websites = array(
        "a.com" => array(
            "url" => "https://a.com",
            "description" => "do1-2，centos系统，已安装php，运行多个脚本，包括可可英语，抖音，youtube视频下载和观看，托福视频，base64编码和解码等"
        ),
        "b.com" => array(
            "url" => "b.com",
            "description" => "az1，ubuntu系统，已经安装ffmpeg转换工具，抖音视频下载api的docker应用，已经安装了php"
        ),
        "c.com" => array(
            "url" => "c.com",
            "description" => "az2，ubuntu系统，已经安装ffmpeg转换工具，抖音视频下载api的docker应用"
        ),
        "d.com" => array(
            "url" => "https://d.com",
            "description" => "Website D - Et harum quidem rerum facilis est et expedita distinctio."
        ),
        "e.com" => array(
            "url" => "https://d.com",
            "description" => "Website D - Et harum quidem rerum facilis est et expedita distinctio."
        ),
        "f.com" => array(
            "url" => "https://d.com",
            "description" => "Website D - Et harum quidem rerum facilis est et expedita distinctio."
        ),
        "g.com" => array(
            "url" => "https://d.com",
            "description" => "Website D - Et harum quidem rerum facilis est et expedita distinctio."
        ),
        "h.com" => array(
            "url" => "https://d.com",
            "description" => "Website D - Et harum quidem rerum facilis est et expedita distinctio."
        )
    );

    foreach ($websites as $website => $data) {
        $url = $data['url'];
        $description = $data['description'];
        echo '<div class="website"><a href="' . $url . '">' . $website . '</a></div>';
        echo '<div class="description">' . $description . '</div>';
    }
    ?>
</div>

</body>
</html>


