# 1. 项目功能

- 下载Google Podcast中的 Simple English News Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5idXp6c3Byb3V0LmNvbS85Njg5ODMucnNz?sa=X&ved=0CAcQrrcFahgKEwjYqeCCrLyEAxUAAAAAHQAAAAAQ9Tc
  
# 2. 文件结构


# 3. 环境配置

### 1. 下载相应podcast主页面为 homepage.html

```bash
curl -o homepage.html https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5idXp6c3Byb3V0LmNvbS85Njg5ODMucnNz?sa=X&ved=0CAcQrrcFahgKEwjYqeCCrLyEAxUAAAAAHQAAAAAQ9Tc
```

判断 homepage.html 中是否存在listitem标签

```bash
grep 'listitem' homepage.html
```

### 2. 提取homepage.html文件中的文件名和音频链接，文件名中仅包含中文汉字、英文字母以及阿拉伯数字

在该步骤中，只需要执行以下命令即可

```python
python nameURL_extract.py
```

### 3. 分析 nameURL.txt 文件中是否有重复的文件名，以及重复文件名出现的次数

```python
python analyze_filenames.py
```


### 4. 在 windows 运行`replace_directory.py`脚本

```py
python replace_directory.py
```

上述python脚本将自动配置如下脚本的路径

```
.
├── download_mp3.sh
├── rclone_limitFileSize.sh
├── source.sh
└── source_move_to_target.sh
```

更改路径的输出结果如下所示

```
D:\onedrive\英语\02_azure2-1\51_SEND7>python replace_directory.py
处理脚本文件: source.sh
  行 5 替换前：script_dir="/home/01_html/50_TheEnglishWeSpeak"
     替换后：script_dir="/home/01_html/51_SEND7"
  行 8 替换前：directory="/home/01_html/50_TheEnglishWeSpeak/01_audio"
     替换后：directory="/home/01_html/51_SEND7/01_audio"
完成替换

处理脚本文件: source_move_to_target.sh
  行 4 替换前：directory_a="/home/01_html/50_TheEnglishWeSpeak/01_audio"
     替换后：directory_a="/home/01_html/51_SEND7/01_audio"
  行 5 替换前：directory_b="/home/01_html/50_TheEnglishWeSpeak/02_audio"
     替换后：directory_b="/home/01_html/51_SEND7/02_audio"
  行 8 替换前：source_file="/home/01_html/50_TheEnglishWeSpeak/source.txt"
     替换后：source_file="/home/01_html/51_SEND7/source.txt"
完成替换

处理脚本文件: rclone_limitFileSize.sh
  行 4 替换前：directory="/home/01_html/50_TheEnglishWeSpeak/01_audio"
     替换后：directory="/home/01_html/51_SEND7/01_audio"
  行 18 替换前：# 删除文件 /home/01_html/50_TheEnglishWeSpeak/source.txt
     替换后：# 删除文件 /home/01_html/51_SEND7/source.txt
  行 19 替换前：rm -f "/home/01_html/50_TheEnglishWeSpeak/source.txt"  && sleep 3
     替换后：rm -f "/home/01_html/51_SEND7/source.txt"  && sleep 3
  行 21 替换前：# 运行脚本 /usr/bin/bash /home/01_html/50_TheEnglishWeSpeak/source.sh
     替换后：# 运行脚本 /usr/bin/bash /home/01_html/51_SEND7/source.sh
  行 22 替换前：/usr/bin/bash "/home/01_html/50_TheEnglishWeSpeak/source.sh"
     替换后：/usr/bin/bash "/home/01_html/51_SEND7/source.sh"
  行 27 替换前：# 删除目录 /home/01_html/50_TheEnglishWeSpeak/02_audio
     替换后：# 删除目录 /home/01_html/51_SEND7/02_audio
  行 28 替换前：rm -rf "/home/01_html/50_TheEnglishWeSpeak/02_audio"  && sleep 3
     替换后：rm -rf "/home/01_html/51_SEND7/02_audio"  && sleep 3
  行 30 替换前：# 运行脚本 /usr/bin/bash /home/01_html/50_TheEnglishWeSpeak/source_move_to_target.sh
     替换后：# 运行脚本 /usr/bin/bash /home/01_html/51_SEND7/source_move_to_target.sh
  行 31 替换前：/usr/bin/bash "/home/01_html/50_TheEnglishWeSpeak/source_move_to_target.sh"  && sleep 3
     替换后：/usr/bin/bash "/home/01_html/51_SEND7/source_move_to_target.sh"  && sleep 3
  行 34 替换前：/usr/bin/rclone copy "/home/01_html/50_TheEnglishWeSpeak/02_audio" "do1-1:do1-1/50_TheEnglishWeSpeak/01_audio"  && sleep 1200
     替换后：/usr/bin/rclone copy "/home/01_html/51_SEND7/02_audio" "do1-1:do1-1/51_SEND7/01_audio"  && sleep 1200
  行 36 替换前：# 删除目录，释放硬盘空间 /home/01_html/50_TheEnglishWeSpeak/02_audio
     替换后：# 删除目录，释放硬盘空间 /home/01_html/51_SEND7/02_audio
  行 37 替换前：rm -rf "/home/01_html/50_TheEnglishWeSpeak/02_audio"
     替换后：rm -rf "/home/01_html/51_SEND7/02_audio"
完成替换

处理脚本文件: download_mp3.sh
  行 4 替换前：output_path="/home/01_html/50_TheEnglishWeSpeak/01_audio"
     替换后：output_path="/home/01_html/51_SEND7/01_audio"
完成替换
```

### 5. 在onedrive和云服务器中创建相关路径

- 需要在云服务器的项目文件夹中先创建 `01_audio` 文件夹，用于保存 `download_mp3.sh` 脚本下载的音频， `02_audio` 不需要提前创建

- 注意onedrive远程目录`/51_SEND7/01_audio`需要提前创建，`rclone_limitFileSize.sh`脚本中远程标签`do1-1:do1-1`要写对，否则rclone上传时会报错

```sh
/usr/bin/rclone copy "/home/01_html/51_SEND7/02_audio" "do1-1:do1-1/51_SEND7/01_audio"  && sleep 1200
```


### 6. 设置`rclone_limitFileSize.sh`脚本参数

- 注意设置rclone的上传时间，该时间在满足完全上传的要求外尽可能小，过了该时间将删除`02_audio`文件夹，考虑服务器上传带宽，digitalocean 对于 8 GB上传一般在15分钟内完成

- 指定执行转移文件的目录大小阈值，如 8 GB，通常设置为可用内存的一半，必须满足在rclone上传期间内，下载量不会达到该阈值


### 7. 创建`rclone_limitFileSize.sh`相关定时任务

- crontab定时任务，每分钟执行一次，小于设定的内存阈值，则退出脚本，注意更换路径

```crontab
* * * * * /usr/bin/bash /home/01_html/45_TodayExplained/rclone_limitFileSize.sh
```


### 8. 后台运行音频下载脚本

- 后台运行 `download_mp3.sh` 脚本

```sh
nohup bash download_mp3.sh > output.txt 2>&1 &
```


### 9. 释放云服务器存储

- 最后不满设置的目录大小阈值的文件需要手动上传，完成之后别忘了核对云端的文件数量以及删除`01_audio`目录释放硬盘容量

```sh
rclone copy "/home/01_html/45_TodayExplained/01_audio" "do1-1:do1-1/45_TodayExplained/01_audio"
```

- 确定所有文件都已上传，并且释放了 `01_audio` 文件夹的内存占用后，取消`rclone_limitFileSize.sh`脚本的crontab定时任务，可以减少cpu占用以及方便管理

```
# * * * * * /usr/bin/bash /home/01_html/45_TodayExplained/rclone_limitFileSize.sh
```

















