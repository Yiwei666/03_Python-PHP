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
$allImages = glob($imagesDirectory . '*.png');

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
    var serverScriptUrl = '<?php echo $serverScript; ?>';

    function transferImage(imageUrl) {
        var imageName = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', serverScriptUrl, true);
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
        xhr.send('imageName=' + encodeURIComponent(imageName));
    }
</script>

</body>

</html>
