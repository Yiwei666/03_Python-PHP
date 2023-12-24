#!/bin/bash

# 定义文件路径变量
inputFile="/home/01_html/05_imageTransferName.txt"
sourceDirectory="/home/01_html/02_LAS1109/35_imageHost/"
destinationDirectory="/home/01_html/02_LAS1109/35_imageTransfer/"

# 读取文本文件中的每一行，提取文件名并存入数组
imageFileArray=($(awk -F ',' '{print $1}' "$inputFile"))

# 遍历数组，将对应的图片文件移动到目标目录
for imageFile in "${imageFileArray[@]}"
do
    sourcePath="$sourceDirectory$imageFile"
    destinationPath="$destinationDirectory$imageFile"

    # 检查文件是否存在再进行移动
    if [ -e "$sourcePath" ]; then
        mv "$sourcePath" "$destinationPath"
        echo "Moved $imageFile to $destinationDirectory"
    else
        echo "Error: File $imageFile not found in $sourceDirectory"
        # 或者你可以记录到日志文件
        # echo "Error: File $imageFile not found in $sourceDirectory" >> /path/to/error.log
    fi
done
