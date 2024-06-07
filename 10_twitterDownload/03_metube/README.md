# 1. 项目功能

在云服务器上部署metube docker容器，在网页上解析twitter、youtube等视频链接，下载视频到服务器并返回下载后的视频链接

# 2. 文件结构




# 3. 环境配置

### 1. 启动容器

```bash
# docker run -d -p 8081:8081 -v /path/to/downloads:/downloads ghcr.io/alexta69/metube
docker run -d -p 8081:8081 -v /home/01_html/05_temp_ffmpeg:/downloads ghcr.io/alexta69/metube
```

参考资料：https://github.com/alexta69/metube


### 2. 将 Docker 容器的端口映射限制为仅对 `localhost（127.0.0.1）`可见

1. 停止并移除现有容器（如果它已经在运行）：

```bash
docker stop [容器ID或名称]
docker rm [容器ID或名称]
```

您可以使用 `docker ps` 查找正在运行的容器的 `ID 或名称`。

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


