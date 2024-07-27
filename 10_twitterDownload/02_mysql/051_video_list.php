<?php
$dir = "/home/01_html/05_twitter_video";

// 获取指定目录下的所有文件
$files = scandir($dir);

// 过滤出MP4视频文件
$videos = array_filter($files, function ($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'mp4';
});

// 为链接添加样式
echo "<style>
        body {
            margin-left: 30%; /* 页面左侧边距为页面宽度的 15% */
            margin-top: 5%; /* 页面顶部边距为 10% */
        }
        a {
            color: #1a73e8; /* 蓝色 */
            text-decoration: none; /* 去除下划线 */
            font-family: Arial, sans-serif; /* 设置字体 */
            margin: 5px; /* 设置外边距 */
            display: inline-block; /* 让链接占据独立的行 */
            padding: 10px 20px; /* 内边距 */
            border: 1px solid #1a73e8; /* 边框 */
            border-radius: 5px; /* 边框圆角 */
            background-color: #f8f9fa; /* 背景颜色 */
            transition: background-color 0.3s, color 0.3s; /* 过渡效果 */
        }
        a:hover {
            background-color: #1a73e8; /* 鼠标悬停背景色 */
            color: white; /* 鼠标悬停文字颜色 */
        }
    </style>";

// 显示视频文件为链接
foreach ($videos as $video) {
    $videoEncoded = urlencode($video);
    echo "<a href='051_videoPlayer_sigURL.php?video=$videoEncoded' target='_blank'>$video</a><br />";
}
?>
