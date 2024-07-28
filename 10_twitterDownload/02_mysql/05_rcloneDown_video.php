<?php
include '05_db_config.php';

function download_video($filename) {
    // 本地目录
    $local_dir = "/home/01_html/05_twitter_video/";
    // 远程目录
    $remote_dir = "rc6:az1-1/01_html/05_twitter_video/";

    // 完整的下载命令
    $copy_command = "/usr/bin/rclone copy '{$remote_dir}{$filename}' '{$local_dir}'";
    
    // 执行下载命令
    exec($copy_command, $output, $return_var);

    // 根据执行结果返回相应的信息
    if ($return_var != 0) {
        return "Failed to copy {$filename}\n";
    } else {
        return "Copied {$filename} successfully\n";
    }
}

// 查询所有视频信息
$query = "SELECT id, video_name, exist_status, operation FROM videos";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $video_name = $row['video_name'];
        $exist_status = $row['exist_status'];
        $operation = $row['operation'];

        if ($operation == 1 && $exist_status == 0) {
            // 下载视频
            $download_result = download_video($video_name);
            echo $download_result;

            // 更新数据库中的 operation 列为 0
            $update_query = "UPDATE videos SET operation = 0 WHERE id = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        } elseif ($operation == -1 && $exist_status == 1) {
            // 删除视频
            $file_path = "/home/01_html/05_twitter_video/" . $video_name;
            if (file_exists($file_path)) {
                unlink($file_path);
                echo "Deleted {$video_name} successfully\n";
            } else {
                echo "File {$video_name} does not exist\n";
            }

            // 更新数据库中的 operation 列为 0
            $update_query = "UPDATE videos SET operation = 0 WHERE id = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
} else {
    echo "No videos found.\n";
}

$mysqli->close();
?>
