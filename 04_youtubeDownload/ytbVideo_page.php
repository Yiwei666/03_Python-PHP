<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// If the user clicked the logout link, log them out and redirect to the login page
if (isset($_GET['logout'])) {
  session_destroy(); // destroy all session data
  header('Location: login.php');
  exit;
}
?>


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
            flex-basis: calc(100% / <?php echo $videosPerRow; ?> - var(--video-column-gap) * 2);
            margin: 0 var(--video-column-gap);
        }
        .video p {
            text-align: center;
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
        
        .sidebar {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            padding: 10px;
            background-color: #f5f5f5;
        }
        
        .sidebar a {
            display: block;
            margin-bottom: 15px;
            color: #333;
            text-decoration: none;
            font-size: 30px;
        }
        
        .sidebar a.current-page {
            text-decoration: underline;
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
    $videosPerPage = 36;
    $totalPages = ceil($totalVideos / $videosPerPage);
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $startIndex = ($page - 1) * $videosPerPage;
    $endIndex = min($startIndex + $videosPerPage, $totalVideos);

    if ($totalVideos > 0) {
        echo '<div class="video-container">';

        for ($i = $startIndex; $i < $endIndex; $i++) {
            if ($i % $videosPerRow === 0) {
                echo '<div class="video-row">';
            }

            $video = $videos[$i];
            $videoName = basename($video);
            $videoUrl = $domain . '/01_yiGongZi/' . $videoName;
            echo '<div class="video">';
            echo '<button class="loop-button" onclick="toggleLoop(this)">LoopPlay</button>';
            echo '<button class="cancel-loop-button" onclick="cancelLoop(this)" style="display: none;">AutoPlay</button>';
            echo '<video controls width="560" height="420" onended="playNextVideo(this)">';
            echo '<source src="' . $videoUrl . '" type="video/mp4">';
            echo '</video>';
            echo '<p>' . $videoName . '</p>';
            echo '</div>';

            if (($i + 1) % $videosPerRow === 0 || $i === $endIndex - 1) {
                echo '</div>';
            }
        }

        echo '</div>';
    } else {
        echo '没有找到任何视频文件。';
    }
    ?>

    <div class="sidebar" style="background-color: #f5f5f5;">
        <?php
        for ($i = 1; $i <= $totalPages; $i++) {
            $currentPageClass = ($i === $page) ? 'current-page' : '';
            echo '<a href="?page=' . $i . '" class="' . $currentPageClass . '">' . $i . '</a>';
        }
        ?>
    </div>

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

        function toggleLoop(button) {
            var video = button.nextElementSibling.nextElementSibling;
            video.loop = !video.loop;

            var cancelLoopButton = button.nextElementSibling;
            if (video.loop) {
                button.textContent = 'AutoPlay';
                cancelLoopButton.style.display = 'inline-block';
            } else {
                button.textContent = 'LoopPlay';
                cancelLoopButton.style.display = 'none';
            }
        }

        function cancelLoop(button) {
            var video = button.nextElementSibling;
            video.loop = false;
            button.style.display = 'none';
        }

        // 在页面加载完成后，为当前页的链接添加下划线样式
        document.addEventListener('DOMContentLoaded', function() {
            var currentPageLink = document.querySelector('.sidebar a.current-page');
            if (currentPageLink) {
                currentPageLink.style.textDecoration = 'underline';
            }
        });
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