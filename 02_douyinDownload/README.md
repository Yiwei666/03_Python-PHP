### 项目功能
---

在web端获取抖音分享url，下载视频到vps指定目录，打印执行过程日志信息，在web页面显示视频
<br>
<br>
**注意事项**
```
尽量不要在winSCP窗口中编辑vps上的代码和txt文件内容，由于winscp可能采用gbk进行编码，造成代码缩进、空格、汉字等出现错误，程序无法识别进而报错
正确做法是在本地计算机用sublime text进行编辑后上传，通常从github复制粘贴到远程计算机上是没有问题的，尽量不要在winscp中进行编辑

```


### 1. 主要脚本和文件位置
---

项目文件结构
```

├── 04_douyin_PHP_download.php
├── run_python_script.php
├── print_log_file.php
├── 05_douyinDownload                      # drwxr-xr-x  2 nginx nginx
│    ├── 01_douyinDown.py
│    ├── douyin_log.txt
│    └── douyin_url.txt
├── douyVideo_AutoCenter_Pad.php
├── lsDouyin.php
├── 02_douyVideo                           # drwxrwxr-x  3 nginx nginx      保存视频的文件夹
│   ├── 20220618-001533.mp4
│   ├── 20230617-234813.mp4
│   ├── 20230618-000354.mp4
│   ......

```


针对如下php脚本，更改视频路径、每行显示的视频数量、视频尺寸大小时，需要更改如下代码
```
douyVideo.php/douyVideo_AutoCenter_Pad.php/douyVideo_AutoCenter.php

$videosPerRow = 3; // 可以根据需要更改每行显示的视频数量
$videoPath = '/home/01_html/02_douyVideo/';
$videoUrl = $domain . '/02_douyVideo/' . $videoName;
echo '<video controls width="300" height="400" onended="playNextVideo(this)">'; // 添加onended事件
```


下面三个php脚本需要位于同一目录
```
04_douyin_PHP_download.php       // 在web上提示用户输入抖音视频分享链接，提取url，覆盖写入到txt文件
run_python_script.php            // 执行python脚本，下载视频
print_log_file.php               // 打印日志内容到web页面
```

视频下载脚本可以位于其他目录，推荐与txt文件位于同一目录，便于管理
```
01_douyinDown.py                 // 读取txt文件的url，下载抖音视频，将日志内容覆盖写入日志文件
01_douyinDown(beta).py           // 基于新的抖音视频下载api
```

**注意在相应路径下创建这两个文件并进行权限设置**

```
/home/01_html/05_douyinDownload/douyin_url.txt          // 保存抖音url

/home/01_html/05_douyinDownload/douyin_log.txt          // 保存python脚本的日志信息
```

- **01_douyinDown.py** 脚本将会从douyin_url.txt读取下载链接，然后将脚本中的print信息写入到douyin_log.txt，将mp4视频下载到指定目录。txt文件中的url和日志都是覆盖写入。



### 2. 权限设置
---

- 浏览器运行php脚本，该php脚本在vps上实现对txt文件的读取和写入，对php脚本的调用，python脚本的调用，然后python脚本执行对txt文件的读取和写入，以及下载视频文件到其他文件夹中  
- 涉及到的所有txt文件需要更改组和读写权限，尤其是python进行读写的txt文件
- 涉及到的所有脚本和文件夹要添加执行权限，包括视频写入的文件夹，python脚本,php脚本等


**示例**

- 您需要确保目标目录 /home/01_html/02_douyVideo 具有适当的权限和所有者，以便 Python 脚本可以下载和写入 MP4 视频文件。

您可以使用以下命令为目录设置权限和所有者：

```
sudo chmod 775 /home/01_html/02_douyVideo
sudo chown www-data:www-data /home/01_html/02_douyVideo
```
**注意：至少满足drwxrwxr-x  2 nginx nginx**

上述命令将为所有者和组提供读、写和执行权限，并为其他用户提供读和执行权限。将 www-data 替换为您的 Web 服务器使用的实际用户和组。

确保将上述命令中的 /home/01_html/02_douyVideo 替换为实际的目录路径。

通过为目录设置适当的权限和所有者，您将允许 Web 服务器用户（如 www-data）能够在该目录下下载和写入 MP4 视频文件。

请注意，如前所述，确保您的权限设置适当平衡安全性和可访问性，以防止潜在的安全风险。


您需要确保以下文件具有适当的权限和所有者：

- PHP 脚本：确保 PHP 脚本文件具有可执行权限，并且所有者设置为 Web 服务器使用的用户（如 www-data）。您可以运行以下命令设置权限和所有者：

```
sudo chmod +x /path/to/your/script.php
sudo chown www-data:www-data /path/to/your/script.php
```

请将 /path/to/your/script.php 替换为实际的 PHP 脚本路径。

**注意：使用默认组用户和读写执行权限-rw-r--r--  1 root  root似乎也没问题**

- Python 脚本：确保 Python 脚本文件具有可执行权限，并且所有者设置为 Web 服务器使用的用户。您可以运行以下命令设置权限和所有者：

```
sudo chmod +x /path/to/your/script.py
sudo chown www-data:www-data /path/to/your/script.py
```

请将 /path/to/your/script.py 替换为实际的 Python 脚本路径。

**注意：至少满足-rwxr-xr-x 1 nginx nginx**

- TXT 文件：确保您希望 Python 脚本读写的 TXT 文件具有适当的权限，以便 Web 服务器用户可以访问和修改它们。通常情况下，为了保护文件，不建议将其所有者更改为 Web 服务器用户。相反，您可以确保 TXT 文件具有适当的权限，以允许 Web 服务器用户读写文件。

```
sudo chmod 664 /path/to/your/file.txt
```

请将 /path/to/your/file.txt 替换为实际的 TXT 文件路径。

**注意：至少满足-rw-rw-rw- 1 nginx nginx**

请注意，确保文件和目录的权限设置能够平衡安全性和可访问性，以防止潜在的安全风险。根据您的实际需求和环境，请适当地调整权限设置。

在更改文件和目录的权限和所有者之后，重新尝试通过 Web 浏览器访问 PHP 脚本并调用 Python 脚本，以查看是否解决了问题。

### 3. 页面显示设置
---

要将页面的背景色修改为黑色，可以在 <style> 标签中添加以下 CSS 规则：

```
<style>
    body {
        background-color: black;
    }
</style>
```

将这段代码添加到 <style> 标签内，然后保存并刷新页面，即可将页面的背景色设置为黑色。


