<?php

// 引入数据库配置文件
include '08_db_config.php';

// 定义图片存储目录
$imagesDirectory = '/home/01_html/08_x/image/01_imageHost';

// 查询images表中所有图片
$query = "SELECT id, image_name FROM images";
$result = $mysqli->query($query);

if (!$result) {
    die('Query Error (' . $mysqli->errno . ') ' . $mysqli->error);
}

// 遍历所有图片
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $image_name = $row['image_name'];
    $image_path = $imagesDirectory . '/' . $image_name;

    // 检查图片是否存在
    $image_exists = file_exists($image_path) ? 1 : 0;

    // 更新数据库中的image_exists字段
    $updateQuery = "UPDATE images SET image_exists = ? WHERE id = ?";
    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param('ii', $image_exists, $id);
    $stmt->execute();
}

// 统计image_exists为0和1的图片数量以及总数
$countQuery = "SELECT image_exists, COUNT(*) as count FROM images GROUP BY image_exists";
$countResult = $mysqli->query($countQuery);

$existsCount = 0;
$notExistsCount = 0;
$totalCount = 0;

while ($countRow = $countResult->fetch_assoc()) {
    if ($countRow['image_exists'] == 1) {
        $existsCount = $countRow['count'];
    } else {
        $notExistsCount = $countRow['count'];
    }
    $totalCount += $countRow['count'];
}

// 打印结果
echo "存在的图片数量: " . $existsCount . "\n";
echo "不存在的图片数量: " . $notExistsCount . "\n";
echo "数据库中图片总数: " . $totalCount . "\n";

?>
