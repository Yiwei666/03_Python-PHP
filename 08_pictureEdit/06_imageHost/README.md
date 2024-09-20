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

### 项目1：图床

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

### 项目2：图片查看和转移


```
05_simpleGallery.php              # 初始简单版本的服务器图片网页查看器
05_imageGallery.php               # 在web上显示指定路径下的所有图片，不包括 05_imageTransferName.txt 文件中列出的文件名
05_serverImageTransfer.php        # 服务器端实现将前端传递给的文件名写入到 05_imageTransferName.txt 文件中
05_imageTransferName.txt          # 点击transfer按钮后，存储对应文件名的文本
05_mvImageServer.sh               # bash脚本，用于将05_imageTransferName.txt列出的图片文件名从源目录剪切到另外一个目录
08_picDisplay.php                 # 在网页上随机显示图库中的5张图片（能够识别终端类型：手机/电脑，图片采用懒加载）
08_picDisplay_one.php             # 网页上随机显示一张图片
```

- 示例

```
├── 051_picPasteUpload.php
├── 051_serverImageHost.php
├── 05_image
│   ├── 01_imageHost
│   └── 02_imageTransfer
├── 05_imageGallery.php
├── 05_imageTransferName.txt
├── 05_mvImageServer.sh
├── 05_serverImageTransfer.php
├── 05_simpleGallery.php


-rw-r--r-- 1 root     root      9035 Dec 24 21:35 051_picPasteUpload.php
-rw-r--r-- 1 root     root      1155 Dec 24 21:37 051_serverImageHost.php
drwxrwxrwx 4 root     root      4096 Dec 24 21:13 05_image
drwxrwxrwx 2 root     root     4096 Dec 29 18:25  01_imageHost
drwxrwxrwx 2 root     root     4096 Dec 29 11:11  02_imageTransfer
-rw-r--r-- 1 root     root      5288 Dec 24 21:45 05_imageGallery.php
-rw-rw-rw- 1 www-data www-data    80 Dec 29 11:10 05_imageTransferName.txt
-rwxr-xr-x 1 root     root       952 Dec 24 21:47 05_mvImageServer.sh
-rw-r--r-- 1 root     root       964 Dec 24 21:46 05_serverImageTransfer.php
-rw-r--r-- 1 root     root      3718 Dec 25 09:13 05_simpleGallery.php


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
client_max_body_size 30M;                                                     # 默认允许nignx客户端上传的请求体、如文件, 最大为1MB
```

- 以ubuntu系统中php脚本的请求为例

```nginx
        location ~ \.php$ {
            root /home/01_html/;                                                                           # 注意修改php文件根目录
            fastcgi_pass unix:/run/php/php7.4-fpm.sock;                                                    # 修改
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            include snippets/fastcgi-php.conf;                                                             # 新增
            client_max_body_size 30M;                                                                       # 默认允许nignx客户端上传的请求体、如文件, 最大为1MB
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

推荐顺便把php会话的生命周期给改掉

```sh
grep  session.gc_maxlifetime  /etc/php/7.4/fpm/php.ini
```

- **重启 PHP-FPM 服务才能够使上述 `php.ini` 的修改生效**

```sh
service php7.4-fpm restart
```

🔹 仅重启nginx的web服务是不能够使其生效的

### 5. 上传成功图片预览

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


### 6. 取消图片预览

1. 上传和下载共享总带宽，如果多个脚本同时上传高清图片，预览图片会可能会消耗大部分下载带宽，从而影响到上传速率。因此，可以考虑取消上传成功后的图片预览。

2. 在最新的`03_picPasteUpload.php`脚本中，如果不想要显示图片预览图，可将代码中的如下部分注释掉。

```js
/*
var resultImageContainer = document.createElement('div');
resultImageContainer.style.textAlign = 'center'; // 设置水平居中
resultImageContainer.style.marginTop = '20px'; // 设置距离顶部的距离为20px
resultImageContainer.style.backgroundColor = '#222426'; // 设置背景色为灰黑色
resultImageContainer.style.padding = '10px'; // 设置内边距为10px

var resultImage = new Image();
resultImage.src = response.adjustedPath;
resultImage.width = 300; // 设置图片宽度为500px
resultImage.alt = 'Result Image';
resultImageContainer.appendChild(resultImage);

uploadInfoDiv.appendChild(resultImageContainer);
*/
```


### 7. 环境变量配置


在一台新服务器部署本项目时，除了更改php和nginx对于上传文件大小的限制外，还需要指定服务器端脚本名称，域名或ip，图床文件夹绝对路径，域名的根目录等


- **web脚本 03_picPasteUpload.php 参数初始化**


```php
<link rel="shortcut icon" href="https://mctea.one/00_logo/imageHost.png">                          // 指定icon网址
 
xhr.open('POST', '/03_serverImageHost.php', true);                                                 // 指定服务器端图片处理脚本

resultImage.width = 300;                                                                           // 设置预览图片宽度为300px
```



- **服务器脚本 03_serverImageHost.php 参数初始化**

注意：`$uploadDirectory` 变量最后有 `/`

```php
$uploadDirectory = '/home/01_html/02_LAS1109/35_imageHost/';                                       // 指定图床文件夹绝对路径

$adjustedFilePath = str_replace('/home/01_html', 'http://120.46.81.41', $targetFilePath);          // 使用ip或域名更换根目录路径
```


# 4. web图片查看及转移环境配置


## 1. `05_imageTransferName.txt`

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



## 2. `05_imageGallery.php`

### 1. 环境变量

```php
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

💎 **新增功能**
1. 添加具有垂直滚动条的页码侧边栏
2. 设置侧边栏的高度，宽度，页码与滚动条的水平距离，滚动条的触发条件
3. 高亮当前页码
4. 无论用户何时回到页面，他们都可以从他们停止浏览的地方继续


### 2. Transfer验证新功能

1. 原代码：点击Transfer按钮不需要验证，即可将需要转移的图片信息发送到服务器处理脚本

```php
<script>
    var serverScriptUrl = '<?php echo $serverScript; ?>';

    function transferImage(imageUrl) {
        var imageName = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', serverScriptUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    alert('Image transfer information recorded successfully!');
                } else {
                    alert('Error: Unable to record transfer information.');
                }
            }
        };
        xhr.send('imageName=' + encodeURIComponent(imageName));
    }
</script>
```


2. 新功能：添加Transfer验证后的代码，提示用户输入密码，服务器脚本核验通过后才进行后续操作

```php
<script>
    var serverScriptUrl = '<?php echo $serverScript; ?>'; // 服务器端处理脚本的URL

    function transferImage(imageUrl) {
        // 提示用户输入密码
        var userPassword = prompt('Please enter the password to transfer the image:');

        // 如果用户取消输入，则退出
        if (!userPassword) {
            alert('Password input canceled.');
            return;
        }

        // 获取图片的文件名
        var imageName = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);

        // 创建 XMLHttpRequest 对象
        var xhr = new XMLHttpRequest();
        xhr.open('POST', serverScriptUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // 设置回调函数，处理服务器端响应
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    if (xhr.responseText === 'success') {
                        alert('Image transfer information recorded successfully!');
                    }
                } else if (xhr.status == 403) { // 处理密码错误的情况
                    if (xhr.responseText.includes('error: incorrect password')) {
                        alert('Incorrect password. Please try again.');
                    }
                } else if (xhr.status == 400) { // 处理 imageName 参数缺失的情况
                    if (xhr.responseText.includes('error: imageName parameter is missing')) {
                        alert('Error: image name is missing.');
                    }
                } else {
                    alert('Error: Unable to record transfer information.');
                }
            }
        };

        // 发送图片名称和用户输入的密码到服务器端进行验证
        xhr.send('imageName=' + encodeURIComponent(imageName) + '&password=' + encodeURIComponent(userPassword));
    }
</script>
```

- 注意：上述两个脚本模块可以互换，不涉及到任何环境变量设置和参数初始化。添加验证后的模块需要在服务器脚本`05_serverImageTransfer.php`初始化密码参数。



## 3. `05_serverImageTransfer.php`

- 环境变量

```php
// 指定文本文件路径
$filePath = '/home/01_html/05_imageTransferName.txt';
```


## 4. `05_mvImageServer.sh`

- 环境变量

```php
# 定义文件路径变量
inputFile="/home/01_html/05_imageTransferName.txt"
sourceDirectory="/home/01_html/02_LAS1109/35_imageHost/"
destinationDirectory="/home/01_html/02_LAS1109/35_imageTransfer/"
```

- 添加执行权限

```
chmod +x 05_mvImageServer.sh
```

- 定时每分钟执行一次

```sh
*/1 * * * * /usr/bin/bash /home/01_html/05_mvImageServer.sh
```


## 5. `05_simpleGallery.php`

```sh
<link rel="shortcut icon" href="https://mctea.one/00_logo/gallary.png">             // icon地址

$baseUrl = 'http://120.46.81.41/02_LAS1109/35_imageTransfer/';                      // 图片url中图片文件名前面部分
$imagesDirectory = '/home/01_html/02_LAS1109/35_imageTransfer/';                    // 转移的目标路径
$imagesPerPage = 60;                                                                // 每页显示的图片数量
```


## 6. `08_picDisplay.php` 随机显示指定目录下 n 张图片

1. 环境配置

```php
$dir4 = "/home/01_html/08_x/image/01_imageHost";  // 需要制定图片绝对路径
$dir5 = str_replace("/home/01_html", "", $dir4); 
$domain = "https://abc.com";                      // 指定根目录对应的域名网址
$picnumber = 8;                                   // 设置需要显示的图片数量
```

2. 设备终端类型检测

- 输出并检查用户代理字符串

```php
<?php echo $_SERVER['HTTP_USER_AGENT']; ?>
```

```sh
Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:124.0) Gecko/20100101 Firefox/124.0                                  # 电脑firefox浏览器
Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36   # 电脑chrome浏览器
Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36             # 手机chrome桌面版网站设置，默认开启    

Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Mobile Safari/537.36   # 手机chrome手机模式
Mozilla/5.0 (Android 11; Mobile; rv:109.0) Gecko/114.0 Firefox/114.0                                              # 手机firefox浏览器
```

注意：手机上的chrome浏览器如果设置网站采用桌面版，那么`HTTP_USER_AGENT`会按照电脑端来处理，所以要关掉对应网址的桌面版选项

- 相关代码

```php
    .image {
        width: <?php echo preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $_SERVER['HTTP_USER_AGENT']) ? '900px' : '500px'; ?>;
        height: auto;
        margin-bottom: 20px;
    }
```


3. 代码总体思路

```
1. 已知在 $dir4 目录下有多张png格式的图片，例如$dir4="/home/01_html/08_x/image/01_imageHost"。注意$dir5为 $dir4去掉"/home/01_html"部分
2. 从中随机选择 $picnumber 张图片
3. 在页面上从上到下居中显示这些图片，注意 服务器根目录 "/home/01_html" 对应域名网址 $domain, 例如 $domain="https://abc.com" 。显示图片的时候，图片的链接应该为$url="$domain/$dir5/图片名称"
4. 在页面右侧垂直方向显示一个圆形按钮，点击该按钮，将重新选择$picnumber 张图片显示在页面，相当于重新加载页面了
5. 能否设置按钮是悬浮的，不随页面放大或者缩小或者移动而改变位置或者大小
6. 主页滑动页面可以查看所有竖直方向上的所有图片。
7. 识别终端是手机还是电脑（获取HTTP_USER_AGENT），手机端请设置这些图片宽度为900px，高度自适应；电脑端设置宽度为500px
8. 新加功能，实现图片从上往下逐张加载，避免用户在等待多张图片加载的同时，一张完整图片也看不到
```


## 7. `08_picDisplay_one.php` 随机显示指定目录下 1 张图片

1. `08_picDisplay_one.php`是`08_picDisplay.php`改进版本，在页面中仅显示指定文件夹下的一张图片，未使用mysql进行数据管理
2. 核心特性：
   - 引入cookie验证用户访问权限
   - 保留终端类型检测，以便用户在不同设备上获得更好的浏览体验

3. 环境变量

```php
$key = 'your-signing-key-1';  // 应与登录脚本中的密钥一致

$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://abc.com"; // 域名网址
```


# 5. to do list

1. uninstall bash脚本
2. 安装脚本中添加终端自适应脚本
3. cookie相关脚本设置
4. 图片压缩设置
5. rclone备份图片到onedrive
6. mysql相关设置







