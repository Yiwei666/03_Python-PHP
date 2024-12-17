# 1. 项目功能

使用 Node.js 在本地或者云服务器中部署计算并显示两个版本代码差异的在线工具

# 2. 文件结构

```
index.html       # 前端网页，用于粘贴不同版本代码，显示计算差异
server.js        # 后端脚本，计算两个版本的差异
```

# 3. 环境配置

## 1. windows本地部署

1. 在你的工作目录中初始化一个新的 Node.js 项目

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


5. 运行应用

```
node server.js
```

打开浏览器，访问 `http://localhost:2000`。你应该会看到一个简单的界面，有两个文本输入框用于粘贴旧版本和新版本的代码，点击“比较差异”按钮后，差异结果会显示在下方。









