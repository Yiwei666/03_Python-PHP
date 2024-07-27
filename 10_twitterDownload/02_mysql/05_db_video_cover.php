<?php
// 引入数据库配置文件
include '05_db_config.php';

// 视频存储目录和封面存储目录
$videoDir = '/home/01_html/05_twitter_video/';
$coverDir = '/home/01_html/05_video_cover/';

// 查询 exist_status 为 1 的视频文件名
$query = "SELECT video_name FROM videos WHERE exist_status = 1 AND video_name LIKE '%.mp4'";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videoName = $row['video_name'];
        $videoPath = $videoDir . $videoName;
        $coverName = pathinfo($videoName, PATHINFO_FILENAME) . '.png';
        $coverPath = $coverDir . $coverName;

        // 检查封面是否已经存在
        if (file_exists($coverPath)) {
            echo "Cover for video $videoName already exists. Skipping...\n";
            continue;
        }

        // 生成封面命令
        $ffmpegCommand = "ffmpeg -i $videoPath -vf 'thumbnail' -frames:v 1 -q:v 2 $coverPath";

        // 执行命令生成封面
        exec($ffmpegCommand, $output, $return_var);

        if ($return_var === 0) {
            echo "Cover for video $videoName generated successfully.\n";
        } else {
            echo "Failed to generate cover for video $videoName. ffmpeg error code: $return_var\n";
        }
    }
} else {
    echo "No videos with exist_status = 1 found.\n";
}

// 关闭数据库连接
$mysqli->close();
?>
