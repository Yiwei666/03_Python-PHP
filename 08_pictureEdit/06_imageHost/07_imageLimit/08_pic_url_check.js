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
