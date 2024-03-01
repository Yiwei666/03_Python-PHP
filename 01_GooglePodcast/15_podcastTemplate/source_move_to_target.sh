#!/bin/bash

# 指定目录A和目录B
directory_a="/home/01_html/51_SEND7/01_audio"
directory_b="/home/01_html/51_SEND7/02_audio"

# 指定源文件路径
source_file="/home/01_html/51_SEND7/source.txt"

# 检查目录A是否存在
if [ ! -d "$directory_a" ]; then
  echo "目录A不存在"
  exit 1
fi

# 检查目录B是否存在，如果不存在则创建
if [ ! -d "$directory_b" ]; then
  mkdir -p "$directory_b"
fi

# 读取txt文件中的文件名并逐行处理
while IFS= read -r filename; do
  # 检查文件是否存在于目录A中
  if [ -e "$directory_a/$filename" ]; then
    # 检查文件是否已经存在于目录B中
    if [ -e "$directory_b/$filename" ]; then
      echo "警告：目录B中已存在文件 $filename"
    else
      # 移动文件到目录B
      mv "$directory_a/$filename" "$directory_b/"
      echo "文件 $filename 移动成功"
    fi
  else
    echo "警告：目录A中不存在文件 $filename"
  fi
done < "$source_file"
