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
├── 05_video_mysql_random.php                    # 随机显示likes-dislikes值在 top 150 范围内的视频
├── 05_video_mysql_random_sigURL.php             # 随机显示likes-dislikes值在 top 150 范围内的视频，视频的url经过签名并加密，并设置有效期
├── 051_video_list.php                               # 列出指定目录下的所有MP4文件，不需要mysql
├── 051_videoPlayer_sigURL.php                       # 播放某一个MP4文件，需要登陆验证以及签名验证，不需要mysql
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


# 3. 环境变量

## 1. php功能模块

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


### 2. `05_video_management.php` 视频点赞/踩

功能模块：将web页面中`点赞/踩`的`action`更新到数据库中

- 环境变量

```php
include '05_db_config.php';
```



### 3. `05_db_sync_videos.php` 文件名数据库写入

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

### 4. `05_db_status_size.php` 写入视频存在状态和大小

1. 表格中新增两列，一列是`size`，代表视频的大小，单位是`MB`，默认值是0，另外一列是`exist_status`，取值为0或者1，默认值是0，代表视频不存在，1代表存在

```
mysql> describe videos;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| video_name   | varchar(255) | NO   |     | NULL    |                |
| likes        | int          | YES  |     | 0       |                |
| dislikes     | int          | YES  |     | 0       |                |
| size         | int          | YES  |     | 0       |                |
| exist_status | tinyint      | YES  |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+
6 rows in set (0.01 sec)
```

- 新增两列的mysql语句

```sql
ALTER TABLE videos
ADD COLUMN size INT DEFAULT 0,
ADD COLUMN exist_status TINYINT DEFAULT 0;
```

2. 功能：
    - 遍历mysql数据库中的所有视频文件名，逐个更新 `size` 和 `exist_status` 值，所有视频文件均位于 `/home/01_html/05_twitter_video/` 目录下，更新过程遵循以下规则
    - 首先判断视频是否存在，存在的视频，`exist_status`设为1，否则设置为0
    - `size`表示视频的大小，单位是MB，保留2位小数，对于存在的视频，size根据实际情况设定，不存在的视频不修改size原有的值

3. 环境变量

```php
// 引入数据库配置文件
include '05_db_config.php';

// 视频存储目录
$dir = '/home/01_html/05_twitter_video/';
```



## 2. 后台管理脚本


### 1. `05_video_dislikes_delete.php`

统计likes和dislikies数在某个区间内的视频数量，删除likes和dislikies数在某个区间内的视频

- 环境变量

```php
include '05_db_config.php';
include '05_db_sync_videos.php';
$dir4='/home/01_html/05_twitter_video/';         // 存放视频的目录
```




## 3. web交互脚本


### 1. `05_video_mysql_orderExist.php`

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


### 2. `05_video_mysql_orderExist_sigURL.php`

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

相当于把`$domain . $dir5 . '/' . htmlspecialchars($video['video_name'])`替换为`generateSignedUrl(htmlspecialchars($video['video_name']))`


### 3. `05_video_mysql_random.php`

随机显示 `likes-dislikes` 值在 top 150 范围内的视频，且在单次循环中不重复播放相同视频

- 环境变量

```php
include '05_db_config.php';

$key = 'your-signing-key-1'; // 应与登录脚本中的密钥一致

// 初始化视频数量的变量
$topVideosLimit = 150;

const videoUrl = `https://mcha.me/05_twitter_video/${randomVideoName}`;
```


### 4. `05_video_mysql_random_sigURL.php`

1. 功能：随机显示 `likes-dislikes` 值在 top 150 范围内的视频，视频的url经过签名并加密，并设置有效期
2. 环境变量：除了包含`05_video_mysql_random.php`参数初始化之外，还需要初始化如下新增函数中的`$signingKey`和`$expiryTime`变量。

```php
// 生成签名的函数
function generateSignedUrl($videoName) {
    $signingKey = 'your-signing-key-2'; // 签名密钥
    $expiryTime = time() + 600; // 有效期10分钟
    $signature = hash_hmac('sha256', $videoName . $expiryTime, $signingKey); // 使用HMAC SHA256生成签名
    return "https://mcha.me/05_twitter_video/{$videoName}?expires={$expiryTime}&signature={$signature}";
}
```

3. 代码替换：将`05_video_mysql_random.php`中的如下代码进行替换

```php
var serverVideoList = <?php echo json_encode($videoList); ?>;
```

替换后`05_video_mysql_random_sigURL.php`中相应代码为

```php
var serverVideoList = <?php echo json_encode(array_map('generateSignedUrl', $videoList)); ?>;
```

这里的 `array_map` 函数将 `generateSignedUrl` 应用于 `$videoList` 数组的每个元素。这样，每个视频名称都会转换成一个带签名的 URL。然后，这些 URL 通过 `json_encode` 被转换成 JSON 格式的数组，最后赋值给 JavaScript 变量 `erverVideoList`。


### 5. `051_video_list.php` 列出指定目录下所有文件名

- 源码：[051_video_list.php](051_video_list.php) 
- 环境变量

```php
$dir = "/home/01_html/05_twitter_video";

// 指定传递参数的php脚本名
echo "<a href='051_videoPlayer_sigURL.php?video=$videoEncoded' target='_blank'>$video</a><br />";
```


### 6. `051_videoPlayer_sigURL.php` 在线播放MP4

- 源码：[051_videoPlayer_sigURL.php](051_videoPlayer_sigURL.php)
- 功能：点击`051_video_list.php`中的文件名，会传递文件名参数给`051_videoPlayer_sigURL.php`脚本并生成签名URL，Node.js校验通过后会在线播放。注意该脚本需要登陆验证后使用，通过`session`校验。
- 环境变量

```php
$key = 'your-signing-key-1';              // 应与加密时使用的密钥相同
return "https://mcha.me/05_twitter_video/{$videoName}?expires={$expiryTime}&signature={$signature}";
$signingKey = 'your-signing-key-2';       // 签名密钥，确保与Node.js中的一致
```

- 可以通过如下代码来调试生成的签名是否满足Node.js核验需求

```php
function generateSignedUrl($videoName, $key, $expiryTime) {
    $signature = hash_hmac('sha256', $videoName . $expiryTime, $key);
    // 调试输出生成的签名
    // echo "PHP generated signature: " . $signature . "<br>";
    // echo "PHP videoName: " . $videoName . "<br>";
    // echo "PHP Expiry Time: " . $expiryTime . "<br>";
    return "https://mcha.me/05_twitter_video/{$videoName}?expires={$expiryTime}&signature={$signature}";
}
```

注意：初始化上述所有参数之后记得重启Node.js应用


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

# 7. alias

```bash
alias clg='cat  /var/log/nginx/access.log'
alias tgn='tail -n 50 /var/log/nginx/access.log'
alias gn='ps aux | grep node'
alias vb='vi ~/.bashrc'
alias sb='source ~/.bashrc'
alias cb='cat ~/.bashrc'
alias cdh='cd /home/01_html; ls -l'
alias cjn='cat /usr/local/etc/v2ray/config.json'

alias lwc='ls -l | grep "^-" | wc -l'
alias rcv='nohup rclone copy /home/01_html/05_twitter_video/  rc6:az1-1/01_html/05_twitter_video/ --transfers=16 &'
alias rsv='rclone size "rc6:az1-1/01_html/05_twitter_video/"'
alias rcrv='nohup rclone copy --ignore-existing rc6:az1-1/01_html/05_twitter_video /home/01_html/05_twitter_video &'

alias sv='nohup node /home/01_html/05_nodejs_sigURL/05_video_mysql_checkURL.js > /home/01_html/05_nodejs_sigURL/nohup.out &'
alias kv='kill $(pgrep -f "05_video_mysql_checkURL.js")'
alias phv='php /home/01_html/05_video_dislikes_delete.php'
alias lwv='echo $(($(ls -l /home/01_html/05_twitter_video/ | wc -l) - 1))'
alias ffg='bash /home/01_html/05_ffmpeg_tool.sh'
```


# 8. 参考资料

1. mysql数据库博客：https://github.com/Yiwei666/12_blog/blob/main/002/002.md
2. 图床管理系统：https://github.com/Yiwei666/03_Python-PHP/blob/main/08_pictureEdit/06_imageHost/06_mysql/README.md
3. twitter视频下载管理系统：https://github.com/Yiwei666/03_Python-PHP/blob/main/10_twitterDownload/02_mysql/README.md
4. 抖音视频下载管理系统：https://github.com/Yiwei666/03_Python-PHP/blob/main/02_douyinAsynDownload/01_mysql/README.md
