#!/bin/bash

error_log="error_log.txt"

# 清空或创建 error_log.txt 文件
> "$error_log"

# 读取 audio_files.txt 文件中的链接，并逐行处理
while IFS= read -r audio_url
do
    # 从链接中提取文件名
    filename=$(basename "$audio_url")

    # 下载音频文件
    if curl -O "$audio_url"; then
        echo "Downloaded: $filename"
    else
        echo "Error downloading: $filename"
        echo "$audio_url" >> "$error_log"
    fi

done < audio_files.txt
