<!DOCTYPE html>
<html>
<head>
    <title>HEX to RGB 转换器</title>
</head>
<body>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="inputHex">输入HEX颜色值：</label>
    <input type="text" name="inputHex" id="inputHex" placeholder="#258fb8" required>
    <input type="submit" value="转换">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取用户输入的HEX颜色值
    $inputHex = isset($_POST["inputHex"]) ? $_POST["inputHex"] : "";

    // 移除十六进制字符串中的 '#'
    $inputHex = ltrim($inputHex, '#');

    // 转换为RGB值
    $r = hexdec(substr($inputHex, 0, 2));
    $g = hexdec(substr($inputHex, 2, 2));
    $b = hexdec(substr($inputHex, 4, 2));

    // 输出转换结果
    echo "<p>HEX颜色值: #$inputHex</p>";
    echo "<p>RGB值: ($r, $g, $b)</p>";
}
?>

</body>
</html>
