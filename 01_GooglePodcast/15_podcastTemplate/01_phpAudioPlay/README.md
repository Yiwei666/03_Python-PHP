# 1. 项目功能

自动化配置网页播放音频的php脚本和定时下载音频的bash脚本

# 2. 文件结构

```
.
├── 511_inputAutoSetPodcast.sh           # 通过终端页面交互对自动化脚本进行参数初始化
├── 51_autoSetPodcast.sh                 # 需要手动参数初始化的自动化下载脚本
├── 51_SEND7.sh                          # 网页播放音频的php脚本，原本后缀是`.php`的，由于通过curl下载的`51_SEND7.php`不是源代码，而是被服务器解释并生成了HTML内容的文件，因此将后缀改成了`.sh`
└── rclone_51_SEND7.sh                   # 定时脚本，用于定时下载音频
```



# 3. 环境配置


### 1. 51_autoSetPodcast.sh 初级版本

需要手动对参数`directoryPod`进行初始化

```sh
#!/bin/bash
set -e

# 定义变量，唯一需要初始化的值
directoryPod="/home/01_html/67_RealSexEducation"

# 判断"$directoryPod"目录是否存在，如果不存在则创建目录
if [ ! -d "$directoryPod" ]; then
    mkdir -p "$directoryPod"
fi

# 下载脚本文件
curl -o "/home/01_html/template_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/51_SEND7.sh"
curl -o "$directoryPod/template_rclone_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/rclone_51_SEND7.sh"

# 获取路径中最后一个部分
directoryName=$(basename "$directoryPod")

# 将 download_mp3.sh 脚本中的 "51_SEND7" 字符串替换为 $directoryName
sed -i "s/51_SEND7/$directoryName/g" "/home/01_html/template_51_SEND7.sh"
sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/template_rclone_51_SEND7.sh"

# 重命名
mv "/home/01_html/template_51_SEND7.sh" "/home/01_html/$directoryName.php"
mv "$directoryPod/template_rclone_51_SEND7.sh" "$directoryPod/rclone_$directoryName.sh"

# 定义要添加的定时任务
current_hour=$(date +"%H")
current_minute=$(date +"%M")

echo "$current_minute:$current_hour"

cronjob="$((current_minute + 1)) $current_hour * * * /usr/bin/bash /home/01_html/$directoryName/rclone_$directoryName.sh"

# 将定时任务添加到当前用户的 crontab 中
{ crontab -l; echo "$cronjob"; } | crontab -

# 输出提示信息
echo "脚本运行结束。"
```


### 2. 511_inputAutoSetPodcast.sh 高级版本

通过界面交互实现对参数的初始化，避免本地修改参数再上传云服务器的繁琐流程

```sh
#!/bin/bash
set -e

# 提示用户输入字符串
echo "请输入文件夹名称，如 51_SEND7："
read directoryName

# 打印用户输入的字符串
echo "您输入的文件夹名称是: $directoryName"

# 定义变量，唯一需要初始化的值
directoryPod="/home/01_html/$directoryName"

echo "新的文件夹路径是: $directoryPod"

read -p "是否继续？(输入 y 继续，输入 q 退出): " choice

# 判断用户选择
if [ "$choice" == "y" ]; then
    # 判断"$directoryPod"目录是否存在，如果不存在则创建目录
    if [ ! -d "$directoryPod" ]; then
        mkdir -p "$directoryPod"
    else
        echo "警告：目录 '$directoryPod' 已经存在。"
    fi

    # 下载脚本文件
    curl -o "/home/01_html/template_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/51_SEND7.sh"
    curl -o "$directoryPod/template_rclone_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/rclone_51_SEND7.sh"

    # 获取路径中最后一个部分
    # directoryName=$(basename "$directoryPod")

    # 将 download_mp3.sh 脚本中的 "51_SEND7" 字符串替换为 $directoryName
    sed -i "s/51_SEND7/$directoryName/g" "/home/01_html/template_51_SEND7.sh"
    sed -i "s/51_SEND7/$directoryName/g" "$directoryPod/template_rclone_51_SEND7.sh"

    # 重命名
    mv "/home/01_html/template_51_SEND7.sh" "/home/01_html/$directoryName.php"
    mv "$directoryPod/template_rclone_51_SEND7.sh" "$directoryPod/rclone_$directoryName.sh"

    # 定义要添加的定时任务
    current_hour=$(date +"%H")
    current_minute=$(date +"%M")

    echo "$current_minute:$current_hour"

    cronjob="$((current_minute + 1)) $current_hour * * * /usr/bin/bash /home/01_html/$directoryName/rclone_$directoryName.sh"

    # 将定时任务添加到当前用户的 crontab 中
    { crontab -l; echo "$cronjob"; } | crontab -

    # 输出提示信息
    echo "脚本运行结束。"
else
    echo "退出脚本"
    exit 1
fi

```





