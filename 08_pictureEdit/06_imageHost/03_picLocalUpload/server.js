const express = require('express');
const multer = require('multer');
const fs = require('fs');
const path = require('path');

const app = express();
const port = 4000;

// 设置存储配置
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        // 指定文件保存路径，确保这个路径已经存在，否则会报错
        const uploadPath = 'D:/onedrive/图片/01_家乡风景/海外风景';
        if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
    },
    filename: function (req, file, cb) {
        // 获取当前时间并格式化为 'Ymd-His'
        const date = new Date();
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');  // 月份从0开始，所以+1，并确保两位数字
        const day = date.getDate().toString().padStart(2, '0');
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const seconds = date.getSeconds().toString().padStart(2, '0');
        
        const timestamp = `${year}${month}${day}-${hours}${minutes}${seconds}`;
        const filename = `${timestamp}.png`;
        cb(null, filename);
    }
});

const upload = multer({ storage: storage });

// 静态文件服务
app.use(express.static('public'));

// 文件上传处理
app.post('/upload', upload.single('image'), (req, res) => {
    if (!req.file) {
        return res.status(500).send('Upload failed.');
    }

    const fileSizeKB = (req.file.size / 1024).toFixed(2);
    const fileSizeMB = (req.file.size / 1024 / 1024).toFixed(3);
    const filePath = req.file.path;
    const fileName = req.file.filename;
    const adjustedPath = filePath.replace('D:/onedrive/', 'D:/onedrive/');  // 需根据实际情况调整路径

    const response = {
        sizeKB: fileSizeKB,
        sizeMB: fileSizeMB,
        fileName: fileName,
        filePath: filePath,
        adjustedPath: adjustedPath
    };

    res.json(response);
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});
