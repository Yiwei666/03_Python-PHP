#!/bin/bash

# 错误日志文件名
error_log="error_log.txt"

# 清空错误日志文件
> "$error_log"

# 读取 mp3_files.txt 文件并逐行处理
while IFS= read -r url; do
  # 提取文件名
  filename=$(basename "$url")

  # 下载音频文件
  if curl -O "$url"; then
    # 下载成功，移动文件到当前目录
    mv "$filename" "./$filename"
  else
    # 下载失败，将链接追加到错误日志文件
    echo "$url" >> "$error_log"
  fi
done < mp3_files.txt
