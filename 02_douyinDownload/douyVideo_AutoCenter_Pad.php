<!DOCTYPE html>
<html>
<head>
    <title>MP4 视频播放器</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/douyin.png">
    <style>
        :root {
            --video-row-gap: 5px; /* 设置每一行视频之间的距离 */
            --video-column-gap: 10px; /* 设置每个视频之间的水平距离 */
        }
        .video-container {
            display: flex;
            justify-content: center;
            width: 50%; /* 将视频容器的宽度设置为100% */
            flex-wrap: wrap; /* 允许视频容器换行 */
            margin: auto; /* 将视频容器水平居中 */
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
            flex-basis: calc(50% / <?php echo $videosPerRow; ?> - var(--video-column-gap) * 2); /* 设置每个视频的宽度为容器宽度除以每行的视频数量，并考虑水平间距 */
            margin: 0 var(--video-column-gap); /* 使用CSS变量设置每个视频之间的水平距离 */
        }
        .video p {
            text-align: center; /* 让视频文件名在其所在的视频容器中水平居中显示 */
        }


        .top-button,
        .bottom-button {
            position: fixed;
            padding: 10px;
            background-color: #ccc;
            color: #fff;
            text-decoration: none;
        }

        .top-button {
            top: 20px;
            right: 20px;
        }

        .bottom-button {
            bottom: 20px;
            right: 20px;
        }
        
    </style>
</head>
<body>
    <?php
    $domain = 'https://mctea.one';
    $videoPath = '/home/01_html/02_douyVideo/';
    $videos = glob($videoPath . '*.mp4');
    $totalVideos = count($videos);
    $videosPerRow = 2; // 可以根据需要更改每行显示的视频数量

    if ($totalVideos > 0) {
        $rows = ceil($totalVideos / $videosPerRow);

        echo '<div class="video-container">'; // 添加一个新的容器

        $videoIndex = 0;

        for ($i = 0; $i < $rows; $i++) {
            echo '<div class="video-row">';
            for ($j = 0; $j < $videosPerRow; $j++) {
                $video = $videos[$videoIndex];
                $videoName = basename($video);
                $videoUrl = $domain . '/02_douyVideo/' . $videoName;
                echo '<div class="video">';
                echo '<video controls width="450" height="600" onended="playNextVideo(this)">'; // 添加onended事件
                echo '<source src="' . $videoUrl . '" type="video/mp4">';
                echo '</video>';
                echo '<p>' . $videoName . '</p>'; // 添加视频文件名
                echo '</div>';

                $videoIndex++;

                if ($videoIndex >= $totalVideos) {
                    break;
                }
            }
            echo '</div>';
        }

        echo '</div>'; // 关闭新的容器
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

    <a href="#top" class="top-button">返回顶部</a>
    <a href="#bottom" class="bottom-button">返回底部</a>

    <script>
        var topButton = document.querySelector('.top-button');
        topButton.addEventListener('click', function(event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        var bottomButton = document.querySelector('.bottom-button');
        bottomButton.addEventListener('click', function(event) {
            event.preventDefault();
            var windowHeight = window.innerHeight;
            var documentHeight = document.documentElement.scrollHeight;
            window.scrollTo({ top: documentHeight - windowHeight, behavior: 'smooth' });
        });
    </script>
</body>
</html>
