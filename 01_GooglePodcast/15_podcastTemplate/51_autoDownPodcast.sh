#!/bin/bash

# 定义变量
podcastURL="https://podcasts.google.com/feed/aHR0cHM6Ly9qb2Vyb2dhbmV4cC5saWJzeW4uY29tL3Jzcw?sa=X&ved=0CAcQrrcFahgKEwiwjKnApNKEAxUAAAAAHQAAAAAQxhc"
directoryPod="/home/01_html/54_JoeRogan"

# 创建目录
mkdir -p "$directoryPod"

# 下载脚本文件
curl -o "$directoryPod/download_mp3.sh" "https://19640810.xyz/51_podcastTemplate/download_mp3.sh"
curl -o "$directoryPod/analyze_filenames.py" "https://19640810.xyz/51_podcastTemplate/analyze_filenames.py"
curl -o "$directoryPod/nameURL_extract.py" "https://19640810.xyz/51_podcastTemplate/nameURL_extract.py"


# 获取路径中最后一个部分
directoryName=$(basename "$directoryPod")

# 将 download_mp3.sh 脚本中的 "51_SEND7" 字符串替换为 $directoryName
sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/download_mp3.sh"
sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/nameURL_extract.py"


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
