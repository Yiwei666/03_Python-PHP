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

// 引入数据库配置
include '08_db_config.php';

// 获取传递的图片 ID 和排序算法
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sortType = isset($_GET['sort']) ? (int)$_GET['sort'] : 1; // 默认为排序1

// 从数据库中获取所有图片
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1";
$result = $mysqli->query($query);

// 将所有图片存入数组
$validImages = [];
while ($row = $result->fetch_assoc()) {
    $validImages[] = $row;
}

// 根据传递的排序算法选择排序方式
if ($sortType === 1) {
    // 排序1：按照 (likes - dislikes) 排序
    usort($validImages, function ($a, $b) {
        return ($b['likes'] - $b['dislikes']) - ($a['likes'] - $a['dislikes']);
    });
}

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
            font-size: 64px;
            padding: 10px;
            cursor: pointer;
        }
        .arrow-left {
            left: 0;
        }
        .arrow-right {
            right: 0;
        }
        .interaction-container {
            position: absolute;
            right: 0;
            top: calc(50% + 120px); /* 设置点赞区域与右箭头的垂直距离为 20px */
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;             /* 点赞与点踩之间的垂直距离 */
        }
        .interaction-btn {
            background: none;
            border: none;
            color: white;
            font-size: 64px;      /* 图标大小 */
            cursor: pointer;
        }
        .interaction-count {
            color: white;
            font-size: 40px; /* 调整数字大小 */
            margin-top: -5px; /* 数字与图标的间距 */
        }
    </style>
    <script>
        // 点赞和点踩功能
        function updateLikes(imageId, action) {
            fetch('08_image_management.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `imageId=${imageId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById(`like-count`).textContent = data.likes;
                document.getElementById(`dislike-count`).textContent = data.dislikes;
            });
        }

        // 收藏和取消收藏功能
        function toggleStar(imageId) {
            fetch('08_db_toggle_star.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `imageId=${imageId}`
            })
            .then(response => response.json())
            .then(data => {
                const starBtn = document.getElementById(`star-btn`);
                starBtn.style.color = data.star == 1 ? 'green' : 'red';
            });
        }
    </script>
</head>
<body>
    <div class="image-container">
        <?php if ($prevIndex >= 0): ?>
            <button class="arrow arrow-left" onclick="window.location.href='08_image_leftRight_navigation_voteStar.php?id=<?php echo $validImages[$prevIndex]['id']; ?>&sort=<?php echo $sortType; ?>'">←</button>
        <?php endif; ?>
        
        <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($currentImage['image_name']); ?>" class="image" alt="Image">
        
        <div class="interaction-container">
            <!-- 点赞按钮 -->
            <button class="interaction-btn" onclick="updateLikes(<?php echo $currentImage['id']; ?>, 'like')">👍</button>
            <span id="like-count" class="interaction-count"><?php echo $currentImage['likes']; ?></span>

            <!-- 点踩按钮 -->
            <button class="interaction-btn" onclick="updateLikes(<?php echo $currentImage['id']; ?>, 'dislike')">👎</button>
            <span id="dislike-count" class="interaction-count"><?php echo $currentImage['dislikes']; ?></span>

            <!-- 收藏按钮 -->
            <button id="star-btn" class="interaction-btn" onclick="toggleStar(<?php echo $currentImage['id']; ?>)" style="color: <?php echo ($currentImage['star'] == 1) ? 'green' : 'red'; ?>;">★</button>
        </div>

        <?php if ($nextIndex >= 0): ?>
            <button class="arrow arrow-right" onclick="window.location.href='08_image_leftRight_navigation_voteStar.php?id=<?php echo $validImages[$nextIndex]['id']; ?>&sort=<?php echo $sortType; ?>'">→</button>
        <?php endif; ?>
    </div>
</body>
</html>
