<?php
// 引入数据库配置和连接
include '03_db_config.php';

// 远程目录
$remote_dir = "HW-1012:do1-2/01_html/02_douyVideo";
// 本地目录
$local_dir = "/home/01_html/01_tecent1017/25_film_videos";

// 使用 rclone 获取远程目录下的所有 mp4 文件名
$command = "rclone lsf --include '*.mp4' $remote_dir";
exec($command, $output, $return_var);

// 检查命令是否成功执行
if ($return_var != 0) {
    die("Failed to execute rclone command.\n");
}

// 用于计数新插入的视频文件名数量
$new_files_count = 0;

// 遍历所有 mp4 文件名
foreach ($output as $file) {
    // 检查数据库中是否已存在该视频文件名
    $query = $mysqli->prepare("SELECT COUNT(*) FROM tk_videos WHERE video_name = ?");
    $query->bind_param("s", $file);
    $query->execute();
    $query->bind_result($count);
    $query->fetch();
    $query->close();

    // 如果该视频不存在于数据库中，则插入新的记录
    if ($count == 0) {
        $insert = $mysqli->prepare("INSERT INTO tk_videos (video_name, create_time, exist_status) VALUES (?, NOW(), 0)");
        $insert->bind_param("s", $file);
        $insert->execute();
        $insert->close();

        // 增加新文件计数
        $new_files_count++;

        // 使用 rclone 复制文件到本地目录
        $remote_file = $remote_dir . '/' . $file;
        $local_file = $local_dir;
        $copy_command = "rclone copy '$remote_file' '$local_file'";
        // exec($copy_command, $copy_output, $copy_return_var);
        // if ($copy_return_var != 0) {
        //     echo "Failed to copy $file\n";
        // } else {
        //     echo "Copied $file successfully\n";
        // }
    }
}

// 关闭数据库连接
$mysqli->close();

// 打印新插入的视频文件名数量
echo "Process completed. New files inserted: $new_files_count.\n";
?>
