# 1. 项目功能

1. 将云服务器指定路径下的视频资源写入到`mysql数据库`中，增加点赞/踩功能
2. 使用`签名url`及`referer`核验限制非法用户对视频资源的请求

# 2. 文件结构

```
.
├── 05_db_config.php                             # 连接数据库
├── 05_db_sync_videos.php                        # 功能模块：将指定目录下的mp4文件名追加到数据库中
├── 05_video_dislikes_delete.php                 # 统计likes和dislikies数在某个区间内的视频数量，删除likes和dislikies数在某个区间内的视频
├── 05_video_management.php                      # 功能模块：将web页面中点赞/踩的action更新到数据库中
├── 05_video_mysql_orderExist.php                # 按照数据库中likes-dislikes值得大小依次显示视频，每页显示固定数量视频
├── 05_video_mysql_orderExist_sigURL.php         # 按照数据库中likes-dislikes值得大小依次显示视频，视频的URL采用签名的统一资源定位符，设置有效期并加密
├── 05_video_mysql_random.php                    # 随机显示likes-dislikes值在某个范围内的视频
├── 05_video_mysql_random_sigURL.php             # 随机显示likes-dislikes值在某个范围内的视频，视频的url经过签名并加密，并设置有效期
├── 05_nodejs_sigURL
│   ├── 05_video_mysql_checkURL.js               # node.js应用，运行在云服务器后端，解析并核验签名的url以及referer是否合法，过滤非法请求
│   ├── node_modules
│   ├── nohup.out
│   ├── package.json
│   └── package-lock.json
├── 05_twitter_video                             # 存储mp4视频的文件夹
│   ├── 20230722-211832-363167243.mp4
│   ├── ...
├── twitterVideo_page.php                        # 显示指定路径下未签名、未基于数据库排序的视频，后端开启nginx反向代理后无法正常工作
└── twitterVideo_random.php                      # 随机显示指定路径下未签名、未基于数据库排序的视频，后端开启nginx反向代理后无法正常工作
```

# 3. 环境配置

### 1. `05_db_config.php` 连接数据库

- 环境变量

```php
$host = 'localhost'; // 通常是 'localhost' 或一个IP地址
$username = 'root'; // 数据库用户名
$password = '123456'; // 数据库密码
$dbname = 'video_db'; // 数据库名称
```

- 数据库中`videos`表格结构

```
mysql> describe videos;
+------------+--------------+------+-----+---------+----------------+
| Field      | Type         | Null | Key | Default | Extra          |
+------------+--------------+------+-----+---------+----------------+
| id         | int          | NO   | PRI | NULL    | auto_increment |
| video_name | varchar(255) | NO   |     | NULL    |                |
| likes      | int          | YES  |     | 0       |                |
| dislikes   | int          | YES  |     | 0       |                |
+------------+--------------+------+-----+---------+----------------+
4 rows in set (0.03 sec)
```

- 创建上述表结构的命令

```sql
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_name VARCHAR(255) NOT NULL,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0
);
```


### 2. `05_db_sync_videos.php` 文件名数据库写入

功能模块：将指定目录下的`mp4文件名`追加到数据库中

- 环境变量

```php
include '05_db_config.php'; // 包含数据库连接信息
```

- 调用方式

```php
include '05_db_sync_videos.php';
$dir4 = '/home/01_html/05_twitter_video/';       // 存放视频的目录
syncVideos($dir4); // 同步目录和数据库中的视频文件
```

### 3. `05_video_dislikes_delete.php`

统计likes和dislikies数在某个区间内的视频数量，删除likes和dislikies数在某个区间内的视频

- 环境变量

```php
include '05_db_config.php';
include '05_db_sync_videos.php';
$dir4='/home/01_html/05_twitter_video/';         // 存放视频的目录
```

### 4. `05_video_management.php`

功能模块：将web页面中`点赞/踩`的`action`更新到数据库中

- 环境变量

```php
include '05_db_config.php';
```


### 5. `05_video_mysql_orderExist.php`

按照数据库中`likes-dislikes`值得大小依次显示视频，每页显示固定数量视频

- 环境变量

```php
$key = 'your-signing-key-1';  // 应与登录脚本中的密钥一致
include '05_db_sync_videos.php';
syncVideos('/home/01_html/05_twitter_video/'); // 调用函数并提供图片存储目录
include '05_db_config.php';

// 设置视频所在的文件夹
$dir4 = "/home/01_html/05_twitter_video";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://mcha.me";

// 设置每页显示的视频数量
$videosPerPage = 8;
```


### 5. 







# 4. 数据库相关命令

1. 查询指定`列`值的`行`

```sql
SELECT *
FROM videos
WHERE video_name = '20240508-171501-11580948017.mp4';
```

2. 删除特定数据

```sql
DELETE FROM videos
WHERE video_name = '20240508-171501-11580948017.mp4';
```

删除 `video_name` 为 `20240508-171501-11580948017.mp4` 的数据


3. 查看数据表中的数据总数

```sql
SELECT COUNT(*)
FROM videos;
```



# 相关资料

- 限制用户通过构造链接访问图片：https://github.com/Yiwei666/03_Python-PHP/tree/main/08_pictureEdit/06_imageHost/07_imageLimit



