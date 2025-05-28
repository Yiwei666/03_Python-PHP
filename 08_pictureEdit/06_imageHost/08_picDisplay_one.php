<?php
session_start();

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'your-signing-key-1';  // 应与登录脚本中的密钥一致

// 如果用户未登录，则尝试通过 Cookie 验证身份
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_COOKIE['user_auth'])) {
        $decryptedValue = decrypt($_COOKIE['user_auth'], $key);
        if ($decryptedValue == 'mcteaone') { // 验证解密后的值是否与预期匹配
            $_SESSION['loggedin'] = true; // 将用户标记为已登录
        } else {
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

// 如果用户点击了注销链接，注销用户并重定向
if (isset($_GET['logout'])) {
    session_destroy(); // 销毁所有 session 数据
    setcookie('user_auth', '', time() - 3600, '/'); // 删除 cookie
    header('Location: login.php');
    exit;
}

// 定义图片存储的目录
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://19640810.xyz"; // 域名网址
$picnumber = 1; // 设置需要显示的图片数量

// 读取目录中的所有 png 图片
// $images = glob($dir4 . '/*.png');
$images = array_merge(
    glob($dir4 . '/*.png')   ?: [],
    glob($dir4 . '/*.jpg')   ?: [],
    glob($dir4 . '/*.jpeg')  ?: []
);

// 从图片数组中随机选取指定数量的图片
$selectedImages = [];
$imageDetails = []; // 存储图片的详细信息，包括尺寸和URL
if ($images && count($images) >= $picnumber) {
    $randomKeys = array_rand($images, $picnumber);
    $randomKeys = (array) $randomKeys; // 确保 $randomKeys 始终是数组
    foreach ($randomKeys as $key) {
        $selectedImages[] = $images[$key];
    }

    // 获取每个选中图片的宽度和高度
    foreach ($selectedImages as $image) {
        list($width, $height) = getimagesize($image);
        $imageName = basename($image);
        $imageUrl = $domain . $dir5 . '/' . $imageName;
        $imageDetails[] = [
            'url' => htmlspecialchars($imageUrl),
            'width' => $width,
            'height' => $height
        ];
    }
}

// 设备类型判断，决定图片宽度
$isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i', $_SERVER['HTTP_USER_AGENT']);
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
        height: 100%; /* 确保 html 和 body 的高度为 100% */
    }
    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 0;
        width: 100vw;
        height: 100%; /* 设置 container 的高度为 100% */
        justify-content: center; /* 在竖直方向上居中 */
    }
    .image {
        width: auto;
        height: auto;
        margin-bottom: 20px;
    }
    <?php foreach ($imageDetails as $details): ?>
    img[src="<?php echo $details['url']; ?>"] {
        width: <?php echo $details['width'] > $details['height'] ? ($isMobile ? '900px' : '1000px') : ($isMobile ? '900px' : '640px'); ?>;
    }
    <?php endforeach; ?>
    .refresh-button {
        position: fixed;
        right: calc(50% - 500px);
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        z-index: 1000;
    }
</style>
</head>
<body>
<div class="container">
    <?php foreach ($imageDetails as $details): ?>
        <img src="<?php echo $details['url']; ?>" class="image" alt="Random Image" loading="lazy">
    <?php endforeach; ?>
</div>
<button class="refresh-button" onclick="window.location.reload();">⟳</button>
</body>
</html>
