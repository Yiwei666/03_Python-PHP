<?php
// 引入数据库配置文件
include '05_db_config.php';

// 视频存储目录
$dir = '/home/01_html/05_twitter_video/';

// 查询所有视频文件名
$query = "SELECT id, video_name, size, exist_status FROM videos";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videoPath = $dir . $row['video_name'];
        $exist_status = 0;
        $size = $row['size']; // 默认保留原有的 size 值

        if (file_exists($videoPath)) {
            $exist_status = 1;
            $size = round(filesize($videoPath) / (1024 * 1024), 2); // 文件大小以MB为单位，保留2位小数
        }

        // 更新数据库中的 size 和 exist_status 值
        $updateQuery = "UPDATE videos SET exist_status = $exist_status, size = $size WHERE id = {$row['id']}";
        if (!$mysqli->query($updateQuery)) {
            echo "Error updating record for video id {$row['id']}: " . $mysqli->error . "\n";
        }
    }
    echo "Database update complete.\n";
} else {
    echo "No videos found in the database.\n";
}

// 关闭数据库连接
$mysqli->close();
?>
