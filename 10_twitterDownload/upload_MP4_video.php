<!DOCTYPE html>
<html>
<head>
    <title>多视频上传脚本</title>
</head>
<body>
    <h1>上传多个MP4视频</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="videos[]">选择视频:</label>
        <input type="file" name="videos[]" id="videos" multiple accept=".mp4"><br><br>
        <input type="submit" value="上传" name="submit">
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $uploadDir = '/home/01_html/05_twitter_video/';
        
        // 处理上传的视频
        if (!empty($_FILES['videos']['name'][0])) {
            $totalFiles = count($_FILES['videos']['name']);
            $uploadSuccess = true;

            for ($i = 0; $i < $totalFiles; $i++) {
                $tmpFilePath = $_FILES['videos']['tmp_name'][$i];
                $newFilePath = $uploadDir . basename($_FILES['videos']['name'][$i]);
                $fileType = strtolower(pathinfo($newFilePath, PATHINFO_EXTENSION));

                // 检查文件类型是否为MP4
                if ($fileType === 'mp4') {
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        echo "文件 " . $_FILES['videos']['name'][$i] . " 上传成功！<br>";
                    } else {
                        echo "文件 " . $_FILES['videos']['name'][$i] . " 上传失败！<br>";
                        $uploadSuccess = false;
                    }
                } else {
                    echo "文件 " . $_FILES['videos']['name'][$i] . " 不是有效的MP4视频文件！<br>";
                    $uploadSuccess = false;
                }
            }

            if ($uploadSuccess) {
                echo "<strong>所有文件上传成功！</strong>";
            }
        }
    }
    ?>
</body>
</html>
