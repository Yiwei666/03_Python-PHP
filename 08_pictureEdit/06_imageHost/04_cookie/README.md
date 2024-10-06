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


# 4. 参考资料

参考项目文件夹：https://github.com/Yiwei666/10_private_code/tree/main/06_smallTools/01_loginCookie
