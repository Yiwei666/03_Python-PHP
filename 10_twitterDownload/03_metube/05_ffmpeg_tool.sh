#!/bin/bash

echo "选择功能："
echo "1. 提取MP4视频音频"
echo "2. 截取MP4视频片段"
echo "3. 转移文件到指定目录"
read -p "请输入选项（1, 2或3）: " option

case $option in
    1)
        read -p "请输入MP4视频文件名: " filename
        if [[ -f "$filename" ]]; then
            # 提取音频
            output="${filename%.*}.mp3"
            ffmpeg -i "$filename" -q:a 0 -map a "$output"
            audio_duration=$(mediainfo --Inform="Audio;%Duration%" "$output")
            audio_duration_sec=$(echo "$audio_duration/1000" | bc)
            audio_size=$(mediainfo --Inform="General;%FileSize%" "$output")
            audio_size_mb=$(echo "scale=2; $audio_size/1048576" | bc)
            echo "音频总时长: ${audio_duration_sec} 秒"
            echo "音频大小: ${audio_size_mb} MB"
        else
            echo "文件不存在！"
        fi
        ;;
    2)
        read -p "请输入MP4视频文件名: " filename
        if [[ -f "$filename" ]]; then
            # 获取视频总时长
            video_duration=$(mediainfo --Inform="General;%Duration%" "$filename")
            video_duration_sec=$(echo "$video_duration/1000" | bc)
            echo "视频总时长: ${video_duration_sec} 秒"
            
            # 输入截取范围
            read -p "请输入需要截取的视频时段范围（如 00:01:20-00:02:05）: " time_range
            start_time=$(echo $time_range | cut -d '-' -f 1)
            end_time=$(echo $time_range | cut -d '-' -f 2)
            output=$(date +"%Y%m%d-%H%M%S")-$(tr -dc 'a-zA-Z0-9' </dev/urandom | head -c 12).mp4
            ffmpeg -i "$filename" -ss "$start_time" -to "$end_time" -c copy "$output"
            clip_duration=$(mediainfo --Inform="General;%Duration%" "$output")
            clip_duration_sec=$(echo "$clip_duration/1000" | bc)
            clip_size=$(mediainfo --Inform="General;%FileSize%" "$output")
            clip_size_mb=$(echo "scale=2; $clip_size/1048576" | bc)
            echo "截取的视频时长: ${clip_duration_sec} 秒"
            echo "视频大小: ${clip_size_mb} MB"
        else
            echo "文件不存在！"
        fi
        ;;
    3)
        echo "当前目录下的所有文件："
        ls
        read -p "请输入要转移的文件名: " filename
        if [[ -f "$filename" ]]; then
            # 转移文件
            mv "$filename" /home/01_html/05_twitter_video/
            echo "文件已成功转移到 /home/01_html/05_twitter_video/ 目录"
        else
            echo "文件不存在！"
        fi
        ;;
    *)
        echo "无效的选项。"
        ;;
esac
