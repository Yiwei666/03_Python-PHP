# 1. 项目功能

使用 Node.js 在本地或者云服务器中部署计算并显示两个版本代码差异的在线工具

# 2. 文件结构

1. 文件

```
index.html             # 前端网页，用于粘贴不同版本代码，显示计算差异。采用内联视图（Inline View）
index_split.html       # 代码差异显示，采用并排视图（Side-by-Side View）
server.js              # 后端脚本，计算两个版本的差异
```

2. 项目文件夹目录树

```
└── code-diff-app
    ├── node_modules
    ├── package-lock.json
    ├── package.json
    ├── public
        └── index.html
    └── server.js
```

# 3. 环境配置

## 1. windows本地部署

1. 在你的工作目录中初始化一个新的 Node.js 项目

创建项目文件夹`code-diff-app`，并初始化

```
mkdir code-diff-app
cd code-diff-app
npm init -y
```


2. 在项目根目录`code-diff-app`下安装依赖

```
npm install express body-parser diff
```


3. 创建后端服务器

在项目根目录`code-diff-app`下创建一个名为 [server.js](server.js) 的文件，并添加以下内容：

```js
// server.js
const express = require('express');
const bodyParser = require('body-parser');
const Diff = require('diff');
const path = require('path');

const app = express();
const PORT = 2000;

// 中间件
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public')));

// API 路由
app.post('/api/diff', (req, res) => {
    const { oldText, newText } = req.body;

    if (typeof oldText !== 'string' || typeof newText !== 'string') {
        return res.status(400).json({ error: 'Invalid input' });
    }

    // 计算差异
    const diff = Diff.createTwoFilesPatch('Old Version', 'New Version', oldText, newText, '', '');

    res.json({ diff });
});

// 启动服务器
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
```

4. 创建前端界面

在项目根目录`code-diff-app`下创建一个 public 文件夹，并在其中创建 [index.html](index.html) 文件：

-  `index.html`：采用内联视图（Inline View）

-  `index_split.html`代码思路：
    -  在页面中左右水平显示两个大的输入框，分别用于粘贴两个版本的代码，然后动态的显示这两个版本代码的区别（自动判断用户是否粘贴了两个版本的代码，并进行计算显示），依次在这两个版本的代码上进行标注，采用并排视图（Side-by-Side View）
    -  两个版本代码差异显示区域，左右各占屏幕宽度50%，对于长行代码，超过50%部分使用横向滚动条。注意，对于代码差异显示区域超过 800 px的部分，采用纵向滚动条。对于横向和纵向滚动条，滑动时，两个版本的代码应该能够同步进行左右或者上下滑动。



5. 运行应用

```
node server.js
```

打开浏览器，访问 `http://localhost:2000`。你应该会看到一个简单的界面，有两个文本输入框用于粘贴旧版本和新版本的代码，点击“比较差异”按钮后，差异结果会显示在下方。




## 2. linux云服务器部署


### 1. 修改前端请求路径

将前端的 API 请求路径从绝对路径 `/api/diff` 修改为相对于当前路径的相对路径 `api/diff`

1. 修改上述 `public/index.html`

找到以下代码部分：

```js
fetch('/api/diff', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ oldText, newText }),
})
```

在这种情况下，浏览器会将请求发送到当前域名的根路径下的 `/api/diff`。例如，如果您的网站是 `https://domain.com/codediffu/`，那么绝对路径 `/api/diff` 会被解析为 `https://domain.com/api/diff`。


2. 将其修改为：

```js
fetch('api/diff', { // 使用相对路径
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ oldText, newText }),
})
```

假设当前页面的 URL 是 `https://mcha.me/codediffu/`，那么相对路径 `api/diff` 会被解析为 `https://mcha.me/codediffu/api/diff`。




### 2. 确保 Nginx 正确代理 API 请求

```nginx
location /codediffu/ {           	
    proxy_pass http://127.0.0.1:2000/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}
```

这样，Nginx 会将 `/codediffu/api/diff` 代理到 `http://127.0.0.1:2000/api/diff`。


在修改完 Nginx 配置后，测试并重启 Nginx：

```
sudo nginx -t
sudo systemctl reload nginx
```


### 3. 确保 Node.js 应用正确运行

检查 `server.js`，确保 Node.js 应用正在监听所有网络接口，并且没有错误。

```js
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
```



### 4. 问题分析

a. 如果直接使用上述windows下的`index.html`文件（使用`/api/diff`绝对路径，即`fetch('/api/diff', { /* options */ })`），则会出现以下问题：
    - 浏览器将请求发送到 `https://mcha.me/api/diff`，而不是 `https://mcha.me/codediffu/api/diff`，
    - 由于 Nginx 没有配置 `/api/diff` 的代理规则，Nginx 会尝试在静态文件目录中查找 `/api/diff`，导致返回 404 错误页面


b. 下面是具体分析：

1. Nginx 配置：

    - 您的 Nginx 配置将 `/codediffu/` 路径下的请求代理到 `http://127.0.0.1:2000/`。
    
    - 例如，`/codediffu/api/diff` 会被代理到 `http://127.0.0.1:2000/api/diff`。


2. 前端请求：

    - 您的前端代码在 `index.html` 中使用了绝对路径 `/api/diff` 发送请求。
    
    - 由于应用部署在 `/codediffu/` 下，绝对路径 `/api/diff` 实际上指向的是 `https://domain.com/api/diff`，而不是 `https://domain.com/codediffu/api/diff`。


3. 结果：

    - Nginx 没有配置 `/api/diff` 路径的代理，因此会尝试在静态文件目录中查找 `/api/diff`，导致 404 错误页面被返回。
    
    - 前端尝试解析返回的 HTML 404 页面作为 JSON，导致 `SyntaxError: Unexpected token '<'` 错误。
















