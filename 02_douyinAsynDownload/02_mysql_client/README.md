# 1. 项目功能

国内云服务器同步、处理抖音视频

# 2. 文件结构



# 3. 环境配置


已经创建一个mysql数据库 tiktok_db，这个数据库有3列，第一列为mp4视频名字 video_name；第三列为视频的创建时间 creat_time；第三列为视频的存在状态 exist_status，0代表不存在，1代表存在；如下所示

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

现在能否编写一个php脚本，将`/home/01_html/01_tecent1017/25_film_videos` 目录下的所有mp4视频的相关信息写入到mysql数据库中，首先判断目录下的每一个mp4名字是否存在于数据库中，如果不存在，则写入到数据库中，同时将该视频的创建时间写入到 create_time 列，将exist_status状态设置为1。

