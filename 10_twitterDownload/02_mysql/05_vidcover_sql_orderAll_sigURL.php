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

include '05_db_sync_videos.php';
$dir4 = '/home/01_html/05_twitter_video/';
syncVideos($dir4); // 同步目录和数据库中的视频文件

include '05_db_status_size.php';        // 将服务器中存在的视频写入到mysql数据库中
include '05_db_video_cover.php';        // 将服务器中存在的视频生成图片封面

include '05_db_config.php';

// 设置视频和封面所在的文件夹和对应的域名路径
$videoDir = "/home/01_html/05_twitter_video";
$coverDir = "/home/01_html/05_video_cover";
$dir5 = str_replace("/home/01_html", "", $videoDir);
$domain = "https://mcha.me";

// 生成带有签名的URL
function generateSignedUrl($videoName) {
    $signingKey = 'your-signing-key-2'; // 签名密钥，确保与Node.js中的一致
    $expiryTime = time() + 600; // URL有效期为10分钟
    $signature = hash_hmac('sha256', $videoName . $expiryTime, $signingKey);
    global $domain, $dir5;
    return "{$domain}{$dir5}/{$videoName}?expires={$expiryTime}&signature={$signature}";
}

// 设置每页显示的视频数量
$videosPerPage = 8;

// 获取数据库中所有视频的记录
$query = "SELECT id, video_name, likes, dislikes, exist_status FROM videos";
$result = $mysqli->query($query);

// 获取所有视频记录，并排序
$videos = [];
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}

// 对视频按照 (likes - dislikes) 排序
usort($videos, function ($a, $b) {
    return ($b['likes'] - $b['dislikes']) - ($a['likes'] - $a['dislikes']);
});

// 计算视频总数，并基于此重新分页
$totalVideos = count($videos);
$totalPages = ceil($totalVideos / $videosPerPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

// 计算当前页要显示的视频
$offset = ($page - 1) * $videosPerPage;
$videosToDisplay = array_slice($videos, $offset, $videosPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Video Gallery with Likes and Dislikes</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f0f0f0;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1700px;
        }
        .video-container {
            width: 400px;
            height: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 10px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .video-cover {
            width: 100%;
            height: 80%;
            margin-bottom: 10px;
            background-size: contain; /* 修改此属性以适应图片不超出容器 */
            background-position: center;
            background-repeat: no-repeat;
        }
        .interaction-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }
        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 100px;
            height: 100vh;
            background-color: #f9f9f9;
            overflow-y: auto;
            box-shadow: -3px 0 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        nav ul {
            display: block;
            padding: 10px;
        }
        nav ul li {
            padding: 5px;
        }
        nav ul li a {
            text-decoration: none;
            color: blue;
            display: block;
        }
        .active-page {
            text-decoration: underline;
            color: red;
        }
        .exist-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
    <script>
    function updateLikes(videoId, action) {
        fetch('05_video_management.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `videoId=${videoId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById(`like-${videoId}`).textContent = data.likes;
            document.getElementById(`dislike-${videoId}`).textContent = data.dislikes;
        });
    }

    function shareVideo(videoName) {
        const url = `051_videoPlayer_sigURL.php?video=${encodeURIComponent(videoName)}`;
        window.open(url, '_blank');
    }

    // 在页面加载时恢复滚动位置
    document.addEventListener("DOMContentLoaded", function() {
        if (localStorage.getItem('sidebarScrollPos')) {
            document.querySelector('.sidebar').scrollTop = localStorage.getItem('sidebarScrollPos');
        }
    });

    // 在页面卸载时保存滚动位置
    window.onbeforeunload = function() {
        localStorage.setItem('sidebarScrollPos', document.querySelector('.sidebar').scrollTop);
    };
    </script>
</head>
<body>
<div class="container">
    <?php foreach ($videosToDisplay as $video): ?>
        <div class="video-container">
            <div class="video-cover" style="background-image: url('<?php echo $domain . str_replace('/home/01_html', '', $coverDir) . '/' . htmlspecialchars(basename($video['video_name'], ".mp4")) . '.png'; ?>');" alt="Video Cover"></div>
            <div class="interaction-container">
                <?php if ($video['exist_status'] == 1): ?>
                    <span class="exist-icon" title="Exists">&#9989;</span> <!-- 绿色对勾图标 -->
                <?php else: ?>
                    <span class="exist-icon" title="Not Exists">&#10060;</span> <!-- 红色叉号图标 -->
                <?php endif; ?>
                <button onclick="updateLikes(<?php echo $video['id']; ?>, 'like')">👍</button>
                <span id="like-<?php echo $video['id']; ?>"><?php echo $video['likes']; ?></span>
                <button onclick="updateLikes(<?php echo $video['id']; ?>, 'dislike')">👎</button>
                <span id="dislike-<?php echo $video['id']; ?>"><?php echo $video['dislikes']; ?></span>
                <button onclick="shareVideo('<?php echo htmlspecialchars($video['video_name']); ?>')">🔗</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="sidebar">
    <nav>
        <ul>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active-page' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>
