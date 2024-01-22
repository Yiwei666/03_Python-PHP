#!/bin/bash

# 读取 nameURL.txt 中的文件名和链接
while IFS=, read -r filename url; do
    # 读取 remote_filename.txt 中已下载的文件名（去除后缀）
    downloaded_files=($(sed 's/\.mp3$//' remote_filename.txt))

    # 检查文件名是否在已下载文件列表中，如果不在则写入 undownload_mp3.txt
    if [[ ! " ${downloaded_files[@]} " =~ " ${filename} " ]]; then
        echo "${filename},${url}" >> undownload_mp3.txt
    fi
done < nameURL.txt

echo "未下载的文件名和链接已写入 undownload_mp3.txt 文件。"
