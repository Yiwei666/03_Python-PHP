<?php
// base32_tool.php

// Base32编码和解码函数
class Base32
{
    private static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function encode($input)
    {
        if (empty($input)) return '';

        $binary = '';
        // 将每个字符转换为其二进制表示
        for ($i = 0; $i < strlen($input); $i++) {
            $binary .= str_pad(decbin(ord($input[$i])), 8, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串填充到5的倍数
        $binary = str_pad($binary, ceil(strlen($binary) / 5) * 5, '0', STR_PAD_RIGHT);

        $base32 = '';
        for ($i = 0; $i < strlen($binary); $i += 5) {
            $chunk = substr($binary, $i, 5);
            $index = bindec($chunk);
            $base32 .= self::$alphabet[$index];
        }

        // 添加填充
        $padding = strlen($base32) % 8;
        if ($padding !== 0) {
            $base32 .= str_repeat('=', 8 - $padding);
        }

        return $base32;
    }

    public static function decode($input)
    {
        if (empty($input)) return '';

        // 移除填充字符
        $input = strtoupper($input);
        $input = rtrim($input, '=');

        $binary = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            $index = strpos(self::$alphabet, $char);
            if ($index === false) {
                // 无效的Base32字符
                return false;
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串转换回原始字符串
        $decoded = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            if (strlen($byte) < 8) {
                // 忽略不完整的字节
                break;
            }
            $decoded .= chr(bindec($byte));
        }

        return $decoded;
    }
}

// 处理表单提交
$encode_result = '';
$decode_result = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['encode_submit'])) {
        $encode_input = trim($_POST['encode_input']);
        if ($encode_input !== '') {
            $encode_result = Base32::encode($encode_input);
        } else {
            $error_message = '请输入要编码的字符串。';
        }
    }

    if (isset($_POST['decode_submit'])) {
        $decode_input = trim($_POST['decode_input']);
        if ($decode_input !== '') {
            $decoded = Base32::decode($decode_input);
            if ($decoded !== false) {
                $decode_result = $decoded;
            } else {
                $error_message = '无效的Base32编码字符串。';
            }
        } else {
            $error_message = '请输入要解码的Base32字符串。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Base32 编码与解码工具</title>
    <link rel="icon" href="https://mctea.one/00_logo/base32.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
        }
        h1 {
            text-align: center;
            color: #202124;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #5f6368;
            font-size: 16px;
        }
        .input-group {
            display: flex;
            align-items: center;
        }
        input[type="text"] {
            flex: 1;
            padding: 12px 20px;
            box-sizing: border-box;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #4285f4;
            outline: none;
        }
        .btn {
            background-color: #4285f4;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        .btn:hover {
            background-color: #357ae8;
        }
        .result {
            margin-top: 10px;
            padding: 12px;
            background-color: #f1f3f4;
            border-radius: 4px;
            word-wrap: break-word;
            color: #202124;
        }
        .error {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #fce8e6;
            border: 1px solid #f5c6cb;
            color: #d93025;
            border-radius: 4px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            h1 {
                font-size: 24px;
            }
            .btn {
                width: 100%;
                margin-left: 0;
                margin-top: 10px;
            }
            .input-group {
                flex-direction: column;
                align-items: stretch;
            }
            input[type="text"] {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Base32 编码与解码工具</h1>

        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="form-group">
            <form method="POST" action="">
                <label for="encode_input">Base32 编码</label>
                <div class="input-group">
                    <input type="text" id="encode_input" name="encode_input" placeholder="输入要编码的字符串" value="<?php echo isset($_POST['encode_input']) ? htmlspecialchars($_POST['encode_input']) : ''; ?>">
                    <button type="submit" name="encode_submit" class="btn">编码</button>
                    <?php if ($encode_result): ?>
                        <button type="button" class="btn" onclick="copyResult('encode_result')">复制</button>
                    <?php else: ?>
                        <button type="button" class="btn" disabled>复制</button>
                    <?php endif; ?>
                </div>
                <?php if ($encode_result): ?>
                    <div class="result" id="encode_result"><?php echo htmlspecialchars($encode_result); ?></div>
                <?php endif; ?>
            </form>
        </div>

        <div class="form-group">
            <form method="POST" action="">
                <label for="decode_input">Base32 解码</label>
                <div class="input-group">
                    <input type="text" id="decode_input" name="decode_input" placeholder="输入要解码的Base32字符串" value="<?php echo isset($_POST['decode_input']) ? htmlspecialchars($_POST['decode_input']) : ''; ?>">
                    <button type="submit" name="decode_submit" class="btn">解码</button>
                    <?php if ($decode_result): ?>
                        <button type="button" class="btn" onclick="copyResult('decode_result')">复制</button>
                    <?php else: ?>
                        <button type="button" class="btn" disabled>复制</button>
                    <?php endif; ?>
                </div>
                <?php if ($decode_result): ?>
                    <div class="result" id="decode_result"><?php echo htmlspecialchars($decode_result); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        function copyResult(resultId) {
            var resultText = document.getElementById(resultId).innerText;
            var tempInput = document.createElement("textarea");
            tempInput.value = resultText;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // 针对移动设备
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            
            // 更改按钮文本为“已复制”并禁用按钮
            var copyButton = event.target;
            var originalText = copyButton.innerText;
            copyButton.innerText = "已复制";
            copyButton.disabled = true;
            setTimeout(function() {
                copyButton.innerText = originalText;
                copyButton.disabled = false;
            }, 2000); // 2秒后恢复按钮
        }
    </script>
</body>
</html>
