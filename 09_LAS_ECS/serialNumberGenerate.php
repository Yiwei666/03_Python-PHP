<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            text-align: center;
        }

        #container {
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translate(-50%, -15%);
            text-align: center;
        }

        #serial-number {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div id="container">
    <h1>生成序列号</h1>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $length = $_POST['length'];
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $serial_number = '';
        for ($i = 0; $i < $length; $i++) {
            $serial_number .= $characters[rand(0, strlen($characters) - 1)];
        }
        echo "<p id='serial-number'>$serial_number</p>";
    }
    ?>

    <form method="post">
        <label for="length">请输入序列号位数: </label>
        <input type="number" id="length" name="length" min="1" required>
        <button type="submit">生成</button>
    </form>
</div>
</body>
</html>
