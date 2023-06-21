


01keke.py 和 musicdown.py 搭配使用的crontab定时任务脚本

```
0 21 * * * rm  /home/01_html/04_kekemusic/musicUrl.txt
0 21 * * * rm  /home/01_html/04_kekemusic/finalmusic.txt
0 21 * * * curl -o /home/01_html/04_kekemusic/latest.html  https://www.kekenet.com/song/tingge/
2 21 * * * /home/00_software/01_Anaconda/bin/python  /home/01_html/04_kekemusic/01keke.py
4 21 * * * /home/00_software/01_Anaconda/bin/python  /home/01_html/04_kekemusic/musicdown.py

```

centos系统查看python安装路径命令
```
which python
```

