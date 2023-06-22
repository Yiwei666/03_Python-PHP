<!DOCTYPE html>
<html>
<head>
    <title>MP4 视频播放器</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/video.png">
    <style>
        :root {
            --video-row-gap: 5px;
            --video-column-gap: 10px;
        }
        .video-container {
            display: flex;
            justify-content: center;
            width: 50%;
            flex-wrap: wrap;
            margin: auto;
        }
        .video-row {
            display: flex;
            justify-content: center;
            margin-bottom: var(--video-row-gap);
        }
        .video-row:not(:last-child) {
            margin-bottom: 0;
        }
        .video-row .video {
            flex-basis: calc(50% / <?php echo $videosPerRow; ?> - var(--video-column-gap) * 2);
            margin: 0 var(--video-column-gap);
        }
        .video p {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php
    $domain = 'https://mctea.one';
    $videoPath = '/home/01_html/01_yiGongZi/';
    $videos = glob($videoPath . '*.mp4');
    $totalVideos = count($videos);
    $videosPerRow = 2;

    if ($totalVideos > 0) {
        $rows = ceil($totalVideos / $videosPerRow);

        echo '<div class="video-container">';

        $videoIndex = 0;

        for ($i = 0; $i < $rows; $i++) {
            echo '<div class="video-row">';
            for ($j = 0; $j < $videosPerRow; $j++) {
                $video = $videos[$videoIndex];
                $videoName = basename($video);
                $videoUrl = $domain . '/01_yiGongZi/' . $videoName;
                echo '<div class="video">';
                echo '<video controls width="400" height="300" onended="playNextVideo(this)">';
                echo '<source src="' . $videoUrl . '" type="video/mp4">';
                echo '</video>';
                echo '<p>' . $videoName . '</p>';
                echo '</div>';

                $videoIndex++;

                if ($videoIndex >= $totalVideos) {
                    break;
                }
            }
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '没有找到任何视频文件。';
    }
    ?>

    <script>
        function playNextVideo(currentVideo) {
            var videoContainer = currentVideo.parentElement.parentElement;
            var videosInContainer = videoContainer.querySelectorAll('.video');
            var currentIndex = Array.from(videosInContainer).indexOf(currentVideo.parentElement);

            if (currentIndex < videosInContainer.length - 1) {
                var nextVideo = videosInContainer[currentIndex + 1].querySelector('video');
                if (nextVideo) {
                    nextVideo.play();
                    scrollToElement(nextVideo.parentElement);
                    return;
                }
            }

            var nextRow = videoContainer.nextElementSibling;
            if (nextRow) {
                var videosInNextRow = nextRow.querySelectorAll('.video');
                var firstVideo = videosInNextRow[0].querySelector('video');
                if (firstVideo) {
                    firstVideo.play();
                    scrollToElement(firstVideo.parentElement.parentElement);
                }
            }
        }

        function scrollToElement(element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    </script>
</body>
</html>
