<!DOCTYPE html>
<html>
<head>
    <title>Douyin Downloader</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/download.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        #inputForm {
            text-align: center;
        }
        #inputText {
            width: 400px;
            height: 200px;
            margin: 10px 0;
        }
        #saveButton, #visitButton, #viewButton {
            display: block;
            margin: 0 auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php
    include '18_db_config.php';  // 引入数据库配置文件，建立 $mysqli 数据库连接对象
    date_default_timezone_set('Asia/Shanghai');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userInput = $_POST["input"];
    
        preg_match_all('/https:\/\/[^ ]+/', $userInput, $matches);
    
        $links = $matches[0];
    
        if (!empty($links)) {
            foreach ($links as $link) {
                $timestamp = date('Y-m-d H:i:s');

                // 检查数据库中是否已存在该 URL
                $query = "SELECT 1 FROM douyin_videos WHERE video_url = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("s", $link);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->fetch_assoc()) {
                    echo "<div id='output'>URL " . htmlspecialchars($link) . " 已存在，不进行写入。</div>";
                } else {
                    // 将新的 URL 和时间戳写入数据库
                    $insertQuery = "INSERT INTO douyin_videos (video_url, url_write_time) VALUES (?, ?)";
                    $insertStmt = $mysqli->prepare($insertQuery);
                    $insertStmt->bind_param("ss", $link, $timestamp);
                    $insertStmt->execute();
                    echo "<div id='output'>链接 " . htmlspecialchars($link) . " 已成功保存到数据库中！</div>";
                }
            }
        } else {
            echo "<div id='output'>未找到有效的链接，请重新输入。</div>";
        }
    }
    ?>

    <form id="inputForm" method="POST">
        <textarea id="inputText" name="input" rows="5" cols="50" placeholder="请输入字符串"></textarea>
        <br>
        <input id="saveButton" type="submit" value="保存并执行">
        <br>
        <br>
        <br>
        <button id="visitButton" onclick="visitUrl()">刷新</button>
        <br>
        <br>
        <br>
        <button id="viewButton" onclick="viewLog()">查看</button>
    </form>

    <script>
        function visitUrl() {
            window.location.href = "https://mctea.one/05_douyinAsynDload/18_url_get.php";
        }

        function viewLog() {
            window.open("18_view_log.php", "_blank");
        }
    </script>
</body>
</html>
