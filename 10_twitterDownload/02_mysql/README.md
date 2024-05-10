# 1. 项目功能

1. 将云服务器指定路径下的视频资源写入到`mysql数据库`中，增加点赞/踩功能
2. 使用`签名url`及`referer`核验限制非法用户对视频资源的请求

# 2. 文件结构

```
.
├── 05_db_config.php
├── 05_db_sync_videos.php
├── 05_nodejs_sigURL
│   ├── 05_video_mysql_checkURL.js
│   ├── node_modules
│   ├── nohup.out
│   ├── package.json
│   └── package-lock.json
├── 05_twitter_bigfile
│   ├── 01_url.txt
│   └── 05_bashDownTwitterBigVideo.sh
├── 05_twitter_bigVideo_download.php
├── 05_twitter_video
│   ├── 20230722-211832-363167243.mp4
│   ├── ...
├── 05_video_dislikes_delete.php
├── 05_video_management.php
├── 05_video_mysql_orderExist.php
├── 05_video_mysql_orderExist_sigURL.php
├── 05_video_mysql_random.php
├── 05_video_mysql_random_sigURL.php
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

### 2. `05_db_sync_videos.php` 视频名数据库写入功能模块

- 环境变量

```php
include '05_db_config.php'; // 包含数据库连接信息
```

- 调用方式

```php
include '05_db_sync_videos.php';
$dir4 = '/home/01_html/05_twitter_video/';
syncVideos($dir4); // 同步目录和数据库中的视频文件
```






# 相关资料

- 限制用户通过构造链接访问图片：https://github.com/Yiwei666/03_Python-PHP/tree/main/08_pictureEdit/06_imageHost/07_imageLimit
