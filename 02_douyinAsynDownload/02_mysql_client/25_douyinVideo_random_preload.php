<!DOCTYPE html>
<html>
<head>
    <title>Preload Random Video Player</title>
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
        <!-- 用于预加载下一条视频的隐藏 <video> -->
        <video id="preloader" preload="auto"></video>
    </div>

    <script>
        window.onload = function () {
            // 从 PHP 注入的视频列表
            var serverVideoList = <?php
                $videoDirectory = '/home/01_html/03_douyVideoLocal/';
                $videoList = [];
                if ($handle = opendir($videoDirectory)) {
                    while (false !== ($entry = readdir($handle))) {
                        if (is_file($videoDirectory . '/' . $entry)
                            && pathinfo($entry, PATHINFO_EXTENSION) === 'mp4') {
                            $videoList[] = $entry;
                        }
                    }
                    closedir($handle);
                }
                echo json_encode($videoList);
            ?>;

            if (!serverVideoList.length) {
                console.error('No videos found.');
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
