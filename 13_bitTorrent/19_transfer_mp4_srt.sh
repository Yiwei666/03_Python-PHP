#!/bin/bash

# 提示用户输入文件名，不包含后缀
read -p "请输入新的文件名（不包含后缀）: " new_name

# 获取当前目录下的 mp4 和 srt 文件
mp4_file=$(ls *.mp4)
srt_file=$(ls *.srt)

# 重命名文件
mv "$mp4_file" "${new_name}.mp4"
mv "$srt_file" "${new_name}.srt"

# 转移文件到指定目录
mv "${new_name}.mp4" "/home/01_html/19_bitTorrent/video"
mv "${new_name}.srt" "/home/01_html/19_bitTorrent/video"

# 打印当前目录的绝对路径
echo "当前目录为：$(pwd)"

# 确认是否删除当前目录
read -p "是否删除当前目录? (y/n): " confirm

if [ "$confirm" == "y" ]; then
    cd ..  # 需要先退出目录，才能删除
    rm -r "$(pwd)/$(basename "$OLDPWD")"
    echo "目录已删除。"
else
    echo "操作已取消。"
fi
