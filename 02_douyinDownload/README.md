### 文件位置
下面三个php脚本需要位于同一目录
```
04_douyin_PHP_download.php
run_python_script.php
print_log_file.php
```


### 注意在相应路径下创建这两个文件

```
/home/01_html/05_douyinDownload/douyin_url.txt

/home/01_html/05_douyinDownload/douyin_log.txt
```

- **01_douyinDown.py** 脚本将会从douyin_url.txt读取下载链接，然后将脚本中的print信息写入到douyin_log.txt，将mp4视频下载到指定目录。txt文件中的url和日志都是覆盖写入。



### 权限设置

- 浏览器运行php脚本，该php脚本在vps上实现对txt文件的读取和写入，对php脚本的调用，python脚本的调用，然后python脚本执行对txt文件的读取和写入，以及下载视频文件到其他文件夹中  
- 涉及到的所有txt文件需要更改组和读写权限，尤其是python进行读写的txt文件
- 涉及到的所有脚本和文件夹要添加执行权限，包括视频写入的文件夹和python脚本


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
