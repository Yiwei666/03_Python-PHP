<?php
session_start();

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'signin-key-1'; // 应与加密时使用的密钥相同

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


include '08_db_config.php';
include '08_db_sync_images.php';
syncImages("/home/01_html/08_x/image/01_imageHost"); // 调用函数并提供图片存储目录

// 设置图片所在的文件夹
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

// 设置每页显示的图片数量
$imagesPerPage = 20;

// 获取数据库中标记为存在的所有图片的记录
// $query = "SELECT id, image_name, likes, dislikes FROM images WHERE image_exists = 1";
// $query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1";
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1";
$result = $mysqli->query($query);

// 按照 (likes - dislikes) 排序
$validImages = [];
while ($row = $result->fetch_assoc()) {
    $validImages[] = $row;
}

usort($validImages, function ($a, $b) {
    return ($b['likes'] - $b['dislikes']) - ($a['likes'] - $a['dislikes']);
});

// 计算实际存在的图片数量，并基于此重新分页
$totalImages = count($validImages);
$totalPages = ceil($totalImages / $imagesPerPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

// 计算当前页要显示的图片
$offset = ($page - 1) * $imagesPerPage;
$imagesToDisplay = array_slice($validImages, $offset, $imagesPerPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order starT</title>
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
        .star-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            transition: color 0.3s ease;
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

    // 对应图片收藏或取消操作
    function toggleStar(imageId) {
        fetch('08_db_toggle_star.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `imageId=${imageId}`
        })
        .then(response => response.json())
        .then(data => {
            // 更新五角星按钮的颜色
            const starBtn = document.getElementById(`star-${imageId}`);
            starBtn.style.color = data.star == 1 ? 'green' : 'red';
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
    <?php foreach ($imagesToDisplay as $image): ?>
        <div class="image-container">
            <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>" class="image" alt="Image" loading="lazy">
            <div class="interaction-container">
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'like')">👍</button>
                <span id="like-<?php echo $image['id']; ?>"><?php echo $image['likes']; ?></span>
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'dislike')">👎</button>
                <span id="dislike-<?php echo $image['id']; ?>"><?php echo $image['dislikes']; ?></span>
                <button onclick="window.open('<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>', '_blank')">🔗</button>
                <button onclick="window.open('08_image_leftRight_navigation_starT.php?id=<?php echo $image['id']; ?>&sort=1', '_blank')">🔁</button>
                <!-- 五角星收藏按钮，颜色根据数据库中的 star 值动态设置 -->
                <button id="star-<?php echo $image['id']; ?>" class="star-btn" 
                    onclick="toggleStar(<?php echo $image['id']; ?>)" 
                    style="color: <?php echo ($image['star'] == 1) ? 'green' : 'red'; ?>;">
                    ★
                </button>
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
