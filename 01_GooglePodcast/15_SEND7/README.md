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


### 4. 在 windows 运行`replace_directory.py`脚本，

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

5. 

