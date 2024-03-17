# 1. 项目功能

1. 仅需要手动初始化`51_autoDownPodcast.sh`脚本中的两个参数，即可实现google podcast中指定博客的音频批量下载
2. 通过终端输入2个参数即可通过`511_autoDownPodcast.sh`脚本实现音频自动下载
3. 自动化部署和初始化脚本参数，脚本功能包括（1）基于rclone定时获取onedrive中的指定数量音频，（2）php脚本在线播放云服务器中的音频

# 2. 项目结构

### 1. 本地文件夹

- 下面的文件位于本地`D:\onedrive\英语\02_azure2-1\51_podcastTemplate`目录下，每次在新环境中部署该项目时，从该本地文件夹中获取模板文件即可


```
51_autoDownPodcast.sh         # 自动化部署的音频下载脚本，只需要手动初始化两个参数即可
511_autoDownPodcast.sh        # 自动化部署的音频下载脚本，只需要在终端界面中交互传递参数即可，不需要手动初始化参数
log.txt                       # 511_autoDownPodcast.sh脚本输出的记录输入参数的日志文件

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

1. `51_autoDownPodcast.sh`这个 Bash 自动化脚本首先定义了一些变量，包括 podcast 的 URL 和存储下载内容的目录路径，然后通过 curl 下载了必要的脚本文件和 podcast 的主页，接着在下载的脚本文件中替换特定字符串为目录名称，运行 Python 脚本提取文件名和URL，创建子目录存储音频文件，并通过 rclone 命令创建远程目录。最后，在后台运行下载音频文件的脚本，并将输出重定向到文件中。

2. 注意：必要的脚本文件按包括上述**本地文件夹**中的所有文件，需要提前上述至云服务器指定目录下，以便自动化脚本能够下载这些脚本。除此之外，自动化脚本还会修改这些这些脚本中的路径参数。

3. 在新的环境部署时，下载音频前需要核对自动化脚本修改的参数是否正确


# 3. 环境配置

### 1. Python库和alias

1. analyze_filenames.py

标准库即可

2. nameURL_extract.py

只需要安装`beautifulsoup4`，`re` 是标准库

```sh
pip install beautifulsoup4
```

3. 常用 Python 库：

- re：用于正则表达式操作。
- requests：用于发送 HTTP 请求。
- time：用于处理时间相关的操作。
- BeautifulSoup：用于解析 HTML 文档。
- chardet：用于检测字符编码。

安装命令如下：

```
pip install requests beautifulsoup4 chardet
```

请注意，`re`和`time`是 Python 标准库，通常情况下无需单独安装。

查看ubuntu系统上是否安装过 `requests, beautifulsoup4, chardet`

```
pip list | grep -E 'requests|beautifulsoup4|chardet'
```

4. alias别名设置

```ls
alias cdhtml='cd /home/01_html; ls -l'

alias lw='ls -l 01_audio/ | wc -l'

alias lw='echo $(($(ls -l 01_audio/ | wc -l) - 1))'             # 减去1的真实文件数

alias pa='python analyze_filenames.py'

alias rs='rclone size "rc2:cc1-1/$(basename "$(pwd)")/01_audio"'

alias gr='ps aux | grep rclone'

alias gd='ps aux | grep download_mp3.sh'

alias ds='du -sh .'

alias rls='rclone lsd rc2:cc1-1'
```




### 2. 51_autoDownPodcast.sh 初始版本

仅下载 `download_mp3.sh, analyze_filenames.py, nameURL_extract.py` 三个脚本，并对 `download_mp3.sh, nameURL_extract.py` 脚本进行参数初始化

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



### 3. 51_autoDownPodcast.sh 进阶版本

除了下载 `download_mp3.sh, analyze_filenames.py, nameURL_extract.py` 三个脚本，新增下载 `rclone_limitFileSize.sh, source.sh, source_move_to_target.sh` 3个脚本，并进行相应地参数初始化

```sh
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
curl -o "$directoryPod/rclone_limitFileSize.sh" "https://19640810.xyz/51_podcastTemplate/rclone_limitFileSize.sh"
curl -o "$directoryPod/source.sh" "https://19640810.xyz/51_podcastTemplate/source.sh"
curl -o "$directoryPod/source_move_to_target.sh" "https://19640810.xyz/51_podcastTemplate/source_move_to_target.sh"

# 获取路径中最后一个部分
directoryName=$(basename "$directoryPod")

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
```

需要参数初始化的变量仍然仅为

```sh
podcastURL="https://podcasts.google.com/feed/aHR0cHM6Ly9qb2Vyb2dhbmV4cC5saWJzeW4uY29tL3Jzcw?sa=X&ved=0CAcQrrcFahgKEwiwjKnApNKEAxUAAAAAHQAAAAAQxhc"
directoryPod="/home/01_html/54_JoeRogan"
```

### 4. 511_autoDownPodcast.sh 高阶版本

通过对 `51_autoDownPodcast.sh` 脚本进行修改，实现在终端界面上通过交互对 `podcastURL 和 directoryPod` 进行参数初始化，并进行检查、确认，最后写入到`log.txt`日志中

```sh
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

```

- 界面显示的过程信息如下所示

```log
请输入google podcast的url：
https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5hY2FzdC5jb20vcHVibGljL3Nob3dzLzAxODVjZWE1LTllM2ItNGI4Mi1hODg3LTI2ZjkxZjkyNzY1Zg?sa=X&ved=0CAcQrrcFahgKEwiwkcCTgN-EAxUAAAAAHQAAAAAQ_gU
请输入文件夹名称，如 51_SEND7：
72_NaturePodcast
您输入的第一个变量是：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5hY2FzdC5jb20vcHVibGljL3Nob3dzLzAxODVjZWE1LTllM2ItNGI4Mi1hODg3LTI2ZjkxZjkyNzY1Zg?sa=X&ved=0CAcQrrcFahgKEwiwkcCTgN-EAxUAAAAAHQAAAAAQ_gU
您输入的第二个变量是：72_NaturePodcast
是否确认输入正确？(输入 y 确认，其他键退出): y
保存成功！
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1062  100  1062    0     0    884      0  0:00:01  0:00:01 --:--:--   885
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1119  100  1119    0     0   1345      0 --:--:-- --:--:-- --:--:--  1346
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1384  100  1384    0     0   1125      0  0:00:01  0:00:01 --:--:--  1125
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1328  100  1328    0     0   1388      0 --:--:-- --:--:-- --:--:--  1389
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100   473  100   473    0     0    497      0 --:--:-- --:--:-- --:--:--   497
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100   971  100   971    0     0   2766      0 --:--:-- --:--:-- --:--:--  2774
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100 3144k    0 3144k    0     0  2957k      0 --:--:--  0:00:01 --:--:-- 2958k
Extraction and writing to nameURL.txt completed.
```

### 5. 更换上传的onedrive云盘

需要修改以下脚本中的远程标签

1. `511_autoDownPodcast.sh` 中创建云盘目录的命令

```sh
rclone mkdir "cc1-1:cc1-1/$directoryName/01_audio"
```

2. `rclone_limitFileSize.sh`上传脚本的远程标签

```sh
# nohup rclone copy /home/01_html/51_SEND7 cc1-1:cc1-1/51_SEND7  &
# nohup rclone copy /home/01_html/51_SEND7 cc1-1:cc1-1/51_SEND7 --transfers=16 &
# rclone size cc1-1:cc1-1/51_SEND7/01_audio
/usr/bin/rclone copy "/home/01_html/51_SEND7/02_audio" "cc1-1:cc1-1/51_SEND7/01_audio" --transfers=12 && sleep 20
```




