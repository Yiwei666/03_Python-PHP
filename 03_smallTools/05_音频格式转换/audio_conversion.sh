#!/bin/bash

# 遍历当前目录下的所有 .m4a 和 .flac 文件
for file in ./*.m4a ./*.flac; do
    # 检查文件是否存在
    if [ -e "$file" ]; then
        # 提取文件名和扩展名
        filename=$(basename -- "$file")
        extension="${filename##*.}"
        filename="${filename%.*}"

        # 设置输出文件名
        output_file="${filename}.mp3"

        # 使用 ffmpeg 进行转换
        ffmpeg -i "$file" "$output_file"

        # 检查是否转换成功
        if [ $? -eq 0 ]; then
            echo "已转换 $file 到 $output_file"
        else
            echo "转换 $file 失败"
        fi
    fi
done
