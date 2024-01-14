#!/bin/bash

# 定义源目录和目标服务器信息
source_dir="/home/01_html/41_PlanetMoney/"
target_server="root@39.105.186.182"
target_dir="/home/01_html/41_PlanetMoney/"

# 执行rsync命令
rsync -avz "$source_dir" "$target_server:$target_dir"
