### 项目功能
---

- 提交mp4链接，下载mp4视频到云服务器指定目录
- 基于php脚本，在线观看云服务器指定目录mp4视频

### 文件结构
---

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

### 注意事项
---
1. 运行上述 twitter_PHP_download.php 脚本需要安装 cURL模块

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

2. 运行上述 twitter_PHP_download.php 脚本，提示输入mp4视频下载链接

推特视频链接可基于如下在线解析网站解析，然后粘贴链接到输入框，提交即可

https://twitterxz.com/
