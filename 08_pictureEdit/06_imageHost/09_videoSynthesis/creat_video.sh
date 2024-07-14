#!/bin/bash

# 设置图片和音频目录
image_dir="/home/01_html/06_videoSynthesis/pic"
music_dir="/home/01_html/06_videoSynthesis/music"

# 获取图片和音频文件列表
image_files=($image_dir/*.png)
music_files=($music_dir/*.mp3)

# 获取图片和音频文件数量
m=${#image_files[@]}
mp3_files=(${music_files[@]##*/})

# 提示用户随机选择n张图片
read -p "请输入要随机选择的图片数量 (n <= $m): " n

if (( n > m )); then
  echo "错误: 选择的图片数量超过了总图片数。"
  exit 1
fi

# 随机选择n张图片
selected_images=($(shuf -n $n -e "${image_files[@]}"))

# 打印音频文件列表并提示用户选择一个
echo "可选的音频文件如下:"
for mp3 in "${mp3_files[@]}"; do
  echo "$mp3"
done

read -p "请输入选择的音频文件名: " selected_mp3

# 检查音频文件是否存在
if [[ ! -f "$music_dir/$selected_mp3" ]]; then
  echo "错误: 音频文件不存在。"
  exit 1
fi

# 获取音频时长
audio_duration=$(ffmpeg -i "$music_dir/$selected_mp3" 2>&1 | grep "Duration" | awk '{print $2}' | tr -d ,)
echo "音频时长: $audio_duration"

# 将音频时长转换为秒
IFS=: read -r h m s <<< "$audio_duration"
audio_duration_sec=$(echo "$h*3600 + $m*60 + $s" | bc)
echo "音频时长 (秒): $audio_duration_sec"

# 计算每张图片展示的时间
display_time=$(echo "$audio_duration_sec / $n" | bc -l)
echo "每张图片展示时间: $display_time 秒"

# 创建临时目录
temp_dir=$(mktemp -d)

# 处理图片，使其宽度和高度为偶数，并拷贝到临时目录
for i in "${!selected_images[@]}"; do
  image=${selected_images[$i]}
  width=$(identify -format "%w" "$image")
  height=$(identify -format "%h" "$image")
  new_width=$((width - width % 2))
  new_height=$((height - height % 2))
  convert "$image" -resize ${new_width}x${new_height}! "$temp_dir/img_$i.png"
  echo "处理图片: $temp_dir/img_$i.png"
done

# 生成ffmpeg输入文件
ffmpeg_input="$temp_dir/input.txt"
for i in $(seq 0 $(($n-1))); do
  echo "file '$temp_dir/img_$i.png'" >> $ffmpeg_input
  echo "duration $display_time" >> $ffmpeg_input
done

# 添加最后一帧持续时间
echo "file '$temp_dir/img_$((n-1)).png'" >> $ffmpeg_input

# 输出视频文件名
output_video="${selected_mp3%.mp3}.mp4"

# 显示ffmpeg输入文件内容
echo "ffmpeg 输入文件内容:"
cat $ffmpeg_input

# 使用ffmpeg合并图片和音频生成视频，增加probesize选项
ffmpeg -f concat -safe 0 -probesize 50M -i $ffmpeg_input -i "$music_dir/$selected_mp3" -c:v libx264 -c:a aac -strict experimental -pix_fmt yuv420p "$output_video"

# 检查视频文件是否生成成功
if [[ ! -f "$output_video" || ! -s "$output_video" ]]; then
  echo "视频生成失败。"
  exit 1
fi

echo "视频已生成: $output_video"

# 清理临时文件
rm -r $temp_dir
