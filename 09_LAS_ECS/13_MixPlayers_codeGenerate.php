<!DOCTYPE html>
<html>
<head>
    <title>Generate Code</title>
    <style>
        /* 设置代码块的样式 */
        pre {
            font-family: Consolas, monospace;
            color: white;
            background-color: black;
            padding: 10px;
            border: 1px solid #ccc;
            white-space: pre-wrap;
        }

        /* 新增样式 */
        .container {
            width: 50%; /* Set the container width to half of the page width */
            margin: 0 auto; /* Center the container horizontally */
            padding: 20px; /* Add some padding for spacing */
            border: 1px solid #ccc; /* Add a border for visual distinction */
        }

        /* Make the input fields larger and longer */
        input[type="text"] {
            width: 98%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            background-color: #f2f2f2;  /* Set the background color to light gray */
            border: 1px solid #ccc;
        }

        /* Add spacing between labels and input fields */
        label {
            display: block; /* Labels are now block elements */
            margin-bottom: 5px; /* Add margin below each label */
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 获取用户输入的值
            $number = $_POST['number'];
            $directory = $_POST['directory'];
            $message = $_POST['message'];
            $baseUrl = $_POST['baseUrl'];

            // 代码模板
            $codeTemplate = <<<'EOT'
$directory{num} = '{dir}';
$message{num} = '{msg}';
$songs{num} = generateAudioLinks($directory{num}, '{url}');

var songs{num} = <?php echo json_encode($songs{num}); ?>;

loadRandomSong(songs{num}, 'audio{num}')

<!-- 播放器{num} -->
<p><?php echo $message{num}; ?></p>
<div class="audio-player">
    <audio id="audio{num}" controls onended="playRandomSong(songs{num}, 'audio{num}')">
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <button onclick="playRandomSong(songs{num}, 'audio{num}')" style="display: none;">播放</button>
</div>
EOT;

            // 替换模板中的变量
            $replacements = array(
                '{num}' => $number,
                '{dir}' => $directory,
                '{msg}' => $message,
                '{url}' => $baseUrl,
            );

            $finalCode = strtr($codeTemplate, $replacements);

            // 输出生成的代码
            echo "<pre>" . htmlspecialchars($finalCode) . "</pre>";
        }
        ?>
        
        <form action="" method="post">
            <label for="number">请输入数字（比如 6 ）：</label>
            <input type="text" name="number" id="number" required>

            <label for="directory">请输入目录路径（比如 /home/01_html/06_TOEFL/ ）：</label>
            <input type="text" name="directory" id="directory" required>

            <label for="message">请输入消息文本（比如 06_TOEFL ）：</label>
            <input type="text" name="message" id="message" required>

            <label for="baseUrl">请输入基本URL（比如 http://101.200.215.127/06_TOEFL/ ）：</label>
            <input type="text" name="baseUrl" id="baseUrl" required>

            <input type="submit" value="生成代码">
        </form>
    </div>
</body>
</html>
