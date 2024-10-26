<?php
// 引入数据库配置
include '08_db_config.php';

// 获取传递的图片 ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 从数据库中获取所有图片，并按照 (likes - dislikes) 排序
$query = "SELECT id, image_name, likes, dislikes FROM images WHERE image_exists = 1";
$result = $mysqli->query($query);

// 按照 (likes - dislikes) 排序
$validImages = [];
while ($row = $result->fetch_assoc()) {
    $validImages[] = $row;
}

usort($validImages, function ($a, $b) {
    return ($b['likes'] - $b['dislikes']) - ($a['likes'] - $a['dislikes']);
});

// 查找当前图片在图片数组中的位置
$currentIndex = -1;
foreach ($validImages as $index => $image) {
    if ($image['id'] == $id) {
        $currentIndex = $index;
        break;
    }
}

// 计算上一张和下一张图片的索引
$prevIndex = $currentIndex > 0 ? $currentIndex - 1 : -1;
$nextIndex = $currentIndex < count($validImages) - 1 ? $currentIndex + 1 : -1;

// 当前图片信息
$currentImage = $validImages[$currentIndex];
$domain = "https://19640810.xyz";
$dir5 = str_replace("/home/01_html", "", "/home/01_html/08_x/image/01_imageHost");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Navigation</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: black;
        }
        .image-container {
            position: relative;
            text-align: center;
        }
        .image {
            max-width: 100%;
            max-height: 100vh;
        }
        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0,0,0,0.5);
            color: white;
            border: none;
            font-size: 30px;
            padding: 10px;
            cursor: pointer;
        }
        .arrow-left {
            left: 0;
        }
        .arrow-right {
            right: 0;
        }
    </style>
</head>
<body>
    <div class="image-container">
        <?php if ($prevIndex >= 0): ?>
            <button class="arrow arrow-left" onclick="window.location.href='08_image_leftRight_navigation.php?id=<?php echo $validImages[$prevIndex]['id']; ?>'">←</button>
        <?php endif; ?>
        
        <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($currentImage['image_name']); ?>" class="image" alt="Image">
        
        <?php if ($nextIndex >= 0): ?>
            <button class="arrow arrow-right" onclick="window.location.href='08_image_leftRight_navigation.php?id=<?php echo $validImages[$nextIndex]['id']; ?>'">→</button>
        <?php endif; ?>
    </div>
</body>
</html>
