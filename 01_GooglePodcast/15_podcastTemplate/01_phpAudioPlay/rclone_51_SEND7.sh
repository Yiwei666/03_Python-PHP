#!/bin/bash

# 远程路径，注意修改cc1-1
remote_path="AECS-1109:cc1-1/51_SEND7/01_audio"

# 本地路径
local_path="/home/01_html/51_SEND7/01_audio"

# 随机选取的文件数量
random_files_count=10

# 1. 删除目录 $local_path
rm -rf "$local_path"

# 2. 创建目录 $local_path
mkdir -p "$local_path"

# 3. 使用rclone读取远程位置 $remote_path 下的所有文件名到一个数组中
file_list=($(rclone ls "$remote_path" | awk '{print $NF}' | shuf))

# 4. 从上述数组中随机选取 $random_files_count 个后缀名为mp3的文件名，下载到指定目录 $local_path
count=0

for file in "${file_list[@]}"; do
    if [[ $file == *.mp3 ]]; then
        if [ $count -lt $random_files_count ]; then
            rclone copy "$remote_path/$file" "$local_path"
            ((count++))
        else
            break
        fi
    fi
done

echo "任务完成"
