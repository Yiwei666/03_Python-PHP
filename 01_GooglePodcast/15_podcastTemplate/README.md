# 1. 项目功能



# 2. 项目结构

### 1. 本地文件夹

- 下面的文件位于本地`D:\onedrive\英语\02_azure2-1\51_podcastTemplate`目录下


```
51_autoDownPodcast.sh         # 自动化部署的音频下载脚本，只需要初始化两个参数即可
analyze_filenames.py          # 分析是否有重复音频链接
download_mp3.sh               # 下载音频的脚本
nameURL_extract.py            # 提取音频链接脚本

rclone_limitFileSize.sh       # rclone上传相关脚本，当系统存储容量小于音频大小时使用
source.sh                     # 将指定文件夹01_audio中的音频文件名写入到txt文件中
source_move_to_target.sh      # 基于上述txt文件将音频文件转移到02_audio文件夹中
```

除了`51_autoDownPodcast.sh`脚本需要初始化两个参数之外，其余均不需要改动参数，均需要提前上传至云服务器的`/home/01_html/51_podcastTemplate`目录下，因为`51_autoDownPodcast.sh`脚本需要从云服务器的该目录下载这些脚本





1. 当下载的音频大小小于服务器内存时，可将如下脚本

```
51_autoDownPodcast.sh
```






3. 

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








