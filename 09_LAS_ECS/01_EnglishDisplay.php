<?php
// 保存内容的文件名
$filename = "01_EnglishWordNoteData_bVraJKkv0i.txt";
// logo的url
$logo_url = "http://ip/00_logo/dict.png";
// title和h3的内容
$write_text = "Dictionary Content";
// 末尾公司名字
$company_name = "Your Company Name";

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="shortcut icon" href="<?php echo $logo_url; ?>">
  <title><?php echo $write_text; ?></title>
  <style>
    body {
      background-color: #333; /* Dark gray background */
      color: #eee; /* Light white text color */
    }

    h3 {
      text-align: center;
      margin: 20px auto; /* Add 20px margin on top and bottom, and center horizontally */
    }

    textarea[readonly] {
      display: block;
      margin: 0 auto;
      text-align: center;
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用微软雅黑字体作为首选字体 */
    }

    /* 文本显示区域的样式 */
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
      height: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '100em' : '60em'; ?>;     /* 根据终端类型设置文本显示区域的高度 */
      overflow-y: auto; /* 如果内容超过指定高度，则启用垂直滚动条 */
      white-space: pre-wrap; /* 保留文本中的空格和换行符 */
      resize: both; /* 允许水平和垂直同时调整大小 */
      overflow: auto; /* 在调整大小后添加溢出属性以启用滚动条 */
    }

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
  </style>
</head>
<body>
<h3><?php echo $write_text; ?></h3>

<?php
// 从文本文件读取内容并进行展示
if (file_exists($filename)) {
  $file = fopen($filename, "r");
  $content = fread($file, filesize($filename));
  fclose($file);

  // 先将 / / 之间的内容替换为占位符，避免干扰
  $content = preg_replace('/\/(.*?)\//', '[[PLACEHOLDER_YELLOW_$1]]', $content);

  // 对 " " 之间的内容进行红色高亮显示
  $content = preg_replace('/"(.*?)"/', '<span class="highlight-text">"$1"</span>', $content);

  // 将占位符替换为蓝色高亮的 / / 内容
  $content = preg_replace('/\[\[PLACEHOLDER_YELLOW_(.*?)\]\]/', '<span style="color: #258fb8;">/$1/</span>', $content);

  echo "<div id='display-textbox'>$content</div>";
} else {
  echo "<div id='display-textbox'>No content available.</div>";
}
?>

<!--下面的main，footer是有关页面底部版权的style-->
<main>
  <p>You are viewing the dictionary content.</p>
</main>
<footer>
  <p>Copyright &copy; <?php echo date("Y"); ?> <?php echo $company_name; ?></p>
</footer>
</body>
</html>
