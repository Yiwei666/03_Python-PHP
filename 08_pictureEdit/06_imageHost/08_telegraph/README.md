# 1. 项目功能

使用爬虫下载 telegra.ph 博客上的图片

# 2. 文件结构


```
├── 03_picTemp
│   └── 海外风景
└── 05_webPicDownload
    ├── 01_total_url.txt
    ├── 02_temp_url.txt
    ├── 03_failure_downURL.txt
    ├── 04_reDownload_failURL.txt
    ├── 08_web_download.py
    ├── 08_web_extract_url.py
    ├── 08_web_failure_redownload.py
    ├── 08_web_procedure.sh
    ├── 08_web_url.html
    └── nohup.out
```


# 3. 环境配置

### 1. `08_web_procedure.sh`

功能：  
1. 提醒用户输入 telehraph 博客中的相应网址，下载网页为 `08_web_url.html`，其中含有所有图片的 url。
2. 运行 `08_web_extract_url.py` 脚本，提取 `08_web_url.html` 中的图片url。


### 2. `08_web_extract_url.py`

功能：提取 `08_web_url.html` 中的url，另存到如下两个文件

- 在`total_url_path`指定的文件中，附加当前日期和时间，然后追加所有图片URL。
- 在`temp_url_path`指定的文件中，覆盖写入所有图片URL。

```py
total_url_path = '01_total_url.txt'
temp_url_path = '02_temp_url.txt'
```

### 3. `08_web_download.py`

1. 确保图片保存目录存在：如果目录不存在，则创建该目录。
2. 读取临时URL文件 `02_temp_url.txt` 中的图片URL列表。
3. 下载图片并保存：对每个图片URL，生成基于时间戳的文件名，下载图片数据并保存为PNG文件。
4. 记录失败的下载URL：如果下载失败，将URL记录到失败日志文件 `03_failure_downURL.txt` 中。
5. 等待2秒：每次下载完成后，程序等待2秒再进行下一次下载。
6. 输出下载结果：打印总URL数、成功下载数和失败下载数。

```py
temp_url_path = '/home/01_html/08_x/image/05_webPicDownload/02_temp_url.txt'
failure_url_path = '/home/01_html/08_x/image/05_webPicDownload/03_failure_downURL.txt'
pic_temp_dir = '/home/01_html/08_x/image/03_picTemp/海外风景'
```



### 4. 










