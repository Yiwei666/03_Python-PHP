<?php
// 引入数据库配置文件
include '08_db_config.php';

// 提醒用户输入a、b和x
echo "Enter three integers (a, b, x): ";
$input = trim(fgets(STDIN));
list($a, $b, $x) = explode(' ', $input);

// 检查a是否小于b
if ($a >= $b) {
    echo "Error: 'a' should be less than 'b'.\n";
    exit(1);
}

// 显示选项
echo "Select an option:\n";
echo "1. Update and count images with likes between [$a, $b]\n";
echo "2. Update and count images with dislikes between [$a, $b]\n";
echo "3. Count images with likes between [$a, $b]\n";
echo "Enter option (1/2/3): ";
$option = trim(fgets(STDIN));

// 获取图片总数
$total_images_result = $mysqli->query("SELECT COUNT(*) AS total FROM images");
$total_images = $total_images_result->fetch_assoc()['total'];

// 功能1：打印likes在[a, b]之间的图片数量并增加x个
if ($option == '1') {
    $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE likes BETWEEN $a AND $b");
    $count = $count_result->fetch_assoc()['count'];
    $mysqli->query("UPDATE images SET likes = likes + $x WHERE likes BETWEEN $a AND $b");
    echo "Number of images with likes between [$a, $b]: $count\n";
    echo "Total images in the database: $total_images\n";
    echo "Updated likes by adding $x to all matching images.\n";
}

// 功能2：打印dislikes在[a, b]之间的图片数量并增加x个
elseif ($option == '2') {
    $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE dislikes BETWEEN $a AND $b");
    $count = $count_result->fetch_assoc()['count'];
    $mysqli->query("UPDATE images SET likes = likes + $x WHERE dislikes BETWEEN $a AND $b");
    echo "Number of images with dislikes between [$a, $b]: $count\n";
    echo "Total images in the database: $total_images\n";
    echo "Updated likes by adding $x to all matching images.\n";
}

// 功能3：打印likes在[a, b]之间的图片数量
elseif ($option == '3') {
    $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE likes BETWEEN $a AND $b");
    $count = $count_result->fetch_assoc()['count'];
    echo "Number of images with likes between [$a, $b]: $count\n";
    echo "Total images in the database: $total_images\n";
}

// 无效选项处理
else {
    echo "Invalid option selected.\n";
}
?>
