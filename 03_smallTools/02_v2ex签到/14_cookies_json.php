<!DOCTYPE html>
<html>
<head>
    <title>Cookie转换工具</title>
    <style>
        /* 定义容器样式 */
        .container {
            width: 60%;
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

        /* 输出框样式 */
        .output-box {
            width: 100%;
            min-height: 400px; /* 设置最小高度，避免内容过少时输出框太小 */
            max-height: 600px; /* 设置最大高度，限制输出框的增长 */
            background-color: black;
            color: white;
            font-size: 16px;
            overflow-x: auto;
            white-space: pre; /* 显示空白字符，保留换行符，不自动换行 */
            margin: 10px auto; /* 居中显示 */
            padding: 10px; /* 添加内边距，美化样式 */
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
            $keys = array_keys($cookies); // Get the keys of the $cookies array
            $lastKey = end($keys); // Get the last key of the $cookies array
            foreach ($cookies as $name => $value) {
                // Append the key-value pair without a comma if it is the last pair
                if ($name === $lastKey) {
                    $pythonCookieString .= "    '{$name}': '{$value}'\n";
                } else {
                    $pythonCookieString .= "    '{$name}': '{$value}',\n";
                }
            }
            $pythonCookieString .= "}";
            ?>

            <h2>转换后的Cookie：</h2>
            <!-- 用输出框展示转换后的Cookie信息 -->
            <div class="output-box">
                <pre><?php echo htmlspecialchars($pythonCookieString); ?></pre>
            </div>
        <?php } ?>
    </div>
</body>
</html>
