#!/bin/bash

# 循环处理当前目录下的所有文件
for file in *; do
    # 检查文件是否是普通文件（非目录）
    if [[ -f "$file" ]]; then
        # 将文件名中的空格替换为下划线
        new_name="${file// /_}"
        # 仅在文件名发生变化时才执行重命名
        if [[ "$file" != "$new_name" ]]; then
            mv "$file" "$new_name"
            echo "已将文件 '$file' 重命名为 '$new_name'"
        fi
    fi
done
