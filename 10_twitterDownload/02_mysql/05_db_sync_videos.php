<?php
include '05_db_config.php'; // 包含数据库连接信息

function syncVideos($directory) {
    global $mysqli;

    $imagesInDirectory = glob($directory . "/*.mp4"); // 获取所有 mp4
    $existingImages = [];

    // 获取数据库中已存在的视频
    $result = $mysqli->query("SELECT video_name FROM videos");
    while ($row = $result->fetch_assoc()) {
        $existingImages[] = $row['video_name'];
    }

    // 检查目录中的图片是否已在数据库中
    foreach ($imagesInDirectory as $filePath) {
        $imageName = basename($filePath);
        if (!in_array($imageName, $existingImages)) {
            // 如果图片不在数据库中，则添加
            $stmt = $mysqli->prepare("INSERT INTO videos (video_name, likes, dislikes) VALUES (?, 0, 0)");
            $stmt->bind_param("s", $imageName);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// 可以根据需要在这个脚本中直接调用 syncImages 函数或在其他文件中调用
?>
