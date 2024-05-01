<?php
session_start();
include '08_db_config.php';

$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

$imagesPerPage = 20; // 设置每页显示的图片数量

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

// 分页逻辑
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

// 查询数据库中总图片数量
$result = $mysqli->query("SELECT COUNT(*) as total FROM images");
$row = $result->fetch_assoc();
$totalImages = $row['total'];
$totalPages = ceil($totalImages / $imagesPerPage);

// 计算当前页应该显示的图片
$offset = ($page - 1) * $imagesPerPage;
$query = "SELECT id, image_name, likes, dislikes FROM images LIMIT $imagesPerPage OFFSET $offset";
$images = [];
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

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
        flex-wrap: wrap;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        background: #f0f0f0;
    }
    .container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 1700px;
    }
    .image-container {
        width: 400px;
        height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 10px;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .image {
        max-width: 100%;
        max-height: 80%;
        margin-bottom: 10px;
    }
    .interaction-container {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        padding: 5px;
    }
    .sidebar {
        position: fixed;
        right: 0;
        top: 0;
        width: 100px;
        height: 100vh;
        background-color: #f9f9f9;
        overflow-y: auto;
        box-shadow: -3px 0 5px rgba(0,0,0,0.2);
        z-index: 1000;
    }
    nav ul {
        display: block;
        padding: 10px;
    }
    nav ul li {
        padding: 5px;
    }
    nav ul li a {
        text-decoration: none;
        color: blue;
        display: block;
    }
    .active-page {
        text-decoration: underline;
        color: red;
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

// 在页面加载时恢复滚动位置
document.addEventListener("DOMContentLoaded", function() {
    if (localStorage.getItem('sidebarScrollPos')) {
        document.querySelector('.sidebar').scrollTop = localStorage.getItem('sidebarScrollPos');
    }
});

// 在页面卸载时保存滚动位置
window.onbeforeunload = function() {
    localStorage.setItem('sidebarScrollPos', document.querySelector('.sidebar').scrollTop);
};
</script>
</head>
<body>
<div class="container">
    <?php foreach ($images as $image): ?>
        <div class="image-container">
            <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>" class="image" alt="Image" loading="lazy">
            <div class="interaction-container">
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'like')">👍</button>
                <span id="like-<?php echo $image['id']; ?>"><?php echo $image['likes']; ?></span>
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'dislike')">👎</button>
                <span id="dislike-<?php echo $image['id']; ?>"><?php echo $image['dislikes']; ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="sidebar">
    <nav>
        <ul>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active-page' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>
