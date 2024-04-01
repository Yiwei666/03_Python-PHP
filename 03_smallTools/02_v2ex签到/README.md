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

# 安装配置

设定cron定时任务，注意v2ex要求签到间隔为24小时
```
3 8 * * * /home/00_software/01_Anaconda/bin/python /home/01_html/09_v2ex_spyder.py

```



