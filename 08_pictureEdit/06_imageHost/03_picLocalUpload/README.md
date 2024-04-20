# 1. 项目功能


# 2. 文件结构


# 3. Node.js 环境配置

### 步骤 1: Node.js 环境准备

确保Node.js已安装在你的Windows机器上。如果未安装，请从 Node.js官网 下载并安装。

### 步骤 2: 创建项目和安装依赖

在你的项目目录中创建Node.js应用：

```sh
mkdir my-image-uploader
cd my-image-uploader
npm init -y
npm install express multer
```

这将安装Express和multer，Express用于创建服务器，multer用于处理文件上传。


### 步骤 3: 编写Node.js服务器代码

在项目目录中创建 `server.js` 文件，加入以下代码：

```js

```


### 步骤 5: 创建前端页面（访问：`http://localhost:4000`）

在项目目录下创建一个名为 `public` 的文件夹，并在其中创建 `index.html` 文件，内容可以是修改后适用于上传的简单HTML页面：


```html

```


### 步骤 6: 运行服务器

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













