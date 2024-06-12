#!/bin/bash

echo "选择功能："
echo "1. 提取MP4视频音频"
echo "2. 截取MP4视频片段"
echo "3. 转移文件到指定目录"
echo "4. MP4文件重命名"
echo "5. 提取多个视频片段并合并为一个文件"
read -p "请输入选项（1, 2, 3, 4或5）: " option

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
            # 保存到指定目录
            save_path="/home/01_html/05_twitter_video/$output"
            ffmpeg -i "$filename" -ss "$start_time" -to "$end_time" -c copy "$save_path"
            clip_duration=$(mediainfo --Inform="General;%Duration%" "$save_path")
            clip_duration_sec=$(echo "$clip_duration/1000" | bc)
            clip_size=$(mediainfo --Inform="General;%FileSize%" "$save_path")
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
    4)
        echo "当前目录下的所有文件："
        ls
        echo "重命名所有MP4文件..."
        count=0
        for file in *.mp4; do
            ((count++))
            name="${file%.*}"
            newname=$(echo "$name" | sed 's/ /_/g' | sed 's/[[:punct:]]/-/g')
            newname="${count}_${newname}.mp4"
            mv "$file" "$newname"
            echo "重命名 '$file' 为 '$newname'"
        done
        ;;
    5)
        read -p "请输入MP4视频文件名: " filename
        if [[ -f "$filename" ]]; then
            # 获取视频总时长
            video_duration=$(mediainfo --Inform="General;%Duration%" "$filename")
            video_duration_sec=$(echo "$video_duration/1000" | bc)
            echo "视频总时长: ${video_duration_sec} 秒"

            # 输入截取范围
            read -p "请输入需要截取的视频时段范围（多个时段用逗号分隔，如 01:02:30-01:36:00,02:05:00-03:38:00）: " time_ranges
            IFS=',' read -r -a ranges <<< "$time_ranges"
            filter_complex=""
            concat_input=""
            index=0

            # 为每个片段生成过滤器描述
            for range in "${ranges[@]}"; do
                start_time=$(echo $range | cut -d '-' -f 1)
                end_time=$(echo $range | cut -d '-' -f 2)
                # 注意：这里每个片段都视为独立的输入
                filter_complex+="[$index:v] [$index:a] "
                concat_input+="-ss $start_time -to $end_time -i \"$filename\" "
                ((index++))
            done

            # 连接所有过滤器输入
            filter_complex+="concat=n=$index:v=1:a=1 [v] [a]"

            # 生成最终输出文件名
            output=$(date +"%Y%m%d-%H%M%S")-$(tr -dc 'a-zA-Z0-9' </dev/urandom | head -c 12).mp4
            
            # 执行ffmpeg命令，合并视频
            ffmpeg_cmd="ffmpeg $concat_input -filter_complex \"$filter_complex\" -map \"[v]\" -map \"[a]\" -y \"$output\""
            eval $ffmpeg_cmd

            # 获取合并视频的时长和大小
            merged_duration=$(mediainfo --Inform="General;%Duration%" "$output")
            merged_duration_sec=$(echo "$merged_duration/1000" | bc)
            merged_size=$(mediainfo --Inform="General;%FileSize%" "$output")
            merged_size_mb=$(echo "scale=2; $merged_size/1048576" | bc)
            echo "合并的视频总时长: ${merged_duration_sec} 秒"
            echo "合并的视频大小: ${merged_size_mb} MB"
        else
            echo "文件不存在！"
        fi
        ;;
    *)
        echo "无效的选项。"
        ;;
esac
