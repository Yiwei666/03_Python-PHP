# 1. 项目功能

获取剪贴板中的截图，上传至云服务器，作为图床使用

# 2. 文件结构





# 3. 环境配置

### 1. 将剪贴板中的截图显示在网页

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

仅重启nginx的web服务是不能够使其生效的









