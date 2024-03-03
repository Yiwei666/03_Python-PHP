# 1. 项目功能

仅需要手动初始化`51_autoDownPodcast.sh`脚本中的两个参数，即可实现google podcast中指定博客的音频批量下载

# 2. 项目结构

### 1. 本地文件夹

- 下面的文件位于本地`D:\onedrive\英语\02_azure2-1\51_podcastTemplate`目录下，每次在新环境中部署该项目时，从该本地文件夹中获取模板文件即可


```
51_autoDownPodcast.sh         # 自动化部署的音频下载脚本，只需要初始化两个参数即可
analyze_filenames.py          # 分析是否有重复音频链接
download_mp3.sh               # 下载音频的脚本
nameURL_extract.py            # 提取音频链接脚本

rclone_limitFileSize.sh       # rclone上传相关脚本，当系统存储容量小于音频大小时使用
source.sh                     # 将指定文件夹01_audio中的音频文件名写入到txt文件中
source_move_to_target.sh      # 基于上述txt文件将音频文件转移到02_audio文件夹中
```

- 除了`51_autoDownPodcast.sh`脚本需要初始化两个参数之外，其余脚本均不需要改动参数

- 上述均需要提前上传至云服务器的`/home/01_html/51_podcastTemplate`目录下，因为`51_autoDownPodcast.sh`脚本需要从云服务器的该目录下载这些脚本




### 2. 云端文件夹

脚本运行结束后的文件夹如下

```
.
├── 01_audio
├── analyze_filenames.py
├── download_mp3.sh
├── homepage.html
├── nameURL_extract.py
├── nameURL.txt
├── output.txt
├── rclone_limitFileSize.sh
├── source_move_to_target.sh
├── source.sh
└── source.txt
```


### 3. 项目总体思路

`51_autoDownPodcast.sh`这个 Bash 自动化脚本首先定义了一些变量，包括 podcast 的 URL 和存储下载内容的目录路径，然后通过 curl 下载了必要的脚本文件和 podcast 的主页，接着在下载的脚本文件中替换特定字符串为目录名称，运行 Python 脚本提取文件名和URL，创建子目录存储音频文件，并通过 rclone 命令创建远程目录。最后，在后台运行下载音频文件的脚本，并将输出重定向到文件中。

注意：必要的脚本文件按包括上述**本地文件夹**中的所有文件，需要提前上述至云服务器指定目录下，以便自动化脚本能够下载这些脚本。除此之外，自动化脚本还会修改这些这些脚本中的路径参数。



# 3. 环境配置


1. 51_autoDownPodcast.sh 初始版本

```bash
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
```

上述脚本在使用时只需要对以下两个变量进行赋值即可

```sh
podcastURL="https://podcasts.google.com/feed/aHR0cHM6Ly9qb2Vyb2dhbmV4cC5saWJzeW4uY29tL3Jzcw?sa=X&ved=0CAcQrrcFahgKEwiwjKnApNKEAxUAAAAAHQAAAAAQxhc"
directoryPod="/home/01_html/54_JoeRogan"
```








