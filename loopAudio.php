<!DOCTYPE html>
<html>
<head>
    <title>循环播放音频</title>
    <style>
        body {
            text-align: center;
        }

        #container {
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translate(-50%, -15%);
            text-align: center;
        }
    </style>
</head>
<body>
<div id="container">
    <form method="post" action="">
        <label for="audio_url">MP3音频链接:</label>
        <input type="text" id="audio_url" name="audio_url" required>
        <input type="submit" value="播放">
    </form>

    <script>
        // PHP 变量传递到 JavaScript
        var audioUrl = "<?php echo isset($_POST['audio_url']) ? $_POST['audio_url'] : ''; ?>";

        // 播放音频
        function playAudio() {
            var audio = new Audio(audioUrl);
            audio.loop = true;
            audio.play();

            // 定时检查音频是否已经播放完毕
            setInterval(function() {
                if (audio.ended) {
                    // 等待 1.5 秒后再次播放音频
                    setTimeout(function() {
                        audio.play();
                    }, 1500);
                }
            }, 100);
        }

        // 如果音频链接不为空，则播放音频
        if (audioUrl !== "") {
            playAudio();
        }
    </script>
</div>
</body>
</html>
