<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/gallary.png">
    <title>Image Gallery Simple</title>
    <style>
        body {
            text-align: center;
            background-color: #303030; /* 灰黑色背景 */
            color: #cccccc; /* 设置文本颜色为白色 */            
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
            margin: 30px; /* 上边距:0, 右边距:10px, 下边距:10px, 左边距:0 */
            border-radius: 15px;
            overflow: hidden;
            background-color: #1a1c1d; /* 灰黑色背景 */
        }

        .gallery img {
            width: 100%;
            height: auto;  /* auto */
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
$baseUrl = 'http://120.46.81.41/02_LAS1109/35_imageTransfer/';
$imagesDirectory = '/home/01_html/02_LAS1109/35_imageTransfer/';
$imagesPerPage = 60;

// Get all PNG images in the directory
$images = glob($imagesDirectory . '*.png');
$totalImages = count($images);

// Calculate total pages
$totalPages = ceil($totalImages / $imagesPerPage);

// Get current page from the query string, default to 1
$currentpage = isset($_GET['page']) ? $_GET['page'] : 1;

// Validate current page value
$currentpage = max(1, min($currentpage, $totalPages));

// Calculate offset for the images array
$offset = ($currentpage - 1) * $imagesPerPage;

// Get images for the current page
$currentImages = array_slice($images, $offset, $imagesPerPage);

// Display images
echo '<div class="gallery">';
foreach ($currentImages as $image) {
    $imageName = basename($image);
    $imageUrl = $baseUrl . $imageName;
    echo '<div class="gallery-item">';
    echo '<a href="' . $imageUrl . '" target="_blank">'; // 添加超链接，target="_blank" 在新页面打开
    echo '<img src="' . $imageUrl . '" alt="' . $imageName . '">';
    echo '</a>';
    echo '<div class="image-name"><a href="' . $imageUrl . '" target="_blank">' . $imageName . '</a></div>'; // 文件名作为超链接
    echo '</div>';
}
echo '</div>';

// Display pagination links
echo '<div class="page-links">';
for ($i = 1; $i <= $totalPages; $i++) {
    echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
}
echo '</div>';
?>

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
