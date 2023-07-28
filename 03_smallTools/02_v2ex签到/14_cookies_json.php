<!DOCTYPE html>
<html>
<head>
    <title>Cookie转换工具</title>
    <style>
        /* 定义容器样式 */
        .container {
            width: 50%;
            margin: 0 auto;
            text-align: center;
        }

        /* 输入框样式 */
        textarea {
            width: 100%;
            height: 300px; /* 设置固定高度 */
            resize: none; /* 禁止输入框的尺寸调整 */
            font-size: 16px; /* 调整字体大小，以便更多内容可见 */
        }

        /* 提交按钮样式 */
        button {
            margin-top: 10px;
        }

        /* 转换后输出样式，左对齐 */
        pre {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cookie转换工具</h1>
        <form method="post" action="">
            <label for="cookieString">请输入Cookie字符串（转换后删除最后一个逗号）：</label><br>
            <textarea id="cookieString" name="cookieString" required></textarea><br>
            <button type="submit">提交</button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // 获取用户提交的Cookie字符串
            $cookieString = $_POST["cookieString"];

            $cookies = [];

            // Split the cookie string by semicolon and iterate over each part
            $cookieParts = explode(';', $cookieString);
            foreach ($cookieParts as $cookiePart) {
                // Extract the cookie name and value
                $cookieData = explode('=', $cookiePart, 2);
                $cookieName = trim($cookieData[0]);
                $cookieValue = trim($cookieData[1]);

                // Check if the cookie value is wrapped in double quotes
                if (substr($cookieValue, 0, 1) === '"' && substr($cookieValue, -1) === '"') {
                    $cookieValue = substr($cookieValue, 1, -1); // Remove the quotes
                }

                // Add the cookie to the $cookies array
                $cookies[$cookieName] = $cookieValue;
            }

            // Convert the $cookies array into a Python-style dictionary string
            $pythonCookieString = "cookies = {\n";
            foreach ($cookies as $name => $value) {
                $pythonCookieString .= "    '{$name}': '{$value}',\n";
            }
            $pythonCookieString .= "}";
            ?>

            <h2>转换后的Cookie：</h2>
            <!-- 转换后的输出信息左对齐 -->
            <pre><?php echo htmlspecialchars($pythonCookieString); ?></pre>
        <?php } ?>
    </div>
</body>
</html>
