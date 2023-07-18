#!/bin/bash

# 定义当前目录和目标URL
current_dir="/home/01_html/"
target_url="https://domain.com/"

export current_dir
export target_url

# 遍历目录及其子目录下的所有mp3文件，并将路径写入txt文件
find "$current_dir" -type f -name "*.mp3" -exec sh -c 'echo "${0/$current_dir/$target_url}"' {} \; > mp3_paths.txt
