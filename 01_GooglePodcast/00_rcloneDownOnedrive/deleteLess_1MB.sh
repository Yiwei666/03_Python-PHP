#!/bin/bash

# 设置远程目录路径
remote_dir="rc2:cc1-1/113_ComedyBangBang/01_audio"

# 检查远程目录是否存在
if ! rclone lsd "$remote_dir" &>/dev/null; then
    echo "远程目录 $remote_dir 不存在或无权访问"
    exit 1
fi

# 初始化删除文件计数器
deleted_count=0

# 创建或清空日志文件
: > delete_log.txt

# 使用 rclone 列出远程目录下的所有 mp3 文件及其大小
while IFS=" " read -r size file; do
    full_path="$remote_dir/$file"
    if [ "$size" == "0" ]; then
        rclone deletefile "$full_path"
        echo "Deleted $full_path"
        echo "$full_path" >> delete_log.txt
        ((deleted_count++))
    fi
done < <(rclone ls "$remote_dir" --max-depth 1 --include "*.mp3")

echo "总共删除了 $deleted_count 个文件"