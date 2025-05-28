<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/gallary.png">
    <title>Image Gallery</title>
    <style>
        body {
            text-align: center;
            background-color: #303030;
            color: #cccccc;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .gallery-item {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            width: 400px;
            height: 400px;
            margin: 30px;
            border-radius: 15px;
            overflow: hidden;
            background-color: #1a1c1d;
        }

        .gallery img {
            width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 15px;
        }

        .image-name {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.8);
            padding: 5px;
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: center;
        }

        .transfer-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }

        .page-links {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            overflow-y: auto; /* 当内容超出高度时显示滚动条 */
            padding-right: 5px; /* 为滚动条留出空间，避免内容被遮挡 */
            width: 100px; /* 设置侧边栏的宽度 */
            max-height: 90vh; /* 设置侧边栏的最大高度为视口高度的90% */
        }

        .page-link {
            margin: 5px;
            padding: 5px;
            text-decoration: none;
            color: #cccccc;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .current-page {
            text-decoration: underline; /* 下划线 */
            color: blue; /* 文字颜色变为红色 */
            font-weight: bold; /* 加粗显示 */
        }
    </style>
</head>

<body>

<?php
$baseUrl = 'http://120.46.81.41/02_LAS1109/35_imageHost/';
$imagesDirectory = '/home/01_html/02_LAS1109/35_imageHost/';
$imagesPerPage = 40;

// 读取图片转移记录文件
$serverScript = '05_serverImageTransfer.php';
$transferFile = '/home/01_html/05_imageTransferName.txt';
$transferredImages = [];

if (file_exists($transferFile)) {
    $lines = file($transferFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // 解析每行，以逗号分隔
        $parts = explode(',', $line);
        if (count($parts) >= 1) {
            // 获取文件名并拼接完整的文件路径
            $imageName = trim($parts[0]); // 去除可能的空格
            $imagePath = $imagesDirectory . $imageName;
            $transferredImages[] = $imagePath;
        }
    }
}

// 获取所有 PNG 图片
// $allImages = glob($imagesDirectory . '*.png');
// 同时匹配 png、jpg、jpeg
$allImages = array_merge(
    glob($imagesDirectory . '*.png')  ?: [],
    glob($imagesDirectory . '*.jpg')  ?: [],
    glob($imagesDirectory . '*.jpeg')?: []
);

// 获取未转移的图片（差集）
$remainingImages = array_diff($allImages, $transferredImages);

$totalImages = count($remainingImages);
$totalPages = ceil($totalImages / $imagesPerPage);
$currentpage = isset($_GET['page']) ? $_GET['page'] : 1;
$currentpage = max(1, min($currentpage, $totalPages));
$offset = ($currentpage - 1) * $imagesPerPage;
$currentImages = array_slice($remainingImages, $offset, $imagesPerPage);

// 显示图片
echo '<div class="gallery">';
foreach ($currentImages as $image) {
    $imageName = basename($image);
    $imageUrl = $baseUrl . $imageName;
    echo '<div class="gallery-item">';
    echo '<button class="transfer-button" onclick="transferImage(\'' . $imageUrl . '\')">Transfer</button>';
    echo '<a href="' . $imageUrl . '" target="_blank">';
    echo '<img src="' . $imageUrl . '" alt="' . $imageName . '">';
    echo '</a>';
    echo '<div class="image-name"><a href="' . $imageUrl . '" target="_blank">' . $imageName . '</a></div>';
    echo '</div>';
}
echo '</div>';

// 显示分页链接
echo '<div class="page-links">';
for ($i = 1; $i <= $totalPages; $i++) {
    echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
}
echo '</div>';
?>

<script>
    var serverScriptUrl = '<?php echo $serverScript; ?>'; // 服务器端处理脚本的URL

    function transferImage(imageUrl) {
        // 提示用户输入密码
        var userPassword = prompt('Please enter the password to transfer the image:');

        // 如果用户取消输入，则退出
        if (!userPassword) {
            alert('Password input canceled.');
            return;
        }

        // 获取图片的文件名
        var imageName = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);

        // 创建 XMLHttpRequest 对象
        var xhr = new XMLHttpRequest();
        xhr.open('POST', serverScriptUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // 设置回调函数，处理服务器端响应
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    if (xhr.responseText === 'success') {
                        alert('Image transfer information recorded successfully!');
                    }
                } else if (xhr.status == 403) { // 处理密码错误的情况
                    if (xhr.responseText.includes('error: incorrect password')) {
                        alert('Incorrect password. Please try again.');
                    }
                } else if (xhr.status == 400) { // 处理 imageName 参数缺失的情况
                    if (xhr.responseText.includes('error: imageName parameter is missing')) {
                        alert('Error: image name is missing.');
                    }
                } else {
                    alert('Error: Unable to record transfer information.');
                }
            }
        };

        // 发送图片名称和用户输入的密码到服务器端进行验证
        xhr.send('imageName=' + encodeURIComponent(imageName) + '&password=' + encodeURIComponent(userPassword));
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var pageLinksSidebar = document.querySelector('.page-links');

        // 恢复滚动位置
        var savedScrollPosition = localStorage.getItem('pageLinksScrollPosition');
        if (savedScrollPosition) {
            pageLinksSidebar.scrollTop = parseInt(savedScrollPosition, 10);
        }

        // 高亮显示当前页面
        var currentPageNumber = window.location.search.match(/page=(\d+)/) ? window.location.search.match(/page=(\d+)/)[1] : 1;
        var currentPageLink = document.querySelector('.page-links a[href*="page=' + currentPageNumber + '"]');
        if (currentPageLink) {
            currentPageLink.classList.add('current-page');
        }
    });

    window.addEventListener('beforeunload', function() {
        var pageLinksSidebar = document.querySelector('.page-links');
        if (pageLinksSidebar) {
            localStorage.setItem('pageLinksScrollPosition', pageLinksSidebar.scrollTop.toString());
        }
    });
</script>
    
</body>

</html>
