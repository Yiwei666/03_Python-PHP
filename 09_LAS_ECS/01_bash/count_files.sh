#!/bin/bash

# 提示用户输入绝对路径
read -p "请输入绝对路径: " path

# 检查路径是否存在
if [ ! -d "$path" ]; then
  echo "错误: 路径不存在或不是一个目录。"
  exit 1
fi

# 计算文件总数
file_count=$(find "$path" -type f | wc -l)

# 计算文件大小，并将单位转换为MB和GB
size_mb=$(du -cBM "$path" | grep "total" | awk '{printf "%.1f", $1}')
size_gb=$(du -cBG "$path" | grep "total" | awk '{printf "%.1f", $1}')

# 打印结果
echo "文件总数: $file_count"
echo "文件大小: ${size_mb} MB"
echo "文件大小: $(echo "scale=3; $size_mb / 1024" | bc) GB"
echo "文件大小: ${size_gb} GB"
