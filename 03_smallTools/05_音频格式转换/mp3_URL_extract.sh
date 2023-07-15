#!/bin/bash

# 设置要搜索的文件类型
file_extension=".mp3"

# 设置要添加的前缀
prefix="domain.com/music/周杰伦/01_补充/"

# 创建一个空的txt文件
output_file="mp3_files.txt"
> "$output_file"

# 遍历当前目录中的所有文件
for file in *"$file_extension"; do
    # 检查文件是否是所需的文件类型
    if [[ -f "$file" ]]; then
        # 构造下载链接
        download_link="${prefix}${file}"
        # 将文件名和下载链接写入txt文件
        echo "$download_link" >> "$output_file"
    fi
done

echo "文件名已写入到 $output_file"
