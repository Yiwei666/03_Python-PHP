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
