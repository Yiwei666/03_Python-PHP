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

// ★ 新增：引入分类操作文件，以便使用 getAllCategories() / getImagesOfCategory()
include '08_image_web_category.php';

// 设置图片所在的文件夹
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

// 获取用户选择的分类（可为空）
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// ★ 新增：获取全部分类（用于左上角按钮点击后显示）
$allCategories = getAllCategories();

// 设置每页显示的图片数量
$imagesPerPage = 20;

// 获取数据库中标记为存在且星标=1的所有图片的记录
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1";
$result = $mysqli->query($query);

// 将结果存入数组
$validImages = [];
while ($row = $result->fetch_assoc()) {
    $validImages[] = $row;
}

// ★ 如果用户选择了某分类，需要进一步筛选仅属于该分类的图片
if ($selectedCategory > 0) {
    $imageIdsInCat = getImagesOfCategory($selectedCategory); // 返回该分类下所有图片ID
    // 在 $validImages 中保留 ID 属于 $imageIdsInCat 的
    $validImages = array_filter($validImages, function($img) use ($imageIdsInCat) {
        return in_array($img['id'], $imageIdsInCat);
    });
    // array_filter() 返回的数组保留索引不变，下面要重新排序索引
    $validImages = array_values($validImages);
}

// 按照 (likes - dislikes) 排序
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

        /* ★ 新增：分类按钮和弹出层的简单样式（可自行美化） */
        .top-left-button {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 2000;
            cursor: pointer;
            padding: 6px 10px;
            background-color: #ccc;
            border: 1px solid #999;
            border-radius: 4px;
        }
        #category-popup {
            display: none;
            position: fixed;
            top: 50px;
            left: 50px;
            width: 250px;
            max-height: 800px;
            overflow-y: auto;
            background-color: white;
            border: 1px solid #999;
            padding: 10px;
            z-index: 3000;
        }
        #category-popup button.close-btn {
            float: right;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        #category-popup ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        #category-popup li {
            margin: 5px 0;
        }
        #category-popup li a {
            color: blue;
            text-decoration: none;
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

    // ★ 新增：显示/隐藏分类弹窗
    function toggleCategoryPopup() {
        const popup = document.getElementById('category-popup');
        if (popup.style.display === 'block') {
            popup.style.display = 'none';
        } else {
            popup.style.display = 'block';
        }
    }

    // ★ 新增：关闭弹窗
    function closeCategoryPopup() {
        document.getElementById('category-popup').style.display = 'none';
    }
    </script>
</head>
<body>
<!-- ★ 新增：左上角分类按钮 -->
<button class="top-left-button" onclick="toggleCategoryPopup()">分类</button>

<!-- ★ 新增：分类弹窗 -->
<div id="category-popup">
    <button class="close-btn" onclick="closeCategoryPopup()">×</button>
    <h4>所有分类</h4>
    <ul>
        <?php foreach ($allCategories as $cat): ?>
            <li>
                <!-- 当用户点击某分类时，跳转到本页面并带上 category=分类ID, 重置 page=1 -->
                <a href="?page=1&category=<?php echo $cat['id']; ?>">
                    <?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="container">
    <?php foreach ($imagesToDisplay as $image): ?>
        <div class="image-container">
            <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>" class="image" alt="Image" loading="lazy">
            <div class="interaction-container">
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'like')">👍</button>
                <span id="like-<?php echo $image['id']; ?>"><?php echo $image['likes']; ?></span>
                <button onclick="updateLikes(<?php echo $image['id']; ?>, 'dislike')">👎</button>
                <span id="dislike-<?php echo $image['id']; ?>"><?php echo $image['dislikes']; ?></span>

                <!-- 打开图片新窗口链接 -->
                <button onclick="window.open('<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>', '_blank')">🔗</button>

                <!-- 
                     当点击“🔁”按钮时，需要同时传递分类ID给 08_image_leftRight_navigation_starT.php，
                     参数名可自定义，如 cat=xxx
                -->
                <button onclick="window.open('08_image_leftRight_navigation_starT.php?id=<?php echo $image['id']; ?>&sort=1&cat=<?php echo $selectedCategory; ?>', '_blank')">
                    🔁
                </button>

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

<!-- 分页导航（保留既有逻辑），也要带上 category 参数 -->
<div class="sidebar">
    <nav>
        <ul>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo $selectedCategory; ?>"
                       class="<?php echo ($page == $i) ? 'active-page' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>
