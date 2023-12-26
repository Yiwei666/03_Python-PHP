<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/reference.png">
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
    <input type="text" name="input_name" value="NAME" class="custom-input">
    <br>
    <label for="input_APAname">Enter string $APAname:</label>
    <input type="text" name="input_APAname" class="custom-input"  required>
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

    // 提取第一个"."和第二个"."之间的字符串部分，作为 string1，对应文章题目
    $string1 = strstr(substr($G, strpos($G, ".") + 1), ".", true);
    echo "<p>Result-G: $string1</p>";

    // 从 string1 中删除 "[J]"，作为 string2，对应修改后的文章题目
    // $string2 = str_replace("[J]", "", $string1);
    $string2 = trim(str_replace("[J]", "", $string1));
    echo "<p>Result-string2: $string2</p>";


    // "Zhang M, Li Y. Breaking of Henry’s law for sulfide liquid–basaltic melt partitioning of Pt and Pd[J]. Nature Communications, 2021, 12(1): 5994."
    // 提取倒数第二个","和最后一个"."之间的字符串部分，作为 string3，例如 "2021, 12(1): 5994"，用来处理 卷 出版年 和 页码范围
    $lastCommaPosition = strrpos($G, ",");
    $string3 = substr($G, strrpos($G, ",", $lastCommaPosition - strlen($G) - 1) + 1, $lastCommaPosition - strrpos($G, ",") - 1);
    echo "<p>Result-string3: $string3</p>";


    // 使用"," ":" "("对 string3 进行分割，得到 $s1，$s2，$s3，$s4
    // list($s1, $s2, $s3, $s4) = array_map('trim', preg_split("/[,:\(\)]+/", $string3, -1, PREG_SPLIT_NO_EMPTY));


    if (strpos($string3, '(') !== false) {
        list($s1, $s2, $s3, $s4) = array_map('trim', preg_split("/[,:\(\)]+/", $string3, -1, PREG_SPLIT_NO_EMPTY));
    } else {
        list($s1, $s2, $s4) = array_map('trim', preg_split("/[,:\(\)]+/", $string3, -1, PREG_SPLIT_NO_EMPTY));
        $s3 = "NULL";
    }


    echo "<p>Result-s1-s2-s3-s4: $s1, $s2, $s3, $s4</p>";

    // 拼接新的字符串 string4   如 "Result-string4: 123 (2023) 2436-2608."
    $string4 = $s2 . " (" . $s1 . ") " . $s4 . ".";
    echo "<p>Result-string4: $string4</p>";



    // 找到倒数第二个 "."
    // $secondLastDotPosition = strrpos(substr($G, 0, strrpos($G, ".", -1)), ".", -1);
    // 提取倒数第二个 "." 和倒数第一个 "." 之间的字符串部分
    // $betweenDotsString = substr($G, $secondLastDotPosition + 1, strrpos($G, ".", -1) - $secondLastDotPosition - 1);
    // 使用","对该字符串进行分割，获取分割后的第一个字符串，并删掉其中的空格作为 string5，对应 期刊的全称 "Nature Communications"
    // list($string5) = array_map('trim', explode(",", $betweenDotsString, 2));
    // echo "<p>Result-string5: $string5</p>";


    // 为了解决期刊名中存在逗号的情况，采取 提取字符串$G中倒数第二个"."和倒数第二个","之间的字符串 作为期刊名，同时去除开头和末尾的空格
    // 找到倒数第二个 "."
    $secondLastDotPosition = strrpos(substr($G, 0, strrpos($G, ".", -1)), ".", -1);
    echo "<p>Result-secondLastDotPosition: $secondLastDotPosition</p>";

    // 找到倒数第二个 ","
    $secondLastCommaPosition = strrpos(substr($G, 0, strrpos($G, ",", -2)), ",", -1);

    // 判断倒数第二个 "." 的位置是否小于倒数第二个 "," 的位置
    if ($secondLastDotPosition !== false && $secondLastCommaPosition !== false && $secondLastDotPosition < $secondLastCommaPosition) {
        // 提取倒数第二个 "." 和倒数第二个 "," 之间的字符串部分
        $betweenDotsAndCommasString = substr($G, $secondLastDotPosition + 1, $secondLastCommaPosition - $secondLastDotPosition - 1);
        // echo $betweenDotsAndCommasString;
        
    } else {
        // 处理无法提取的情况，例如没有倒数第二个 "." 或倒数第二个 ","，或者倒数第二个 "." 大于等于倒数第二个 "," 的情况
        echo "无法提取符合条件的字符串";
    }

    $string5 = trim($betweenDotsAndCommasString);
    echo "<p>Result-string5: $string5</p>";




    // 读取文件内容，将期刊全写替换为期刊简写
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
        // if (trim($parts[0]) === $string5) {
        //    // 如果相同，将 "/ " 后面的部分作为 string6
        //    $string6 = trim($parts[1]);
        //    break; // 停止循环，因为已经找到匹配的字符串
        // }

        if (strcasecmp(trim($parts[0]), $string5) === 0) {
            // 如果相同，将 "/ " 后面的部分作为 string6
            $string6 = trim($parts[1]);
            break; // 停止循环，因为已经找到匹配的字符串
        }

    }

    // 输出 string6  从txt文件中查找到的期刊简写
    echo "<p>Result-string6: $string6</p>";




    // 获取用户输入的 $name 和 $journal

    // $name = "M. Zhang, Y. Li, "，末尾有空格

    $name = $_POST["input_name"];
    echo "<p>Result-name: $name</p>";



    // $journal = "ACS Sustain. Chem. Eng."

    $journal = $_POST["input_journal"];
    echo "<p>Result-journal: $journal</p>";




    // 下面是从输入的完整APA格式的引文格式中提取 作者名

    // $APAname = "Rubie, D.C., Laurenz, V., Jacobson, S.A., Morbidelli, A., Palme, H., Vogel, A.K., & Frost, D.J. (2016). Highly siderophile elements were stripped from Earth’s mantle by iron sulfide segregation. Science, 353, 1141 - 1144."

    $APAname = $_POST["input_APAname"];
    echo "<p>Result-APAname: $APAname</p>";


    // 假设 $APAname 是一个包含括号和空格的字符串
    // $APAname = "JOURNAL 353 (2016) 1141-1144";

    // 使用正则表达式匹配括号前的内容
    if (preg_match('/^(.*?)\s*\(/', $APAname, $matches)) {
        // 删除第一部分末尾的空格
        $firstPart = rtrim($matches[1]);

        // 重新赋值给 $APAname
        $APAname = $firstPart;
    }

    // 输出结果，仅包含APA人名 "Zhang, M., & Li, Y."

    echo "<p>Result-APAname: $APAname</p>";


    // $APAname = "1, 2, 3, 4, 5, &6"; // 你的输入字符串

    // 使用","分割字符串，并去除空格和"&"
    $parts = array_map(function($part) {
        return str_replace([' ', '&'], '', $part);
    }, explode(',', $APAname));

    // 计算2n
    $n = count($parts) / 2;

    // 将第2n和第2n-1个字符串互换位置
    for ($i = 0; $i < $n; $i++) {
        $temp = $parts[$i * 2];
        $parts[$i * 2] = $parts[$i * 2 + 1];
        $parts[$i * 2 + 1] = $temp;
    }

    // 使用", "连接成新的字符串$string7
    $string7 = implode(', ', $parts);
    // echo $string7;


    // $string7 = "2, 1, 4, 3, 6, 5";

    // 使用逗号分割字符串并去掉子字符串中的空格
    $splitArray = array_map('trim', explode(',', $string7));

    // 初始化结果字符串
    $string7 = '';

    // 循环连接子字符串
    for ($i = 0; $i < count($splitArray); $i++) {
        // 奇数索引，连接使用" "
        if ($i % 2 == 0) {
            $string7 .= $splitArray[$i] . ' ';
        } else {
            // 偶数索引，连接使用", "
            $string7 .= $splitArray[$i] . ', ';
        }
    }

    // 去除末尾的", "
    $string7 = rtrim($string7, ', ');

    // 在末尾再添加一个", "作为最后一个人名和文章标题间的连接符，确保之前处理的文章名开头没有空格
    $string7 .= ', ';

    // echo $string7;
    echo "<p>Result-string7: $string7</p>";


    // 拼接最终字符串并输出
    $result = $name . $string2 . ", " . $journal . " " . $string4;
    $result2 = $name . $string2 . ", " . $string6 . " " . $string4;
    $result3 = $string7 . $string2 . ", " . $string6 . " " . $string4;

    echo "<p>Result-result: $result</p>";
    echo "<p>Result-result2: $result2</p>";
    echo "<p>Result-result3: $result3</p>";
}
?>

<div>
    <p id="result3"><?php echo $result3; ?></p>
    <button onclick="copyToClipboard()">Copy to Clipboard</button>
</div>

<script>
    function copyToClipboard() {
        /* Get the text field */
        var copyText = document.getElementById("result3");

        /* Create a temporary textarea element to copy the text */
        var tempTextArea = document.createElement("textarea");
        tempTextArea.value = copyText.textContent;

        /* Append the textarea to the body */
        document.body.appendChild(tempTextArea);

        /* Select the text inside the textarea */
        tempTextArea.select();
        tempTextArea.setSelectionRange(0, 99999); /* For mobile devices */

        /* Copy the text to the clipboard */
        document.execCommand("copy");

        /* Remove the temporary textarea */
        document.body.removeChild(tempTextArea);

        /* Alert the user that the text has been copied */
        alert("Copied to clipboard: " + copyText.textContent);
    }
</script>

<!-- 刷新按钮 -->
<button onclick="refreshPage()" style="margin-top: 20px;">Refresh Page</button>

<script>
    function refreshPage() {
        /* Redirect to the specified URL */
        window.location.href = "http://120.46.81.41/06_referenceJML.php";
    }
</script>

</body>
</html>
