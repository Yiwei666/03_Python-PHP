<?php
// 引入数据库配置和连接
include '03_db_config.php';

// 指定视频文件所在目录
$video_dir = "/home/01_html/01_tecent1017/25_film_videos";

// 检查目录是否存在并可读
if (!is_dir($video_dir) || !is_readable($video_dir)) {
    die("Cannot read directory: $video_dir");
}

// 打开目录
$dir = opendir($video_dir);
if (!$dir) {
    die("Failed to open directory: $video_dir");
}

// 遍历目录下的所有文件
while (($file = readdir($dir)) !== false) {
    // 确保文件是mp4视频
    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
        // 提取视频文件名
        $video_name = $file;
        
        // 获取文件的完整路径
        $video_path = $video_dir . '/' . $video_name;

        // 获取文件的创建时间
        $create_time = date('Y-m-d H:i:s', filemtime($video_path));

        // 检查数据库中是否已存在该视频文件名
        $query = $mysqli->prepare("SELECT COUNT(*) FROM tk_videos WHERE video_name = ?");
        $query->bind_param("s", $video_name);
        $query->execute();
        $query->bind_result($count);
        $query->fetch();
        $query->close();

        // 如果该视频不存在于数据库中，则插入新的记录
        if ($count == 0) {
            $insert = $mysqli->prepare("INSERT INTO tk_videos (video_name, create_time, exist_status) VALUES (?, ?, 1)");
            $insert->bind_param("ss", $video_name, $create_time);
            $insert->execute();
            $insert->close();
        }
    }
}

// 关闭目录
closedir($dir);

// 检查数据库中的视频文件是否存在于本地
$query = $mysqli->prepare("SELECT video_name, exist_status FROM tk_videos");
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $video_name = $row['video_name'];
    $exist_status = $row['exist_status'];
    $video_path = $video_dir . '/' . $video_name;

    // 检查视频文件是否存在于本地
    if (file_exists($video_path)) {
        // 获取文件的创建时间
        $create_time = date('Y-m-d H:i:s', filemtime($video_path));

        // 如果视频文件存在且 exist_status 为 0，则更新其 exist_status 为 1 并更新 create_time
        if ($exist_status == 0) {
            $update = $mysqli->prepare("UPDATE tk_videos SET exist_status = 1, create_time = ? WHERE video_name = ?");
            $update->bind_param("ss", $create_time, $video_name);
            $update->execute();
            $update->close();
        }
    } else {
        // 如果视频文件不存在，则更新其 exist_status 为 0
        $update = $mysqli->prepare("UPDATE tk_videos SET exist_status = 0 WHERE video_name = ?");
        $update->bind_param("s", $video_name);
        $update->execute();
        $update->close();
    }
}

$query->close();

// 打印数据库中所有视频的数量
$total_query = $mysqli->prepare("SELECT COUNT(*) FROM tk_videos");
$total_query->execute();
$total_query->bind_result($total_count);
$total_query->fetch();
$total_query->close();
echo "Total number of videos in the database: $total_count\n";

// 打印 exist_status 为 1 的视频数量
$status_1_query = $mysqli->prepare("SELECT COUNT(*) FROM tk_videos WHERE exist_status = 1");
$status_1_query->execute();
$status_1_query->bind_result($status_1_count);
$status_1_query->fetch();
$status_1_query->close();
echo "Number of videos with exist_status = 1: $status_1_count\n";

// 打印 exist_status 为 0 的视频数量
$status_0_query = $mysqli->prepare("SELECT COUNT(*) FROM tk_videos WHERE exist_status = 0");
$status_0_query->execute();
$status_0_query->bind_result($status_0_count);
$status_0_query->fetch();
$status_0_query->close();
echo "Number of videos with exist_status = 0: $status_0_count\n";

// 关闭数据库连接
$mysqli->close();

echo "Video processing and verification completed.\n";
?>
