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
