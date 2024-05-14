# 1. 项目功能

使用mysql数据库来储存、管理抖音视频的下载

# 2. 文件结构

```
├── 18_db_config.php        # 创建mysql连接对象
├── 18_url_get.php          # 将web页面上提交的抖音链接保存到数据库中，忽略已存在的链接
├── 18_view_log.php         # 在web页面上显示最后两次提交的抖音视频链接
├── 18_douyinDown.py        # 下载抖音视频的爬虫脚本
├── 02_douyVideo            # 存储视频的文件夹
│   ├── 20240513-204331.mp4
│   ├── ...
```


# 3. 环境配置


### 1. 创建数据库

```sql
CREATE DATABASE douyin_db;
```

2. 创建表结构

```sql
CREATE TABLE douyin_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_url VARCHAR(255) NOT NULL,
    url_write_time DATETIME NOT NULL,
    download_status TINYINT NOT NULL DEFAULT 0,
    downloaded_video_name VARCHAR(255),
    video_download_time DATETIME
);
```

- `id`: 自增的主键，用于唯一标识每条记录。
- `video_url`: 存储视频的 URL。
- `url_write_time`: 记录视频 URL 写入的时间。
- `download_status`: 标记视频的下载状态，0 表示未下载，1 表示已下载。
- `downloaded_video_name`: 下载后的视频命名。
- `video_download_time`: 视频的下载时间。


```
+-----------------------+--------------+------+-----+---------+----------------+
| Field                 | Type         | Null | Key | Default | Extra          |
+-----------------------+--------------+------+-----+---------+----------------+
| id                    | int          | NO   | PRI | NULL    | auto_increment |
| video_url             | varchar(255) | NO   |     | NULL    |                |
| url_write_time        | datetime     | NO   |     | NULL    |                |
| download_status       | tinyint      | NO   |     | 0       |                |
| downloaded_video_name | varchar(255) | YES  |     | NULL    |                |
| video_download_time   | datetime     | YES  |     | NULL    |                |
+-----------------------+--------------+------+-----+---------+----------------+
```

























