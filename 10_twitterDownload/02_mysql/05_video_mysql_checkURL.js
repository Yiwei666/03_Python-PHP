const express = require('express');
const crypto = require('crypto');
const fs = require('fs'); // 使用同步版本的 fs 模块
const fsPromises = fs.promises; // 使用异步版本的 fs.promises 操作
const path = require('path');
const url = require('url');

const app = express();
const port = 3000;
const signingKey = 'your-signing-key'; // 替换为您的实际签名密钥
const phpScriptDirectory = '/home/01_html/'; // PHP脚本所在的根目录

let allowedPHPFiles = []; // 允许的PHP脚本名数组，初始化为空

// 动态更新允许的PHP文件列表
async function updateAllowedPHPFiles() {
  try {
    const files = await fsPromises.readdir(phpScriptDirectory);
    allowedPHPFiles = files.filter(file => file.endsWith('.php'));
    console.log('Updated allowed PHP files:', allowedPHPFiles);
  } catch (error) {
    console.error('Error updating allowed PHP files:', error);
  }
}

// 验证签名
function validateSignature(videoName, expires, signature) {
  const expectedSignature = crypto
    .createHmac('sha256', signingKey)
    .update(`${videoName}${expires}`)
    .digest('hex');

  return expectedSignature === signature && parseInt(expires) > Date.now() / 1000;
}

// 验证Referer
function validateReferer(req) {
  const referer = req.headers.referer || '';
  console.log(referer);
  const parsedUrl = url.parse(referer);
  console.log(parsedUrl);
  const pathname = parsedUrl.pathname || '';
  console.log(pathname);
  // 检查Referer的路径是否为允许的PHP脚本之一
  return allowedPHPFiles.some(file => pathname.endsWith(file));
}

// 配置视频资源路由
app.get('/05_twitter_video/:videoName', (req, res) => {
  const { videoName } = req.params;
  const { expires, signature } = req.query;

  // 先验证Referer
  if (!validateReferer(req)) {
    return res.status(403).send('Forbidden: Invalid referer');
  }

  // 再验证签名
  if (!validateSignature(videoName, expires, signature)) {
    return res.status(403).send('Forbidden: Invalid signature or expired link');
  }

  const videoPath = path.join('/home/01_html/05_twitter_video', videoName);
  if (fs.existsSync(videoPath)) {  // 使用同步 fs 的 existsSync 方法
    res.sendFile(videoPath);
  } else {
    res.status(404).send('Not Found');
  }
});

// 启动服务器
app.listen(port, () => {
  console.log(`Server running at http://localhost:${port}`);
  updateAllowedPHPFiles(); // 初始更新PHP文件列表
  setInterval(updateAllowedPHPFiles, 5 * 60 * 1000); // 每5分钟更新一次PHP文件列表
});
