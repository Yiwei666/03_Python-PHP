<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/RGBHEX.png">  
    <title>颜色转换器</title>
    <style>

        table {
            width: 45%;
            border-collapse: collapse;
            margin-bottom: 60px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            height: 30px; 
            font-size: 18px;
        }

        th {
            background-color: #303030;

        }

        img {
            max-width: 100%;
            height: auto;
        }


        /* RGB与HEX互转提示... */

        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            position: relative;
            top: 100px; /* 使用position和top来设置顶部间距 */
            /* margin: 160px 0 0 0;  顶部距离为16px，调整其他方向的边距为0 */
            background-color: #303030; /* 背景颜色为灰黑色 */
            color: #258fb8; /* 字体颜色为白色 */
            font-size: 30px; /* 设置 body 元素的字体大小为 16 像素 */
        }

        #container {
            text-align: left;
            width: 100%; /* 设置容器宽度，可以根据需要调整 */
            margin-top: 1px; /* 调整容器上边距 */
            display: flex;
            flex-direction: column;
            align-items: center; /* 调整竖直方向上的对齐方式 */
        }

        form {
            margin-bottom: 30px;
        }

        label, input, p {
            margin-bottom: 10px;
            font-size: 20px; /* 设置 label、input、p 元素的字体大小为 14 像素 */
            white-space: pre;
            color: #258fb8       /* 设置字体颜色 */
        }


        input[type="number"], 
        input[type="text"] {
            width: 238px; /* 输入框宽度为100像素 */
            height: 30px; /* 输入框高度为30像素 */
            border: 1px solid #258fb8; /* 设置输入框边框为白色，1px的边框线 */
            background-color: #303030; /* 设置输入框背景颜色为灰色 */
            color: #258fb8       /* 设置字体颜色 */
        }

        input[type="submit"] {
            font-size: 20px; /* 设置按钮字体大小为16像素 */
            width: 100px; /* 输入框宽度为100像素 */
            height: 32px; 
            color: #ffffff; /* 设置按钮字体颜色色 */
            background-color: #258fb8; /* 设置按钮背景颜色为深绿色 */
            box-shadow: none; /* 移除按钮的阴影效果 */
            border: 1px solid #258fb8; /* 设置输入框边框为白色，1px的边框线 */
            /* 其他样式... */
        }


    </style>
</head>
<body>

<div id="container">


    <table>
        <thead>
            <tr>
                <th>COLOR1</th>
                <th>RGB/HEX</th>
                <th>COLOR2</th>
                <th>RGB/HEX</th>
                <th>COLOR3</th>
                <th>RGB/HEX</th>        
                <th>COLOR4</th>
                <th>RGB/HEX</th>         
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img src="https://via.placeholder.com/32/ff0000/000000?text=+" alt="RGB/HEX"></td>
                <td>#ff0000, (255,0,0)</td>
                <td><img src="https://via.placeholder.com/32/750000/000000?text=+" alt="图片2"></td>
                <td>#750000, (117,0,0)</td>   
                <td><img src="https://via.placeholder.com/32/00ff00/000000?text=+" alt="图片2"></td>
                <td>#00ff00, (0,255,0)</td>
                <td><img src="https://via.placeholder.com/32/921aff/000000?text=+" alt="图片2"></td>
                <td>#921aff, (146,26,255)</td>
            </tr>
            <tr>
                <td><img src="https://via.placeholder.com/32/000064/000000?text=+" alt="图片2"></td>
                <td>#000064, (0,0,100)</td>  
                <td><img src="https://via.placeholder.com/32/0000ff/000000?text=+" alt="图片3"></td>
                <td>#0000ff, (0,0,255)</td>
                <td><img src="https://via.placeholder.com/32/00ffff/000000?text=+" alt="图片7"></td>
                <td>#00ffff, (0,255,255)</td>    
                <td><img src="https://via.placeholder.com/32/ad5a5a/000000?text=+" alt="图片2"></td>
                <td>#ad5a5a, (173,90,90)</td>    
            </tr>
            <tr>
                <td><img src="https://via.placeholder.com/32/800080/000000?text=+" alt="图片5"></td>
                <td>#800080, (128,0,128)</td>
                <td><img src="https://via.placeholder.com/32/ffffff/000000?text=+" alt="图片6"></td>
                <td>#ffffff, (255,255,255)</td>
                <td><img src="https://via.placeholder.com/32/ffff00/000000?text=+" alt="图片4"></td>
                <td>#ffff00, (255,255,0)</td>
                <td><img src="https://via.placeholder.com/32/a3d1d1/000000?text=+" alt="图片2"></td>
                <td>#a3d1d1, (163,209,209)</td>
            </tr>
            <tr>
                <td><img src="https://via.placeholder.com/32/7b7b7b/000000?text=+" alt="图片2"></td>
                <td>#7b7b7b, (123,123,123)</td> 
                <td><img src="https://via.placeholder.com/32/000000/000000?text=+" alt="图片8"></td>
                <td>#000000, (0,0,0)</td>
                <td><img src="https://via.placeholder.com/32/ff5809/000000?text=+" alt="图片2"></td>
                <td>#ff5809, (255,88,9)</td>   
                <td><img src="https://via.placeholder.com/32/2d5959/000000?text=+" alt="图片2"></td>
                <td>#2d5959, (45,89,89)</td>
            </tr>
        </tbody>
    </table>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="inputR">红 ( R: 0~255 ):</label>
        <input type="number" name="inputR" id="inputR" min="0" max="255" value="<?php echo isset($inputR) ? $inputR : ''; ?>"><br>

        <label for="inputG">绿 ( G: 0~255 ):</label>
        <input type="number" name="inputG" id="inputG" min="0" max="255" value="<?php echo isset($inputG) ? $inputG : ''; ?>"><br>

        <label for="inputB">蓝 ( B: 0~255 ):</label>
        <input type="number" name="inputB" id="inputB" min="0" max="255" value="<?php echo isset($inputB) ? $inputB : ''; ?>"><br>

        <label for="inputHex">Hex(#000000):</label>
        <input type="text" name="inputHex" id="inputHex" value="<?php echo $inputHex; ?>"><br>

        <input type="submit" value="Convert">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 获取用户输入的颜色值
        $inputR = isset($_POST["inputR"]) && $_POST["inputR"] !== "" ? intval($_POST["inputR"]) : null;
        $inputG = isset($_POST["inputG"]) && $_POST["inputG"] !== "" ? intval($_POST["inputG"]) : null;
        $inputB = isset($_POST["inputB"]) && $_POST["inputB"] !== "" ? intval($_POST["inputB"]) : null;
        $inputHex = isset($_POST["inputHex"]) ? $_POST["inputHex"] : "";

        // echo "$inputR, $inputG, $inputB";
        // echo "$inputHex";

        // 如果RGB值非空且十六进制值为空，则将RGB转换为HEX
        if (!is_null($inputR) && !is_null($inputG) && !is_null($inputB) && empty($inputHex)) {
            $inputHex = sprintf("#%02x%02x%02x", $inputR, $inputG, $inputB);
        }
        // 如果RGB值部分为空且十六进制值非空，则将HEX转换为RGB
        elseif ((is_null($inputR) || is_null($inputG) || is_null($inputB)) && !empty($inputHex)) {
            // 解析HEX为RGB
            $inputHex = ltrim($inputHex, '#');
            $inputR = hexdec(substr($inputHex, 0, 2));
            $inputG = hexdec(substr($inputHex, 2, 2));
            $inputB = hexdec(substr($inputHex, 4, 2));
            // $hexValue = $inputHex;
        }

        // 根据RGB值获取对应颜色中文名称
        $colorName = "颜色";

        // 输出颜色矩形和代码行
        $rgbValue = "($inputR,$inputG,$inputB)";
        $hexValue = sprintf("#%02x%02x%02x", $inputR, $inputG, $inputB);

        echo "<div style='width: 120px; height: 75px; background-color: $hexValue;'></div>";
        // echo "<p>| $rgbValue | $hexValue | $colorName | 用户输入 | ![Color Box](https://via.placeholder.com/50/$hexValue/000000?text=+) | 通用 | 无 |</p>";

        // 假设 $hexValue 是一个包含十六进制颜色值的变量
        // 使用 str_replace 将 "#" 替换为空字符串
        $cleanedHexValue = str_replace('#', '', $hexValue);

        // 输出带有清理后的十六进制颜色值的字符串
        echo "<p>| $rgbValue | $cleanedHexValue | $colorName | 用户输入 | ![Color Box](https://via.placeholder.com/32/$cleanedHexValue/000000?text=+) | 通用 | 无 |</p>";
        echo "<p>$hexValue, $rgbValue</p>";

    }
    ?>

    <img src="https://via.placeholder.com/50/<?php echo $cleanedHexValue; ?>/000000?text=+" alt="Color Box">


</div>

</body>
</html>