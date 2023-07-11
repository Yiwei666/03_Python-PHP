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
    <title>Website Display</title>
    <style>
        .container {
            width: 40%;
            margin: 60px auto 0; /* 设置容器顶部间距为 60px，其余间距自动 */
        }
        .website {
            clear: left;
            margin-bottom: 10px; /* 设置网站和介绍文本之间的距离 */
        }
        .description {
            font-size: 12px;
            margin-left: 20px; /* 添加此样式以在容器内部左对齐 */
            margin-bottom: 30px; /* 设置介绍文本和网站之间的距离 */
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    $websites = array(
        "a.com" => array(
            "url" => "https://a.com",
            "description" => "Website A - Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        ),
        "b.com" => array(
            "url" => "https://b.com",
            "description" => "Website B - Sed ut perspiciatis unde omnis iste natus error sit voluptatem."
        ),
        "c.com" => array(
            "url" => "https://c.com",
            "description" => "Website C - At vero eos et accusamus et iusto odio dignissimos ducimus."
        ),
        "d.com" => array(
            "url" => "https://d.com",
            "description" => "Website D - Et harum quidem rerum facilis est et expedita distinctio."
        )
    );

    foreach ($websites as $website => $data) {
        $url = $data['url'];
        $description = $data['description'];
        echo '<div class="website"><a href="' . $url . '">' . $website . '</a></div>';
        echo '<div class="description">' . $description . '</div>';
    }
    ?>
</div>

</body>
</html>


