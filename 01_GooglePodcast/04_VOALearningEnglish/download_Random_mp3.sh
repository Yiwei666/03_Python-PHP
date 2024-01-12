#!/bin/bash

input_file="nameURL.txt"
output_path="/home/01_html/09_VOALearningEnglish/01_audio"
download_limit=5

# 将所有行读入数组
mapfile -t lines < "$input_file"

# 随机打乱数组
shuf_lines=($(shuf -e "${lines[@]}"))

# 迭代前 download_limit 行（或更少）
for ((i=0; i<download_limit && i<${#shuf_lines[@]}; i++)); do
    line="${shuf_lines[$i]}"
    
    # 提取文件名和URL
    IFS=, read -r filename url <<< "$line"
    
    # 去除文件名和URL中的空格
    filename=$(echo "$filename" | tr -d ' ')
    url=$(echo "$url" | tr -d ' ')

    # 检查文件是否已存在
    if [ -e "$output_path/$filename.mp3" ]; then
        echo "文件 $filename.mp3 已存在于 $output_path。跳过..."
    else
        # 使用curl获取重定向后的mp3链接
        redirected_url=$(curl -s -L -o /dev/null -w '%{url_effective}' "$url")

        # 使用wget下载mp3音频，并以文件名命名，输出到指定路径
        wget -O "$output_path/$filename.mp3" "$redirected_url"

        echo "已从 $redirected_url 下载 $filename.mp3 到 $output_path"
    fi

    # 添加5秒的延迟
    sleep 5
done