#!/bin/bash

# 获取当前目录
directory=$(dirname "$0")

# 进入目录
cd "$directory"

# 遍历目录下的所有文件
for file in *; do
  if [[ -f $file ]]; then
    # 替换空格为下划线
    new_name=$(echo "$file" | sed 's/ /_/g')

    # 替换英文括号为下划线
    new_name=$(echo "$new_name" | sed 's/(/_/g')
    new_name=$(echo "$new_name" | sed 's/)/_/g')

    # 替换中文括号为下划线
    new_name=$(echo "$new_name" | sed 's/（/_/g')
    new_name=$(echo "$new_name" | sed 's/）/_/g')

    # 重命名文件
    mv "$file" "$new_name"
    echo "重命名文件: $file 为 $new_name"
  fi
done
