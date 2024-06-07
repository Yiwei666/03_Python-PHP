# 1. 项目功能

1. 在云服务器上部署`metube` docker容器，在网页上解析twitter、youtube等视频链接，下载视频到服务器并返回下载后的视频链接

2. `metube`介绍：MeTube是一个自托管的YouTube下载器，提供了`youtube-dl和yt-dlp`工具的Web用户界面。用户可以通过它从YouTube及其他多个站点直接下载视频到本地存储。MeTube设计为在Docker环境中运行，易于设置和扩展。它支持通过环境变量自定义下载路径、用户权限和文件命名规则等设置。此外，它还支持浏览器扩展和iOS快捷方式，提高了访问性和易用性。该项目采用AGPL-3.0许可证，鼓励社区贡献和修改。

3. `yt-dlp`介绍：yt-dlp 是一个命令行程序，用于从YouTube和其他视频网站下载视频。它是youtube-dl的一个分支，添加了许多改进和新特性，比如更快的下载速度、更多的视频网站支持和频繁的更新。用户可以通过yt-dlp下载高清视频，甚至包括4K和8K视频，还支持下载字幕、播放列表和用户频道。它适用于多种操作系统，如Windows、macOS和Linux，是一种非常灵活和强大的媒体下载工具。

# 2. 文件结构




# 3. metube安装和配置

### 1. 启动容器

```bash
# docker run -d -p 8081:8081 -v /path/to/downloads:/downloads ghcr.io/alexta69/metube
docker run -d -p 8081:8081 -v /home/01_html/05_temp_ffmpeg:/downloads ghcr.io/alexta69/metube
```

:star: 项目地址`metube`：https://github.com/alexta69/metube  
:star: 相关项目`yt-dlp`：https://github.com/yt-dlp/yt-dlp  

### 2. 将 Docker 容器的端口映射限制为仅对 `localhost（127.0.0.1）`可见

1. 停止并移除现有容器（如果它已经在运行）：

```bash
docker stop [容器ID或名称]
docker rm [容器ID或名称]
```

您可以使用 `docker ps` 查找正在运行的容器的 `ID 或名称`。docker命令[参考](https://github.com/Yiwei666/03_Python-PHP/wiki/06_docker%E5%91%BD%E4%BB%A4)


2. 使用更新的端口映射重新启动容器：

```bash
docker run -d -p 127.0.0.1:8081:8081 -v /home/01_html/05_temp_ffmpeg:/downloads ghcr.io/alexta69/metube
```

这条命令将容器的 8081 端口仅映射到本地环境，外部无法访问。

3. nginx反向代理

```nginx
location /metube/ {
        proxy_pass http://127.0.0.1:8081/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
}
```

4. htpasswd

- 创建密码文件

```bash
htpasswd -c /path/to/.htpasswd username
```

- 修改nginx配置文件

```nginx
location /metube/ {
        auth_basic "Restricted Access";
        auth_basic_user_file /home/02_htpasswd/.htpasswd;

        proxy_pass http://127.0.0.1:8081/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
}
```


参考博客：[htpasswd 身份验证](https://github.com/Yiwei666/12_blog/blob/main/004/004.md)



# 4. `yt-dlp`安装和配置

### 1. `yt-dlp`下载安装

- 执行文件下载到`/usr/local/bin`目录下

```bash
curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp
chmod a+rx /usr/local/bin/yt-dlp
```

参考资料：https://github.com/yt-dlp/yt-dlp/wiki/Installation

- 判断是否安装成功

```bash
yt-dlp --version
```

- 更新

```bash
yt-dlp -U
```

### 2. 命令行





