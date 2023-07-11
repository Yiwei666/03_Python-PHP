### 项目地址
---

https://github.com/Evil0ctal/Douyin_TikTok_Download_API


### 项目结构
```
├─01_douyin_docker_api
│      config.ini                       # api配置文件，需要修改，将domain.com改为自己的域名
│      docker-compose.yml               # docker配置文件，无需修改

```



### 💽部署(方式二 Docker)

---

> 💡Docker Image repo: [Docker Hub](https://hub.docker.com/repository/docker/evil0ctal/douyin_tiktok_download_api)

- 安装docker

```yaml
curl -fsSL get.docker.com -o get-docker.sh&&sh get-docker.sh &&systemctl enable docker&&systemctl start docker
```

- 留下config.ini和docker-compose.yml文件即可
- 运行命令,让容器在后台运行

```yaml
docker-compose up -d
```

- 查看容器日志

```yaml
docker logs -f douyin_tiktok_download_api
```

- 删除容器

```yaml
docker rm -f douyin_tiktok_download_api
```

- 更新

```yaml
docker-compose pull && docker-compose down && docker-compose up -d
```






