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
    date_default_timezone_set('Asia/Shanghai');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userInput = $_POST["input"];
    
        preg_match_all('/https:\/\/[^ ]+/', $userInput, $matches);
    
        $links = $matches[0];
    
        $filePath = '/home/01_html/05_douyinAsynDload/2.txt';
        $filePathLog = '/home/01_html/05_douyinAsynDload/2_addTotalLog.txt';
    
        if (!empty($links)) {
            $file = fopen($filePath, "a");
            $logFile = fopen($filePathLog, "a");
    
            foreach ($links as $link) {
                $timestamp = date('Y-m-d H:i:s');
                fwrite($file, $link . PHP_EOL);
                fwrite($logFile, $link . ',' . $timestamp . PHP_EOL);
            }
    
            fclose($file);
            fclose($logFile);
    
            echo "<div id='output'>链接已成功保存到 $filePath 和 $filePathLog 文件中！</div>";
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
            window.location.href = "https://mctea.one/05_douyinAsynDload/01_url_get.php";
        }

        function viewLog() {
            window.open("01_view_log.php", "_blank");
        }
    </script>
</body>
</html>
