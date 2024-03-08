#!/bin/bash

# 指定目录路径，判断该目录是否小于指定大小
directory="/home/01_html/51_SEND7/01_audio"

# 判断目录大小是否小于12GB
size=$(du -s "$directory" | awk '{print $1}')
limit=8000000  # 8GB的大小限制，阈值通常需要小于可用内存的一半

if [ $size -lt $limit ]; then
  echo "目录大小小于12GB，退出脚本"
  exit 0
fi

# 目录大于等于15GB的情况
echo "目录大小大于等于12GB，执行操作"

# 删除文件 /home/01_html/51_SEND7/source.txt
rm -f "/home/01_html/51_SEND7/source.txt"  && sleep 3

# 运行脚本 /usr/bin/bash /home/01_html/51_SEND7/source.sh
/usr/bin/bash "/home/01_html/51_SEND7/source.sh"

# 等待30秒
sleep 30

# 删除目录 /home/01_html/51_SEND7/02_audio
rm -rf "/home/01_html/51_SEND7/02_audio"  && sleep 3

# 运行脚本 /usr/bin/bash /home/01_html/51_SEND7/source_move_to_target.sh
/usr/bin/bash "/home/01_html/51_SEND7/source_move_to_target.sh"  && sleep 3

# 执行 rclone 命令，onedrive上该目录需要提前创建，等待时间需要保证rclone上传完毕，同时新下载的文件大小小于阈值
# nohup rclone copy /home/01_html/51_SEND7 cc1-1:cc1-1/51_SEND7  &
# nohup rclone copy /home/01_html/51_SEND7 cc1-1:cc1-1/51_SEND7 --transfers=16 &
# rclone size cc1-1:cc1-1/51_SEND7/01_audio
/usr/bin/rclone copy "/home/01_html/51_SEND7/02_audio" "cc1-1:cc1-1/51_SEND7/01_audio"  && sleep 20

# 删除目录，释放硬盘空间 /home/01_html/51_SEND7/02_audio
rm -rf "/home/01_html/51_SEND7/02_audio"

