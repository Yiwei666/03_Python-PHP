<?php
session_start();

// logo图片地址
$logo_url = "http://mctea.one/00_logo/list.png";

// 服务器根目录，通常位域名或者ip地址
$root_url = "http://icha.one/";

// php文件名
$scriptname = "lsfile_js.php";

// 末尾公司名字
$company_name = "ICha Company";

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
        background-color: #f5f5f5;
        color: #333;
        margin: 0;
        padding: 0;
      }
      h1 {
        text-align: center;
        margin-top: 50px;
      }
      table {
        border-collapse: collapse;
        margin: auto;
        width: 50%;
      }
      th, td {
        padding: 10px;
        border: 1px solid black;
      }
      th {
        background-color: #ddd;
        font-weight: bold;
      }
      tr:nth-child(even) {
        background-color: #f2f2f2;
      }
      a {
        text-decoration: none;
      }
/*下面的main，footer是有关logout的style*/
      main,footer {
        font-family: Arial, sans-serif;
        text-align: center;
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

<!--下面的main，footer是有关logout的style-->
<main>
  <p>You have successfully logged in.</p>
  <p><a href="<?php echo $scriptname; ?>?logout=true">Logout</a></p>
</main>
<footer>
  <p>Copyright &copy; 2023 <?php echo $company_name; ?></p>
</footer>
</body>
</html>
