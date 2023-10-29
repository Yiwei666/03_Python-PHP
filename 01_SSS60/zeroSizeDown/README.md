# 项目功能



# 文件结构

```
.
├── sss.html                    # 临时文件，内含多个音频文件，大概有300页
├── parse_html.py               # 解析sss.html文件，将音频文件名和链接写入 audio_url.txt 文件
├── audio_url.txt               # 临时文件，存储每一页中的音频链接
├── total_audio_url.txt         # 储存所有的音频链接和音频文件名
├── download_audio.sh           # 下载audio_url.txt中重定向后的音频文件
├── total_control.sh            # 控制单页的音频链接下载
├── page_range_control.sh       # 控制指定页码范围内的音频链接下载
├── /01_audio/                    # 存储音频的文件夹
└── sync_audio.sh               # 同步音频到其他云服务器


.
├── 02_zeroSizeList_Audio
├── download_audio_zeroSizeList.sh
├── zeroNameUrl_extract.py
├── pre_downloadUrl.txt
├── cut_pastAudio.py
├── cut_paste.txt
├── total_audio_url.txt
├── zero-size.txt
└── zeroSize_audioName_find.py

```

- **zeroSize_audioName_find.py**

当您需要将指定目录下大小为0 KB的MP3文件名写入到一个名为`zero-size.txt`的文件中时，您可以使用Python来完成这个任务。以下是一个Python脚本示例，它可以做到这一点：

```python
import os

# 指定目录
directory = '/home/01_html/04_sss60/01_audio'  # 将此路径替换为实际的目录路径

# 列出目录中的所有文件
files = os.listdir(directory)

# 打开 zero-size.txt 文件以写入文件名
with open('zero-size.txt', 'w') as output_file:
    # 遍历目录中的文件
    for filename in files:
        file_path = os.path.join(directory, filename)
        # 检查文件是否为0字节
        if os.path.isfile(file_path) and os.path.getsize(file_path) == 0 and filename.endswith('.mp3'):
            output_file.write(filename + '\n')

print("文件名已写入 zero-size.txt 文件。")
```




