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
        #saveButton, #visitButton {
            display: block;
            margin: 0 auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 获取用户输入的字符串
        $userInput = $_POST["input"];

        // 正则表达式匹配 https 链接
        preg_match_all('/https:\/\/[^ ]+/', $userInput, $matches);

        // 获取匹配到的链接
        $links = $matches[0];

        // 定义文件路径
        $filePath = '/home/01_html/05_douyinAsynDload/2.txt';

        // 将链接追加到文件
        if (!empty($links)) {
            $file = fopen($filePath, "a");
            foreach ($links as $link) {
                fwrite($file, $link . PHP_EOL);
            }
            fclose($file);
            echo "<div id='output'>链接已成功保存到 $filePath 文件中！</div>";
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
    </form>

    <script>
        function visitUrl() {
            window.location.href = "https://mctea.one/05_douyinAsynDload/01_url_get.php";
        }
    </script>
</body>
</html>
