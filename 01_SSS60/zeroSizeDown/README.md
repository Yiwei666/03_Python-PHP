# 项目功能



# 文件结构

- **一级文件目录**

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
```

- **二级文件目录**

```
.
├── /01_audio/ 
├── /02_zeroSizeList_Audio/
├── total_audio_url.txt
├── zeroSize_audioName_find.py        # 获取目录中为空的音频文件名
├── zero-size.txt
├── zeroNameUrl_extract.py            # 基于待下载文件名获取待下载音频链接
├── pre_downloadUrl.txt
├── download_audio_zeroSizeList.sh    # 下载pre_downloadUrl.txt中的音频到/02_zeroSizeList_Audio/目录中
├── cut_pastAudio.py                  # 将/02_zeroSizeList_Audio/目录下非空的mp3文件剪切到/01_audio/目录下同名的非空音频文件中
└── cut_paste.txt
```

- **计算流程**

1. 使用`zeroSize_audioName_find.py`脚本将`/01_audio/ `目录下大小为 0 KB的mp3文件名写入到 `zero-size.txt` 文本中
2. 使用`zeroNameUrl_extract.py`脚本，比对 `zero-size.txt` 文本和 `total_audio_url.txt` 文本，将大小为 0 KB 的mp3文件名以及对应下载链接写入到`pre_downloadUrl.txt`文本中
3. 使用`download_audio_zeroSizeList.sh`脚本基于`pre_downloadUrl.txt`文本中的音频链接下载音频文件
4. 使用`cut_pastAudio.py`脚本将`/02_zeroSizeList_Audio/`目录下非空的mp3文件剪切到`/01_audio/`目录下同名的非空音频文件中，将成功剪切的文件名写入到`cut_paste.txt`文本中



# 环境配置

- **zeroSize_audioName_find.py**

当您需要将指定目录下大小为 0 KB的MP3文件名写入到一个名为`zero-size.txt`的文件中时，您可以使用Python来完成这个任务。以下是一个Python脚本示例，它可以做到这一点：

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

- **zeroNameUrl_extract.py**

已知 A文件有多行，每一行中依次有文件名和链接，通过英文“,”分隔。现在有B文件，每一行是一个文件名，现在需要将A文件中文件名存在于B文件中的行，包括文件名和链接重新写入到C文件中，仍然使用英文“,”分隔二者，使用python完成，对于文件名采用变量赋值

```python
# 打开A文件以读取内容，包含所有文件名和链接
with open('total_audio_url.txt', 'r') as a_file:
    a_lines = a_file.readlines()

# 打开B文件以读取内容，仅空文件名
with open('zero-size.txt', 'r') as b_file:
    b_lines = b_file.read().splitlines()

# 打开C文件以写入内容
with open('pre_downloadUrl.txt', 'w') as c_file:
    for line in a_lines:
        parts = line.strip().split(',')
        if len(parts) == 2:
            filename, link = parts
            if filename+".mp3" in b_lines:
                c_file.write(f"{filename},{link}\n")

print("匹配的行已写入到 pre_downloadUrl.txt 文件中。")
```

- **download_audio_zeroSizeList.sh**

下载`pre_downloadUrl.txt`中的链接到`/home/01_html/04_sss60/02_zeroSizeList_Audio`目录中

```bash
#!/bin/bash

input_file="pre_downloadUrl.txt"
output_path="/home/01_html/04_sss60/02_zeroSizeList_Audio"

# 读取每一行
while IFS=, read -r filename url; do
    # 移除文件名和网址中的空格
    filename=$(echo "$filename" | tr -d ' ')
    url=$(echo "$url" | tr -d ' ')

    # 检查文件是否已存在
    if [ -e "$output_path/$filename.mp3" ]; then
        echo "File $filename.mp3 already exists in $output_path. Skipping..."
    else
        # 使用curl获取重定向后的mp3链接
        redirected_url=$(curl -s -L -o /dev/null -w '%{url_effective}' "$url")

        # 使用wget下载mp3音频，并以文件名命名，输出到指定路径
        wget -O "$output_path/$filename.mp3" "$redirected_url"

        echo "Downloaded $filename.mp3 from $redirected_url to $output_path"
    fi
done < "$input_file"
```


- **cut_pastAudio.py**

查找X目录下的0 KB的MP3文件，然后在Y目录中查找同名的非零MP3文件，将其剪切到X目录，并将剪切成功的文件名写入`cut_paste.txt`文件中。

```python
import os
import shutil

# X目录和Y目录的路径
x_directory = '/home/01_html/04_sss60/01_audio'
y_directory = '/home/01_html/04_sss60/02_zeroSizeList_Audio'

# 列出X目录中大小为0的MP3文件
zero_size_mp3_files = [f for f in os.listdir(x_directory) if f.endswith('.mp3') and os.path.getsize(os.path.join(x_directory, f)) == 0]

# 创建cut_paste.txt文件以写入剪切成功的文件名
with open('cut_paste.txt', 'w') as cut_paste_file:
    # 遍历X目录中的0 KB MP3文件
    for zero_size_mp3_file in zero_size_mp3_files:
        # 构造同名文件在Y目录中的路径
        y_mp3_file = os.path.join(y_directory, zero_size_mp3_file)

        # 如果在Y目录中找到同名非零MP3文件，则剪切到X目录
        if os.path.exists(y_mp3_file) and os.path.getsize(y_mp3_file) > 0:
            shutil.move(y_mp3_file, os.path.join(x_directory, zero_size_mp3_file))
            cut_paste_file.write(zero_size_mp3_file + '\n')

print("剪切成功的MP3文件名已写入 cut_paste.txt 文件。")
```

