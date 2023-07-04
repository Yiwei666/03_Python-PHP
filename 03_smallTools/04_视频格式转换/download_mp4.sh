#!/bin/bash

# 保存链接的文本文件路径
link_file="./output.txt"

# 下载视频的目标目录
download_directory="./"

# 读取链接文件中的每一行
while IFS= read -r download_link; do
    # 提取文件名
    filename=$(basename "$download_link")
    
    # 使用curl命令下载视频文件
    curl -O "$download_link"
    
    # 移动文件到目标目录
    mv "$filename" "$download_directory"
    
    echo "已下载视频文件: $filename"
done < "$link_file"

echo "视频下载完成"
