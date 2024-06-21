<?php
date_default_timezone_set('Asia/Shanghai');  // 确保设置为北京时间

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "平台: " . $_POST["platform"] . "<br>";
    echo "姓名: " . $_POST["name"] . "<br>";
    echo "入住日期: " . $_POST["checkin_date"] . "<br>";

    list($startDate, $endDate) = explode(' - ', $_POST["checkin_date"]);
    $startDay = date('l', strtotime($startDate));
    $endDay = date('l', strtotime($endDate));
    $weekDays = [
        'Monday' => '周一',
        'Tuesday' => '周二',
        'Wednesday' => '周三',
        'Thursday' => '周四',
        'Friday' => '周五',
        'Saturday' => '周六',
        'Sunday' => '周日'
    ];
    $startDayChinese = isset($weekDays[$startDay]) ? $weekDays[$startDay] : '';
    $endDayChinese = isset($weekDays[$endDay]) ? $weekDays[$endDay] : '';

    echo "入住星期: " . $startDayChinese . " - " . $endDayChinese . "<br>";
    
    // 处理到账金额，支持基本的算术运算
    $amount = eval('return ' . $_POST["amount"] . ';');
    echo "到账金额: " . $amount . " 元" . "<br>";
    
    echo "房型: " . $_POST["room_type"] . "<br>";
    echo "间数: " . $_POST["number_of_rooms"] . "<br>";
    echo "备注: " . $_POST["remarks"] . "<br>";
}

?>



<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>酒店预订信息表单</title>
</head>
<body>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="platform">平台:</label>
        <select name="platform" id="platform">
            <option value="携程">携程</option>
            <option value="美团酒店">美团酒店</option>
            <option value="美团民宿">美团民宿</option>
        </select>
        <br>
        <label for="name">姓名:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="checkin_date">入住日期:</label>
        <select name="checkin_date" id="checkin_date">
            <option value="<?php echo date("Y/m/d") . ' - ' . date("Y/m/d", strtotime("+1 day")); ?>">
                <?php echo date("Y/m/d") . ' - ' . date("Y/m/d", strtotime("+1 day")); ?>
            </option>
            <option value="<?php echo date("Y/m/d", strtotime("+1 day")) . ' - ' . date("Y/m/d", strtotime("+2 days")); ?>">
                <?php echo date("Y/m/d", strtotime("+1 day")) . ' - ' . date("Y/m/d", strtotime("+2 days")); ?>
            </option>
            <option value="<?php echo date("Y/m/d", strtotime("+2 day")) . ' - ' . date("Y/m/d", strtotime("+3 days")); ?>">
                <?php echo date("Y/m/d", strtotime("+2 day")) . ' - ' . date("Y/m/d", strtotime("+3 days")); ?>
            </option>
        </select>
        <br>
        <label for="amount">到账金额:</label>
        <input type="text" id="amount" name="amount" required>
        <br>
        <label for="room_type">房型:</label>
        <select name="room_type" id="room_type">
            <option value="大床房（不带餐）">大床房（不带餐）</option>
            <option value="标准大床房（含早）">标准大床房（含早）</option>
            <option value="标准双床房（含早）">标准双床房（含早）</option>
            <option value="轻奢大床房（含早晚）">轻奢大床房（含早晚）</option>
            <option value="轻奢双床房（含早晚）">轻奢双床房（含早晚）</option>
            <option value="豪华双床房（含早中晚）">豪华双床房（含早中晚）</option>
        </select>
        <br>
        <label for="number_of_rooms">间数:</label>
        <select name="number_of_rooms" id="number_of_rooms">
            <option value="1间1晚">1间1晚</option>
            <option value="1间2晚">1间2晚</option>
            <option value="2间1晚">2间1晚</option>
            <option value="2间2晚">2间2晚</option>
            <option value="3间1晚">3间1晚</option>
        </select>
        <br>
        <label for="remarks">备注:</label>
        <input type="text" id="remarks" name="remarks">
        <br>
        <input type="submit" value="提交">
    </form>
</body>
</html>
