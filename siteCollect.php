<?php
session_start();

// 设置保存标题和网址的文件名
$filename = "siteCollectUrl.txt";
// 设置网站页面标题
$title = "siteCollect";
// 将siteCollect.php 替换为新的 php 脚本文件名
$logout_script = "siteCollect.php?logout=true";

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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="http://101.200.215.127/00_logo/firewall.png">
  <title><?php echo $title; ?></title>
  <style>
    .container {
      width: 88%;
      margin: 1.5% auto 0;
      display: flex; /* 添加这段代码 */
      justify-content: center; /* 水平居中 */
    }

    table {
      font-family: Arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }

    th, td {
      border: 1px solid black;
      padding: 10px;
      text-align: center;
    }
    
    th {
      background-color: #f2f2f2;
    }

    form {
      text-align: center;
    }

    form label, form input[type="text"], form input[type="submit"] {
      display: block;
      margin: 2px auto;
      width: 20%;
    }

    form input[type="submit"] {
      width: 5%;
    }

    body {
      text-align: left;
    }
    
    /*下面的main，footer是有关logout的style*/
    main,footer {
      font-family: Arial, sans-serif;
      text-align: center;
    }
    p {
      font-size: 14px;
      margin-top: 20px;
      margin-bottom: 20px;
    }
  </style>
</head>

<body>

  <?php
    if(isset($_POST['siteCollect']) && isset($_POST['siteUrl'])) {
      // 读取表单数据
      // $siteCollect = $_POST['siteCollect'];
      $siteCollect = str_replace(',', '，', $_POST['siteCollect']);
      $siteUrl = $_POST['siteUrl'];
      
      if(!empty($siteCollect) && !empty($siteUrl)) {
        // 读取现有网站信息
        $file = file($filename);

        // 检查是否存在相同的网址
        foreach($file as $line) {
          list($name, $url) = explode(',', $line);
          if(trim($url) === trim($siteUrl)) {
            // 如果网址已经存在，弹出提示框
            echo "<script>alert('该网址已存在，请重新输入。');</script>";

            // 清除已输入的表单数据
            $_POST['siteCollect'] = '';
            $_POST['siteUrl'] = '';

            // 重定向到当前页面，避免重新提交数据
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
          }
        }

        // 如果网址不存在，则写入文件
        $fp = fopen($filename, 'a');
        fwrite($fp, "$siteCollect,$siteUrl\n");
        fclose($fp);

        // 清除已输入的表单数据
        $_POST['siteCollect'] = '';
        $_POST['siteUrl'] = '';

        // 重定向到当前页面，避免重新提交数据
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
      }
    }
  ?>

  <form method="post">
    <label for="siteCollect">网站名字：</label>
    <input type="text" name="siteCollect" id="siteCollect" value="<?php echo isset($_POST['siteCollect']) ? htmlspecialchars($_POST['siteCollect']) : ''; ?>"><br>

    <label for="siteUrl">网址：</label>
    <input type="text" name="siteUrl" id="siteUrl" value="<?php echo isset($_POST['siteUrl']) ? htmlspecialchars($_POST['siteUrl']) : ''; ?>"><br>

    <input type="submit" value="提交">
  </form>

  <div class="container">
    <table>
      <thead>
        <tr>
          <th>Column 1</th>
          <th>Column 2</th>
          <th>Column 3</th>
          <th>Column 4</th>
          <th>Column 5</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // 读取文件并输出网站链接
        $file = file($filename);
        for($i=0; $i<count($file); $i+=5) {
          echo '<tr>';
          for($j=$i; $j<$i+5 && $j<count($file); $j++) {
            list($name, $url) = explode(',', $file[$j]);
            echo '<td><a href="' . htmlspecialchars($url) . '" target="_blank" style="text-decoration:none;">' . htmlspecialchars($name) . '</a></td>';
          }
          echo '</tr>';
        }
        ?>
      </tbody>
    </table>
  </div>

  <!--下面的main，footer是有关logout的style-->
  <main>
    <p>You have successfully logged in.</p>
    <p><a href="<?php echo $logout_script; ?>">Logout</a></p>
  </main>
  <footer>
    <p>&copy; <?php echo date("Y"); ?> Your Company Name</p>
  </footer>
</body>
</html>
