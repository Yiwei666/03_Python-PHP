#!/bin/bash

# 远程路径
remote_path="do1-1:do1-1/45_TodayExplained/01_audio"

# 1. 使用rclone读取远程位置 $remote_path 下的所有文件名，并将其存储到 remote_filename.txt 文件中
rclone ls "$remote_path" | awk '{print $NF}' > remote_filename.txt

echo "远程文件名已保存到 remote_filename.txt 文件中"
