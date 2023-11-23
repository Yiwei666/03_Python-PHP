#!/bin/bash

# 设置起始编号和结尾编号
start_number=201
end_number=400

# 获取 end_number 的位数
end_number_digits=${#end_number}

# 设置音频链接的固定部分和文件后缀
base_url="https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/"
file_prefix="ce"  # 新增的变量

# https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/ce603.mp3

file_extension=".mp3"

# 设置保存成功和失败链接的文件（在脚本相同的路径下）
success_file="01_success.txt"
failure_file="02_failure.txt"

# 设置输出路径
output_path="/home/01_html/10_ESLpod/01_audio"

# 创建输出路径
mkdir -p "$output_path"

# 循环下载音频文件
for ((number=start_number; number<=end_number; number++)); do
    # 格式化编号，确保数字位数相同，不足的位数用0补齐
    formatted_number=$(printf "%0${end_number_digits}d" $number)
    
    # 构建完整的音频链接
    audio_url="$base_url$file_prefix$formatted_number$file_extension"
    
    # 构建保存音频文件的文件名
    file_name="$file_prefix$formatted_number$file_extension"
    
    # 使用wget下载音频文件到指定路径，并等待5秒后再进行下一次下载
    wget -O "$output_path/$file_name" $audio_url
    sleep 5
    
    # 检查下载是否成功
    if [ $? -eq 0 ]; then
        # 下载成功，保存链接到成功文件
        echo $audio_url >> "$success_file"
        echo "Downloaded $file_name - Success"
    else
        # 下载失败，保存链接到失败文件
        echo $audio_url >> "$failure_file"
        echo "Failed to download $file_name"
    fi
done

echo "Download complete!"
