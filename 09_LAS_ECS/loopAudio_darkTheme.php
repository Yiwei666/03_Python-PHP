<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>循环播放音频</title>
    <style>
        body {
            text-align: center;
            background-color: #222;
            color: #00bcd4;
        }

        #container {
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translate(-50%, -15%);
            text-align: center;
        }

        form {
            margin-bottom: 20px;
        }

        #progressBar {
            width: 100%;
            height: 20px;
            margin-top: 10px;
            background-color: #333;
        }

        #progress {
            height: 100%;
            width: 0;
            background-color: #4caf50;
        }

        #timeDisplay {
            margin-top: 10px;
        }

        input, button {
            color: #00bcd4;
            background-color: #333;
            border: 1px solid #00bcd4;
            padding: 5px 10px;
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

        <button id="toggleBtn" onclick="togglePlayPause()">暂停/播放</button>

        <div id="progressBar">
            <div id="progress"></div>
        </div>

        <div id="timeDisplay">0:00 / 0:00</div>

        <script>
            var audioUrl = "<?php echo isset($_POST['audio_url']) ? $_POST['audio_url'] : ''; ?>";
            var audio = new Audio(audioUrl);

            function playAudio() {
                audio.loop = true;
                audio.play();

                setInterval(function() {
                    if (audio.ended) {
                        setTimeout(function() {
                            audio.play();
                        }, 1500);
                    } else {
                        updateProgressBar();
                        updateTimeDisplay();
                    }
                }, 100);
            }

            function togglePlayPause() {
                if (audio.paused) {
                    audio.play();
                } else {
                    audio.pause();
                }
            }

            function updateProgressBar() {
                var progress = (audio.currentTime / audio.duration) * 100;
                document.getElementById("progress").style.width = progress + "%";
            }

            function updateTimeDisplay() {
                var currentTime = formatTime(audio.currentTime);
                var duration = formatTime(audio.duration);
                document.getElementById("timeDisplay").innerText = currentTime + " / " + duration;
            }

            function formatTime(time) {
                var minutes = Math.floor(time / 60);
                var seconds = Math.floor(time % 60);
                seconds = seconds < 10 ? "0" + seconds : seconds;
                return minutes + ":" + seconds;
            }

            audio.addEventListener('timeupdate', updateProgressBar);

            if (audioUrl !== "") {
                playAudio();
            }
        </script>
    </div>
</body>
</html>
