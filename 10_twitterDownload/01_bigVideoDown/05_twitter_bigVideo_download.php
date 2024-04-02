<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// If the user clicked the logout link, log them out and redirect to the login page
if (isset($_GET['logout'])) {
  session_destroy(); // destroy all session data
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MP4视频下载</title>
    <style>
        .container {
            width: 400px;
            margin: 50px auto;
            text-align: center;
        }
        .container label, .container textarea, .container button {
            display: block;
            margin: 10px auto;
        }
        .website-link {
            margin-top: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="videoLink">请输入MP4视频下载链接：</label>
            <textarea name="videoLink" id="videoLink" rows="10" cols="55" required></textarea>
            <button type="submit" id="submitBtn">提交</button>
        </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["videoLink"])) {
            $videoLink = $_POST["videoLink"];

            // Validate the URL to ensure it's a valid URL
            if (filter_var($videoLink, FILTER_VALIDATE_URL)) {
                // Append the video URL to the specified file
                $filePath = '/home/01_html/05_twitter_bigfile/01_url.txt';
                // Append URL to the file with a newline
                file_put_contents($filePath, $videoLink . PHP_EOL, FILE_APPEND);

                echo '<div style="width: 400px; margin: 50px auto; color: green;">';
                echo '链接已成功保存！';
                echo '</div>';
            } else {
                echo '<div style="width: 400px; margin: 50px auto; color: red;">';
                echo '请输入有效的视频下载链接！';
                echo '</div>';
            }
        }
    }
    ?>

    <!-- Add the refresh button using JavaScript to trigger the reload without the query parameter -->
    <div class="container">
        <button onclick="refreshPage()" style="display: block; margin: 0 auto;">刷新页面</button>
    </div>

    <script>
        function refreshPage() {
            // Use JavaScript to remove the query parameter and trigger the reload
            var currentURL = window.location.href;
            var newURL = currentURL.replace(/\?refresh=true/g, '');
            window.location.href = newURL;
        }
    </script>

    <div class="website-link">
        <a href="https://twitterxz.com/" target="_blank">点击访问网站1</a>
        <br>
        <br>
        <a href="https://twittervideodownloader.com/" target="_blank">网站2(videodownloader)</a>
        <br>
        <br>
        <a href="https://twitter.iiilab.com/" target="_blank">点击访问网站3(iiilab)</a>
        <br>
        <br>
        <a href="https://twittervid.com/" target="_blank">网站4(twittervid)</a>
    </div>
</body>
</html>
