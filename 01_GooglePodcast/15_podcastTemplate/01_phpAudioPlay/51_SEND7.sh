<?php

// 生成音频链接的路径，一般为domain或ip+文件夹
$baseUrl = 'http://39.105.186.182/51_SEND7/01_audio/';

// vps中音频文件所在的文件夹
$directory = '/home/01_html/51_SEND7/01_audio/';

// 播放器上方的提示信息
$message = '51_SEND7';

$files = array_diff(scandir($directory), array('..', '.'));
$songs = array();
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'mp3') {
        $songs[] = $baseUrl . $file;
    }
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>51_SEND7</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/googlepodcast.png">
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
      var songs = <?php echo json_encode($songs); ?>;
      console.log("songs list: " + decodeURIComponent(songs));
      var lastPlayedIndex;

      function playRandomSong() {
        var randomIndex = Math.floor(Math.random() * songs.length);
        // Check if the song has already been played
        while(randomIndex === lastPlayedIndex){
          randomIndex = Math.floor(Math.random() * songs.length);
        }
        lastPlayedIndex = randomIndex;
        var audio = document.querySelector("audio");
        audio.src = songs[randomIndex];
        console.log("Selected audio URL: " + decodeURIComponent(audio.src));
        audio.play();
      }
    </script>
  </head>
  <body onload="playRandomSong()">
    <p><?php echo $message; ?></p>
    <div class="audio-player">
      <audio controls autoplay onended="playRandomSong()">
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
      </audio>
    </div>
  </body>
</html>
