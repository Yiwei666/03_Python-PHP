#!/bin/bash

# 获取脚本所在目录
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# 指定目录
directory="/home/01_html/42_TheDaily/01_audio"

# 检查目录是否存在
if [ ! -d "$directory" ]; then
  echo "指定的目录不存在"
  exit 1
fi

# 切换到目录
cd "$directory" || exit 1

# 获取所有文件名并写入source.txt
ls -1 > "$script_dir/source.txt"

echo "文件名已写入到 source.txt 中"
