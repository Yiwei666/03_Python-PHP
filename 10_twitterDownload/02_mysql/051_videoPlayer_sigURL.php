<?php
session_start();

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'your-signing-key-1'; // 应与加密时使用的密钥相同

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


// 生成视频URL签名

function generateSignedUrl($videoName, $key, $expiryTime) {
    $signature = hash_hmac('sha256', $videoName . $expiryTime, $key);
    // 调试输出生成的签名
    // echo "PHP generated signature: " . $signature . "<br>";
    // echo "PHP videoName: " . $videoName . "<br>";
    // echo "PHP Expiry Time: " . $expiryTime . "<br>";
    return "https://mcha.me/05_twitter_video/{$videoName}?expires={$expiryTime}&signature={$signature}";
}

$videoName = urldecode($_GET['video']);
$videoName = basename($videoName, ".mp4");

$signingKey = 'your-signing-key-2';       // 签名密钥，确保与Node.js中的一致
$expiryTime = time() + 600;

$videoUrl = generateSignedUrl($videoName . ".mp4", $signingKey, $expiryTime);
?>

<!DOCTYPE html>
<html>
<head>
    <title>播放视频</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        video {
            width: auto;
            height: 100%;
        }
    </style>
</head>
<body>
    <video id="videoPlayer" controls></video>
    <script>
        // 定义视频的路径
        const videoUrl = '<?php echo $videoUrl; ?>';

        // 获取 video 元素
        const videoPlayer = document.getElementById('videoPlayer');

        // 创建并添加视频源
        const sourceElement = document.createElement('source');
        sourceElement.src = videoUrl;
        sourceElement.type = 'video/mp4';
        videoPlayer.appendChild(sourceElement);

        videoPlayer.addEventListener('loadedmetadata', function() {
            if (videoPlayer.videoWidth > videoPlayer.videoHeight) {
                videoPlayer.style.width = '100%';
                videoPlayer.style.height = 'auto';
            } else {
                videoPlayer.style.width = 'auto';
                videoPlayer.style.height = '100%';
            }
        });

        /*
        // 页面加载时，从 localStorage 获取保存的播放时间并设置
        document.addEventListener('DOMContentLoaded', function() {
            const lastPlayedTime = localStorage.getItem('lastPlayedTime');
            if (lastPlayedTime) {
                videoPlayer.currentTime = parseFloat(lastPlayedTime);
            }
            videoPlayer.play();
        });

        // 保存视频当前播放时间到 localStorage
        videoPlayer.addEventListener('timeupdate', function() {
            localStorage.setItem('lastPlayedTime', videoPlayer.currentTime);
        });
        */
    </script>
</body>
</html>
