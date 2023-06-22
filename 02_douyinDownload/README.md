### 项目功能：在web端获取抖音分享url，下载视频到vps指定目录，打印执行过程日志信息


### 主要脚本和文件位置
下面三个php脚本需要位于同一目录
```
04_douyin_PHP_download.php       // 在web上提示用户输入抖音视频分享链接，提取url，覆盖写入到txt文件
run_python_script.php            // 执行python脚本，下载视频
print_log_file.php               // 打印日志内容到web页面
```

视频下载脚本可以位于其他目录，推荐与txt文件位于同一目录，便于管理
```
01_douyinDown.py                 // 读取txt文件的url，下载抖音视频，将日志内容覆盖写入日志文件
```

### 注意在相应路径下创建这两个文件

```
/home/01_html/05_douyinDownload/douyin_url.txt          // 保存抖音url

/home/01_html/05_douyinDownload/douyin_log.txt          // 保存python脚本的日志信息
```

- **01_douyinDown.py** 脚本将会从douyin_url.txt读取下载链接，然后将脚本中的print信息写入到douyin_log.txt，将mp4视频下载到指定目录。txt文件中的url和日志都是覆盖写入。



### 权限设置

- 浏览器运行php脚本，该php脚本在vps上实现对txt文件的读取和写入，对php脚本的调用，python脚本的调用，然后python脚本执行对txt文件的读取和写入，以及下载视频文件到其他文件夹中  
- 涉及到的所有txt文件需要更改组和读写权限，尤其是python进行读写的txt文件
- 涉及到的所有脚本和文件夹要添加执行权限，包括视频写入的文件夹，python脚本,php脚本等


### 示例

- 您需要确保目标目录 /home/01_html/02_douyVideo 具有适当的权限和所有者，以便 Python 脚本可以下载和写入 MP4 视频文件。

您可以使用以下命令为目录设置权限和所有者：

```
sudo chmod 775 /home/01_html/02_douyVideo
sudo chown www-data:www-data /home/01_html/02_douyVideo
```

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

- Python 脚本：确保 Python 脚本文件具有可执行权限，并且所有者设置为 Web 服务器使用的用户。您可以运行以下命令设置权限和所有者：

```
sudo chmod +x /path/to/your/script.py
sudo chown www-data:www-data /path/to/your/script.py
```

请将 /path/to/your/script.py 替换为实际的 Python 脚本路径。

- TXT 文件：确保您希望 Python 脚本读写的 TXT 文件具有适当的权限，以便 Web 服务器用户可以访问和修改它们。通常情况下，为了保护文件，不建议将其所有者更改为 Web 服务器用户。相反，您可以确保 TXT 文件具有适当的权限，以允许 Web 服务器用户读写文件。

```
sudo chmod 664 /path/to/your/file.txt
```

请将 /path/to/your/file.txt 替换为实际的 TXT 文件路径。

请注意，确保文件和目录的权限设置能够平衡安全性和可访问性，以防止潜在的安全风险。根据您的实际需求和环境，请适当地调整权限设置。

在更改文件和目录的权限和所有者之后，重新尝试通过 Web 浏览器访问 PHP 脚本并调用 Python 脚本，以查看是否解决了问题。
