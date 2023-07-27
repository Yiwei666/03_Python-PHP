<?php

function generateAudioLinks($directory, $baseUrl)
{
    $files = array_diff(scandir($directory), array('..', '.'));
    $songs = array();
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'mp3') {
            $songs[] = $baseUrl . $file;
        }
    }
    return $songs;
}

// 播放器1对应的音频文件夹和提示信息
$directory1 = '/home/01_html/11_wordsmart/';
$message1 = '11_wordsmart';
$songs1 = generateAudioLinks($directory1, 'http://101.200.215.127/11_wordsmart/');

// 播放器2对应的音频文件夹和提示信息
$directory2 = '/home/01_html/13_PlanetEarth/';
$message2 = '13_PlanetEarth';
$songs2 = generateAudioLinks($directory2, 'http://101.200.215.127/13_PlanetEarth/');

// 播放器3对应的音频文件夹和提示信息
$directory3 = '/home/01_html/12_EssentialWord/';
$message3 = '12_EssentialWord';
$songs3 = generateAudioLinks($directory3, 'http://101.200.215.127/12_EssentialWord/');

$directory4 = '/home/01_html/04_CET6/';
$message4 = '04_CET6';
$songs4 = generateAudioLinks($directory4, 'http://101.200.215.127/04_CET6/');

$directory5 = '/home/01_html/05_NewConcept2/';
$message5 = '05_NewConcept2';
$songs5 = generateAudioLinks($directory5, 'http://101.200.215.127/05_NewConcept2/');

$directory6 = '/home/01_html/06_TOEFL/';
$message6 = '06_TOEFL';
$songs6 = generateAudioLinks($directory6, 'http://101.200.215.127/06_TOEFL/');

$directory7 = '/home/01_html/07_NewConcept3/';
$message7 = '07_NewConcept3';
$songs7 = generateAudioLinks($directory7, 'http://101.200.215.127/07_NewConcept3/');

$directory8 = '/home/01_html/08_NewConcept4/';
$message8 = '08_NewConcept4';
$songs8 = generateAudioLinks($directory8, 'http://101.200.215.127/08_NewConcept4/');

$directory9 = '/home/01_html/10_VerbalAdvantage/';
$message9 = '10_VerbalAdvantage';
$songs9 = generateAudioLinks($directory9, 'http://101.200.215.127/10_VerbalAdvantage/');

$directory10 = '/home/01_html/17_CET4/';
$message10 = '17_CET4';
$songs10 = generateAudioLinks($directory10, 'http://101.200.215.127/17_CET4/');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Multiple MusicMix</title>
    <meta charset="UTF-8">
    <style>
        .audio-player {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 6vh;
        }

        p {
            color: white; /* 设置文字颜色为白色 */
            font-size: 18px; /* 设置字号为18像素 */
            font-weight: bold; /* 设置文字加粗效果 */
            text-align: center;
        }

        html, body {
            background: #1e2525;
            height: 100%;
        }
    </style>
    <script>
        <!-- 需要修改 -->
        var songs1 = <?php echo json_encode($songs1); ?>;
        var songs2 = <?php echo json_encode($songs2); ?>;
        var songs3 = <?php echo json_encode($songs3); ?>;
        var songs4 = <?php echo json_encode($songs4); ?>;
        var songs5 = <?php echo json_encode($songs5); ?>;
        var songs6 = <?php echo json_encode($songs6); ?>;
        var songs7 = <?php echo json_encode($songs7); ?>;
        var songs8 = <?php echo json_encode($songs8); ?>;
        var songs9 = <?php echo json_encode($songs9); ?>;
        var songs10 = <?php echo json_encode($songs10); ?>;
        var lastPlayedIndex;

        function playRandomSong(songs, audioId) {
            var randomIndex = Math.floor(Math.random() * songs.length);
            // Check if the song has already been played
            while (randomIndex === lastPlayedIndex) {
                randomIndex = Math.floor(Math.random() * songs.length);
            }
            lastPlayedIndex = randomIndex;
            var audio = document.getElementById(audioId);
            audio.src = songs[randomIndex];
            console.log("Selected audio URL: " + decodeURIComponent(audio.src));
            audio.play();
        }

        function loadRandomSong(songs, audioId) {
            var randomIndex = Math.floor(Math.random() * songs.length);
            var audio = document.getElementById(audioId);
            audio.src = songs[randomIndex];
            console.log("Selected audio URL: " + decodeURIComponent(audio.src));
        }
    </script>
</head>
<!-- 需要修改 -->
<body onload="loadRandomSong(songs1, 'audio1'); loadRandomSong(songs2, 'audio2'); loadRandomSong(songs3, 'audio3'); loadRandomSong(songs4, 'audio4'); loadRandomSong(songs5, 'audio5'); loadRandomSong(songs6, 'audio6'); loadRandomSong(songs7, 'audio7'); loadRandomSong(songs8, 'audio8'); loadRandomSong(songs9, 'audio9'); loadRandomSong(songs10, 'audio10')">
    
    <p><?php echo $message1; ?></p>
    <div class="audio-player">
        <audio id="audio1" controls onended="playRandomSong(songs1, 'audio1')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs1, 'audio1')" style="display: none;">播放</button>
    </div>

    <!-- 播放器2 -->
    <p><?php echo $message2; ?></p>
    <div class="audio-player">
        <audio id="audio2" controls onended="playRandomSong(songs2, 'audio2')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs2, 'audio2')" style="display: none;">播放</button>
    </div>

    <!-- 播放器3 -->
    <p><?php echo $message3; ?></p>
    <div class="audio-player">
        <audio id="audio3" controls onended="playRandomSong(songs3, 'audio3')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs3, 'audio3')" style="display: none;">播放</button>
    </div>

    <!-- 播放器4 -->
    <p><?php echo $message4; ?></p>
    <div class="audio-player">
        <audio id="audio4" controls onended="playRandomSong(songs4, 'audio4')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs4, 'audio4')" style="display: none;">播放</button>
    </div>

    <!-- 播放器5 -->
    <p><?php echo $message5; ?></p>
    <div class="audio-player">
        <audio id="audio5" controls onended="playRandomSong(songs5, 'audio5')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs5, 'audio5')" style="display: none;">播放</button>
    </div>

    <!-- 播放器6 -->
    <p><?php echo $message6; ?></p>
    <div class="audio-player">
        <audio id="audio6" controls onended="playRandomSong(songs6, 'audio6')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs6, 'audio6')" style="display: none;">播放</button>
    </div>

    <!-- 播放器7 -->
    <p><?php echo $message7; ?></p>
    <div class="audio-player">
        <audio id="audio7" controls onended="playRandomSong(songs7, 'audio7')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs7, 'audio7')" style="display: none;">播放</button>
    </div>

    <!-- 播放器8 -->
    <p><?php echo $message8; ?></p>
    <div class="audio-player">
        <audio id="audio8" controls onended="playRandomSong(songs8, 'audio8')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs8, 'audio8')" style="display: none;">播放</button>
    </div>

    <!-- 播放器9 -->
    <p><?php echo $message9; ?></p>
    <div class="audio-player">
        <audio id="audio9" controls onended="playRandomSong(songs9, 'audio9')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs9, 'audio9')" style="display: none;">播放</button>
    </div>
    
    <!-- 播放器10 -->
    <p><?php echo $message10; ?></p>
    <div class="audio-player">
        <audio id="audio10" controls onended="playRandomSong(songs10, 'audio10')">
            <source src="" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <button onclick="playRandomSong(songs10, 'audio10')" style="display: none;">播放</button>
    </div>

</body>
</html>



