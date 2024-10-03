#!/bin/bash

# 远程路径
remote_path="rc1:cc1-1/51_SEND7/01_audio"

# 本地路径
local_path="/home/01_html/51_SEND7/01_audio"

# 随机选取的文件数量
random_files_count=5

# 1. 删除目录 $local_path
rm -rf "$local_path"

# 2. 创建目录 $local_path
mkdir -p "$local_path"

# 3. 使用rclone读取远程位置 $remote_path 下的所有文件名到一个数组中，正确处理包含空格的文件名
# 将 rclone 的输出用 IFS 处理为换行符，以确保空格不丢失
IFS=$'\n' file_list=($(rclone lsf --files-only "$remote_path"))

# 4. 打乱文件顺序，并随机选择 $random_files_count 个文件名
# shuffled_list=($(shuf -e "${file_list[@]}"))
shuffled_list=($(printf "%s\n" "${file_list[@]}" | shuf))

# 5. 从上述数组中随机选取 $random_files_count 个后缀名为 mp3 的文件名，下载到指定目录 $local_path
count=0

for file in "${shuffled_list[@]}"; do
    if [[ "$file" == *.mp3 ]]; then
        if [ $count -lt $random_files_count ]; then
            echo "\"$remote_path/$file\"" "\"$local_path\""
            # 确保复制时路径中的空格处理正确
            rclone copy "$remote_path/$file" "$local_path"
            ((count++))
        else
            break
        fi
    fi
done

echo "任务完成"
