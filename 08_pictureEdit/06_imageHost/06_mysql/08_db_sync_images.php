<?php
include '08_db_config.php'; // 包含数据库连接信息

function syncImages($directory)
{
    global $mysqli;

    // 保留原来的匹配范围、默认排序和扩展名分组顺序。
    $imagesInDirectory = array_merge(
        glob($directory . '/*.png') ?: [],
        glob($directory . '/*.jpg') ?: [],
        glob($directory . '/*.jpeg') ?: []
    );

    $existingImages = [];

    // 查询方式和 SQL 保持不变；仅将查询结果组织为文件名集合。
    $result = $mysqli->query('SELECT image_name FROM images');
    while ($row = $result->fetch_assoc()) {
        $existingImages[$row['image_name']] = true;
    }

    foreach ($imagesInDirectory as $filePath) {
        $imageName = basename($filePath);

        if (!isset($existingImages[$imageName])) {
            // 保留原来的逐条准备、绑定、执行、关闭操作。
            $stmt = $mysqli->prepare("INSERT INTO images (image_name, likes, dislikes) VALUES (?, 0, 0)");
            $stmt->bind_param("s", $imageName);
            $stmt->execute();
            $stmt->close();
        }
    }
}
