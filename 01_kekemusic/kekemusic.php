<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>顺序播放音频</title>
    <style>
        /* 容器居中对齐 */
        .container {
            display: flex;
            justify-content: center;
        }

        /* 容器内部左对齐 */
        .audio-player {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 600px;
        }
    </style>
</head>
<body>
    <h4>顺序播放音频</h1>
    <div class="container">
        <div class="audio-player">
            <?php
            // 定义文件路径变量
            $filePath = '/home/01_html/04_kekemusic/finalmusic.txt';
            // 读取文件内容，每个链接占据一行
            $links = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // 逐个播放链接音频
            foreach ($links as $index => $link) {
                echo "<p>正在播放 $link...</p>";
                echo "<audio controls onended=\"playNext($index)\"><source src=\"$link\" type=\"audio/mpeg\"></audio>";
                // 在这里添加调用播放音频的代码，例如使用 HTML5 的 <audio> 标签播放音频
                // 注意：如果音频文件过大，可能需要使用分段播放或者流式传输等技术，以避免占用过多内存或加载时间过长
            }
            ?>
        </div>
    </div>

    <script>
        var audioList = document.getElementsByTagName('audio');

        function playNext(index) {
            if (index < audioList.length - 1) {
                audioList[index + 1].play();
            }
        }

        audioList[0].play();
    </script>
</body>
</html>
