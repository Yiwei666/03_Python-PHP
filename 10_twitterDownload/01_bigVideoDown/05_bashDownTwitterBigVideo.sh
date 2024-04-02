#!/bin/bash

# 目标目录和文件定义
DIR="/home/01_html/05_twitter_bigfile"
FILE="$DIR/01_url.txt"
VIDEO_DIR="/home/01_html/05_twitter_video"

# 检查01_url.txt文件是否存在，如果不存在则创建
if [ ! -f "$FILE" ]; then
    touch "$FILE"
fi

# 检查文件是否为空
if [ ! -s "$FILE" ]; then
    echo "01_url.txt is empty. Exiting..."
    exit 0
fi

# 读取文件中的URL
URL=$(cat "$FILE")

# 尝试访问URL
if ! curl --output /dev/null --silent --head --fail "$URL"; then
    echo "URL is not responding. Exiting and clearing 01_url.txt..."
    > "$FILE"
    exit 1
fi

# 如果URL响应，则下载视频
# 生成目标文件名
TARGET_NAME=$(date "+%Y%m%d-%H%M%S")-$(tr -dc '0-9' < /dev/urandom | fold -w 11 | head -n 1).mp4
TARGET_PATH="$VIDEO_DIR/$TARGET_NAME"

# 使用wget下载视频，处理可能的重定向
wget -O "$TARGET_PATH" "$URL"

# 清空01_url.txt文件内容
> "$FILE"
