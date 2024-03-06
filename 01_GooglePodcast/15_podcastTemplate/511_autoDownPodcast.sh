#!/bin/bash

# 定义变量
# podcastURL="https://podcasts.google.com/feed/aHR0cHM6Ly9qb2Vyb2dhbmV4cC5saWJzeW4uY29tL3Jzcw?sa=X&ved=0CAcQrrcFahgKEwiwjKnApNKEAxUAAAAAHQAAAAAQxhc"

# 获取当前时间，精确到秒
current_time=$(date +"%Y-%m-%d %H:%M:%S")

# 提示用户输入第一个变量
echo "请输入google podcast的url："
read podcastURL

# 提示用户输入第二个变量
echo "请输入文件夹名称，如 51_SEND7："
read directoryName

# 显示用户输入的变量并要求确认
echo "您输入的第一个变量是：$podcastURL"
echo "您输入的第二个变量是：$directoryName"

read -p "是否确认输入正确？(输入 y 确认，其他键退出): " confirmation

# 判断用户的确认
if [ "$confirmation" == "y" ]; then
    # 将变量和当前时间保存到log.txt文件中
    echo "时间: $current_time" >> log.txt
    echo "变量1: $directoryName" >> log.txt
    echo "变量2: $podcastURL" >> log.txt
    echo "保存成功！"

    directoryPod="/home/01_html/$directoryName"

    # 判断"$directoryPod"目录是否存在，如果不存在则创建目录
    if [ ! -d "$directoryPod" ]; then
        mkdir -p "$directoryPod"
    else
        echo "警告：目录 '$directoryPod' 已经存在。"
    fi

	# 下载脚本文件
	curl -o "$directoryPod/download_mp3.sh" "https://19640810.xyz/51_podcastTemplate/download_mp3.sh"
	curl -o "$directoryPod/analyze_filenames.py" "https://19640810.xyz/51_podcastTemplate/analyze_filenames.py"
	curl -o "$directoryPod/nameURL_extract.py" "https://19640810.xyz/51_podcastTemplate/nameURL_extract.py"
	curl -o "$directoryPod/rclone_limitFileSize.sh" "https://19640810.xyz/51_podcastTemplate/rclone_limitFileSize.sh"
	curl -o "$directoryPod/source.sh" "https://19640810.xyz/51_podcastTemplate/source.sh"
	curl -o "$directoryPod/source_move_to_target.sh" "https://19640810.xyz/51_podcastTemplate/source_move_to_target.sh"

	# 获取路径中最后一个部分
	# directoryName=$(basename "$directoryPod")

	# 将 download_mp3.sh 脚本中的 "51_SEND7" 字符串替换为 $directoryName
	sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/download_mp3.sh"
	sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/nameURL_extract.py"
	sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/rclone_limitFileSize.sh"
	sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/source.sh"
	sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/source_move_to_target.sh"

	# 下载网页
	curl -o "$directoryPod/homepage.html" "$podcastURL"

	# 运行 Python 脚本
	python "$directoryPod/nameURL_extract.py"

	# 创建子目录
	mkdir -p "$directoryPod/01_audio"

	# 创建远程目录
	rclone mkdir "cc1-1:cc1-1/$directoryName/01_audio"

	# 下载音频
	nohup bash "$directoryPod/download_mp3.sh" > "$directoryPod/output.txt" 2>&1 &

	# 上传文件
	# rclone copy "$directoryPod" "cc1-1:cc1-1/$directoryName"

	# 设置定时任务
	# * * * * * /usr/bin/bash /home/01_html/54_JoeRogan/rclone_limitFileSize.sh

	# 释放云服务器存储
	# rm -rf "$directoryPod/01_audio"

else
    echo "退出脚本"
    exit 1
fi



