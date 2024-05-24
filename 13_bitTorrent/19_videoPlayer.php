<?php
$videoName = urldecode($_GET['video']);
// 去除视频名称中的.mp4后缀，如果存在
$videoName = basename($videoName, ".mp4");
// 构建视频URL
$videoUrl = "https://mctea.one/19_bitTorrent/video/" . urlencode($videoName) . ".mp4";
// 构建字幕URL
$srtUrl = "https://mctea.one/19_bitTorrent/video/" . urlencode($videoName) . ".srt";
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
            width: 50%;
            height: auto;
        }
    </style>
</head>
<body>
    <video id="videoPlayer" controls></video>
    <script>
        // 定义视频和字幕的路径
        const videoUrl = '<?php echo $videoUrl; ?>';
        const subtitlesUrl = '<?php echo $srtUrl; ?>';

        // 获取 video 元素
        const videoPlayer = document.getElementById('videoPlayer');

        // 创建并添加视频源
        const sourceElement = document.createElement('source');
        sourceElement.src = videoUrl;
        sourceElement.type = 'video/mp4';
        videoPlayer.appendChild(sourceElement);

        // 加载并转换字幕文件
        fetch(subtitlesUrl)
            .then(response => response.text())
            .then(srtData => {
                // 转换 SRT 到 VTT 格式
                const vttData = srtToVtt(srtData);

                // 使用 Blob 创建一个可用的 VTT URL
                const vttBlob = new Blob([vttData], { type: 'text/vtt' });
                const vttUrl = URL.createObjectURL(vttBlob);

                // 创建并添加字幕轨道
                const trackElement = document.createElement('track');
                trackElement.src = vttUrl;
                trackElement.kind = 'subtitles';
                trackElement.label = '中文字幕';
                trackElement.default = true;
                videoPlayer.appendChild(trackElement);
            });

        // SRT 到 VTT 格式的转换函数
        function srtToVtt(srt) {
            return 'WEBVTT\n\n' + srt
                .replace(/\r/g, '') // 移除 \r
                .replace(/(\d\d:\d\d:\d\d),(\d\d\d)/g, '$1.$2'); // 替换时间码格式
        }
    </script>
</body>
</html>
