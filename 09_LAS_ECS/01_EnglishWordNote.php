<?php
session_start();

// 保存内容的文件名
$filename = "01_EnglishWordNoteData_bVraJKkv0i.txt";
// php文件名
$scriptname = "01_EnglishWordNote.php";
// logo的url
$logo_url = "http://ip/00_logo/dict.png";
// php文件名的url
$question_url = "http://ip/01_EnglishWordNote.php";
// title和h3的内容
$write_text = "Write to Dictonary File";
// 输入框上面的文字
$enter_text = "Enter Dictonary Data";
// 提交按钮的文字
$submit_text = "Submit Dictonary";
// 显示按钮的文字
$display_text = "Display Latest Content";
// 末尾公司名字
$company_name = "Your Company Name";
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
  <meta charset="utf-8">
  <link rel="shortcut icon" href="<?php echo $logo_url; ?>">
  <title><?php echo $write_text; ?></title>
  <style>
    /* 其他样式保持不变 */

    body {
      background-color: #333; /* Dark gray background */
      color: #eee; /* Light white text color */
    }

    h3 {
      text-align: center;
      margin: 20px auto; /* Add 20px margin on top and bottom, and center horizontally */
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    #questiondata {
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用微软雅黑字体作为中文首选字体，英文和数字使用Arial字体 */
    }

    textarea {
      background-color: #333; /* Dark gray background for textarea */
      color: #eee; /* Light white text color for textarea */
    }

    
    textarea[readonly] {
      display: block;
      margin: 0 auto;
      text-align: center;
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用微软雅黑字体作为首选字体 */
    }

    .unshow-container {
      text-align: center;
      margin-top: 20px;
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

    a {
      color: #00bcd4; /* Blue-green color for links */
    }

    /* 新增规则，选择.highlight-text样式的文字，设置为红色 */
    .highlight-text {
      color: red;
    }

    #display-textbox {
      background-color: #333; /* 文本区域的深灰色背景 */
      color: #eee; /* 文本区域的浅白色文字颜色 */
      display: block; /* 将显示属性设置为块级元素 */
      margin: 0 auto; /* 使用自动边距水平居中元素 */
      text-align: center; /* 将文本在元素中居中 */
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用Microsoft YaHei、Arial或sans-serif作为首选字体 */
      padding: 10px; /* 在元素内部添加10像素的填充 */
      border: 0.5px solid #eee; /* 添加0.5像素的实线边框以提高可见性 */
      width: 78ch; /* 将元素的宽度设置为字符宽度的80个字符 */
      height: 20em; /* 将元素的高度设置为大约16行的高度 */
      overflow-y: auto; /* 如果内容超过指定高度，则启用垂直滚动条 */
      white-space: pre-wrap; /* 保留文本中的空格和换行符 */
      resize: both; /* 允许水平和垂直同时调整大小 */
      overflow: auto; /* 在调整大小后添加溢出属性以启用滚动条 */
    }

  </style>
  <script>
    function toggleVisibility() {
      window.location.href = "<?php echo $question_url; ?>";
    }
  </script>
</head>
<body>
<h3><?php echo $write_text; ?></h3>
<form action="<?php echo $scriptname; ?>" method="post">
  <p><label for="questiondata"><?php echo $enter_text; ?>:</label></p>
  <textarea rows="16" cols="100" id="questiondata" name="questiondata"></textarea><br><br>
  <input type="submit" value="<?php echo $submit_text; ?>">
</form>

<br><br>
<form action="<?php echo $scriptname; ?>" method="get">
  <input type="submit" value="<?php echo $display_text; ?>" name="display_content">
</form>
<div class="unshow-container">
  <input type="button" value="Unshow" onclick="toggleVisibility()">
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  date_default_timezone_set("Asia/Shanghai");
  $data = $_POST['questiondata'];
  // $data = "[" . date("Y-m-d H:i:s") . "]\n" . $data . "\n\n";
  $data = "[" . date("Y-m-d H:i:s") . " " . date("l") . "]\n" . $data . "\n\n";
  $file = fopen($filename, "r");
  $content = fread($file, filesize($filename));
  fclose($file);
  $file = fopen($filename, "w");
  fwrite($file, $data . $content);
  fclose($file);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['display_content'])) {
  $file = fopen($filename, "r");
  $content = fread($file, filesize($filename));
  fclose($file);

  // 先将 / / 之间的内容替换为占位符，避免干扰
  $content = preg_replace('/\/(.*?)\//', '[[PLACEHOLDER_YELLOW_$1]]', $content);

  // 对 " " 之间的内容进行红色高亮显示
  $content = preg_replace('/"(.*?)"/', '<span class="highlight-text">"$1"</span>', $content);

  // 将占位符替换为蓝色高亮的 / / 内容
  $content = preg_replace('/\[\[PLACEHOLDER_YELLOW_(.*?)\]\]/', '<span style="color: #258fb8;">/$1/</span>', $content);

  echo "<br><br><div id='display-textbox'>$content</div>";
}
?>
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
