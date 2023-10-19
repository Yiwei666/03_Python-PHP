<?php
header('Content-Type: text/html; charset=utf-8');

// 设置时区为亚洲/上海
date_default_timezone_set('Asia/Shanghai');

// 连接到 MySQL 服务器
$servername = "101.200.215.126";
$username = "xiaomin";                 
$password = "xiaomin123";
$dbname = "dict_03_GRE";
$port = 3306; // MySQL 服务器端口号，默认为 3306

$conn = new mysqli($servername, $username, $password, $dbname, $port);
$conn->set_charset("utf8mb4");

// 检查连接是否成功
if ($conn->connect_error) {
  die("连接失败: " . $conn->connect_error);
}

// 获取请求的表格名称
$requestedTable = $_GET['table'];

// 如果请求的表格名称为空，设置默认表格名称为 GREtable
if (empty($requestedTable)) {
  $requestedTable = "GREtable";
}

// 查询所有表格的名称
$tableNames = array("GREtable", "TOEFLtable","ILETStable", "CET6table","GRE7500table","SATtable","GEEtable","CET4table");

echo '<div style="display:flex; justify-content:space-between;">';
echo '<div style="position:fixed; top:50%; left:15%; transform:translateY(-50%); width:20%;">';

// 显示所有表格的名称，并添加链接，以便在单击时更改所请求的表格
foreach ($tableNames as $tableName) {
  if ($tableName == $requestedTable) {
    echo "<p><strong>{$tableName}</strong></p>";
  } else {
    echo "<p><a href='?table={$tableName}'>{$tableName}</a></p>";
  }
}
echo '</div>';

echo '<div style="text-align: left; margin: 0 auto; width: 40%; padding: 10px; border: 1px solid #ccc;">';

// 查询表格数据并输出
$sql = "SELECT ID, word, meaning FROM $requestedTable";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // 获取当前年份总天数和当前日期是当年的第几天
  $currentYearDays = date('z', time()) + 1;
  $currentYearTotalDays = date('L') ? 366 : 365; // Leap year check

  // 计算每个表格的长度
  $tableLength = floor($result->num_rows / $currentYearTotalDays);

  // 计算每组的长度
  $groupLength = floor($tableLength);

  // 计算起始和结束位置
  $startPosition = ($currentYearDays - 1) * $groupLength;
  $endPosition = $currentYearDays * $groupLength;

  // 输出表头
  echo "<table><tr><th>ID</th><th>Word</th><th>Meaning</th></tr>";

  // 输出表格数据
  $count = 0;
  while($row = $result->fetch_assoc()) {
    $count++;
    if ($count > $startPosition && $count <= $endPosition) {
      echo "<tr><td>".$row["ID"]."</td><td style='padding-left: 40px'>".$row["word"]."</td><td style='padding-left: 20px'>".$row["meaning"]."</td></tr>";
    }
  }
  echo "</table>";
} else {
  echo "没有数据";
}

echo '</div>';
echo '</div>';

// 关闭数据库连接
$conn->close();
?>
