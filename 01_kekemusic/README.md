
### 项目文件结构
```
    ├── kkmusic.php
    ├── 04_kekemusic
    │   ├── 01keke.py
    │   ├── musicdown.py
    │   ├── finalmusic.txt       # 临时
    │   ├── latest.html          # 临时
    │   ├── music.html           # 临时
    │   ├── musicUrl.txt         # 临时
    │   └── kkDateUrl.sh         # bash脚本，将finalmusic.txt中的数据写入到mysql数据库中


musicUrl.txt, music.html, latest.html, finalmusic.txt 上述四个文件都是python脚本运行产生的临时文件
```

- kkDateUrl.sh

可以将给定的文本内容写入到 MariaDB 数据库中。请注意，运行此脚本需要您已经设置好了与 MariaDB 的连接，并具有相应的权限。在脚本中，您需要替换 <database_name>, <username>, <password> 和 <table_name> 为适当的值。

```
#!/bin/bash

# Database connection details
DB_HOST="localhost"
DB_NAME="<database_name>"
DB_USER="<username>"
DB_PASS="<password>"
TABLE_NAME="<table_name>"

# Read and process the text file
while IFS= read -r line; do
  datetime=$(echo "$line" | cut -d ' ' -f 1,2)
  url=$(echo "$line" | cut -d ',' -f 2)
  query="INSERT INTO $TABLE_NAME (datetime, url) VALUES ('$datetime', '$url');"

  # Execute the SQL query
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$query"
done < input.txt

```

确保您在运行脚本之前做好了以下几点：

替换 <database_name>、<username>、<password> 和 <table_name> 为您的数据库信息和表名。

将脚本中的 input.txt 替换为包含数据的实际文本文件的路径。

运行脚本时，它将逐行读取文本文件的内容，并将每行的数据插入到指定的表中。请注意，此示例假设数据库表已经存在且具有与脚本中的列名相对应的字段。



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
```

centos系统查看python安装路径命令
```
which python
```

注意kekemusic.php脚本可以不用与python脚本放在同一个目录下

**注意：由于本项目没有涉及到浏览器php脚本对后端文本的读写和python脚本调用，因此不需要设置权限。python脚本的调用和对txt文件的读写依赖于crontab定时任务，因此不需要设置执行和读写权限。**
