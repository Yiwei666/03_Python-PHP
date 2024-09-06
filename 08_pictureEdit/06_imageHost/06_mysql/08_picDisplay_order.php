<?php
session_start();

// 如果用户未登录，重定向到登录页面
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 如果用户点击了登出链接，注销用户并重定向到登录页面
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include '08_db_config.php';  // 包含数据库连接信息
?>

<?php
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://19640810.xyz"; // 域名网址
$picnumber = 50; // 设置需要显示的图片数量

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // 设置 PDO 错误模式为异常
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 从数据库中读取图片，按照 (likes - dislikes) 排序
    // $stmt = $pdo->prepare("SELECT image_name FROM images ORDER BY (likes - dislikes) DESC LIMIT :picnumber");
    // $stmt = $pdo->prepare("SELECT image_name FROM images WHERE image_exists = 1 ORDER BY (likes - dislikes) DESC LIMIT :picnumber");
    $stmt = $pdo->prepare("SELECT image_name FROM images WHERE image_exists = 1 AND (likes - dislikes) > 5 ORDER BY RAND() LIMIT :picnumber");
    $stmt->bindParam(':picnumber', $picnumber, PDO::PARAM_INT);
    $stmt->execute();
    $selectedImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
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
        padding: 20px 0;
        width: 100vw;
    }
    .image {
        width: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i', $_SERVER['HTTP_USER_AGENT']) ? '900px' : '500px'; ?>;
        height: auto;
        margin-bottom: 20px;
    }
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
    <?php foreach ($selectedImages as $image): ?>
        <?php $imageUrl = $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>
        <img src="<?php echo $imageUrl; ?>" class="image" alt="Random Image" loading="lazy">
    <?php endforeach; ?>
</div>
<button class="refresh-button" onclick="window.location.reload();">⟳</button>
</body>
</html>
