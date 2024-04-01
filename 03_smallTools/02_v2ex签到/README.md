# 1. 项目功能

- v2ex自动签到
- v2ex自定义CSS参考案例

# 2. 项目结构

- 08_v2ex_spyder.py

爬虫脚本，用于v2ex自动签到，需要手动设置cookie和header信息

- 14_cookies_json.php

将chrome等浏览器获取的cookie字符串转化成字典格式



# 3. 注意事项


1. 登陆账号获取cookies，headers等信息。
```
https://www.v2ex.com/mission/daily                      # 首次request获取once值

https://www.v2ex.com/mission/daily/redeem?once=70042    # 合成签到链接
```   

- 打开开发者工具——快捷键`Ctrl+Shift+I`
- 在开发者工具中，点击`应用程序`（Application）标签。如果你看不到这个标签，可能需要点击`>>`按钮来找到它。
- 在左侧的菜单中，找到`存储`（Storage）下的`Cookies`，然后选择你想查看的网站。你会在右侧看到该网站的所有Cookie列表。
- 这里可以查看Cookie的名称、值、域、路径、过期时间等信息。你还可以对Cookie进行编辑或删除操作，以测试网站的不同行为。这对于开发者调试网站功能特别有用。

<p align="center">
<img src="https://19640810.xyz/05_image/01_imageHost/20240401-210732.png" alt="Image Description" width="900">
</p>


2. cron的日志目录
```
/var/log/cron
```

3. 爬虫cookie设置

cookie信息满足以下8项即可

```
cookies = {
    'V2EX_LANG': '1',
    '_ga': '2',
    'A2': '3',
    'PB3_SESSION': '4',
    '_gid': '5',
    '__gads': '6',
    '__gpi': '7',
    'V2EX_TAB': '8'
}
```
注意：

- cookie信息可能会在一个月后失效，届时需要重新设置

- 推荐在chrome浏览器中登录v2ex获取cookie信息，firefox浏览器获取的cookies运行报错

# 4. 安装配置

设定cron定时任务，注意v2ex要求签到间隔为24小时
```
3 8 * * * /home/00_software/01_Anaconda/bin/python /home/01_html/09_v2ex_spyder.py

```



