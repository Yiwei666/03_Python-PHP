#!/bin/bash

# 指定目录路径，判断该目录是否小于指定大小
directory="/home/01_html/45_TodayExplained/01_audio"

# 判断目录大小是否小于12GB
size=$(du -s "$directory" | awk '{print $1}')
limit=12000000  # 12GB的大小限制

if [ $size -lt $limit ]; then
  echo "目录大小小于12GB，退出脚本"
  exit 0
fi

# 目录大于等于15GB的情况
echo "目录大小大于等于12GB，执行操作"

# 删除文件 /home/01_html/45_TodayExplained/source.txt
rm -f "/home/01_html/45_TodayExplained/source.txt"

# 运行脚本 /usr/bin/bash /home/01_html/45_TodayExplained/source.sh
/usr/bin/bash "/home/01_html/45_TodayExplained/source.sh"

# 等待30秒
sleep 30

# 删除目录 /home/01_html/45_TodayExplained/02_audio
rm -rf "/home/01_html/45_TodayExplained/02_audio"

# 运行脚本 /usr/bin/bash /home/01_html/45_TodayExplained/source_move_to_target.sh
/usr/bin/bash "/home/01_html/45_TodayExplained/source_move_to_target.sh"

# 执行 rclone 命令，onedrive上该目录需要提前创建
/usr/bin/rclone copy "/home/01_html/45_TodayExplained/02_audio" "do1-1:do1-1/45_TodayExplained/01_audio"

# 等待30秒
sleep 30

# 删除目录，释放硬盘空间 /home/01_html/45_TodayExplained/02_audio
rm -rf "/home/01_html/45_TodayExplained/02_audio"
