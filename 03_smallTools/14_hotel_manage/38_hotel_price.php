<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>房间价格计算器</title>
</head>
<body>
    <h2>房间价格计算器</h2>
    <form method="POST" action="">
        <label for="x1">大床房打折后价格 x1：</label>
        <input type="number" id="x1" name="x1" step="0.01" required><br><br>
        
        <label for="a">早餐价格 a：</label>
        <input type="number" id="a" name="a" value="40" step="0.01" required><br><br>
        
        <label for="b">午餐价格 b：</label>
        <input type="number" id="b" name="b" value="120" step="0.01" required><br><br>
        
        <label for="c">双床房额外价格 c：</label>
        <input type="number" id="c" name="c" value="90" step="0.01" required><br><br>
        
        <label for="k">折扣系数 k：</label>
        <input type="number" id="k" name="k" value="0.5" step="0.0001" required><br><br>
        
        <input type="submit" value="计算">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 获取输入的参数
        $x1 = isset($_POST['x1']) ? floatval($_POST['x1']) : 0;
        $a = isset($_POST['a']) ? floatval($_POST['a']) : 40;
        $b = isset($_POST['b']) ? floatval($_POST['b']) : 120;
        $c = isset($_POST['c']) ? floatval($_POST['c']) : 90;
        $k = isset($_POST['k']) ? floatval($_POST['k']) : 0.5;

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

        // 显示结果
        echo "<h3>计算结果</h3>";
        echo "大床房(不含早餐)：折扣前价格 y1 = ￥" . number_format($y1, 0) . "，预计到手金额 z1 = ￥" . number_format($z1, 2) . "<br>";
        echo "标准大床房(含早餐)：折扣前价格 y2 = ￥" . number_format($y2, 0) . "，预计到手金额 z2 = ￥" . number_format($z2, 2) . "<br>";
        echo "标准双床房(含早餐)：折扣前价格 y3 = ￥" . number_format($y3, 0) . "，预计到手金额 z3 = ￥" . number_format($z3, 2) . "<br>";
        echo "轻奢大床房(含早晚餐)：折扣前价格 y4 = ￥" . number_format($y4, 0) . "，预计到手金额 z4 = ￥" . number_format($z4, 2) . "<br>";
        echo "轻奢双床房(含早晚餐)：折扣前价格 y5 = ￥" . number_format($y5, 0) . "，预计到手金额 z5 = ￥" . number_format($z5, 2) . "<br>";
        echo "豪华大床房(含早中晚餐)：折扣前价格 y6 = ￥" . number_format($y6, 0) . "，预计到手金额 z6 = ￥" . number_format($z6, 2) . "<br>";
        echo "豪华双床房(含早中晚餐)：折扣前价格 y7 = ￥" . number_format($y7, 0) . "，预计到手金额 z7 = ￥" . number_format($z7, 2) . "<br>";
    }
    ?>
</body>
</html>
