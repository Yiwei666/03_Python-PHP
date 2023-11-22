# 项目功能

- 使用python和bash脚本下载 scientific American podcasts 音频，官网是 https://www.scientificamerican.com/podcasts/?page=70

- `zeroSizeDown`是一个独立的目录，主要针对未下载成功的大小为 0 KB的mp3文件的重新下载

# 项目结构

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


total_control.sh 脚本内容：

1. 首先执行 rm -rf  sss.html  audio_url.txt
2. 再执行 curl -o sss.html https://www.scientificamerican.com/podcasts/?page=2
3. 然后执行python脚本 parse_html.py，相应脚本运行结束后
4. 接着 运行bash脚本 download_audio.sh
5. 再接着执行 cat audio_url.txt >> total_audio_url.txt
6. 再执行 ls -l   /home/01_html/04_sss60/01_audio   | grep "^-" | wc -l

```


# 环境配置

- **download_audio.sh**

上述 audio_url.txt 文件中的每一行中使用英文逗号分隔成两个元素，第一个元素是文件名，第二个元素是网址。该bash脚本读取每一行中的的网址，然后访问该网址会重定向到一个mp3音频链接，下载该mp3音频，并且使用第一个元素文件名进行mp3音频的命名。

```bash
#!/bin/bash

input_file="audio_url.txt"
output_path="/home/01_html/04_sss60/01_audio"

# 读取每一行
while IFS=, read -r filename url; do
    # 移除文件名和网址中的空格
    filename=$(echo "$filename" | tr -d ' ')
    url=$(echo "$url" | tr -d ' ')

    # 使用curl获取重定向后的mp3链接
    redirected_url=$(curl -s -L -o /dev/null -w '%{url_effective}' "$url")

    # 使用wget下载mp3音频，并以文件名命名，输出到指定路径
    wget -O "$output_path/$filename.mp3" "$redirected_url"

    echo "Downloaded $filename.mp3 from $redirected_url to $output_path"
done < "$input_file"

```

- **download_audio.sh**

脚本会在下载前检查指定路径下是否已存在相同文件名的 mp3 文件，如果存在，则会输出提示信息并跳过下载。

```bash
#!/bin/bash

input_file="audio_url.txt"
output_path="/home/01_html/04_sss60/01_audio"

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



- **total_control.sh**

以下是针对 page = 2 （对应网址：`https://www.scientificamerican.com/podcasts/?page=2`），页面中所有视频下载的bash脚本

```bash
#!/bin/bash

# 删除文件
rm -rf sss.html audio_url.txt

# 下载网页
curl -o sss.html https://www.scientificamerican.com/podcasts/?page=2

# 运行 Python 脚本
python parse_html.py

# 运行下载音频的 Bash 脚本
bash download_audio.sh

# 将 audio_url.txt 内容追加到 total_audio_url.txt
cat audio_url.txt >> total_audio_url.txt

# 统计目录下的文件数量
file_count=$(ls -l /home/01_html/04_sss60/01_audio | grep "^-" | wc -l)
echo "文件数量: $file_count"

```

- **page_range_control.sh**

下载 41~70 页码范围内的音频。注意：`total_control.sh`只能下载单页，为了提高下载效率，`page_range_control.sh`支持多页下载

```bash
#!/bin/bash

# 要处理的页面范围 已处理 31-40
start_page=41
end_page=70

for ((page=start_page; page<=end_page; page++)); do
    # 删除文件
    rm -rf sss.html audio_url.txt

    # 下载网页
    curl -o sss.html "https://www.scientificamerican.com/podcasts/?page=$page"

    # 运行 Python 脚本
    python parse_html.py

    # 运行下载音频的 Bash 脚本
    bash download_audio.sh

    # 将 audio_url.txt 内容追加到 total_audio_url.txt
    cat audio_url.txt >> total_audio_url.txt

    # 统计目录下的文件数量
    file_count=$(ls -l /home/01_html/04_sss60/01_audio | grep "^-" | wc -l)
    echo "处理页面 $page，文件数量: $file_count"
done
```

# Englishpod 下载

Internet Archive 网址：https://archive.org/details/englishpod_all/englishpod_0003pb.mp3

音频下载链接：https://archive.org/compress/englishpod_all/formats=VBR%20MP3&file=/englishpod_all.zip

共计5.2G，1197个MP3文件


# ESLpod 下载

Internet Archive 网址：https://archive.org/details/ipodcast

```
a-day-in-the-life-of-jeff/	09-Sep-2022 23:36	-
a-day-in-the-life-of-lucy/	10-Sep-2022 04:22	-
cultural-english/	10-Sep-2022 17:06	-
daily-english/	10-Sep-2022 17:13	-
english-for-business-meetings/	10-Sep-2022 02:08	-
history/	14-Dec-2019 11:38	-
interview-questions-answered/	10-Sep-2022 02:36	-
introduction-to-the-united-states/	10-Sep-2022 04:55	-
using-english-at-work/	09-Sep-2022 21:37	-
ESLPod.jpg	08-Dec-2019 14:55	152.0K
ESLPod_thumb.jpg	08-Dec-2019 15:47	3.6K
__ia_thumb.jpg	12-Feb-2021 00:38	8.8K
eslpod.xml	14-Dec-2019 11:38	1.1M
ipodcast_archive.torrent	06-Jun-2022 09:27	2.5M
ipodcast_files.xml	10-Sep-2022 17:13	3.5M
ipodcast_meta.sqlite	14-Dec-2019 11:38	4.3M
ipodcast_meta.xml	10-Sep-2022 14:36	1.1K
```











