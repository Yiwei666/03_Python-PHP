<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
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
        }

        .page-link {
            margin: 5px;
            padding: 5px;
            text-decoration: none;
            color: #cccccc;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php
$baseUrl = 'http://120.46.81.41/02_LAS1109/35_imageHost/';
$imagesDirectory = '/home/01_html/02_LAS1109/35_imageHost/';
$transferDirectory = '/home/01_html/02_LAS1109/35_imageTransfer/';
$imagesPerPage = 40;

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
    echo '<button class="transfer-button" onclick="transferImage(\'' . $imageUrl . '\')">Transfer</button>';
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
    function transferImage(imageUrl) {
        // 获取图片文件名
        var imageName = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);

        // 使用 AJAX 发送 POST 请求到服务器，只传递文件名
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '05_serverImageTransfer.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    alert('Image transfer information recorded successfully!');
                } else {
                    alert('Error: Unable to record transfer information.');
                }
            }
        };

        // 只传递文件名，不包含时间信息
        xhr.send('imageName=' + encodeURIComponent(imageName));


    }
</script>

</body>
</html>
