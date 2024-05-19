<?php
// 引入数据库配置和连接
include '03_db_config.php'; // 根据实际路径调整

exec('php 03_copy_remote_to_local.php');

// 定义本地和远程目录
$local_dir = "/home/01_html/01_tecent1017/25_film_videos";
$remote_dir = "HW-1012:do1-2/01_html/02_douyVideo";

// 从本地目录读取 mp4 文件名到数组 A
$local_files = glob($local_dir . '/*.mp4');
$arrayA = array_map('basename', $local_files);

// 从数据库中获取所有 mp4 文件名到数组 B
$arrayB = [];
$result = $mysqli->query("SELECT video_name FROM tk_videos");
while ($row = $result->fetch_assoc()) {
    $arrayB[] = $row['video_name'];
}
$result->close();

// 从数组 A 中随机选取 N 个视频并从本地目录删除
$N = 50; // 可以根据实际需要调整这个值
$randomA = (count($arrayA) > $N) ? array_rand($arrayA, $N) : $arrayA;
foreach ($randomA as $index) {
    $file_path = $local_dir . '/' . $arrayA[$index];
    if (file_exists($file_path)) {
        unlink($file_path);
        echo "Deleted: " . $arrayA[$index] . "\n";
    }
}

// 计算 B - A 的补集并从中随机选取 M 个视频
$diffBA = array_diff($arrayB, $arrayA);
$M = 50; // 确保 M 小于等于 B-A 数组的元素个数
$randomDiffBA = (count($diffBA) > $M) ? array_rand($diffBA, $M) : $diffBA;
foreach ($randomDiffBA as $index) {
    $remote_file_path = $remote_dir . '/' . $diffBA[$index];
    $local_file_path = $local_dir;
    $copy_command = "rclone copy '$remote_file_path' '$local_file_path'";
    exec($copy_command, $copy_output, $copy_return_var);
    if ($copy_return_var != 0) {
        echo "Failed to copy " . $diffBA[$index] . "\n";
    } else {
        echo "Copied " . $diffBA[$index] . " successfully\n";
    }
}

// 关闭数据库连接
$mysqli->close();

exec('php 03_tk_video_check.php');

echo "Process completed.\n";

?>
