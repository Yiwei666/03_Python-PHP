<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>房间价格计算器</title>
    <style>
        /* 设置容器样式 */
        .container {
            width: 80%; /* 可以根据需要调整 */
            max-width: 800px;
            margin: 0 auto; /* 容器水平居中 */
            text-align: left; /* 容器内内容左对齐 */
        }

        /* 设置表格样式 */
        table {
            width: auto; /* 表格占据容器的宽度 */
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center; /* 数字居中对齐 */
        }
        th {
            background-color: #f2f2f2;
        }
        /* 交替行背景色 */
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>房间价格计算器</h2>
        <form method="POST" action="">
            <label for="x1">大床房打折后价格 x1：</label>
            <input type="number" id="x1" name="x1" value="229" step="0.01" required><br><br>
            
            <label for="a">早餐价格 a：</label>
            <input type="number" id="a" name="a" value="40" step="0.01" required><br><br>
            
            <label for="b">午餐价格 b：</label>
            <input type="number" id="b" name="b" value="120" step="0.01" required><br><br>
            
            <label for="c">双床房额外价格 c：</label>
            <input type="number" id="c" name="c" value="90" step="0.01" required><br><br>
            
            <label for="k">折扣系数 k：</label>
            <input type="number" id="k" name="k" value="0.518" step="0.0001" required><br><br>
            
            <input type="submit" value="计算">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 获取输入的参数
            $x1 = isset($_POST['x1']) ? floatval($_POST['x1']) : 229;
            $a = isset($_POST['a']) ? floatval($_POST['a']) : 40;
            $b = isset($_POST['b']) ? floatval($_POST['b']) : 120;
            $c = isset($_POST['c']) ? floatval($_POST['c']) : 90;
            $k = isset($_POST['k']) ? floatval($_POST['k']) : 0.518;

            // 计算折扣前价格 y 和预计到手金额 z
            $y1 = $x1 / $k;
            $y2 = ($x1 + $a) / $k;
            $y3 = ($x1 + $a + $c) / $k;
            $y4 = ($x1 + $a + $b) / $k;
            $y5 = ($x1 + $a + $b + $c) / $k;
            $y6 = ($x1 + $a + 2 * $b) / $k;
            $y7 = ($x1 + $a + 2 * $b + $c) / $k;

            // 计算预计到手金额 z
            $z1 = 0.9 * $y1 * $k;
            $z2 = 0.9 * $y2 * $k;
            $z3 = 0.9 * $y3 * $k;
            $z4 = 0.9 * $y4 * $k;
            $z5 = 0.9 * $y5 * $k;
            $z6 = 0.9 * $y6 * $k;
            $z7 = 0.9 * $y7 * $k;

            // 计算折扣后价格 w
            $w1 = $y1 * $k;
            $w2 = $y2 * $k;
            $w3 = $y3 * $k;
            $w4 = $y4 * $k;
            $w5 = $y5 * $k;
            $w6 = $y6 * $k;
            $w7 = $y7 * $k;

            // 显示结果
            echo "<h3>计算结果</h3>";
            echo "大床房(不含早餐)：折扣前价格 y1 = ￥" . number_format($y1, 0) . "，折扣后价格 w1 = ￥" . number_format($w1, 0) . "，预计到手金额 z1 = ￥" . number_format($z1, 2) . "<br>";
            echo "标准大床房(含早餐)：折扣前价格 y2 = ￥" . number_format($y2, 0) . "，折扣后价格 w2 = ￥" . number_format($w2, 0)  . "，预计到手金额 z2 = ￥" . number_format($z2, 2) . "<br>";
            echo "标准双床房(含早餐)：折扣前价格 y3 = ￥" . number_format($y3, 0) . "，折扣后价格 w3 = ￥" . number_format($w3, 0)  . "，预计到手金额 z3 = ￥" . number_format($z3, 2) . "<br>";
            echo "轻奢大床房(含早晚餐)：折扣前价格 y4 = ￥" . number_format($y4, 0) . "，折扣后价格 w4 = ￥" . number_format($w4, 0)  . "，预计到手金额 z4 = ￥" . number_format($z4, 2) . "<br>";
            echo "轻奢双床房(含早晚餐)：折扣前价格 y5 = ￥" . number_format($y5, 0) . "，折扣后价格 w5 = ￥" . number_format($w5, 0)  . "，预计到手金额 z5 = ￥" . number_format($z5, 2) . "<br>";
            echo "豪华大床房(含早中晚餐)：折扣前价格 y6 = ￥" . number_format($y6, 0) . "，折扣后价格 w6 = ￥" . number_format($w6, 0)  . "，预计到手金额 z6 = ￥" . number_format($z6, 2) . "<br>";
            echo "豪华双床房(含早中晚餐)：折扣前价格 y7 = ￥" . number_format($y7, 0) . "，折扣后价格 w7 = ￥" . number_format($w7, 0)  . "，预计到手金额 z7 = ￥" . number_format($z7, 2) . "<br>";

            // 显示输入参数的总结
            echo "<h3>输入参数总结</h3>";
            echo "大床房打折后价格 x1 = ￥" . number_format($x1, 2, '.', '') . "<br>";
            echo "早餐价格 a = ￥" . number_format($a, 2, '.', '') . "<br>";
            echo "午餐价格 b = ￥" . number_format($b, 2, '.', '') . "<br>";
            echo "双床房额外价格 c = ￥" . number_format($c, 2, '.', '') . "<br>";
            echo "折扣系数 k = " . number_format($k, 4, '.', '') . "<br><br>";

            // 显示结果表格
            echo "<h3>计算结果</h3>";
            echo "<table>";
            echo "<tr><th>房型</th><th>折扣前价格(￥)</th><th>折扣后价格(￥)</th><th>预计到手金额(￥)</th></tr>";
            echo "<tr><td>大床房(不含早餐)</td><td>" . number_format($y1, 0) . "</td><td>" . number_format($w1, 0) . "</td><td>" . number_format($z1, 2) . "</td></tr>";
            echo "<tr><td>标准大床房(含早餐)</td><td>" . number_format($y2, 0) . "</td><td>" . number_format($w2, 0) . "</td><td>" . number_format($z2, 2) . "</td></tr>";
            echo "<tr><td>标准双床房(含早餐)</td><td>" . number_format($y3, 0) . "</td><td>" . number_format($w3, 0) . "</td><td>" . number_format($z3, 2) . "</td></tr>";
            echo "<tr><td>轻奢大床房(含早晚餐)</td><td>" . number_format($y4, 0) . "</td><td>" . number_format($w4, 0) . "</td><td>" . number_format($z4, 2) . "</td></tr>";
            echo "<tr><td>轻奢双床房(含早晚餐)</td><td>" . number_format($y5, 0) . "</td><td>" . number_format($w5, 0) . "</td><td>" . number_format($z5, 2) . "</td></tr>";
            echo "<tr><td>豪华大床房(含早中晚餐)</td><td>" . number_format($y6, 0) . "</td><td>" . number_format($w6, 0) . "</td><td>" . number_format($z6, 2) . "</td></tr>";
            echo "<tr><td>豪华双床房(含早中晚餐)</td><td>" . number_format($y7, 0) . "</td><td>" . number_format($w7, 0) . "</td><td>" . number_format($z7, 2) . "</td></tr>";
            echo "</table>";
        }
        ?>
    </div>
</body>
</html>
