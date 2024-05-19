# 1. 项目功能

国内云服务器同步、处理抖音视频

1. 将onedrive中的mp4文件名同步到本地数据库中
2. 定时更新服务器中的mp4文件

# 2. 文件结构

```
├── 03_mysql_douyin
│   ├── 03_copy_remote_to_local.php
│   ├── 03_db_config.php                 // 连接到数据库，创建 $mysql 连接对象
│   ├── 03_random_replace_video.php
│   └── 03_tk_video_check.php
```

# 3. 环境配置

### 1. `03_db_config.php`

1. 数据库

- 创建一个mysql数据库 tiktok_db，这个数据库有3列，第一列为mp4视频名字 video_name；第三列为视频的创建时间 creat_time；第三列为视频的存在状态 exist_status，0代表不存在，1代表存在；如下所示

```
mysql> describe tk_videos;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| video_name   | varchar(255) | NO   |     | NULL    |                |
| create_time  | datetime     | NO   |     | NULL    |                |
| exist_status | tinyint      | NO   |     | NULL    |                |
+--------------+--------------+------+-----+---------+----------------+
4 rows in set (0.00 sec)
```

2. 环境变量

```php
$host = 'localhost'; // 通常是 'localhost' 或一个IP地址
$username = 'root'; // 数据库用户名
$password = '123456'; // 数据库密码
$dbname = 'tiktok_db'; // 数据库名称
```

### 2. `03_tk_video_check.php`

1. 功能

- 检查和打开目录：验证指定目录（存放视频文件）是否存在并可读，然后打开这个目录。
- 遍历目录中的视频文件：遍历指定目录下的所有文件，对于每个 .mp4 视频文件，检查数据库中是否已存在该视频文件的记录。
- 数据库操作：
  - 如果视频文件在数据库中不存在，将其信息（文件名和创建时间）插入数据库，并设置其存在状态为 1。
  - 遍历数据库中的视频记录，检查每个视频文件是否存在于本地目录。如果文件存在但数据库标记为不存在（exist_status = 0），则更新状态为存在（exist_status = 1）并更新创建时间。如果文件不存在，则更新状态为不存在（exist_status = 0）。
  - 统计和打印信息：计算并打印数据库中所有视频的总数，以及存在状态为 1 和 0 的视频数量。
- 关闭数据库连接：在操作完成后关闭数据库连接。

2. 环境变量

```php
// 引入数据库配置和连接
include '03_db_config.php';

// 指定视频文件所在目录
$video_dir = "/home/01_html/01_tecent1017/25_film_videos";
```

### 3. `03_copy_remote_to_local.php`

1. 功能

- 使用 rclone 获取远程目录的视频文件：通过 rclone 命令获取远程目录中所有 .mp4 格式的视频文件名。
- 数据库操作：
  - 遍历获取到的视频文件名，检查数据库中是否已存在这些视频文件的记录。
  - 如果视频文件在数据库中不存在，则将其插入数据库，并设置其创建时间为当前时间，存在状态为 0。
  - 文件复制：代码中有注释掉的部分，本意是使用 rclone 复制不存在于数据库中的视频文件到本地目录，但这部分代码在实际运行中被注释掉了。
- 统计和打印信息：计算并打印出新插入数据库的视频文件数量。
- 关闭数据库连接：在操作完成后关闭数据库连接。

2. 环境变量

```php
// 引入数据库配置和连接
include '03_db_config.php';

// 远程目录
$remote_dir = "HW-1012:do1-2/01_html/02_douyVideo";
// 本地目录
$local_dir = "/home/01_html/01_tecent1017/25_film_videos";
```


### 4. `03_random_replace_video.php`




- 定时任务

```bash
/30 * * * * /usr/bin/php /home/01_html/03_mysql_douyin/03_random_replace_video.php
```








