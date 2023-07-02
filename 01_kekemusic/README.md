
### 项目文件结构
```
    ├── kkmusic.php
    ├── 04_kekemusic
    │   ├── 01keke.py
    │   ├── finalmusic.txt
    │   ├── latest.html
    │   ├── musicdown.py
    │   ├── music.html
    │   └── musicUrl.txt

musicUrl.txt
music.html
latest.html
finalmusic.txt
上述四个文件都是python脚本运行产生的临时文件
```

### 定时任务

01keke.py 和 musicdown.py 搭配使用的crontab定时任务脚本

```
0 21 * * * rm  /home/01_html/04_kekemusic/musicUrl.txt
0 21 * * * rm  /home/01_html/04_kekemusic/finalmusic.txt
0 21 * * * curl -o /home/01_html/04_kekemusic/latest.html  https://www.kekenet.com/song/tingge/
2 21 * * * /home/00_software/01_Anaconda/bin/python  /home/01_html/04_kekemusic/01keke.py
4 21 * * * /home/00_software/01_Anaconda/bin/python  /home/01_html/04_kekemusic/musicdown.py

```

cron的日志目录
```
/var/log/cron
``

centos系统查看python安装路径命令
```
which python
```

注意kekemusic.php脚本可以不用与python脚本放在同一个目录下

**注意：由于本项目没有涉及到浏览器php脚本对后端文本的读写和python脚本调用，因此不需要设置权限。python脚本的调用和对txt文件的读写依赖于crontab定时任务，因此不需要设置执行和读写权限。**
