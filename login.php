<?php
ob_start(); // 开始输出缓冲
session_start();

// 设置用户名
$username = 'example';
// 设置密码
$password = 'password123';
// 登陆成功后跳转的文件名
$redirect = 'lsfile.php';
// 登陆脚本文件名，推荐使用默认的 login.php
$filename = 'login.php';

// 如果用户已经登录，重定向到受保护的页面
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: $redirect");
    exit;
}

// 检查用户是否提交了登录表单
if (isset($_POST['username']) && isset($_POST['password'])) {
    // 验证用户名和密码（请使用自己的验证代码替换）
    if ($_POST['username'] === $username && $_POST['password'] === $password) {
        // 认证成功，设置会话变量
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $_POST['username'];

        // 重定向到受保护的页面
        header("Location: $redirect");
        exit;
    } else {
        // 认证失败，显示错误消息
        $error = 'Incorrect username or password';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background-color: #f2f2f2;
        }

        #login-form {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: #f00;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div id="login-form">
    <h1>Login</h1>
    <?php if (isset($error)) { ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php } ?>
    <form method="post" action="<?php echo $filename; ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password">

        <button type="submit">Log in</button>
    </form>
</div>
</body>
</html>

<?php
ob_end_flush(); // 刷新输出缓冲
?>
