# 1. 项目功能

- 提交MP4链接，下载mp4视频到云服务器指定目录
- 基于php脚本，在线观看云服务器指定目录mp4视频
- 上传本地MP4视频到云服务器指定目录
- 异步下载较大的twitter视频到云服务器指定目录


# 2. 文件结构

1. 基于mp4视频链接下载视频到指定目录

```
├── twitter_PHP_download.php               # -rw-r--r-- 1 root     root            # 下载视频
├── 05_twitter_video                       # drwxr-xr-x 2 www-data www-data        # 存储视频文件夹，注意权限设置
│   ├── 1651760241992945664(720p).mp4
│   ├── 1675841668707131392(1258p).mp4
│   ├── 1680136486853935105(720p).mp4
│   ......
├── lsTwitter.php                          # 查看文件目录       
├── twitterVideo_page.php                  # -rw-r--r-- 1 root     root

```


2. 从本地上传mp4文件到云服务器指定目录

```
├── 06_multiple_Video_Upload.php           # -rw-r--r-- 1 root     root             使用JavaScript和jQuery实现了一个简单的MP4视频上传功能，并在上传过程中显示上传进度条
├── 06_upload.php                          # -rw-r--r-- 1 root     root             服务器端处理脚本，用于接收上传的MP4视频文件并处理它们
├── MuChaManor                             # drwxr-xr-x 3 www-data www-data         存储mp4的目标文件夹  
│   ├── VID_20220810_181049.mp4
│   ├── VID_20220811_190114.mp4
│   .......
├── upload_MP4_video.php                   # MP4视频上传脚本，简易版本，独立使用，对多个size较大的视频，如 50MB，支持不好
```

注意：
1. `06_multiple_Video_Upload.php` 需要指定 `06_upload.php` 脚本名称
2. `06_upload.php` 中需要指定云服务器中存储视频的目录
3. `upload_MP4_video.php` 是一个独立使用的视频上传脚本，支持多个视频同时上传



# 3. 注意事项

### 1. cURL模块安装

- 运行上述 twitter_PHP_download.php 脚本需要安装 cURL模块

- 对于ubuntu系统
```
sudo apt-get update
sudo apt-get install php-curl
```

- 对于centos系统

```
sudo yum install php-curl
```

- 重启相关进程

```
sudo service nginx restart
sudo service php-fpm restart

```

### 2. 第三方twitter视频解析网站

- 运行上述 twitter_PHP_download.php 脚本，提示输入mp4视频下载链接

推特视频链接可基于如下在线解析网站解析，然后粘贴链接到输入框，提交即可

- https://twitterxz.com/

- https://twittervideodownloader.com/

- https://twitter.iiilab.com/

- https://twittervid.com/


# 4. 环境部署

### 1. 权限和所属组

- 存储视频的文件夹需要有读写权限，改变文件或目录的所有者

```
sudo chmod 775 /home/01_html/05_twitter_video
sudo chown www-data:www-data /home/01_html/05_twitter_video
```

## 2. 环境变量配置

- twitter_PHP_download.php 中需要结合实际情况自定义下载视频的目录

```
 $filePath = '/home/01_html/05_twitter_video/' . $fileName;
```

- 06_multiple_Video_Upload.php 需要指定同级目录下服务器端处理视频上传的php脚本

```
url: '06_upload.php',
```

- 06_upload.php 需要自定义文件上传目录

```
 $uploadDir = '/home/01_html/MuChaManor/';
```

- lsTwitter.php 自定义需要查看的目录

```
$directory = '/home/01_html/05_twitter_video';
```

- twitterVideo_page.php 自定义域名以及播放视频的目录

```
$domain = 'https://domain.com';
$videoPath = '/home/01_html/05_twitter_video/';
$videoUrl = $domain . '/05_twitter_video/' . $videoName;
```

- upload_MP4_video.php 自定义上传视频的目录

```
$uploadDir = '/home/01_html/05_twitter_video/';
```

# 5. php 配置文件设置


主要影响文件上传的两个设置是upload_max_filesize和post_max_size。以下是具体的修改方法：

**1. 检查当前PHP配置：**

在进行任何更改之前，请通过创建一个包含以下内容的PHP文件并在服务器上运行它来检查当前PHP配置：

```
<?php
phpinfo();
?>
```

查找upload_max_filesize和post_max_size的值。这些值将决定可以上传的文件的最大大小以及包含上传文件的整个POST请求的最大大小。

**2. 增加文件上传限制：**

如果upload_max_filesize和post_max_size的当前值低于你的MP4视频大小，你需要将它们增加。编辑服务器的PHP配置文件（通常命名为php.ini），并为以下设置指定适当的值：

```
upload_max_filesize = 100M
post_max_size = 100M
```

ubuntu系统中php配置文件路径

```
/etc/php/7.4/fpm/php.ini
```

上述的"100M"仅为示例，你可以将其设置为所需的允许上传文件的最大大小。记得选择一个足够大的值，以适应你最大的MP4视频文件大小。

**3. 重启Web服务器：**

在更改php.ini文件后，需要重启Web服务器以应用新的配置设置。

```
sudo service nginx restart
sudo service php7.4-fpm restart  # 将 "7.4" 替换为你安装的 PHP 版本号
```

**4. 验证更改：**

再次运行处理视频上传的PHP脚本，并检查视频是否能够被正确上传并具有有效的大小。

通过以上修改，上传的视频应该不再出现大小为0的问题，脚本也会按预期工作，允许你上传MP4视频到指定目录。



5. 413 Request Entity Too Large nginx/1.18.0 (Ubuntu)报错

当出现 "413 Request Entity Too Large" 错误时，这意味着 Nginx 服务器拒绝了太大的请求实体，其中包括上传的文件和其他 POST 数据。这是由于 Nginx 服务器的默认请求大小限制导致的。

- 在 HTTPS 配置块中添加或修改以下配置项：

在 HTTPS 配置块中找到 server 块，然后添加或修改以下配置项，将上传大小限制设置为更大的值，比如 20MB：

```
server {
    # 其他配置项...
    client_max_body_size 20M;
    # 其他HTTPS配置...
}
```

相关代码
```
client_max_body_size 20M;
```

配置文件通常位于
```
/etc/nginx/nginx.conf
```

这里的 client_max_body_size 设置了上传文件和 POST 请求体的最大大小，单位是 MB。

- 检查 Nginx 配置是否正确：

运行以下命令检查 Nginx 配置是否正确：

```
sudo nginx -t
```

如果输出显示配置正确，则继续下一步。如果有错误，请检查配置文件并修正。

- 重启 Nginx：

使配置更改生效，重启 Nginx 服务：

```
sudo service nginx restart
```

现在，Nginx 已经配置为接受更大的请求实体，包括上传的文件和其他 POST 数据，在 HTTPS 下也能够正常处理较大的文件上传。











