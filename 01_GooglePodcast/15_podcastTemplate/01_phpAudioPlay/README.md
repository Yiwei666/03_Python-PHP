# 1. 项目功能

自动化配置网页播放音频的php脚本和定时下载音频的bash脚本

# 2. 文件结构

### 1. 自动化安装脚本

- 对onedrive中储存的mp3音频，设置rclone批量化定时下载的自动安装脚本
- 要求mp3音频存储目录具有统一的结构

```
├── 51_podcastTemplate                       # 文件夹
    ├── 511_inputAutoSetPodcast.sh           # 通过终端页面交互对自动化脚本进行参数初始化
    ├── 51_autoSetPodcast.sh                 # 需要手动参数初始化的自动化下载脚本
    ├── 51_SEND7.sh                          # 网页播放音频的php脚本，原本后缀是`.php`的，由于通过curl下载的`51_SEND7.php`不是源代码，而是被服务器解释并生成了HTML内容的文件，因此将后缀改成了`.sh`
    └── rclone_51_SEND7.sh                   # 定时脚本，用于定时下载音频
```

### 2. rclone手动下载定时脚本

```
rclone_09_music.sh                           # 对于音频分散在多个子文件夹中的定时下载脚本，功能类似于 rclone_51_SEND7.sh
```



# 3. 环境配置


## 1. 51_autoSetPodcast.sh 初级版本

1. 下载两个模板脚本文件，根据用户输入的目录名进行路径替换和定制化，并为其中一个脚本设置定时任务（Cron job）
2. 需要手动对参数`directoryPod`进行初始化

### 1. 安装脚本代码

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

### 2. 代码注释

<p align="center">
<img src="https://19640810.xyz/05_image/01_imageHost/20241004-204329.png" alt="Image Description" width="700">
</p>


### 3. 环境变量


```sh
# 定义变量，唯一需要初始化的值
directoryPod="/home/01_html/67_RealSexEducation"

# 下载脚本文件
curl -o "/home/01_html/template_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/51_SEND7.sh"
curl -o "$directoryPod/template_rclone_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/rclone_51_SEND7.sh"
```


1. 确保服务器 `/home/01_html/51_podcastTemplate`路径下存在`51_SEND7.sh`和`rclone_51_SEND7.sh`两个文件，准确配置服务器ip地址或者域名
2. 初始化`directoryPod`参数，确保该文件夹在onedrive中存在



## 2. 511_inputAutoSetPodcast.sh 高级版本

1. 通过界面交互实现对参数的初始化，避免本地修改参数再上传云服务器的繁琐流程
2. 功能与`51_autoSetPodcast.sh`类似，但是`$directoryName`参数是通过页面交互初始化的


### 1. 安装脚本代码

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

### 2. 环境变量

```sh
curl -o "/home/01_html/template_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/51_SEND7.sh"
curl -o "$directoryPod/template_rclone_51_SEND7.sh" "http://39.105.186.182/51_podcastTemplate/rclone_51_SEND7.sh"
```

- 确保服务器 `/home/01_html/51_podcastTemplate`路径下存在`51_SEND7.sh`和`rclone_51_SEND7.sh`两个文件，准确配置服务器ip地址或者域名



## 3. 51_SEND7.sh

### 1. 环境变量

```php
// 生成音频链接的路径，一般为domain或ip+文件夹
$baseUrl = 'http://39.105.186.182/51_SEND7/01_audio/';
```

确保ip地址或者域名正确，`51_SEND7`不需要修改，`511_inputAutoSetPodcast.sh`安装脚本会自动设置



## 4. rclone_51_SEND7.sh

### 1. 环境变量

```sh
# 远程路径
remote_path="rc1:cc1-1/51_SEND7/01_audio"
```

1. 参数`rc1:cc1-1`需要根据实际情况来设置，否则rclone无法下载音频。
2. 确保onedrive中`rc1:cc1-1/51_SEND7/01_audio`存在且有效



# 4. rclone_09_music.sh

### 1. 环境变量

```sh
# 远程路径的主目录
remote_base_path="rc1:cc1-1/09_music/"

# 本地路径
local_path="/home/01_html/09_music/01_audio"
```

注意：`rc1:cc1-1/09_music/`目录下有多个子文件夹，代表不同歌手







