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



### 2. `03_tk_video_check.php`



### 3. `03_copy_remote_to_local.php`




### 4. `03_random_replace_video.php`




- 定时任务

```bash
/30 * * * * /usr/bin/php /home/01_html/03_mysql_douyin/03_random_replace_video.php
```








