#!/bin/bash

# 视频目录路径
video_directory="./"

# 保存链接的文本文件路径
output_file="./output.txt"

# 清空输出文件
> "$output_file"

# 遍历视频目录下的所有mp4文件
for file in "$video_directory"/*.mp4; do
    # 提取文件名
    filename=$(basename "$file")
    
    # 构建下载链接
    # download_link="https://mcha.xyz/01_TOEFL/01_flv_test/$filename"
    download_link="https://icha.one/01_TOEFL/02_mp4/$filename"
    
    # 将链接追加到输出文件
    echo "$download_link" >> "$output_file"
done

echo "链接构建完成并保存到 $output_file"
