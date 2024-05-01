<?php
session_start();
include '08_db_config.php';  // 包含数据库连接信息

$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://19640810.xyz"; // 域名网址
$picnumber = 3; // 设置需要显示的图片数量

// 用户登录和登出检查
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// 处理点赞和点踩的请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['imageId']) && isset($_POST['action'])) {
    $imageId = intval($_POST['imageId']);
    $action = $_POST['action'];
    if ($action == 'like') {
        $mysqli->query("UPDATE images SET likes = likes + 1 WHERE id = $imageId");
    } elseif ($action == 'dislike') {
        $mysqli->query("UPDATE images SET dislikes = dislikes + 1 WHERE id = $imageId");
    }

    // 获取更新后的值
    $result = $mysqli->query("SELECT likes, dislikes FROM images WHERE id = $imageId");
    $row = $result->fetch_assoc();
    echo json_encode($row);
    exit;
}

// 初始化图片数据库
$directory = $dir4;
$imagesInDirectory = glob($directory . "/*.png"); // 获取所有 png 图片
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

// 从数据库读取所有图片信息，并随机选取指定数量
$images = [];
$result = $mysqli->query("SELECT id, image_name, likes, dislikes FROM images");
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$selectedImages = array_rand($images, min($picnumber, count($images)));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Image Gallery with Likes and Dislikes</title>
<style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }
    .image-container {
        position: relative;
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    .image {
        width: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i', $_SERVER['HTTP_USER_AGENT']) ? '900px' : '500px'; ?>;
        height: auto;
    }
    .interaction-container {
        position: absolute;
        right: 10px;  /* Slightly inside the right edge of the image */
        top: 50%;     /* Vertically center */
        transform: translateY(-50%); /* Adjust to perfectly center */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .interaction-container button {
        background: transparent; /* Make button background transparent */
        border: none;            /* Remove border if not needed */
        color: white;            /* Change text color to white for visibility */
        font-size: 24px;         /* Adjust font size as needed */
        cursor: pointer;         /* Change cursor to pointer to indicate clickable */
    }
</style>
<script>
function updateLikes(imageId, action) {
    fetch('08_image_management.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `imageId=${imageId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById(`like-${imageId}`).textContent = data.likes;
        document.getElementById(`dislike-${imageId}`).textContent = data.dislikes;
    });
}
</script>
</head>
<body>
<div class="container">
    <?php foreach ($selectedImages as $key): ?>
        <?php $image = $images[$key]; ?>
        <div class="image-container">
            <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>" class="image" alt="Random Image">
            <div class="interaction-container">
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'like')">👍</button>
                <span id="like-<?php echo $image['id']; ?>"><?php echo $image['likes']; ?></span>
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'dislike')">👎</button>
                <span id="dislike-<?php echo $image['id']; ?>"><?php echo $image['dislikes']; ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
