#!/bin/bash

# 定义源目录和目标服务器信息
source_dir="/home/01_html/04_sss60/"
target_server="root@101.200.215.127"
target_dir="/home/01_html/32_SSS60/"

# 执行rsync命令
rsync -avz "$source_dir" "$target_server:$target_dir"
