#!/bin/bash

# 获取当前目录下的所有子文件夹
subfolders=$(find . -maxdepth 1 -type d ! -path .)

# 遍历每个子文件夹
for folder in $subfolders; do
    # 子文件夹名字作为txt文件名
    txt_file="${folder/\.\//}.txt"
    
    # 遍历子文件夹中的音频文件
    find "$folder" -type f ! -name "*.mp3" -printf "%f\n" > "$txt_file"
    
    echo "生成文件: $txt_file"
done
