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

// 引入分类操作文件，以便使用 getImagesOfCategory()、getCategoriesOfImage() 等
include '08_image_web_category.php';

// 获取传递的图片 ID 和排序算法
$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sortType = isset($_GET['sort']) ? (int)$_GET['sort'] : 1; // 默认为排序1

// ★ 新增：获取传递的分类ID（若存在，则只在该分类内导航）
$catId    = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// 先获取所有满足 image_exists=1 的图片
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1";
$result = $mysqli->query($query);

// 将所有图片存入数组
$validImages = [];
while ($row = $result->fetch_assoc()) {
    $validImages[] = $row;
}

// ★ 若 catId > 0，则仅保留属于该分类的图片ID
if ($catId > 0) {
    $imageIdsInCat = getImagesOfCategory($catId);
    $validImages = array_filter($validImages, function($img) use ($imageIdsInCat) {
        return in_array($img['id'], $imageIdsInCat);
    });
    // 重新索引
    $validImages = array_values($validImages);
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

// 若没找到或数组为空，可能说明该分类下没有这张图
if ($currentIndex === -1) {
    // 可以做一个简单处理，比如退出或显示错误
    die("No image found in this category.");
}

// 计算上一张和下一张图片的索引
$prevIndex = $currentIndex > 0 ? $currentIndex - 1 : -1;
$nextIndex = $currentIndex < count($validImages) - 1 ? $currentIndex + 1 : -1;

// 当前图片信息
$currentImage = $validImages[$currentIndex];
$domain = "https://19640810.xyz";
$dir5 = str_replace("/home/01_html", "", "/home/01_html/08_x/image/01_imageHost");

// 获取当前图片所属的所有分类，然后拼接成字符串
$imageCategories   = getCategoriesOfImage($currentImage['id']);
$imageCategoryNames = array_map(function($c) {
    return $c['category_name'];
}, $imageCategories);
$categoriesText = implode(", ", $imageCategoryNames);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Navigation starTF</title>
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
            cursor: pointer;
        }
        .arrow-left {
            left: 0;
            font-size: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '64px' : '30px'; ?>;
            padding: 10px;
        }
        .arrow-right {
            right: 0;
            font-size: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '64px' : '30px'; ?>;
            padding: 10px;
        }
        .interaction-container {
            position: absolute;
            right: 0;
            /* 在移动端/PC端分别调整大概在右侧中下方的位置 */
            top: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? 'calc(50% + 150px)' : '60%'; ?>;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '30px' : '10px'; ?>;
        }
        .interaction-btn {
            background: none;
            border: none;
            color: white;
            font-size: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '64px' : '30px'; ?>;
            cursor: pointer;
        }
        .interaction-count {
            color: white;
            font-size: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '40px' : '20px'; ?>;
            margin-top: -5px; /* 数字与图标的间距 */
        }

        /* 弹窗相关样式 */
        #category-popup {
            display: none;
            position: fixed;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 70%;
            background-color: white;
            color: black;
            overflow-y: auto;
            z-index: 999;
            border: 2px solid gray;
            border-radius: 10px;
            padding: 20px;
        }
        #category-popup .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 20px;
        }
        #category-list {
            display: flex;
            flex-wrap: wrap;
            /* 五列，每列 20% 宽度 */
        }
        #category-list div {
            width: 20%;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        #category-buttons {
            margin-top: 20px;
            text-align: center;
        }

        /* 右上角显示当前图片所属分类的样式 */
        .image-categories {
            position: absolute;
            top: 10px;
            right: 10px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: blue;
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

        // 打开分类弹窗：获取所有分类 + 当前图片所属分类
        function openCategoryWindow(imageId) {
            fetch('08_image_web_category.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=getCategoriesForImage&imageId=' + imageId
            })
            .then(response => response.json())
            .then(data => {
                // data.allCategories: 所有分类
                // data.imageCategories: 当前图片已关联的分类
                const categoryContainer = document.getElementById('category-list');
                categoryContainer.innerHTML = '';

                // 把当前图片所属的分类ID记录成一个数组, 方便判断是否勾选
                const imageCatIds = data.imageCategories.map(item => item.id);

                data.allCategories.forEach(cat => {
                    // 创建 checkbox
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = cat.category_name;
                    // 如果该分类在 imageCatIds 里则设为已选中
                    checkbox.checked = imageCatIds.includes(cat.id);

                    const label = document.createElement('label');
                    label.style.marginLeft = '5px';
                    label.textContent = cat.category_name;

                    const divItem = document.createElement('div');
                    divItem.appendChild(checkbox);
                    divItem.appendChild(label);

                    categoryContainer.appendChild(divItem);
                });

                // 记录当前操作的 imageId，后续保存时要用
                document.getElementById('save-category-btn').setAttribute('data-image-id', imageId);

                // 显示弹窗
                document.getElementById('category-popup').style.display = 'block';
            });
        }

        // 关闭分类弹窗
        function closeCategoryWindow() {
            document.getElementById('category-popup').style.display = 'none';
        }

        // 保存当前图片的勾选分类
        function saveCategories() {
            const imageId = document.getElementById('save-category-btn').getAttribute('data-image-id');
            // 收集所有勾选的分类名
            const checkboxes = document.querySelectorAll('#category-list input[type="checkbox"]');
            const selected = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selected.push(cb.value);
                }
            });

            // 发送到后端
            fetch('08_image_web_category.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=setImageCategories'
                    + '&imageId=' + imageId
                    + '&categories=' + encodeURIComponent(JSON.stringify(selected))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('分类更新成功！');
                    closeCategoryWindow();
                    // location.reload(); // 可根据需要刷新页面
                } else {
                    alert('分类更新失败: ' + (data.error || '未知错误'));
                }
            });
        }

        // —— 新增：页面加载完后预加载前后两张图片 —— 
        document.addEventListener('DOMContentLoaded', function() {
            // 构建当前分类/排序下所有有效图片的 URL 列表
            var images = <?php echo json_encode(array_map(function($img) use ($domain, $dir5) {
                return $domain . $dir5 . '/' . $img['image_name'];
            }, $validImages)); ?>;
            // 当前图片索引
            var currentIndex = <?php echo $currentIndex; ?>;
            var loaded = {};

            function preloadIndex(idx) {
                if (idx >= 0 && idx < images.length && !loaded[idx]) {
                    var img = new Image();
                    img.src = images[idx];
                    loaded[idx] = true;
                }
            }

            // 预加载前后两张
            preloadIndex(currentIndex - 2);
            preloadIndex(currentIndex - 1);
            preloadIndex(currentIndex + 1);
            preloadIndex(currentIndex + 2);
        });
        // —— 预加载逻辑结束 —— 
    </script>
</head>
<body>
    <div class="image-container">
        <?php if ($prevIndex >= 0): ?>
            <!-- ★ 修改：左右导航箭头也要带上 cat 参数 -->
            <button class="arrow arrow-left"
                    onclick="window.location.href='08_image_leftRight_navigation.php?id=<?php echo $validImages[$prevIndex]['id']; ?>&sort=<?php echo $sortType; ?>&cat=<?php echo $catId; ?>'">
                ←
            </button>
        <?php endif; ?>
        
        <img src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($currentImage['image_name']); ?>" class="image" alt="Image">
        
        <!-- 右上角显示当前图片所属分类 -->
        <div class="image-categories">
            <?php foreach ($imageCategories as $cat): ?>
                <a href="<?php echo ($sortType === 1) ? '08_picDisplay_mysql_orderExistTab.php' : '08_picDisplay_mysql_galleryExistTab.php'; ?>?page=1&category=<?php echo $cat['id']; ?>" target="_blank"><?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endforeach; ?>
        </div>

        <div class="interaction-container">
            <!-- 分类按钮：🎨 -->
            <button class="interaction-btn" onclick="openCategoryWindow(<?php echo $currentImage['id']; ?>)">🎨</button>

            <!-- 点赞按钮 -->
            <button class="interaction-btn" onclick="updateLikes(<?php echo $currentImage['id']; ?>, 'like')">👍</button>
            <span id="like-count" class="interaction-count"><?php echo $currentImage['likes']; ?></span>

            <!-- 点踩按钮 -->
            <button class="interaction-btn" onclick="updateLikes(<?php echo $currentImage['id']; ?>, 'dislike')">👎</button>
            <span id="dislike-count" class="interaction-count"><?php echo $currentImage['dislikes']; ?></span>

            <!-- 收藏按钮 -->
            <button id="star-btn"
                    class="interaction-btn"
                    onclick="toggleStar(<?php echo $currentImage['id']; ?>)"
                    style="color: <?php echo ($currentImage['star'] == 1) ? 'green' : 'red'; ?>;">
                ★
            </button>
        </div>

        <?php if ($nextIndex >= 0): ?>
            <!-- ★ 修改：左右导航箭头也要带上 cat 参数 -->
            <button class="arrow arrow-right"
                    onclick="window.location.href='08_image_leftRight_navigation.php?id=<?php echo $validImages[$nextIndex]['id']; ?>&sort=<?php echo $sortType; ?>&cat=<?php echo $catId; ?>'">
                →
            </button>
        <?php endif; ?>
    </div>

    <!-- 分类弹窗 -->
    <div id="category-popup">
        <button class="close-btn" onclick="closeCategoryWindow()">✖</button>

        <h3>图片分类管理</h3>
        <div id="category-list">
            <!-- 这里通过 JS 动态生成分类 checkbox 列表 -->
        </div>

        <div id="category-buttons">
            <button id="save-category-btn" onclick="saveCategories()">保存</button>
            <button onclick="closeCategoryWindow()">取消</button>
        </div>
    </div>
</body>
</html>
