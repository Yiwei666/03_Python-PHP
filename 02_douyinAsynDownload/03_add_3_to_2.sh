#!/bin/bash

# 定义文件名变量
failure_file="/home/01_html/05_douyinAsynDload/3_failure.txt"
success_file="/home/01_html/05_douyinAsynDload/2.txt"

# 检查文件是否存在
if [ -e "$failure_file" ] && [ -e "$success_file" ]; then
    # 追加内容到4_success.txt
    cat "$failure_file" >> "$success_file"

    # 清空3_failure.txt
    > "$failure_file"

    echo "内容已成功追加到$success_file并清空了$failure_file"
else
    echo "文件不存在，请检查文件路径或创建文件"
fi
