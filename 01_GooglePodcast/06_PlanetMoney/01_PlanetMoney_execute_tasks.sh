#!/bin/bash

# Step 1: 删除文件夹
rm -r /home/01_html/41_PlanetMoney/01_audio

# Step 2: 创建文件夹
mkdir -p /home/01_html/41_PlanetMoney/01_audio

# Step 3: 运行第一个脚本
# /usr/bin/bash /home/01_html/41_PlanetMoney/download_Random_mp3.sh
/home/00_software/02_anaconda/install/bin/python /home/01_html/41_PlanetMoney/download_Random_mp3.py

# Step 4: 等待10分钟
sleep 100  # 10分钟 = 10 * 60 秒

# Step 5: 运行第二个脚本
/usr/bin/bash /home/01_html/41_PlanetMoney/83_syn_azure2-1_to_AECS_PlanetMoney.sh
