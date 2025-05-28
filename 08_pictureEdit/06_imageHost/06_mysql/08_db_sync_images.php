<?php
include '08_db_config.php'; // 包含数据库连接信息

function syncImages($directory) {
    global $mysqli;

    // $imagesInDirectory = glob($directory . "/*.png"); // 获取所有 png 图片
    // 同时匹配 png、jpg、jpeg
    $imagesInDirectory = array_merge(
        glob($directory . "/*.png"),
        glob($directory . "/*.jpg"),
        glob($directory . "/*.jpeg")
    );
    
    $existingImages = [];

    // 获取数据库中已存在的图片
    $result = $mysqli->query("SELECT image_name FROM images");
    while ($row = $result->fetch_assoc()) {
        $existingImages[] = $row['image_name'];
    }

    // 检查目录中的图片是否已在数据库中
    foreach ($imagesInDirectory as $filePath) {
        $imageName = basename($filePath);
        if (!in_array($imageName, $existingImages)) {
            // 如果图片不在数据库中，则添加
            $stmt = $mysqli->prepare("INSERT INTO images (image_name, likes, dislikes) VALUES (?, 0, 0)");
            $stmt->bind_param("s", $imageName);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// 可以根据需要在这个脚本中直接调用 syncImages 函数或在其他文件中调用
?>
