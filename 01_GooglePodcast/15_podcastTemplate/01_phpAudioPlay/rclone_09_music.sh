#!/bin/bash

# 远程路径的主目录
remote_base_path="rc1:cc1-1/09_music/"

# 本地路径
local_path="/home/01_html/09_music/01_audio"

# 随机选取的文件数量
random_files_count=50

# 1. 删除目录 $local_path
rm -rf "$local_path"

# 2. 创建目录 $local_path
mkdir -p "$local_path"

# 3. 使用rclone读取远程主目录下的所有子文件夹和文件名，处理空格
# 获取所有子文件夹中的文件列表
IFS=$'\n' file_list=($(rclone lsf --recursive --files-only "$remote_base_path"))

# 4. 打乱文件顺序，并随机选择 $random_files_count 个文件名
shuffled_list=($(printf "%s\n" "${file_list[@]}" | shuf))

# 5. 从上述数组中随机选取 $random_files_count 个后缀名为 mp3 的文件名，下载到指定目录 $local_path
count=0

for file in "${shuffled_list[@]}"; do
    if [[ "$file" == *.mp3 ]]; then
        if [ $count -lt $random_files_count ]; then
            echo "\"$remote_base_path$file\"" "\"$local_path\""
            # 确保复制时路径中的空格处理正确
            rclone copy "$remote_base_path$file" "$local_path"
            ((count++))
        else
            break
        fi
    fi
done

echo "任务完成"
