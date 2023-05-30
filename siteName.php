<?php
session_start();

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
  <link rel="shortcut icon" href="http://101.200.215.127/00_logo/siteName.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>chatGPT-3.5</title>
  <style>
    .container {
      width: 60%;
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
    if(isset($_POST['siteName']) && isset($_POST['siteUrl'])) {
      // 读取表单数据
      $siteName = $_POST['siteName'];
      $siteUrl = $_POST['siteUrl'];
      
      if(!empty($siteName) && !empty($siteUrl)) {
        // 读取现有网站信息
        $file = file('siteNameUrl.txt');

        // 检查是否存在相同的网址
        foreach($file as $line) {
          list($name, $url) = explode(',', $line);
          if(trim($url) === trim($siteUrl)) {
            // 如果网址已经存在，弹出提示框
            echo "<script>alert('该网址已存在，请重新输入。');</script>";

            // 清除已输入的表单数据
            $_POST['siteName'] = '';
            $_POST['siteUrl'] = '';

            // 重定向到当前页面，避免重新提交数据
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
          }
        }

        // 如果网址不存在，则写入文件
        $fp = fopen('siteNameUrl.txt', 'a');
        fwrite($fp, "$siteName,$siteUrl\n");
        fclose($fp);

        // 清除已输入的表单数据
        $_POST['siteName'] = '';
        $_POST['siteUrl'] = '';

        // 重定向到当前页面，避免重新提交数据
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
      }
    }
  ?>

  <form method="post">
    <label for="siteName">网站名字：</label>
    <input type="text" name="siteName" id="siteName" value="<?php echo isset($_POST['siteName']) ? htmlspecialchars($_POST['siteName']) : ''; ?>"><br>

    <label for="siteUrl">网址：</label>
    <input type="text" name="siteUrl" id="siteUrl" value="<?php echo isset($_POST['siteUrl']) ? htmlspecialchars($_POST['siteUrl']) : ''; ?>"><br>

    <input type="submit" value="提交">
  </form>

  <div class="container">
    <table>
      <thead>
        <tr>
          <th>网站1名字</th>
          <th>网站1链接</th>
          <th>网站2名字</th>
          <th>网站2链接</th>
          <th>网站3名字</th>
          <th>网站3链接</th>
        </tr>
      </thead>
      <tbody>
        <?php
          // 读取文件并输出网站链接
          $file = file('siteNameUrl.txt');
          for($i=0; $i<count($file); $i+=3) {
            echo '<tr>';
            for($j=$i; $j<$i+3 && $j<count($file); $j++) {
              list($name, $url) = explode(',', $file[$j]);
              echo '<td><a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($name) . '</a></td>';
              echo '<td>' . htmlspecialchars($url) . '</td>';
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
    <p><a href="siteName.php?logout=true">Logout</a></p>
  </main>
  <footer>
    <p>&copy; <?php echo date("Y"); ?> Your Company Name</p>
  </footer>
</body>
</html>
