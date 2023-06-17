<!DOCTYPE html>
<html>
<head>
    <title>MP4 视频播放器</title>
    <style>
        :root {
            --video-row-gap: 10px; /* 设置每一行视频之间的距离 */
            --video-column-gap: 10px; /* 设置每个视频之间的水平距离 */
        }
        .video-container {
            display: flex;
            justify-content: center;
            width: 100%; /* 将视频容器的宽度设置为100% */
            flex-wrap: wrap; /* 允许视频容器换行 */
        }
        .video-row {
            display: flex;
            justify-content: center; /* 将视频水平居中显示 */
            margin-bottom: var(--video-row-gap); /* 使用CSS变量设置每一行视频之间的距离 */
        }
        .video-row:not(:last-child) {
            margin-bottom: 0; /* 修改为0，取消行之间的距离 */
        }
        .video-row .video {
            flex-basis: calc(100% / <?php echo $videosPerRow; ?> - var(--video-column-gap) * 2); /* 设置每个视频的宽度为容器宽度除以每行的视频数量，并考虑水平间距 */
            margin: 0 var(--video-column-gap); /* 使用CSS变量设置每个视频之间的水平距离 */
        }
    </style>
</head>
<body>
    <?php
    $domain = 'https://mctea.one';
    $videoPath = '/home/01_html/01_yiGongZi/';
    $videos = glob($videoPath . '*.mp4');
    $totalVideos = count($videos);
    $videosPerRow = 3; // 可以根据需要更改每行显示的视频数量

    if ($totalVideos > 0) {
        $rows = ceil($totalVideos / $videosPerRow);

        echo '<div class="video-container">'; // 添加一个新的容器

        for ($i = 0; $i < $rows; $i++) {
            echo '<div class="video-row">';
            for ($j = 0; $j < $videosPerRow; $j++) {
                $index = $i * $videosPerRow + $j;
                if ($index < $totalVideos) {
                    $video = $videos[$index];
                    $videoName = basename($video);
                    $videoUrl = $domain . '/01_yiGongZi/' . $videoName;
                    echo '<div class="video">';
                    echo '<video controls width="320" height="240">';
                    echo '<source src="' . $videoUrl . '" type="video/mp4">';
                    echo '</video>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }

        echo '</div>'; // 关闭新的容器
    } else {
        echo '没有找到任何视频文件。';
    }
    ?>
</body>
</html>
