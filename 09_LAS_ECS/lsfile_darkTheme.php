<?php
session_start();

// logo图片地址
$logo_url = "https://domain.com/00_logo/list.png";

// 服务器根目录，通常为域名或者ip地址，用于构造跳转链接，末尾"/"需保留
$root_url = "https://domain.com/";

// php文件名，实际脚本名与此处需要一致
$scriptname = "lsfile_darkTheme.php";

// 末尾公司名字
$company_name = "HW Company";

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// If the user clicked the logout link, log them out and redirect to the login page
if (isset($_GET['logout'])) {
  session_destroy(); // destroy all session data
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo $logo_url; ?>">
    <title>List of Files</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #303030; /* 修改为深色主题的背景色 */
        color: #CCCCCC; /* 修改为深色主题的文本颜色 */
        margin: 0;
        padding: 0;
      }
      h1 {
        text-align: center;
        margin-top: 50px;
        color: #CCCCCC; /* 修改为深色主题的标题颜色 */
      }
      table {
        border-collapse: collapse;
        margin: auto;
        width: 50%;
        color: #333; /* 修改为深色主题的文本颜色 */
      }
      th, td {
        padding: 10px;
        border: 1px solid #CCCCCC; /* 修改为深色主题的边框颜色 */
      }
      th {
        background-color: #555; /* 修改为深色主题的表头背景色 */
        font-weight: bold;
        color: #CCCCCC; /* 修改为深色主题的表头文本颜色 */
      }
      tr:nth-child(even) {
        background-color: #444; /* 修改为深色主题的偶数行背景色 */
        color: #CCCCCC; /* 修改为深色主题的偶数行文本颜色 */
      }
      a {
        text-decoration: none;
        color: #258fb8; /* 修改为深色主题的链接颜色 */
      }
      .theme-buttons-container {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 10px;
      }
      .theme-button {
        width: 25px;
        height: 25px;
        background-color: #303030;
        color: #CCCCCC;
        border: 1px solid #CCCCCC;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .theme-button:nth-child(1) {
        background-color: #CCCCCC;
      }
      .theme-button:nth-child(2) {
        background-color: #258fb8;
      }
      /* 下面的main，footer是关于logout的样式 */
      main, footer {
        font-family: Arial, sans-serif;
        text-align: center;
        color: #CCCCCC; /* 修改为深色主题的文本颜色 */
      }
      p {
        font-size: 15px;
        margin-top: 20px;
        margin-bottom: 20px;
      }
    </style>

</head>
<body>

<h1>List of Files</h1>

<table>
  <tr>
    <th>File Name</th>
    <th>File Name</th>
    <th>File Name</th>
    <th>File Name</th>
    <th>File Name</th>
    <th>File Name</th>
  </tr>

  <?php
  // Directory path
  $dir = "./";

  // Get all files and directories sorted by modification time
  $files = scandir($dir, SCANDIR_SORT_ASCENDING);  //按照首字母升序

  // Filter files with ".html", ".php", and ".js" extensions
  $filteredFiles = array();
  foreach ($files as $file) {
    if ($file != "." && $file != ".." && (substr($file, -5) == ".html" || substr($file, -4) == ".php" || substr($file, -3) == ".js")) {
      $filteredFiles[] = $file;
    }
  }

  $count = 0;
  foreach ($filteredFiles as $file) {
    if ($count % 6 == 0) {
      echo "<tr>";
    }
    echo "<td><a target='_blank' rel='noopener' href='" . $root_url . $file . "'>" . $file . "</a></td>";
    $count++;
    if ($count % 6 == 0) {
      echo "</tr>";
    }
  }

  if ($count % 6 != 0) {
    while ($count % 6 != 0) {
      echo "<td></td>";
      $count++;
    }
    echo "</tr>";
  }
  ?>

</table>

<!-- Theme buttons -->
<div class="theme-buttons-container">
  <div class="theme-button" onclick="changeTheme('light')"></div>
  <div class="theme-button" onclick="changeTheme('blue-green')"></div>
</div>

<script>
  function changeTheme(theme) {
    const body = document.body;
    const links = document.querySelectorAll('a');

    if (theme === 'light') {
      body.style.color = '#CCCCCC';
      links.forEach(link => link.style.color = '#CCCCCC');
    } else if (theme === 'blue-green') {
      body.style.color = '#258fb8';
      links.forEach(link => link.style.color = '#258fb8');
    }
  }
</script>

<!--下面的main，footer是有关logout的样式-->
<main>
  <p>You have successfully logged in.</p>
  <p><a href="<?php echo $scriptname; ?>?logout=true">Logout</a></p>
</main>
<footer>
  <p>Copyright &copy; <?php echo date("Y"); ?> <?php echo $company_name; ?></p>
</footer>
</body>
</html>
