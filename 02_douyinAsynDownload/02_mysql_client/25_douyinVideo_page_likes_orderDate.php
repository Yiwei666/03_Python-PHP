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

// 引入数据库配置
require_once '/home/01_html/03_mysql_douyin/03_db_config.php';
?>


<!DOCTYPE html>
<html>
<head>
    <title>MP4 视频播放器</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/douyin.png">
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

        /* --- CSS样式修改开始 --- */
        .likes-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 1px; /* 原为 5px，减小与下方元素的间距 */
            font-size: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2px 5px;   /* 原为 padding: 5px，减小了上下内边距 */
            user-select: none;
        }
        /* --- CSS样式修改结束 --- */
        
        .like-btn {
            cursor: pointer;
            font-weight: bold;
            font-size: 24px;
            line-height: 1;
        }
        .like-btn:hover {
            transform: scale(1.2);
            transition: transform 0.2s;
        }
        .like-btn.plus { color: #28a745; }
        .like-btn.minus { color: #dc3545; }
        .likes-count { font-weight: 600; }

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
            overflow-y: auto;
            max-height: 80vh; 
            width: 50px; 
            text-align: center; 
        }
        
        .sidebar a {
            display: block;
            margin-bottom: 15px;
            color: #333;
            text-decoration: none;
            font-size: 20px; 
            font-family: 'Times New Roman', Times, serif; 
        }
        
        .sidebar a.current-page {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    $domain = 'http://domain.com';
    $videoPath = '/home/01_html/03_douyVideoLocal/';
    $videos = glob($videoPath . '*.mp4');
    $totalVideos = count($videos);
    $videosPerRow = 2;
    $videosPerPage = 20;
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

            $likes = 0; 
            $stmt = $mysqli->prepare("SELECT likes FROM tk_videos WHERE video_name = ?");
            if ($stmt) {
                $stmt->bind_param("s", $videoName);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $likes = $row['likes'];
                } else {
                    $insert_stmt = $mysqli->prepare("INSERT INTO tk_videos (video_name, create_time, exist_status, likes) VALUES (?, NOW(), 1, 0)");
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("s", $videoName);
                        $insert_stmt->execute();
                        $insert_stmt->close();
                    }
                }
                $stmt->close();
            }
            
            $videoUrl = $domain . '/03_douyVideoLocal/' . $videoName;
            echo '<div class="video">';

            echo '<div class="likes-section" data-video-name="' . htmlspecialchars($videoName) . '">';
            echo '  <span class="like-btn plus" title="赞">+</span>';
            echo '  <span class="likes-count">' . $likes . '</span>';
            echo '  <span class="like-btn minus" title="踩">-</span>';
            echo '</div>';
            
            echo '<button class="loop-button" onclick="toggleLoop(this)">LoopPlay</button>';
            echo '<button class="cancel-loop-button" onclick="cancelLoop(this)" style="display: none;">AutoPlay</button>';
            echo '<video controls width="450" height="600" onended="playNextVideo(this)">';
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.querySelector('.sidebar');
            var currentPageLink = document.querySelector('.sidebar a.current-page');
            if (currentPageLink) {
                currentPageLink.style.textDecoration = 'underline';
            }

            if (localStorage.getItem('sidebarScrollPosition')) {
                sidebar.scrollTop = localStorage.getItem('sidebarScrollPosition');
            }
        });

        window.addEventListener('beforeunload', function() {
            var sidebar = document.querySelector('.sidebar');
            localStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
        });
    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const videoContainer = document.querySelector('.video-container');
        if(videoContainer) {
            videoContainer.addEventListener('click', function(event) {
                if (event.target.matches('.like-btn')) {
                    const button = event.target;
                    const likesSection = button.parentElement;
                    const videoName = likesSection.dataset.videoName;
                    const likesCountSpan = likesSection.querySelector('.likes-count');
                    
                    const action = button.classList.contains('plus') ? 'increment' : 'decrement';

                    const formData = new FormData();
                    formData.append('video_name', videoName);
                    formData.append('action', action);

                    fetch('25_douyin_likes_operation.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            likesCountSpan.textContent = data.likes;
                        } else {
                            console.error('更新likes失败:', data.message);
                            alert('操作失败: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch请求错误:', error);
                        alert('请求时发生错误，请检查网络或联系管理员。');
                    });
                }
            });
        }
    });
    </script>

</body>
</html>
