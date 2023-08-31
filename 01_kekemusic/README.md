
### 1. 项目文件结构
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

### 2. mysql数据写入脚本

- **kkDateUrl.sh**

可以将给定的文本内容写入到 MariaDB 数据库中。请注意，运行此脚本需要您已经设置好了与 MariaDB 的连接，并具有相应的权限。在脚本中，您需要替换 <database_name>, username, password 和 <table_name> 为适当的值。

```
#!/bin/bash

# Database connection details
DB_HOST="localhost"
DB_NAME="<database_name>"
DB_USER="<username>"
DB_PASS="<password>"
TABLE_NAME="<table_name>"

# Read and process the text file
while IFS=',' read -r datetime url; do
  query="INSERT INTO $TABLE_NAME (datetime, url) VALUES ('$datetime', '$url');"

  # Execute the SQL query
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$query"
done < input.txt

```

确保您在运行脚本之前做好了以下几点：

1. 替换 <database_name>、username、password 和 <table_name> 为您的数据库信息和表名。

2. 将脚本中的 input.txt 替换为包含数据的实际文本文件的路径。

运行脚本时，它将逐行读取文本文件的内容，并将每行的数据插入到指定的表中。请注意，此示例假设数据库表已经存在且具有与脚本中的列名相对应的字段。


- **insert_unique_urls.sh**

mysql数据写入脚本，能否避免重复的url的写入

```
#!/bin/bash

# Database connection details
DB_HOST="localhost"
DB_NAME="<database_name>"
DB_USER="<username>"
DB_PASS="<password>"
TABLE_NAME="<table_name>"

# Read and process the text file
while IFS=',' read -r datetime url; do
  # Check if the URL already exists in the table
  existing_url=$(mysql -N -s -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT url FROM $TABLE_NAME WHERE url='$url'")

  # If the URL doesn't exist, insert the data into the table
  if [ -z "$existing_url" ]; then
    query="INSERT INTO $TABLE_NAME (datetime, url) VALUES ('$datetime', '$url');"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$query"
    echo "Inserted: $datetime, $url"
  else
    echo "Skipped (URL already exists): $datetime, $url"
  fi
done < input.txt

```

我们首先执行了一个 SELECT 查询来检查是否存在相同的 URL。如果查询结果为空（即该 URL 在表中不存在），那么我们才会执行插入操作，并在控制台上显示已插入的数据。如果 URL 已经存在，那么我们将显示一条消息表明数据被跳过。

请确保将 <database_name>, username, password, <table_name> 替换为实际的数据库和表名，并根据实际情况修改脚本。


### 3. mysql数据库创建

当您想要创建一个数据库以及其中的表结构时，您可以使用 MySQL 的命令行界面或图形化工具（如 phpMyAdmin）来执行这些操作。以下是在 MySQL 命令行中创建数据库和表结构的步骤示例：

1. 登录到 MySQL 命令行：

打开终端并输入以下命令，然后输入您的 MySQL 密码：

```
mysql -u your_username -p
```

2. 创建数据库：

在 MySQL 命令行中，输入以下命令来创建您的数据库：

```
CREATE DATABASE your_database_name;
```

替换 your_database_name 为您希望的数据库名称。

3. 选择数据库：

创建数据库后，您需要选择它以便执行后续操作。在 MySQL 命令行中，输入以下命令：

```
USE your_database_name;
```

4. 创建表结构：

输入以下命令来创建表结构，以匹配您的文本数据：

```
CREATE TABLE your_table_name (
    datetime DATETIME,
    url VARCHAR(255)
);
```

这将创建一个名为 your_table_name 的表，其中包含两列：datetime 和 url。根据您的需求，您可以调整列的名称和数据类型。

5. 退出 MySQL 命令行：

在完成上述步骤后，您可以输入以下命令退出 MySQL 命令行：

```
exit;
```

以上就是在 MySQL 中创建数据库和表结构的基本步骤。在您运行脚本之前，请确保已经执行了这些步骤，以便脚本可以正确地插入数据到已创建的表中。同时，务必谨慎操作，以免意外删除或更改数据库中的数据。

6. 数据库访问

使用kkmusicdb数据库中的kkmusicTABLE表，查看表的组成，以及datetime和url列

```
mysql -u root -p

SHOW DATABASES;
USE kkmusicdb;
SHOW TABLES;
DESCRIBE kkmusicTABLE;
SELECT datetime,url FROM kkmusicTABLE;

```

### 4. 定时任务

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
