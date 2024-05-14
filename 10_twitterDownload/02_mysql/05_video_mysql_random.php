<?php
session_start();
include '05_db_config.php';

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'your-signing-key-1'; // 应与登录脚本中的密钥一致

// 如果用户未登录，则尝试通过 Cookie 验证身份
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_COOKIE['user_auth'])) {
        $decryptedValue = decrypt($_COOKIE['user_auth'], $key);
        if ($decryptedValue == 'mcteaone') {
            $_SESSION['loggedin'] = true;
        } else {
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

// 如果用户点击了注销链接，注销用户并重定向
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('user_auth', '', time() - 3600, '/');
    header('Location: login.php');
    exit;
}

// 初始化视频数量的变量
$topVideosLimit = 150;

// 从数据库获取排名前 $topVideosLimit 的视频
$videoList = [];
$query = "SELECT video_name FROM videos ORDER BY (likes - dislikes) DESC LIMIT ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $topVideosLimit);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $videoList[] = $row['video_name'];
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Twitter Random Video Player</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #000;
        }
        #videoContainer {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <div id="videoContainer">
        <video id="videoPlayer" width="100%" height="100%" controls autoplay>
            <source id="videoSource" src="" type="video/mp4">
        </video>
    </div>

    <script>
        function playRandomVideo(videoList) {
            // If the video list is empty, reload the list to avoid empty rotations
            if (videoList.length === 0) {
                console.error('All videos have been played.');
                return;
            }

            // Select a random video
            const randomVideoIndex = Math.floor(Math.random() * videoList.length);
            const randomVideoName = videoList.splice(randomVideoIndex, 1)[0];
            const videoUrl = `https://mcha.me/05_twitter_video/${randomVideoName}`;
            const videoPlayer = document.getElementById('videoPlayer');
            const videoSource = document.getElementById('videoSource');
            videoSource.setAttribute('src', videoUrl);
            videoPlayer.load();

            videoPlayer.addEventListener('loadedmetadata', () => {
                const naturalWidth = videoPlayer.videoWidth;
                const naturalHeight = videoPlayer.videoHeight;
                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;
                if (screenWidth < screenHeight) {
                    videoPlayer.style.width = '680px';
                    videoPlayer.style.height = 'auto';
                } else {
                    const aspectRatio = naturalWidth / naturalHeight;
                    if (aspectRatio > 1) {
                        videoPlayer.style.width = '600px';
                        videoPlayer.style.height = 'auto';
                    } else {
                        videoPlayer.style.width = '360px';
                        videoPlayer.style.height = 'auto';
                    }
                }
                videoPlayer.play();
            });
        }

        window.onload = function () {
            var serverVideoList = <?php echo json_encode($videoList); ?>;
            if (serverVideoList.length > 0) {
                const videoPlayer = document.getElementById('videoPlayer');
                videoPlayer.addEventListener('ended', () => playRandomVideo(serverVideoList));
                playRandomVideo(serverVideoList);
            } else {
                console.error('No videos found.');
            }
        };
    </script>
</body>
</html>
