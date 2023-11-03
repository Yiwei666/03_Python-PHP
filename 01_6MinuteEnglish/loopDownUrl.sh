#!/bin/bash

# 检查homeUrl.txt文件是否存在
if [ ! -f "homeUrl.txt" ]; then
    echo "homeUrl.txt文件不存在"
    exit 1
fi

# 逐行读取homeUrl.txt文件中的链接
while IFS= read -r url
do
    # 下载链接并命名为subPage.html，强制覆盖已有文件
    wget -O subPage.html -N "$url"
    
    # 检查下载是否成功
    if [ $? -eq 0 ]; then
        # 运行parser_subPage_html.py脚本
        python parser_subPage_html.py

        # 等待1.5秒钟
        sleep 1.5
    else
        echo "链接下载失败: $url"
    fi
done < homeUrl.txt
