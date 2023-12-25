<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>String Manipulation</title>
    <style>
        /* 自定义样式 */
        .custom-input {
            width: 600px;
            height: 30px;
            margin-bottom: 15px; /* 设置行间距，根据需要调整值 */
        }
    </style>
</head>
<body>

<!-- 显示输入框 -->
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="input_G">Enter string $G:</label>
    <input type="text" name="input_G" class="custom-input" required>
    <br>
    <label for="input_name">Enter string $name:</label>
    <input type="text" name="input_name" class="custom-input" required>
    <br>
    <label for="input_journal">Enter string $journal:</label>
    <input type="text" name="input_journal" value="JOURNAL" class="custom-input">
    <br>
    <input type="submit" value="Submit">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取用户输入的字符串 $G
    $G = $_POST["input_G"];

    echo "<p>Result: $G</p>";

    // 提取第一个"."和第二个"."之间的字符串部分，作为 string1
    $string1 = strstr(substr($G, strpos($G, ".") + 1), ".", true);
    echo "<p>Result-G: $string1</p>";

    // 从 string1 中删除 "[J]"，作为 string2
    $string2 = str_replace("[J]", "", $string1);
    echo "<p>Result-string2: $string2</p>";

    // 提取倒数第二个","和最后一个"."之间的字符串部分，作为 string3
    $lastCommaPosition = strrpos($G, ",");
    $string3 = substr($G, strrpos($G, ",", $lastCommaPosition - strlen($G) - 1) + 1, $lastCommaPosition - strrpos($G, ",") - 1);
    echo "<p>Result-string3: $string3</p>";


    // 使用"," ":" "("对 string3 进行分割，得到 $s1，$s2，$s3，$s4
    list($s1, $s2, $s3, $s4) = array_map('trim', preg_split("/[,:\(\)]+/", $string3, -1, PREG_SPLIT_NO_EMPTY));

    echo "<p>Result-s1-s2-s3-s4: $s1, $s2, $s3, $s4</p>";

    // 拼接新的字符串 string4
    $string4 = $s2 . " (" . $s1 . ") " . $s4 . ".";
    echo "<p>Result-string4: $string4</p>";


    // 找到倒数第二个 "."
    $secondLastDotPosition = strrpos(substr($G, 0, strrpos($G, ".", -1)), ".", -1);
    // 提取倒数第二个 "." 和倒数第一个 "." 之间的字符串部分
    $betweenDotsString = substr($G, $secondLastDotPosition + 1, strrpos($G, ".", -1) - $secondLastDotPosition - 1);
    // 使用","对该字符串进行分割，获取分割后的第一个字符串，并删掉其中的空格作为 string5
    list($string5) = array_map('trim', explode(",", $betweenDotsString, 2));
    echo "<p>Result-string5: $string5</p>";



    // 读取文件内容
    $fileContent = file_get_contents('/home/01_html/06_journal_Abbreviation.txt');

    // 将文件内容按行分割成数组
    $fileLines = explode("\n", $fileContent);

    // 初始化 string6
    $string6 = '';

    // 循环处理每一行
    foreach ($fileLines as $line) {
        // 使用 "/" 分割每行，得到两部分
        $parts = explode("/", $line, 2);

        // 去除空格并比较第一部分是否和 string5 完全相同
        if (trim($parts[0]) === $string5) {
            // 如果相同，将 "/ " 后面的部分作为 string6
            $string6 = trim($parts[1]);
            break; // 停止循环，因为已经找到匹配的字符串
        }
    }

    // 输出 string6
    echo "<p>Result-string6: $string6</p>";



    // 获取用户输入的 $name 和 $journal
    $name = $_POST["input_name"];
    echo "<p>Result-name: $name</p>";

    $journal = $_POST["input_journal"];
    echo "<p>Result-journal: $journal</p>";

    // 拼接最终字符串并输出
    $result = $name . $string2 . ", " . $journal . " " . $string4;
    $result2 = $name . $string2 . ", " . $string6 . " " . $string4;
    echo "<p>Result-result: $result</p>";
    echo "<p>Result-result2: $result2</p>";
}
?>


</body>
</html>
