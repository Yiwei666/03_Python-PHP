# 1. 项目功能

通过Cookie（结合Session）对用户身份进行验证，限制网站的访问权限

# 2. 文件结构

```
login.php                       # 同时包含session和cookie登录验证
login_onlySession.php           # 只通过session登录验证
login_onlyCookie.php            # 仅通过cookie登陆验证

logout.php                      # 同时销毁session和cookie数据
logout_onlySession.php          # 仅销毁所有 session 数据

08_picDisplay_onlySession.php   # 仅验证session
08_picDisplay_onlyCookie.php    # 仅验证cookie
08_picDisplay.php               # 同时验证session和cookie，满足一个即可

lsfile.php                      # 基于session和cookie验证的示例脚本，满足其一即可
```

# 3. 环境配置

### 1. 08_picDisplay_onlySession.php

```php
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
```


### 2. 08_picDisplay_onlyCookie.php

```
<?php

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = '11111xxxxxxxxxxxxxxxxxxxxxx'; // 这应该与加密密钥相匹配

// 尝试仅通过Cookie验证用户身份
if (isset($_COOKIE['user_auth'])) {
    $decryptedValue = decrypt($_COOKIE['user_auth'], $key);
    if ($decryptedValue != 'mcteaone') { // 如果解密后的值不匹配预期，重定向到登录页面
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}

// 如果用户点击了注销链接，清除Cookie并重定向
if (isset($_GET['logout'])) {
    setcookie('user_auth', '', time() - 3600, '/'); // 清除身份验证Cookie
    header('Location: login.php');
    exit;
}
?>
```



# 4. 参考资料

参考项目文件夹：https://github.com/Yiwei666/10_private_code/tree/main/06_smallTools/01_loginCookie
