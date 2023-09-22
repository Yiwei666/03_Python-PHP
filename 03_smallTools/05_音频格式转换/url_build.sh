#!/bin/bash

# 设置要添加的前缀
prefix="https://domain.com/07_NewConcept3/"

# 打印Markdown格式的链接，每个链接之间有一个空行
for file in *.mp3; do
    if [ -f "$file" ]; then
        # 输出Markdown格式的链接
        printf "[$file]($prefix$file)\n"

        # 打印一个空行
        printf "\n"
    fi
done
