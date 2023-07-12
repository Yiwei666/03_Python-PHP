#!/bin/bash

# 构造下载链接前缀
prefix="domain.com/music/周杰伦/01_补充/"

# 获取当前目录下所有非MP3格式音频文件，并将文件名写入txt文件
find . -type f ! -name "*.mp3" | while read -r file; do
    # 获取文件名
    filename=$(basename "$file")
    
    # 构造下载链接
    download_link="$prefix$filename"
    
    # 写入txt文件
    echo "$download_link" >> audio_files.txt
done

echo "已生成 audio_files.txt 文件"
