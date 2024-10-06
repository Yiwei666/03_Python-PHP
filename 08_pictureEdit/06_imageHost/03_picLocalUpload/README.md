# 1. 项目功能

在浏览器 http://localhost:4000 保存PNG图片到指定文件夹，后端通过node.js实现

# 2. 文件结构

```
server.js                  # cmd控制台运行脚本
index.html                 # 浏览器中打开，打开网址 http://localhost:4000

move_duplicates.py         # 查找指定文件夹下的所有相同PNG图片，并移动到子文件夹中

01_scp_proxy.py                   # 使用本地socks5代理上传文件到云服务器指定目录
02_scp_proxy_threadPool.py        # 创建多个ssh连接进行传输，提高效率，缩短时间
03_local_remote_compare.py
04_scp_proxy_check_Reupload.py
```

# 3. Node.js 环境配置

### 1. Node.js 环境准备

确保Node.js已安装在你的Windows机器上。如果未安装，请从 Node.js官网 下载并安装。

### 2. 创建项目和安装依赖

在你的项目目录中创建Node.js应用：

```sh
mkdir my-image-uploader
cd my-image-uploader
npm init -y
npm install express multer
```

这将安装Express和multer，Express用于创建服务器，multer用于处理文件上传。


### 3. 编写Node.js服务器代码

在项目目录中创建 `server.js` 文件，加入以下代码：

```js
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
```


- 参数初始化需要注意的环境变量

```js
const uploadPath = 'D:/onedrive/图片/01_家乡风景/海外风景';                      // 指定存储图片的路径
const adjustedPath = filePath.replace('D:/onedrive/', 'D:/onedrive/');          // 替换 filePath 中的部分路径字符串，显示在返回成功后的页面，需根据实际情况调整路径
```

注意：上述第一个 `D:/onedrive/` 是 `uploadPath` 中的一部分


### 4. 创建前端页面（访问：`http://localhost:4000`）

在项目目录下创建一个名为 `public` 的文件夹，并在其中创建 `index.html` 文件，内容可以是修改后适用于上传的简单HTML页面：


```html

```


### 5. 运行服务器

在命令行中，启动你的服务器：

```sh
node server.js
```

1. 此时，当你在浏览器中访问 `http://localhost:4000`，你将看到一个文件上传表单。选择文件后，文件将上传到指定的Windows路径下。

2. 注意：如果是直接通过浏览器打开`index.html`的绝对路径`file:///D:/software/27_nodejs/my-image-uploader/public/index.html`，那么是无法上传成功的

3. 前端脚本出现问题了，记得去浏览器控制台查看报错信息，例如：

```
Access to XMLHttpRequest at 'file:///D:/upload' from origin 'null' has been blocked by CORS policy: Cross origin requests are only supported for protocol schemes: http, data, isolated-app, chrome-extension, chrome-untrusted, https, edge.
```

这个错误信息表明您遇到了一个跨源资源共享（CORS）政策的问题，它阻止了您的页面从`file://`协议发出的`XMLHttpRequest（XHR）`请求。这个问题通常在直接从文件系统（如双击打开HTML文件）而不是通过`HTTP服务器`访问网页时出现。由于安全原因，现代浏览器限制了从`file://协议`发出的`XHR请求`。

4. 确保你提前在Windows上创建了目标文件夹 `D:/hotmail/OneDrive/图片/01_家乡风景`，以避免出现路径错误。这种方法满足你直接在本地Windows环境中运行并处理文件的需求。



# 4. windows图片上传到云服务器

### 1. `scp`命令行上传

1. 上传文件

```sh
scp -r "D:\onedrive\图片\01_家乡风景\海外风景" root@75.46.107.63:/home/01_html/08_x/image/03_picTemp
```

2. 将文件从临时文件夹转移到图床文件夹

```
mv /home/01_html/08_x/image/03_picTemp/海外风景/* /home/01_html/08_x/image/01_imageHost/
```

3. windows快捷命令

参考`https://github.com/Yiwei666/05_C_programing/blob/main/sft/python.txt`

```cmd
scp -r "D:\onedrive\图片\01_家乡风景\海外风景" root@75.46.108.63:/home/01_html/08_x/image/03_picTemp
cd /d D:\software\27_nodejs\my-image-uploader && node server.js
```

4. linux快捷命令

```bash
alias mvp='mv /home/01_html/08_x/image/03_picTemp/海外风景/* /home/01_html/08_x/image/01_imageHost/'
alias lwp='echo $(($(ls -l /home/01_html/08_x/image/01_imageHost/ | wc -l) - 1))'
alias lwt='echo $(($(ls -l /home/01_html/08_x/image/03_picTemp/海外风景/ | wc -l) - 1))'
alias dsp='du -sh /home/01_html/08_x/image/01_imageHost/'
alias cdp='cd /home/01_html/08_x/image/03_picTemp/海外风景/'
```


### 2. `01_scp_proxy.py` 脚本上传

使用本地socks5，1080端口代理上传，尤其是在ssh连接不上的时候

1. 环境变量

```py
local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
remote_host = "74.48.107.63"  # 远程服务器IP
remote_port = 22  # 远程服务器SSH端口
username = "root"  # SSH用户名
password = "your_password"  # SSH密码
```

### 3. `02_scp_proxy_threadPool.py` 并行传输

1. 思路：假设本地目录下有`n`个文件（没有目录），并行创建`m+1`个ssh连接，`n/m`的商是`a`，余数是`b`，确保 `a*m+b=n`，然后前`m`个连接平均每个传递`a`个文件，最后一个ssh连接传递`b`个文件，先赋值`m=5`

2. 环境变量

```py
local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
remote_host = "74.48.107.63"  # 远程服务器IP
remote_port = 22  # 远程服务器SSH端口
username = "root"  # SSH用户名
password = "your_password"  # SSH密码

scp_transfer_parallel(local_path, remote_path, remote_host, remote_port, username, password, m=5)
```

注意：通过修改参数`m`值可以改变创建的ssh连接数。参数m不要设置太大，经测试设置`m=15`时，会有约`5%`的图片上传失败。


### 4. `03_local_remote_compare.py` 服务器和本地图片比较

1. 环境变量

```py
if __name__ == "__main__":
    local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
    remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
    remote_host = "74.48.107.63"  # 远程服务器IP
    remote_port = 22  # 远程服务器SSH端口
    username = "root"  # SSH用户名
    password = "your_password"  # SSH密码
```

### 5. `04_scp_proxy_check_Reupload.py` 服务器和本地图片比较并上传

1. 环境变量

```py
if __name__ == "__main__":
    local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
    remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
    remote_host = "74.48.107.63"  # 远程服务器IP
    remote_port = 22  # 远程服务器SSH端口
    username = "root"  # SSH用户名
    password = "your_password"  # SSH密码
```



# 5. 查找指定文件夹下所有相同PNG图片

这段代码的功能是：

1. 在指定的源文件夹中查找重复的PNG图片，并将这些重复的图片移动到目标文件夹中。
2. 它通过计算每个图片文件的MD5哈希值来检测重复。如果发现有相同哈希值的图片，说明它们是重复的。
3. 此代码还会打印出处理过程中的一些统计信息，包括总文件数、没有重复的文件数、移动的重复文件数和唯一文件的数量。


`move_duplicates.py`

```py
import os
import hashlib
from shutil import move
from PIL import Image

def get_image_hash(image_path):
    with Image.open(image_path) as img:
        return hashlib.md5(img.tobytes()).hexdigest()

def find_and_move_duplicates(src_folder, dest_folder):
    if not os.path.exists(dest_folder):
        os.makedirs(dest_folder)

    hash_map = {}
    duplicates = []
    total_files = 0

    for filename in os.listdir(src_folder):
        if filename.lower().endswith('.png'):
            total_files += 1
            file_path = os.path.join(src_folder, filename)
            if not os.path.exists(file_path):
                continue
            img_hash = get_image_hash(file_path)

            if img_hash in hash_map:
                duplicates.append(file_path)
                if len(hash_map[img_hash]) == 1:
                    duplicates.append(hash_map[img_hash][0])
            else:
                hash_map[img_hash] = [file_path]

    for dup_path in duplicates:
        if os.path.exists(dup_path):
            basename = os.path.basename(dup_path)
            new_path = os.path.join(dest_folder, basename)
            try:
                move(dup_path, new_path)
                print(f"Moved: {dup_path} -> {new_path}")
            except Exception as e:
                print(f"Error moving {dup_path} to {new_path}: {e}")

    # 统计信息
    unique_files_count = len(hash_map)
    duplicate_files_count = len(duplicates)
    non_duplicate_files_count = total_files - duplicate_files_count

    print(f"Total files: {total_files}")
    print(f"Files without duplicates: {non_duplicate_files_count}")
    print(f"Duplicate files moved: {duplicate_files_count}")
    print(f"Unique files (by hash): {unique_files_count}")

source_folder = r"D:\onedrive\图片\01_家乡风景\海外风景"
destination_folder = r"D:\onedrive\图片\01_家乡风景\海外风景\01_repeat"
find_and_move_duplicates(source_folder, destination_folder)
```


- 环境变量设置

```py
source_folder = r"D:\onedrive\图片\01_家乡风景\海外风景"                          # 所有图片位于的源文件夹
destination_folder = r"D:\onedrive\图片\01_家乡风景\海外风景\01_repeat"           # 存放相同图片的子文件夹，可以自定义
```



# 6. 服务器项目相关alias

```bash
alias mvp='mv /home/01_html/08_x/image/03_picTemp/海外风景/* /home/01_html/08_x/image/01_imageHost/'
alias lwp='echo $(($(ls -l /home/01_html/08_x/image/01_imageHost/ | wc -l) - 1))'
alias lwt='echo $(($(ls -l /home/01_html/08_x/image/03_picTemp/海外风景/ | wc -l) - 1))'
alias dsp='du -sh /home/01_html/08_x/image/01_imageHost/'
alias cdp='cd /home/01_html/08_x/image/03_picTemp/海外风景/'
alias cdh='cd /home/01_html; ls -l'
alias lwc='ls -l | grep "^-" | wc -l'

# rc6_onedrive = 
alias rsp='rclone size "rc6:cc1-1/01_html/08_x/image/01_imageHost/"'
alias rcp='nohup rclone copy /home/01_html/08_x/image/01_imageHost/  rc6:cc1-1/01_html/08_x/image/01_imageHost/ --transfers=16 &'
alias clg='cat  /var/log/nginx/access.log'
alias tgn='tail -n 50 /var/log/nginx/access.log'
alias gn='ps aux | grep node'
alias cb='cat ~/.bashrc'
alias vb='vi ~/.bashrc'
alias sb='source ~/.bashrc'
alias kn='kill $(pgrep -f "08_pic_url_check.js")'
# alias np='nohup node /home/01_html/08_x_nodejs/08_pic_url_check.js &'
alias sn='nohup node /home/01_html/08_x_nodejs/08_pic_url_check.js > /home/01_html/08_x_nodejs/nohup.out &'
alias phd='php /home/01_html/08_image_dislikes_delete.php'
alias sbp='mysqldump -p image_db > /home/01_html/08_image_backup_$(date +%Y%m%d_%H%M%S).sql'
```





