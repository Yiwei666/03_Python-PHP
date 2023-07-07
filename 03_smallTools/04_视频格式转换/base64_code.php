<!DOCTYPE html>
<html>
<head>
    <title>Base64 编码和解码工具</title>
    <style>
        .container {
            width: 50%;
            margin: 0 auto;
            text-align: center;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            vertical-align: top;
        }

        
        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Base64 编码和解码工具</h1>

        <form method="post">
            <label for="input">输入字符串:</label>
            <br>
            <textarea id="input" name="input" rows="10" required></textarea>


            <br><br>

            <input type="submit" name="encode" value="编码">
            <input type="submit" name="decode" value="解码">
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $input = $_POST["input"];

            if (isset($_POST["encode"])) {
                $encoded = base64_encode($input);
                echo "<h2>编码结果:</h2>";
                echo "<p>$encoded</p>";
            } elseif (isset($_POST["decode"])) {
                $decoded = base64_decode($input);
                echo "<h2>解码结果:</h2>";
                echo "<p>$decoded</p>";
            }
        }
        ?>
    </div>
</body>
</html>
