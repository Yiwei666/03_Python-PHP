# 1. 项目功能

代码对比在线工具：使用 Node.js 在本地和云服务器中部署计算并显示两个版本代码差异的在线工具

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

### 1. 初始化项目

在你的工作目录中初始化一个新的 Node.js 项目

创建项目文件夹`code-diff-app`，并初始化

```
mkdir code-diff-app
cd code-diff-app
npm init -y
```

### 2. 安装依赖

在项目根目录`code-diff-app`下安装依赖

```
npm install express body-parser diff
```

- 库说明：
    - `Express`：一个快速、灵活的Node.js Web框架，用于构建Web应用程序和API，提供了路由、视图渲染以及中间件等功能。
    - `Body-parser`：一个Express的中间件，用于解析传入请求体的数据，特别是`application/json`和`application/x-www-form-urlencoded`格式的数据，便于在服务端访问客户端提交的参数。
    - `Diff`：一个轻量级工具库，用于对比两段文本、数组或对象，生成差异数据，通常用于版本控制、文档比较等场景。




### 3. 创建后端服务器

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

### 4. 创建前端界面

在项目根目录`code-diff-app`下创建一个 public 文件夹，并在其中创建 [index.html](index.html) 文件：

🟦 **1. 内联视图**

1.  `index.html`：采用内联视图（Inline View）
    -  解决了左侧两列行号在水平方向未对齐的问题，且每行仅显示一个行号
    -  控制显示代码差异结果的区域高度，如800px，超过这个范围的部分使用 纵向滚动条来翻看
    -  删除头部标题和介绍文本，删除 “比较差异” 按钮，以节省页面占用空间
    -  自动判断用户是否粘贴了两个版本的代码，并进行计算显示
    -  针对长行代码添加横向滚动周。在容器的上方有一个滑轨，滑轨中也有滑块，容器内内容的左右运动行为同步滑块的左右滑动行为
    -  在容器右侧显示一个内联视图的缩略图，缩略图中对于增加代码的区域显示绿色，删除代码的区域显示红色，没有变动的区域保持默认颜色。


🟦 **2. 并排视图**

1.  `index_split.html`：采用并排视图（Side-by-Side View）
    -  在页面中左右水平显示两个大的输入框，分别用于粘贴两个版本的代码，然后动态的显示这两个版本代码的区别（自动判断用户是否粘贴了两个版本的代码，并进行计算显示），依次在这两个版本的代码上进行标注，采用并排视图（Side-by-Side View）
    -  两个版本代码差异显示区域，左右各占屏幕宽度50%，对于长行代码，超过50%部分使用横向滚动条。注意，对于代码差异显示区域超过 800 px的部分，采用纵向滚动条。对于横向和纵向滚动条，滑动时，两个版本的代码应该能够同步进行左右或者上下滑动。


2. 对于并排视图，需要确保：
    -  左右视图中代码没有跳行、没有省略行，代码行号顺序正确。代码总行数要正确，左右视图中行号与实际代码要符合。
    -  左右视图中对于修改行和不变行能够水平对齐，空行和非空行高度一致
    -  算法能够正确识别后端返回的diff数据，包括新增行、删除行、修改行和不变行识别，尤其是修改行，将修改行识别为新增/删除行会引入额外空行，导致左右两侧不变行和修改行无法对齐。
    -  添加左右试图的缩略图，缩略图中包含红色和绿色，不含省略的灰色。
    -  左右视图中需要包含 `hunk header`，删除行“-”，新增行“+”的行表示。
    -  左侧和右侧视图中删除行、新增行和修改行及相应修改处的的颜色高亮要正确。左侧视图不能出现绿色，右侧视图不能出现红色。
    -  右侧视图不忽略新增代码行中的空行，标记为绿色并以“+”开头
    -  修改行、新增行和删减行的缩进，与不变行缩进保持一致（通常需要在不变行开头引入两个空格来保持一致）


⭐ **2.1 并排视图可视化算法**

上述代码中是如何判断哪些是删除行，哪些是新增行，哪些是修改行，哪些是不变行呢？

1. 对于并排视图的代码差异展示应满足如下要求：
    -  对于 删除行（-），在左侧缓冲区中添加该行内容，并在右侧缓冲区添加一个空行。
    -  对于 添加行（+），在右侧缓冲区中添加该行内容，并在左侧缓冲区添加一个空行。
    -  对于完全不变的代码行，在左右缓冲区中都添加该行内容。  
    -  对于部分修改的代码行（仅增加、删减或者修改少量代码内容，主体部分不变的代码行），在左右缓冲区中都添加该行内容。

2. 基于后端返回的差异（diff）数据，前端通过解析每行的开头字符来判断行的类型：
   - 以 `-` 开头的行标识为删除行，`+` 开头的为新增行，`空格` 开头的为不变行，`@@` 开头的为差异块头部。
   - 对于修改行，当删除行和新增行数量相同且一一对应时，将其视为修改，通过在左右视图中分别高亮显示删除和新增部分。
   - 对于修改行，如果删除行多于新增行，在右侧对应位置插入不带行号的空白行，保持行数一致。如果新增行多于删除行，在左侧对应位置插入不带行号的空白行，确保行对齐。
   - 此外，为了确保左右视图中的行对齐，前端在一侧有删除行时在另一侧插入不带行号的空白行，反之亦然，这样即使存在新增或删除行，其他不变或修改行依然能够保持对齐，确保用户在浏览差异时获得一致且直观的视觉体验。


⭐ **2.2 后端返回diff数据示例**

1. 两个版本代码

```js
版本1：
// ==UserScript==
// @name         Extract Citation Data with DOI Lookup and Complete Reference Info
// @namespace    http://tampermonkey.net/
// @version      1.7
// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号和文章编号
// @author
// @match        https://scholar.google.com/*
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest


版本2：
// ==UserScript==
// @name         Extract Citation Data with DOI Lookup and Complete Reference Info
// @namespace    http://tampermonkey.net/
// @version      1.9
// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号、文章编号、出版商和 ISSN（标注类型）
// @author
// @match        https://scholar.google.com/*
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest
```


2. 后端diff数据返回实例

```js
@@ -3,7 +3,7 @@
 // @namespace    http://tampermonkey.net/
-// @version      1.7
-// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号和文章编号
+// @version      1.9
+// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号、文章编号、出版商和 ISSN（标注类型）
 // @author
 // @match        https://scholar.google.com/*
 // @match        https://scholar.google.com.hk/*
```

问题：如上，测试幽灵空行版本（`3f1daeb`）代码时发现，如果有2行连续删除行和2行连续新增行，代码会在右侧视图中额外添加一个空行，仅将第2个删除行和第1个新增行视为同一修改行，并将第2新增行作为独立新增行，并在左侧添加一个空行。这与实际情况不符，因为实际上是两个修改行。


总结：基于上述删除行和新增行数据，如果算法中不考虑连续新增行和连续删减行的出现，并将（连续新增行和连续删减行）数量相等时视为连续修改行，则会造成右侧视图区域新增多个无意义的空行，且左右视图中修改行无法对齐。这是因为左侧视图中的删减行会在右侧视图中对应添加一个空行。


⭐ **2.3 并排视图颜色高亮**

1. 上述代码中对于左侧旧版本代码视图中的如下部分分别使用相对应颜色突出显示
    -  修改行使用的背景色是 浅黄色 `#fdf2d0`，修改行中具体修改的地方高亮色用 粉红色  `#ffb6ba`
    -  删除行使用的背景色是 浅粉色 `#fee8e9`

2. 上述代码中对于右侧新版本代码视图中的如下部分分别使用相对应颜色突出显示
    -  修改行使用的背景色是 浅绿色 `#ddeedd`，修改行中具体修改的地方高亮色用 草绿色 `#97f295`
    -  新增行使用的背景色是  柔和的浅绿色 `#ddffdd`

3. 左右视图中不变行和hunk header 行分别使用如下相对应颜色突出显示
    -  不变行背景色： `#fff`  白色
    -  hunk header 行背景色： `#f6f8fa` 浅灰色



### 5. 运行应用

```
node server.js
```

打开浏览器，访问 `http://localhost:2000`。你应该会看到一个简单的界面，有两个文本输入框用于粘贴旧版本和新版本的代码，点击“比较差异”按钮后，差异结果会显示在下方。


### 6. 视图内容分析

1. 在代码对比工具中，`@@ -68,9 +68,9 @@` 表示以下内容：
    - `-68,9`：这是对比的旧版本代码中，从第 68 行开始，接下来的 9 行被修改或删除的内容。
    - `+68,9`：这是对比的新版本代码中，从第 68 行开始，接下来的 9 行被添加或修改的内容。
    - `@@`：标志着这是一个代码变化的区域（hunk）。这一部分代码上下文将展示旧版本和新版本的具体差异。

总结：这个标记显示了修改的代码行范围（68行起，影响9行），并且分别指出了旧版本和新版本的对应变化。



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

- 假设当前页面的 URL 是 `https://mcha.me/codediffu/`，那么相对路径 `api/diff` 会被解析为 `https://mcha.me/codediffu/api/diff`。

- 在绝对路径这种情况下，浏览器会将请求发送到当前域名的根路径下的 `/api/diff`。例如，如果您的网站是 `https://domain.com/codediffu/`，那么绝对路径 `/api/diff` 会被解析为 `https://domain.com/api/diff`。由于 Nginx 没有配置 `/api/diff` 的代理规则，Nginx 会尝试在静态文件目录中查找 `/api/diff`，导致返回 404 错误页面。

- 云服务器 `server.js` 脚本中的 `app.post('/api/diff', (req, res)` 绝对路径不需要修改。


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

1. 检查 `server.js`，确保 Node.js 应用正在监听所有网络接口，并且没有错误。

找到以下部分（本地运行）：

```js
// 启动服务器
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
```

将其修改为（云服务器运行）：

```js
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
```


2. 注意修改端口号：

```js
const PORT = 2000;
```

云服务器实际部署时，`codeDiff_unified` 视图运行在 2000 端口上，`codeDiff_split` 视图运行在 2001 端口上。注意避免不同应用的端口冲突。


3. `server.js`修改后的完整代码（运行在云服务器上）：

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
app.listen(PORT,'0.0.0.0', () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
```



### 4. 问题分析总结

- 如果直接使用上述windows下的`index.html`文件（使用`/api/diff`绝对路径，即`fetch('/api/diff', { /* options */ })`），则会出现以下问题：
    - 浏览器将请求发送到 `https://domain.com/api/diff`，而不是 `https://domain.com/codediffu/api/diff`。
    - 由于 Nginx 没有配置 `/api/diff` 的代理规则，Nginx 会尝试在静态文件目录中查找 `/api/diff`，导致返回 404 错误页面。


- 下面是具体分析：

1. Nginx 配置：

    - 您的 Nginx 配置将 `/codediffu/` 路径下的请求代理到 `http://127.0.0.1:2000/`。
    
    - 例如，`/codediffu/api/diff` 会被代理到 `http://127.0.0.1:2000/api/diff`。


2. 前端请求：

    - 您的前端代码在 `index.html` 中使用了绝对路径 `/api/diff` 发送请求。
    
    - 由于应用部署在 `/codediffu/` 下，绝对路径 `/api/diff` 实际上指向的是 `https://domain.com/api/diff`，而不是 `https://domain.com/codediffu/api/diff`。


3. 结果：

    - Nginx 没有配置 `/api/diff` 路径的代理，因此会尝试在静态文件目录中查找 `/api/diff`，导致 404 错误页面被返回。
    
    - 前端尝试解析返回的 HTML 404 页面作为 JSON，导致 `SyntaxError: Unexpected token '<'` 错误。

4. 小结

    - 绝对路径（以 `/` 开头）始终基于网站的根路径，容易导致在子路径部署时出错。

    - 相对路径（`不以 / 开头`）基于当前页面的路径，适合子路径部署。

    - 在您的情况下，将 `fetch('/api/diff', ...)` 改为 `fetch('api/diff', ...)` 确保请求路径相对于 `/codediffu/`，从而正确地被 Nginx 代理到后端应用。

    - 确保 Nginx 配置与前端请求路径匹配，是成功部署的关键。














