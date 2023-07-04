#!/bin/bash

# 保存链接的文本文件路径
link_file="./output.txt"

# 下载视频的目标目录
download_directory="./"

# 保存下载失败的链接的文件路径
failed_links_file="./failed_links.txt"

# 清空保存失败链接的文件
> "$failed_links_file"

# 读取链接文件中的每一行
while IFS= read -r download_link; do
    # 提取文件名
    filename=$(basename "$download_link")
    
    # 使用curl命令下载视频文件
    if curl -O "$download_link"; then
        # 下载成功，移动文件到目标目录
        mv "$filename" "$download_directory"
        echo "已下载视频文件: $filename"
    else
        # 下载失败，将链接追加到失败链接文件中
        echo "$download_link" >> "$failed_links_file"
        echo "下载失败的链接: $download_link"
    fi
done < "$link_file"

echo "视频下载完成"

if [ -s "$failed_links_file" ]; then
    echo "以下链接下载失败，请检查链接的有效性:"
    cat "$failed_links_file"
fi
