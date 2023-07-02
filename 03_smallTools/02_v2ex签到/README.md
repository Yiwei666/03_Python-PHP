### 项目功能
```
v2ex自动签到

```

### 注意事项
1. 登陆账号获取cookies，headers等信息。
```
https://www.v2ex.com/mission/daily                      # 首次request获取once值

https://www.v2ex.com/mission/daily/redeem?once=70042    # 合成签到链接
```   

3. cron的日志目录
```
/var/log/cron
```

### 安装配置

cron定时任务
```
3 8 * * * /home/00_software/01_Anaconda/bin/python /home/01_html/09_v2ex_spyder.py

```



