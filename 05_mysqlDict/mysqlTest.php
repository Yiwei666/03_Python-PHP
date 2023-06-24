<?php
header('Content-Type: text/html; charset=utf-8');
$dbhost = '101.200.200.100';  // mysql服务器主机地址
$dbuser = 'xiaomin';            // mysql用户名
$dbpass = 'xm123456';          // mysql用户名密码
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
$conn->set_charset("utf8mb4");
if(! $conn )
{
    die('Could not connect: ' . mysqli_error());
}
echo '数据库连接成功！';
mysqli_close($conn);
?>
