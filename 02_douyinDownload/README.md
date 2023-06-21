
注意在相应路径下创建这两个文件

```
/home/01_html/05_douyinDownload/douyin_url.txt

/home/01_html/05_douyinDownload/douyin_log.txt
```

- 浏览器运行php脚本，该php脚本在vps上实现对txt文件的读取和写入，对php脚本的调用，python脚本的调用  
- 然后python脚本执行对txt文件的读取和写入，以及下载视频文件到其他文件夹中  
- 涉及到的所有txt文件需要更改组和权限
- 涉及到的所有脚本和文件夹要添加执行权限

