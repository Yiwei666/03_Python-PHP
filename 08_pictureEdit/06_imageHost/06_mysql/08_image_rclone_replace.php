<?php
// 引入数据库配置文件
include '08_db_config.php';
include '08_db_sync_images.php';                     // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");    // 调用函数并提供图片存储目录

// 获取图片数据库中 likes - dislikes 大于等于0 的图片名，存到数组A中
$arrayA = [];
$result = $mysqli->query("SELECT image_name FROM images WHERE likes - dislikes >= 0");
while ($row = $result->fetch_assoc()) {
    $arrayA[] = $row['image_name'];
}

// 从数组A中随机抽取5000张图片名存到数组B中
if (count($arrayA) > 5000) {
    $arrayB = array_rand(array_flip($arrayA), 5000);
} else {
    $arrayB = $arrayA;
}

// 获取目录下的所有png图片名，存到数组C中
$directory = '/home/01_html/08_x/image/01_imageHost';
$arrayC = glob($directory . '/*.png');
$arrayC = array_map('basename', $arrayC);

// 数组B和数组C的交集称为数组D
$arrayD = array_intersect($arrayB, $arrayC);

// 删除掉目录下存在于 C-D 数组的图片
$arrayC_diff_D = array_diff($arrayC, $arrayD);
foreach ($arrayC_diff_D as $filename) {
    $filepath = $directory . '/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
        echo "Deleted $filepath\n";
    } else {
        echo "File $filepath does not exist\n";
    }
}

// 若数组D的长度等于5000，则退出脚本；若数组D的长度小于5000，则利用rclone copy命令下载 B-D 中的图片到目录
if (count($arrayD) == 5000) {
    echo "Array D has 5000 elements. Exiting script.\n";
    $mysqli->close();
    exit(0);
} else {
    $diffBD = array_diff($arrayB, $arrayD);
    $remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 请替换为远程目录路径
    $local_dir = $directory;

    foreach ($diffBD as $filename) {
        $remote_file_path = $remote_dir . '/' . $filename;
        $local_file_path = $local_dir;
        $copy_command = "rclone copy '$remote_file_path' '$local_file_path' --transfers=8";
        exec($copy_command, $copy_output, $copy_return_var);
        if ($copy_return_var != 0) {
            echo "Failed to copy " . $filename . "\n";
        } else {
            echo "Copied " . $filename . " successfully\n";
        }
    }
}

$mysqli->close();
exec('php /home/01_html/08_db_image_status.php');
echo "Process completed.\n";
?>
