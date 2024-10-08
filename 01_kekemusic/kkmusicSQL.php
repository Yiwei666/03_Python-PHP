<?php
$logoUrl = "http://domain.com/00_logo/kekemusic.png";

$servername = "your_servername"; // 修改为您的数据库服务器名，一般为localhost
$username = "your_username";     // 修改为您的数据库用户名
$password = "your_password";     // 修改为您的数据库密码
$dbname = "kkmusicdb";           // 修改为您的数据库名

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT datetime, url FROM kkmusicTABLE ORDER BY RAND() LIMIT 15"; // 修改为您的表名
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="<?php echo $logoUrl; ?>">
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
            width: 400px;
        }
    </style>
</head>
<body>
    <h4>顺序播放音频</h4>
    <div class="container">
        <div class="audio-player">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $datetime = $row["datetime"];
                    $url = $row["url"];

                    echo "<p><span>$datetime</span></p>";
                    echo "<audio controls onended=\"playNext()\"><source src=\"$url\" type=\"audio/mpeg\"></audio>";
                }
            } else {
                echo "No audio data available.";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script>
        var audioList = document.getElementsByTagName('audio');
        var currentAudioIndex = 0;

        function playNext() {
            if (currentAudioIndex < audioList.length - 1) {
                currentAudioIndex++;
                audioList[currentAudioIndex].play();
            }
        }

        audioList[currentAudioIndex].play();
    </script>
</body>
</html>
