# 1. 项目功能

1. 限制用户通过构造链接或爬虫直接访问图片

# 2. 文件结构


# 3. 环境配置

### 1. 安装`express`依赖包 ：

```bash
npm install express
```

### 2. 配置nginx反向代理

```nginx
# 转发到 Node.js 服务器
location /08_x/image/01_imageHost/ {
    proxy_pass http://127.0.0.1:3000/08_x/image/01_imageHost/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto https;
}
```

### 3. 创建 Node.js 应用








