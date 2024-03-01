#!/bin/bash

input_file="/home/01_html/51_SEND7/nameURL.txt"
output_path="/home/01_html/51_SEND7/01_audio"

# 检查输出路径是否存在
if [ ! -d "$output_path" ]; then
    echo "Output path $output_path does not exist. Please create the path and run the script again."
    exit 1
fi

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

    # 添加5秒的延迟
    sleep 5
done < "$input_file"
