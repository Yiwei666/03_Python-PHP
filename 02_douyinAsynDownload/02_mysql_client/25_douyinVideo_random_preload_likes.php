<!DOCTYPE html>
<html>
<head>
    <title>Preload Likes Random Video Player</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/douyin.png">
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
            position: relative;
        }
        /* 隐藏预加载播放器 */
        #preloader {
            display: none;
        }
    </style>
</head>
<body>
    <div id="videoContainer">
        <video id="videoPlayer" width="100%" height="100%" controls autoplay>
            <source id="videoSource" src="" type="video/mp4">
        </video>
        <video id="preloader" preload="auto"></video>
    </div>

    <script>
        window.onload = function () {
            // --- PHP代码块修改开始 ---
            // 从 PHP 注入的、经过筛选（likes > 0）的视频列表
            var serverVideoList = <?php
                // 1. 引入数据库配置
                require_once '/home/01_html/03_mysql_douyin/03_db_config.php';

                $videoList = [];
                $videoDirectory = '/home/01_html/03_douyVideoLocal/';

                // 2. 准备 SQL 查询语句，只选择 likes 大于 0 的视频
                $sql = "SELECT video_name FROM tk_videos WHERE likes > 0";
                $result = $mysqli->query($sql);

                // 3. 处理查询结果
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // 健壮性检查：确保数据库中记录的视频文件在物理磁盘上真实存在
                        if (is_file($videoDirectory . $row['video_name'])) {
                            $videoList[] = $row['video_name'];
                        }
                    }
                }

                // 如果没有点赞数大于0的视频，为防止脚本出错，可以考虑一个备用逻辑。
                // 当前逻辑下，若无符合条件的视频，$videoList会是一个空数组，前端JS会提示"No videos found."
                // 例如：if (empty($videoList)) { /* 可在此处加入备用逻辑 */ }

                // 4. 关闭数据库连接
                $mysqli->close();

                // 5. 将筛选后的列表输出为 JSON 格式
                echo json_encode($videoList);
            ?>;
            // --- PHP代码块修改结束 ---

            if (!serverVideoList.length) {
                console.error('No videos with likes > 0 found.');
                // 可以在页面上显示提示信息
                document.body.innerHTML = '<p style="color: white; font-family: sans-serif;">没有找到点赞数大于0的视频。</p>';
                return;
            }

            const player   = document.getElementById('videoPlayer');
            const sourceEl = document.getElementById('videoSource');
            const preloader = document.getElementById('preloader');

            let nextVideoName = null;

            // 随机选一条
            function pickRandom(excludeName) {
                if (serverVideoList.length === 1) return serverVideoList[0];
                let name;
                do {
                    name = serverVideoList[Math.floor(Math.random() * serverVideoList.length)];
                } while (name === excludeName);
                return name;
            }

            // 根据文件名构造完整 URL
            function videoUrl(name) {
                return `http://domain.com/03_douyVideoLocal/${encodeURIComponent(name)}`;
            }

            // 预加载下一条视频
            function preloadNext() {
                nextVideoName = pickRandom(sourceEl.getAttribute('data-current'));
                preloader.src = videoUrl(nextVideoName);
                preloader.load();
            }

            // 调整播放器尺寸并播放
            function onMetadataAndPlay() {
                const w = player.videoWidth, h = player.videoHeight;
                const sw = window.innerWidth, sh = window.innerHeight;

                if (sw < sh) {
                    // 手机竖屏
                    player.style.width = '680px';
                } else {
                    // 桌面横屏
                    const aspect = w / h;
                    player.style.width = aspect > 1 ? '600px' : '360px';
                }
                player.style.height = 'auto';
                player.play();
            }

            // 切换到已预加载的视频
            function playNext() {
                // 将预加载的视频设为当前播放源
                sourceEl.setAttribute('src', preloader.currentSrc || videoUrl(nextVideoName));
                sourceEl.setAttribute('data-current', nextVideoName);
                player.load();

                // 元数据准备好后调整尺寸并播放
                player.addEventListener('loadedmetadata', onMetadataAndPlay, { once: true });

                // 预加载下一条
                preloadNext();
            }

            // 初始播放
            function startPlayback() {
                // 先选一条作为“当前”
                const first = pickRandom(null);
                sourceEl.setAttribute('src', videoUrl(first));
                sourceEl.setAttribute('data-current', first);
                player.load();

                player.addEventListener('loadedmetadata', onMetadataAndPlay, { once: true });

                // 然后预加载下一条
                preloadNext();

                // 播放完毕后直接切换到已预加载的视频
                player.addEventListener('ended', playNext);
            }

            startPlayback();
        };
    </script>
</body>
</html>
