# 1. 项目功能

服务器端使用mysql数据库来储存、管理抖音视频的下载

# 2. 文件结构

```
├── 18_db_config.php        # 创建mysql连接对象
├── 18_url_get.php          # 将web页面上提交的抖音链接保存到数据库中，忽略已存在的链接
├── 18_tm_url_api.php       # 后端脚本，接收油猴脚本前端 POST 提交的文本，提取其中的链接，判断是否重复并存入到数据库中
├── 18_tm_webGetURL.js      # 油猴脚本，与 18_tm_url_api.php 搭配使用，在抖音页面右下角显示一个按钮，一键获取剪贴板内容并将其中的Douyin链接提交到服务器
├── 18_view_log.php         # 在web页面上显示最后两次提交的抖音视频链接
├── 18_douyinDown.py        # 下载抖音视频的爬虫脚本
├── 02_douyVideo            # 存储视频的文件夹
│   ├── 20240513-204331.mp4
│   ├── ...
```


# 3. 环境配置

### 1. 创建数据库和表

1. 创建数据库

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

3. 表结构

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


### 2. `18_db_config.php`

创建mysql连接对象

- 环境变量

```php
$username = 'root'; // 数据库用户名
$password = '123456'; // 数据库密码
$dbname = 'douyin_db'; // 数据库名称
```


### 3. 18_url_get.php

将web页面上提交的抖音链接保存到数据库中，忽略已存在的链接

- 环境变量

```php
include '18_db_config.php';   // 引入数据库配置文件，建立 $mysqli 数据库连接对象

<script>
    function visitUrl() {
        window.location.href = "https://mctea.one/18_url_get.php";    // 刷新按钮
    }

    function viewLog() {
        window.open("18_view_log.php", "_blank");     // 查看最后输入两条url
    }
</script>
```



### 4.1 `18_tm_webGetURL.js`

1. 编程思路

上述 `18_url_get.php` 脚本通过解析用户在页面输入框中提交的字符串，获取链接并储存到`douyin_db`数据库中，现在有一个需求，
   - 能不能编写一个油猴脚本，在页面右下角显示一个按钮，点击按钮能够获取当前剪贴板中的内容并显示出来，提示用户是否提取链接并提交到数据库？
   - 如果同于点击同意，则提取其中的 url 并获取时间戳，写入到数据库中（相关实现同上述 `18_url_get.php` 脚本 相关部分，包括提示url在数据库中是否已存在，是否成功写入，是否有有效url等）。
   - 如果有必要，可以重新编写一个php模块运行在后端，配合前端的油猴脚本工作。请输出完整的油猴脚本代码和php后端模块代码。


2. 环境变量

```js
const postUrl = 'https://domain.com/18_tm_url_api.php'; // 这里替换成你的后端脚本地址
```


### 4.2 `18_tm_url_api.php`


- 后端脚本功能：

1. 接收前端 POST 提交的文本 (`clipboardContent`)。
2. 用正则表达式提取其中的所有 `https://` 开头的链接。
3. 遍历每个链接，判断数据库是否已有记录，没有则插入并记录成功次数。
4. 返回 JSON 数据，告知前端每条链接的处理情况，以及总体状态。

注意：此脚本依赖 `18_db_config.php` 来连接数据库，你需要确保 `18_db_config.php` 中的数据库名称、用户和密码是正确的。也要保证数据库中存在 `douyin_videos` 表，表结构与之前展示的相匹配。


- 环境变量

```php
// 引入数据库配置文件
include '18_db_config.php';
```


- 注意：上述`18_tm_url_api.php`后端脚本开头必须添加如下内容，以便解决 CORS 跨域问题

```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
```

或者只允许特定域名访问 `header("Access-Control-Allow-Origin: https://你的域名.com");`




### 5. 18_view_log.php

在web页面上显示最后两次提交的抖音视频链接

- 环境变量

```php
include '18_db_config.php';  // 引入数据库配置文件，建立 $mysqli 数据库连接对象
```



### 6. 18_douyinDown.py

下载抖音视频的爬虫脚本

1. python第三方模块安装

```bash
pip install pymysql
pip install requests
pip install beautifulsoup4
```

查看上述3个模块是否已安装

```bash
pip list | grep -E "PyMySQL|requests|beautifulsoup4"
```


2. 环境变量

```py
# 数据库配置
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '123456',
    'database': 'douyin_db'
}

# 定义下载目录
download_dir = "/home/01_html/02_douyVideo/"
```

3. 功能

这段代码实现了以下功能：

- 连接到名为 `douyin_db` 的 MySQL 数据库，使用表 `douyin_videos`。
- 查询所有未下载（`download_status` 为 0）的视频链接。
- 随机选择一个视频链接，并构造一个新的 URL 来通过第三方服务 `dlpanda.com` 获取真实的视频下载链接。
- 解析返回的 HTML，获取视频源 URL，并下载视频。
- 下载的视频以当前时间命名，并保存在指定目录 `/home/01_html/02_douyVideo/` 下。
- 更新数据库中的视频记录，标记为已下载（`download_status` 设为 1），并记录下载的视频名称和下载时间。
- 关闭数据库连接。

4. cron 定时

- 每两分钟执行一次 `18_douyinDown.py`

```bash
*/2 * * * * /home/00_software/01_Anaconda/bin/python /home/01_html/18_douyinDown.py
```




### 7. rclone上传onedrive

```bash
# cc1-2 to onedrive
0 * * * * rclone copy --ignore-existing /home/01_html/02_douyVideo cc1-2:do1-2/01_html/02_douyVideo
# 0 * * * * rclone copy --ignore-existing /home/01_html/02_douyVideo rc4:do1-2/01_html/02_douyVideo
```




# 参考资料

1. mysql数据库博客：https://github.com/Yiwei666/12_blog/blob/main/002/002.md
2. 图床管理系统：https://github.com/Yiwei666/03_Python-PHP/blob/main/08_pictureEdit/06_imageHost/06_mysql/README.md
3. twitter视频下载管理系统：https://github.com/Yiwei666/03_Python-PHP/blob/main/10_twitterDownload/02_mysql/README.md





