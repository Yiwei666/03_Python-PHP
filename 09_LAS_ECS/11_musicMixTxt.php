<?php

// MP3音频链接文件的路径
$filePath = '/home/01_html/12_music/mp3_paths.txt';

// 从文件中读取MP3音频链接
$links = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// 将链接转换为JavaScript兼容的格式
$jsLinks = json_encode($links);

?>
<!DOCTYPE html>
<html>
  <head>
    <title>MusicMix</title>
    <meta charset="UTF-8">
    <style>
      .audio-player {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 6vh;
      }
      h4, p {
        color: grey;
        text-align: center;
      }
      html, body {
          background: #1e2525;
          height: 100%;
      }
    </style>
    <script>
      var songs = <?php echo $jsLinks; ?>;
      console.log("歌曲列表: " + decodeURIComponent(songs));
      var lastPlayedIndex;

      function playRandomSong() {
        var randomIndex = Math.floor(Math.random() * songs.length);
        // 检查是否已经播放过该歌曲
        while (randomIndex === lastPlayedIndex) {
          randomIndex = Math.floor(Math.random() * songs.length);
        }
        lastPlayedIndex = randomIndex;
        var audio = document.querySelector("audio");
        audio.src = songs[randomIndex];
        console.log("选择的音频链接: " + decodeURIComponent(audio.src));
        audio.play();
      }
    </script>
  </head>
  <body onload="playRandomSong()">
    <p><?php echo 'MusicMix'; ?></p>
    <div class="audio-player">
      <audio controls autoplay onended="playRandomSong()">
        <source src="" type="audio/mpeg">
        您的浏览器不支持音频元素。
      </audio>
    </div>
  </body>
</html>
