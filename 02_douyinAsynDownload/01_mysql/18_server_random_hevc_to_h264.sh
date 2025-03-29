#!/bin/bash

# 目录配置
dir1="/home/01_html/18_temp_video/1_hevc"   # 存放 HEVC 编码的 MP4 文件
dir2="/home/01_html/18_temp_video/2_h264"  # 存放 H264 编码的 MP4 文件

# 查找 dir1 中存在但 dir2 中不存在的文件列表
mapfile -t files < <(
    comm -23 <(ls "$dir1" | sort) <(ls "$dir2" | sort)
)

# 如果没有新文件可供转换，直接退出
if [ ${#files[@]} -eq 0 ]; then
    echo "$(date +'%F %T') - No new file to convert. Exit."
    exit 0
fi

# 随机选择一个需要转换的文件
random_index=$(( RANDOM % ${#files[@]} ))
file="${files[$random_index]}"

input_file="${dir1}/${file}"
output_file="${dir2}/${file}"

echo "$(date +'%F %T') - Start converting: $input_file"

# 执行转换：-y 表示覆盖输出，-threads 1 表示使用单线程
# ffmpeg -y -i "$input_file" -c:v libx264 -threads 1 -c:a copy "$output_file"
ffmpeg -y -i "$input_file" -c:v libx264 -preset fast -crf 28 -c:a copy "$output_file"

if [ $? -eq 0 ]; then
    echo "$(date +'%F %T') - Conversion succeeded: $output_file"
else
    echo "$(date +'%F %T') - Conversion failed: $input_file"
fi

exit 0
