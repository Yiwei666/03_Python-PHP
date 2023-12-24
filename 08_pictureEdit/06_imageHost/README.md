# 1. 项目功能

### 1. 图床

获取剪贴板中的截图，上传至云服务器，作为图床使用，具体包括

1. 用户可以粘贴图片到页面上的可编辑区域，然后点击 "Upload Image" 按钮将图片上传到服务器。
2. 上传完成后，页面会显示上传的图片信息，包括文件大小、文件名、文件路径等，并提供一个可点击跳转的图床链接。
3. 还添加了一个复制按钮，允许用户复制包含图片的 HTML 代码到剪贴板。
4. 显示上传后的图片预览

### 2. web查看和迁移图片

1. 显示指定文件夹下的所有png图片，不包括 transfer.txt 文件中文件名
2. 点击 transfer 按钮，将对应图片文件名写入到指定transfer.txt文件中
3. 服务器crontab定时脚本，每60秒转移一次transfer.txt文件中的图片到新路径中，实现原路径文件删除，同时保留副本



# 2. 文件结构

- 项目1：图床

```
# v1 文件夹中是最初始版本的客户端和服务器端处理脚本，仅能实现剪贴板图片上传，然后返回图片大小、名称以及绝对路径

03_picPasteUpload.php             # 主脚本，获取剪贴板中的图像数据，点击`upload image`上传至云服务器，并返回图床链接和图片大小，需要指定服务器端处理图像的脚本
03_serverImageHost.php            # 服务器端处理图像的脚本


# 待完成

# markdown web预览脚本，支持图片预览
# 服务器端图床文件夹图片预览，方便管理
# sync图床文件夹同步备份脚本，避免图片丢失
# RGB转16进制颜色脚本

```

- 项目2：图片查看和转移


```
05_imageGallery.php               # 在web上显示指定路径下的所有图片，不包括 05_imageTransferName.txt 文件中列出的文件名
05_serverImageTransfer.php        # 服务器端实现将前端传递给的文件名写入到 05_imageTransferName.txt 文件中
05_imageTransferName.txt          # 点击transfer按钮后，存储对应文件名的文本
05_mvImageServer.sh               # bash脚本，用于将05_imageTransferName.txt列出的图片文件名从源目录剪切到另外一个目录
```


# 3. 图床搭建环境配置

### 1. 从剪贴板中获取截图显示在网页

- 如下代码展示了一种**从剪贴板获取图像**的方法，是后续编写脚本的基础

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clipboard Image Viewer with Size Control</title>
    <style>
        #imageContainer {
            border: 1px solid #ccc;
            padding: 10px;
            max-width: 300px; /* 设置最大宽度 */
            max-height: 300px; /* 设置最大高度 */
            overflow: hidden; /* 超出尺寸时隐藏 */
        }
    </style>
</head>
<body>
    <div>
        <p>Paste your image here:</p>
        <div contenteditable="true" id="imageContainer"></div>
    </div>

    <script>
        document.getElementById('imageContainer').addEventListener('paste', function (event) {
            event.preventDefault();

            var items = (event.clipboardData || event.originalEvent.clipboardData).items;

            for (var index in items) {
                var item = items[index];

                if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
                    var blob = item.getAsFile();
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        var img = new Image();
                        img.src = e.target.result;
                        document.getElementById('imageContainer').innerHTML = ''; // 清空容器
                        document.getElementById('imageContainer').appendChild(img);
                    };

                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
    </script>
</body>
</html>
```

这个简单的例子中，当用户在`<div>`元素中粘贴图片时，会触发paste事件。通过检查剪贴板中的项，找到包含图片的项，然后使用`FileReader`读取该项并将其显示在网页上。

请注意，这里将图片以base64格式显示，但在实际应用中，你可能想将它上传到服务器或以其他方式处理。

### 2. 图床文件夹权限设置

- 权限设置命令

```
chmod 777 /home/01_html/02_LAS1109/35_imageHost/
```

- 图床文件夹相应权限

```
drwxrwxrwx   2 root root   4096 Dec 21 14:46 35_imageHost
```

### 3. 更改nginx最大上传文件限制（默认1MB/单个文件）

在 NGINX 配置文件`/etc/nginx/nginx.conf`（ubuntu）中，`client_max_body_size` 参数的默认值通常是1m，表示1兆字节。这意味着默认情况下 NGINX 允许客户端上传的请求体（包括文件上传）的最大大小为1兆字节。

如果没有显式地在配置文件中设置 client_max_body_size，NGINX 将使用这个默认值。

```
client_max_body_size 5M;                                                     # 默认允许nignx客户端上传的请求体、如文件, 最大为1MB
```

- 以ubuntu系统中php脚本的请求为例

```nginx
        location ~ \.php$ {
            root /home/01_html/;                                                                           # 注意修改php文件根目录
            fastcgi_pass unix:/run/php/php7.4-fpm.sock;                                                    # 修改
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            include snippets/fastcgi-php.conf;                                                             # 新增
            client_max_body_size 5M;                                                                       # 默认允许nignx客户端上传的请求体、如文件, 最大为1MB
        }
```


### 4. 设置 `php.ini` 中对于文件上传大小的限制

- `upload_max_filesize`和`post_max_size`含义
   - 这两个参数都是用于限制通过 HTTP POST 请求上传到服务器的数据量的 PHP 配置项。当你在 PHP 配置文件（通常是 php.ini 文件）中设置这两个参数时，需要确保将它们设置为相同或更大的值，以便 post_max_size 能够容纳。
   - `upload_max_filesize`：该参数限制了单个文件上传的最大大小。通常默认值是较小的值，例如 2M，表示允许上传最大为2兆字节（2MB）的文件。如果用户尝试上传一个超过该大小的文件，上传请求将被拒绝。
   - `post_max_size`：该参数限制了整个 POST 请求的最大大小，包括除了文件上传之外的所有 POST 数据。通常默认值也是较小的值，例如 8M，表示整个 POST 请求的最大大小为8兆字节（8MB）。
 
- ubuntu查看`upload_max_filesize`和`post_max_size`参数命令

```sh
grep  upload_max_filesize  /etc/php/7.4/fpm/php.ini

grep  post_max_size  /etc/php/7.4/fpm/php.ini
```

`upload_max_filesize`和`post_max_size`默认值分别 2M 和 8M

```ini
; Maximum size of POST data that PHP will accept.
; http://php.net/post-max-size
post_max_size = 8M
; Maximum allowed size for uploaded files.
; http://php.net/upload-max-filesize
upload_max_filesize = 2M
```

- **重启 PHP-FPM 服务才能够使上述 `php.ini` 的修改生效**

```sh
service php7.4-fpm restart
```

🔹 仅重启nginx的web服务是不能够使其生效的

### 5. 显示图片

- 该部分代码片段可用于预览上传成功后的图床图片

```php
// 新增显示内容
var imageContainer = document.createElement('div');
var imageCode = `<p align="center">
                  <img src="${response.adjustedPath}" alt="Image Description" width="700">
                 </p>`;
imageContainer.textContent = imageCode;

// 添加样式
imageContainer.style.backgroundColor = 'black'; // 背景颜色为黑色
imageContainer.style.color = 'white'; // 文字颜色为白色

uploadInfoDiv.appendChild(document.createElement('br'));
uploadInfoDiv.appendChild(imageContainer);
```


<p align="center">
<img src="http://120.46.81.41/02_LAS1109/35_imageHost/20231221-221512.png" alt="Image Description" width="700">
</p>


### 6. 环境变量配置


在一台新服务器部署本项目时，除了更改php和nginx对于上传文件大小的限制外，还需要指定服务器端脚本名称，域名或ip，图床文件夹绝对路径，域名的根目录等


- **web脚本 03_picPasteUpload.php 参数初始化**


```php
<link rel="shortcut icon" href="https://mctea.one/00_logo/imageHost.png">                          // 指定icon网址
 
xhr.open('POST', '/03_serverImageHost.php', true);                                                 // 指定服务器端图片处理脚本

resultImage.width = 300;                                                                           // 设置预览图片宽度为300px
```



- **服务器脚本 03_serverImageHost.php 参数初始化**


```php
$uploadDirectory = '/home/01_html/02_LAS1109/35_imageHost/';                                       // 指定图床文件夹绝对路径

$adjustedFilePath = str_replace('/home/01_html', 'http://120.46.81.41', $targetFilePath);          // 使用ip或域名更换根目录路径
```


# 4. web图片查看及转移环境配置


### 1. 05_imageTransferName.txt

- 以`20231222-113823.png,2023-12-24 23:45:04`格式存储不显示和需要转移的图片

1. 提前创建该文本

```bash
touch 05_imageTransferName.txt
```


2. 设置权限和所属组，满足php脚本读写要求

```bash
chmod 666 05_imageTransferName.txt
chown www-data:www-data 05_imageTransferName.txt
```

### 2. 05_mvImageServer.sh

- 环境变量

```
# 定义文件路径变量
inputFile="/home/01_html/05_imageTransferName.txt"
sourceDirectory="/home/01_html/02_LAS1109/35_imageHost/"
destinationDirectory="/home/01_html/02_LAS1109/35_imageTransfer/"
```

- 定时每分钟执行一次

```
*/1 * * * * /usr/bin/bash /home/01_html/05_mvImageServer.sh
```


### 3. 05_serverImageTransfer.php

- 环境变量

```
// 指定文本文件路径
$filePath = '/home/01_html/05_imageTransferName.txt';
```


### 4. 05_imageGallery.php

```
$baseUrl = 'http://120.46.81.41/02_LAS1109/35_imageHost/';         // 图片url中文件名之前的部分
$imagesDirectory = '/home/01_html/02_LAS1109/35_imageHost/';       // 图片文件夹
$imagesPerPage = 40;                                               // web页面中每页显示的图片数量

// 读取图片转移记录文件
$serverScript = '05_serverImageTransfer.php';                      // 点击transfer后记录对应图片名的服务器处理脚本
$transferFile = '/home/01_html/05_imageTransferName.txt';          // 点击transfer后记录对应图片名的文本

body {
    text-align: center;
    background-color: #303030;                                     // 页面背景颜色
    color: #cccccc;                                                // 页面字体颜色
}

.gallery-item {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    width: 400px;                                                 // 图像容器的宽
    height: 400px;                                                // 图像容器的高
    margin: 30px;
    border-radius: 15px;                                          // 图像容器的圆角
    overflow: hidden;
    background-color: #1a1c1d;                                    // 图像容器背景颜色
}

.gallery img {
    width: 100%;                                                 // 图像容器内图片宽度与容器宽度的比例
    height: auto;                                                // 高度自适应
    object-fit: contain;
    border-radius: 15px;
}

```


