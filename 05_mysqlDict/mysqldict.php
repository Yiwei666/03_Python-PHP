<?php
header('Content-Type: text/html; charset=utf-8');
// 连接到 MySQL 服务器
$servername = "101.200.200.100";   // ip 或 domian，当php脚本和mysql数据库位于同一云服务器中，此处可写为localhost
$username = "xiaomin";             // mysql 用户名
$password = "xiaomin123";          // mysql 密码
$dbname = "dict_03_GRE";           // 数据库名称
$port = 3306;                      // MySQL 服务器端口号，默认为 3306

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

// 页面背景颜色改为灰黑色，字体颜色为白色
echo '<style>';
echo 'body { background-color: #333; color: #eee; }';
echo 'a { color: #00bcd4; }'; // Blue-green color for links
echo '</style>';

echo '<div style="display:flex; justify-content:space-between;">';
echo '<div style="position:fixed; top:50%; left:5%; transform:translateY(-50%); width:20%;">';
// 显示所有表格的名称，并添加链接，以便在单击时更改所请求的表格
foreach ($tableNames as $tableName) {
  if ($tableName == $requestedTable) {
    echo "<p><strong>{$tableName}</strong></p>";
  } else {
    echo "<p><a href='?table={$tableName}'>{$tableName}</a></p>";
  }
}
echo '</div>';

echo '<div style="text-align: left; margin: 0 auto; width: 60%; padding: 10px; border: 1px solid #ccc;">';
// 查询表格数据并输出
$sql = "SELECT ID, word, meaning FROM $requestedTable";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // 输出表头
  echo "<table><tr><th>ID</th><th>Word</th><th>Meaning</th></tr>";
  // 输出表格数据
  while($row = $result->fetch_assoc()) {
    echo "<tr><td>".$row["ID"]."</td><td style='padding-left: 40px'>".$row["word"]."</td><td>".$row["meaning"]."</td></tr>";
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
