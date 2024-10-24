# 1. 项目功能

1. 限制用户通过构造链接或爬虫直接访问图片
2. 不需要修改原有php代码，只需要在nginx中配置反向代理，对`/08_x/image/01_imageHost/`路径下的请求进行转发，然后node.js应用进行检查即可。

# 2. 文件结构

```
08_pic_url_check.js      # 通过referer限制用户通过构造链接访问图片
```

# 3. 环境配置

### 1. 安装`express`依赖包 ：


```bash
npm install express
```

### 2. 配置nginx反向代理

1. 打开并编辑 Nginx 配置文件，例如 `/etc/nginx/nginx.conf` 或自定义配置文件。
2. 添加以下配置，将请求转发到本地 Node.js 服务器（端口为 `3000`）：

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

- Nginx 配置文件中用于设置反向代理的指令，确保对特定路径的请求可以被转发到 Node.js 服务器。
 

### 3. 创建 Node.js 应用


`08_pic_url_check.js`

```js
// app.js

const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const rootDir = '/home/01_html';

// 启用信任代理
app.set('trust proxy', 1);

// 获取 PHP 文件列表
const getPhpFiles = () => {
  let phpFiles = [];
  const walkDir = (currentPath) => {
    const files = fs.readdirSync(currentPath);
    files.forEach((file) => {
      const fullPath = path.join(currentPath, file);
      const stat = fs.statSync(fullPath);
      if (stat.isDirectory()) {
        walkDir(fullPath);
      } else if (path.extname(file) === '.php') {
        phpFiles.push(file);
      }
    });
  };
  walkDir(rootDir);
  return phpFiles;
};

let phpFiles = getPhpFiles();
setInterval(() => {
  phpFiles = getPhpFiles();
}, 5000);

// 校验 Referer
app.use((req, res, next) => {
  const referer = req.get('Referer') || '';
  const isValid = phpFiles.some((file) => referer.includes(file));
  if (!isValid) {
    res.redirect('https://19640810.xyz/login.php');
  } else {
    next();
  }
});

app.use('/08_x/image/01_imageHost', express.static('/home/01_html/08_x/image/01_imageHost'));

const port = 3000;
app.listen(port, () => {
  console.log(`Server listening on port ${port}`);
});
```

- 环境变量

```js
const rootDir = '/home/01_html';
res.redirect('https://19640810.xyz/login.php');
app.use('/08_x/image/01_imageHost', express.static('/home/01_html/08_x/image/01_imageHost'));
```



1. Nginx 配置：
   - Nginx 充当反向代理，将客户端对指定路径（`/08_x/image/01_imageHost/`）的请求转发到在本地运行的 Node.js 服务器。
2. Node.js 中的逻辑：
   - Node.js 服务器会接收到 Nginx 转发的请求。
   - 通过中间件检查请求的 Referer 头。
   - 如果 Referer 中包含指定 PHP 脚本文件的名称（从 `/home/01_html` 目录中提取），则认为请求合法，继续提供资源。
   - 如果 Referer 中不包含任何合法的 PHP 文件名，则判断请求非法，重定向到登录页面。
3. 资源提供：
   - 如果请求被认为合法，Node.js 服务器会通过 express.static 中间件提供相应的图片资源给客户端。

总结：这种设计将请求的初步过滤责任交给 Node.js，由它负责验证来源的合法性，再决定是否提供图片资源或重定向到登录页面。


### 4. 运行及alias

```bash
alias tgn='tail -n 50 /var/log/nginx/access.log'
alias gn='ps aux | grep node'
alias vb='vi ~/.bashrc'
alias sb='source ~/.bashrc'
alias kn='kill $(pgrep -f "08_pic_url_check.js")'
# alias np='nohup node /home/01_html/08_x_nodejs/08_pic_url_check.js &'
alias sn='nohup node /home/01_html/08_x_nodejs/08_pic_url_check.js > /home/01_html/08_x_nodejs/nohup.out &'
alias lwc='ls -l | grep "^-" | wc -l'
alias sbp='mysqldump -p image_db > /home/01_html/08_image_backup_$(date +%Y%m%d_%H%M%S).sql'

alias pms='pm2 stop /home/01_html/08_x_nodejs/08_pic_url_check.js'
alias pmr='pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js'
alias pmd='pm2 delete /home/01_html/08_x_nodejs/08_pic_url_check.js'
alias pml='pm2 list'
```

注意：PM2 的 `restart` 命令 通常用于已经通过 PM2 启动并且正在运行的进程。如果一个进程没有被 PM2 管理或者从未启动过，`pm2 restart` 就会报错，提示找不到该进程，这是因为 PM2 只能重启它已经管理的进程。

```sh
pm2 start /home/01_html/08_x_nodejs/08_pic_url_check.js
```

