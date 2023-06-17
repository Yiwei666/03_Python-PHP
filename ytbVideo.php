<!DOCTYPE html>
<html>
<head>
    <title>MP4 视频播放器</title>
    <style>
        .video-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .video-row .video {
            flex-basis: 30%;
        }
    </style>
</head>
<body>
    <?php
    $domain = 'https://mctea.one';
    $videoPath = '/home/01_html/01_yiGongZi/';
    $videos = glob($videoPath . '*.mp4');
    $totalVideos = count($videos);

    if ($totalVideos > 0) {
        $rows = ceil($totalVideos / 3);

        for ($i = 0; $i < $rows; $i++) {
            echo '<div class="video-row">';
            for ($j = 0; $j < 3; $j++) {
                $index = $i * 3 + $j;
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
    } else {
        echo '没有找到任何视频文件。';
    }
    ?>
</body>
</html>
