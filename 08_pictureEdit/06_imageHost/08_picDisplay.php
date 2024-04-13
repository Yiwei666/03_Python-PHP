<?php
// 定义图片存储的目录
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://abc.com"; // 域名网址
$picnumber = 5; // 设置需要显示的图片数量

// 读取目录中的所有 png 图片
$images = glob($dir4 . '/*.png');

// 从图片数组中随机选取指定数量的图片
$selectedImages = [];
if ($images && count($images) >= $picnumber) {
    $randomKeys = array_rand($images, $picnumber);
    foreach ($randomKeys as $key) {
        $selectedImages[] = $images[$key];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Random Image Display</title>
<style>
    body, html {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        overflow-y: auto;
    }
    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        padding: 20px 0;
    }
    .image {
        width: 400px;
        height: auto;
        margin-bottom: 20px;
    }
    .refresh-button {
        position: fixed;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>
</head>
<body>
<div class="container">
    <?php foreach ($selectedImages as $image): ?>
        <?php
        $imageName = basename($image);
        $imageUrl = $domain . $dir5 . '/' . $imageName;
        ?>
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="image" alt="Random Image" loading="lazy">
    <?php endforeach; ?>
</div>
<button class="refresh-button" onclick="window.location.reload();">⟳</button>
</body>
</html>
