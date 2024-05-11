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

function updateLikes(videoId, action) {
        fetch('05_video_management.php', {              // 调用功能模块，执行action
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `videoId=${videoId}&action=${action}`
        })
```


### 6. `05_video_mysql_orderExist_sigURL.php`

按照数据库中`likes-dislikes`值得大小依次显示视频，视频的URL采用签名的统一资源定位符，设置有效期并加密

- 环境变量：除了初始化`05_video_mysql_orderExist.php`中的参数外，还需要额外初始化如下代码中的 `$signingKey`和`$expiryTime` 这两个参数

```php
// 生成带有签名的URL
function generateSignedUrl($videoName) {
    $signingKey = 'your-signing-key-2'; // 签名密钥，确保与Node.js中的一致
    $expiryTime = time() + 600; // URL有效期为10分钟
    $signature = hash_hmac('sha256', $videoName . $expiryTime, $signingKey);
    global $domain, $dir5;
    return "{$domain}{$dir5}/{$videoName}?expires={$expiryTime}&signature={$signature}";
}

<video src="<?php echo generateSignedUrl(htmlspecialchars($video['video_name'])); ?>" class="video" controls alt="Video" loading="lazy"></video>
```

- 上述代码部分时相较于`05_video_mysql_orderExist.php`添加和替换的部分，用于对视频的url进行加密和签名，下面是被替换的部分

```php
<video src="<?php echo $domain . $dir5 . '/' . htmlspecialchars($video['video_name']); ?>" class="video" controls alt="Video" loading="lazy"></video>
```


# 4. Nginx反向代理

### 1. Nginx代码块

这段 Nginx 反向代理配置使得所有以 `/05_twitter_video/` 开始的请求都被转发到本地的 `3000` 端口上的服务（如 `Node.js` 应用），同时保持了原始请求的`主机`和 `IP 信息`，并设置了与后端服务通信的`超时时间`。这样配置的目的是确保请求能够正确、安全地从客户端转发到后端服务，并能及时响应。

```nginx
location /05_twitter_video/ {
    # 转发请求到本地的3000端口上的Node.js应用
    proxy_pass http://localhost:3000;

    # 保持请求头不变
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;

    # 可以设置一些超时时间
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;
}
```

### 2. 注释

```nginx
location /05_twitter_video/ {
```

- 这一行定义了一个请求匹配规则，当请求的路径以 /05_twitter_video/ 开始时，这个配置块会被应用。

```nginx
proxy_pass http://localhost:3000;
```

- 这行指示 Nginx 将匹配到的请求转发到本地的 3000 端口。通常这意味着有一个在本地 3000 端口运行的服务（例如 Node.js 应用），Nginx 会将请求转发给这个服务。

```nginx
proxy_set_header Host $host;
```

- 这行设置请求头中的 Host 字段为原始请求的 Host。这样转发的请求保持了原始请求的 Host 信息。

```nginx
proxy_set_header X-Real-IP $remote_addr;
```

- 这行设置请求头中的 X-Real-IP 为原始请求的远程地址，也就是访问 Nginx 的用户的 IP 地址。

```nginx
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
```

- 这行添加或更新 X-Forwarded-For 请求头，包含了原始请求的 IP 地址链。这个头部用于跟踪通过代理的请求的原始 IP 地址。

```nginx
proxy_set_header X-Forwarded-Proto $scheme;
```

- 这行设置 X-Forwarded-Proto 请求头为原始请求使用的协议（http 或 https），用于指示后端服务请求的原始协议。

```nginx
proxy_connect_timeout 60s;
```

- 设置与后端服务建立连接的超时时间为 60 秒。如果在这段时间内无法与后端服务建立连接，Nginx 将返回错误。

```nginx
proxy_send_timeout 60s;
```

- 设置向后端服务发送请求的超时时间为 60 秒。如果在这段时间内无法发送完整的请求，Nginx 将返回错误。

```nginx
proxy_read_timeout 60s;
```

- 设置从后端服务读取响应的超时时间为 60 秒。如果在这段时间内无法读取到响应，Nginx 将返回错误。


# 5. Node.js应用

### 1. `05_video_mysql_checkURL.js`

这段代码实现了一个用于提供视频文件的 Node.js 服务器。它使用 Express 框架来处理 HTTP 请求，并具有以下功能：

- 动态更新允许的 PHP 文件列表：服务器会定期扫描指定目录（/home/01_html/），自动更新允许访问的 PHP 文件列表。
- 验证 Referer：确保请求来源于允许的 PHP 脚本。这有助于增强安全性，防止非法请求。
- 签名验证：使用 HMAC SHA-256 算法和签名密钥验证请求的签名和过期时间，确保视频链接的安全性和时效性。
- 提供视频文件：如果请求通过验证，服务器将提供请求的视频文件。如果视频不存在或验证失败，将重定向到登录页面。

### 2. 环境变量

```js
const port = 3000;
const signingKey = 'your-signing-key';                               // 替换为您的实际签名密钥，与php中的密钥一致
const phpScriptDirectory = '/home/01_html/';                         // PHP脚本所在的根目录

app.get('/05_twitter_video/:videoName', (req, res) => {              // 视频所在文件夹

return res.redirect('https://mcha.me/login.php');                    // 非法请求重定向页面

const videoPath = path.join('/home/01_html/05_twitter_video', videoName);         // 视频资源完整目录

setInterval(updateAllowedPHPFiles, 5 * 60 * 1000);                                // 每5分钟更新一次PHP文件列表，与referer验证相关
```


# 6. MySQL相关命令

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



# 7. 相关资料

- 限制用户通过构造链接访问图片：https://github.com/Yiwei666/03_Python-PHP/tree/main/08_pictureEdit/06_imageHost/07_imageLimit



