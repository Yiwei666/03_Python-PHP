# 1. 项目功能

在浏览器 http://localhost:4000 保存PNG图片到指定文件夹，后端通过node.js实现

# 2. 文件结构

```
server.js          # cmd控制台运行脚本
index.html         # 浏览器中打开，打开网址 http://localhost:4000
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

```


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



