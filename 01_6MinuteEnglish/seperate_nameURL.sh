#!/bin/bash

# 检查audioUrl.txt文件是否存在
if [ ! -f "audioUrl.txt" ]; then
    echo "audioUrl.txt文件不存在"
    exit 1
fi

# 清空或创建nameURL_audio.txt文件
> nameURL_audio.txt

# 逐行读取audioUrl.txt文件中的链接
while IFS= read -r url
do
    # 提取音频文件名
    file_name=$(basename "$url" | sed 's/\.mp3$//')

    # 将文件名和链接写入nameURL_audio.txt文件，用逗号分隔
    echo "$file_name,$url" >> nameURL_audio.txt
done < audioUrl.txt
