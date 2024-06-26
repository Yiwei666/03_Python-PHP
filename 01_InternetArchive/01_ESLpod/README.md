# 项目功能

下载 English as a Second Language (ESL) Podcast 音频

Internet Archive网址： https://archive.org/details/ipodcast

本项目通过do1-1进行下载，已完成前三部分，包括dill， dilj以及ce的下载，受限于内存容量，部分同步至AECS，待同步部分包括 ce500.mp3-ce603.mp3。cc1-1下载了ESLpod中除do1-1已下载之外的其余所有音频，但未同步至任何云服务器。

# 文件结构

- 官网一级目录

```
a-day-in-the-life-of-jeff/	          09-Sep-2022 23:36	-
a-day-in-the-life-of-lucy/	          10-Sep-2022 04:22	-
cultural-english/	                    10-Sep-2022 17:06	-
daily-english/	                      10-Sep-2022 17:13	-
english-for-business-meetings/       	10-Sep-2022 02:08	-
history/	                            14-Dec-2019 11:38	-
interview-questions-answered/	        10-Sep-2022 02:36	-
introduction-to-the-united-states/	  10-Sep-2022 04:55	-
using-english-at-work/	              09-Sep-2022 21:37	-
ESLPod.jpg	                          08-Dec-2019 14:55	152.0K
ESLPod_thumb.jpg	                    08-Dec-2019 15:47	3.6K
__ia_thumb.jpg	                      12-Feb-2021 00:38	8.8K
eslpod.xml	                          14-Dec-2019 11:38	1.1M
ipodcast_archive.torrent	            06-Jun-2022 09:27	2.5M
ipodcast_files.xml	                  10-Sep-2022 17:13	3.5M
ipodcast_meta.sqlite	                14-Dec-2019 11:38	4.3M
ipodcast_meta.xml	                    10-Sep-2022 14:36	1.1K
```

1. 上面一级目录下的每个子文件夹是一个主题的音频，需要从子文件夹中获取音频真正的下载链接
2. 主页中显示的下载链接需要重定向，每个主题中音频下载链接的前缀往往都不同
```
https://archive.org/details/ipodcast/a-day-in-the-life-of-lucy/dill10.mp3                         # 主页音频下载链接，基于该链接下载的文件大小一样，且都无法播放

https://ia902808.us.archive.org/35/items/ipodcast/a-day-in-the-life-of-lucy/dill10.mp3            # a-day-in-the-life-of-lucy/ 子目录中的音频链接，该链接可用于音频下载
https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/ce001.mp3                      # cultural-english/	子目录中的音频链接，该链接可用于音频下载
```



- 项目目录

```
.
├── 01_audio                         # 保存音频的文件夹
├── 01_success.txt                   # 保存下载成功的音频网址
└── downloadArchive_mp3.sh           # 下载音频的bash脚本
```

# 环境配置

- downloadArchive_mp3.sh

脚本功能：通过分析音频链接的组成，构造下载链接，循环下载

1. 脚本参数赋值

如果下载同一项目不同子目录的音频，以下4个参数在下载之前是需要进行赋值的

```bash
# 设置起始编号和结尾编号
start_number=201
end_number=400

# 设置音频链接的固定部分和文件后缀，参考以下链接组成进行赋值
# https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/ce603.mp3

base_url="https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/"
file_prefix="ce"          # 文件名
```

如果下载不同项目的音频，保存音频的路径可能需要修改

```
# 设置输出路径
output_path="/home/01_html/10_ESLpod/01_audio"
```

完整脚本

```bash
#!/bin/bash

# 设置起始编号和结尾编号
start_number=201
end_number=400

# 获取 end_number 的位数
end_number_digits=${#end_number}

# 设置音频链接的固定部分和文件后缀
base_url="https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/"
file_prefix="ce"  # 新增的变量

# https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/ce603.mp3

file_extension=".mp3"

# 设置保存成功和失败链接的文件（在脚本相同的路径下）
success_file="01_success.txt"
failure_file="02_failure.txt"

# 设置输出路径
output_path="/home/01_html/10_ESLpod/01_audio"

# 创建输出路径
mkdir -p "$output_path"

# 循环下载音频文件
for ((number=start_number; number<=end_number; number++)); do
    # 格式化编号，确保数字位数相同，不足的位数用0补齐
    formatted_number=$(printf "%0${end_number_digits}d" $number)
    
    # 构建完整的音频链接
    audio_url="$base_url$file_prefix$formatted_number$file_extension"
    
    # 构建保存音频文件的文件名
    file_name="$file_prefix$formatted_number$file_extension"
    
    # 使用wget下载音频文件到指定路径，并等待5秒后再进行下一次下载
    wget -O "$output_path/$file_name" $audio_url
    sleep 5
    
    # 检查下载是否成功
    if [ $? -eq 0 ]; then
        # 下载成功，保存链接到成功文件
        echo $audio_url >> "$success_file"
        echo "Downloaded $file_name - Success"
    else
        # 下载失败，保存链接到失败文件
        echo $audio_url >> "$failure_file"
        echo "Failed to download $file_name"
    fi
done

echo "Download complete!"
```


总结：该Bash脚本用于自动化下载一系列音频文件。通过指定起始和结束编号、音频链接的固定部分、文件前缀和后缀，脚本循环迭代下载音频文件并将其保存到指定路径。在每次下载后，脚本会等待5秒，并根据wget命令的退出状态判断下载成功与否，将成功的链接记录到一个文件中，将失败的链接记录到另一个文件中。该脚本的目的是提供一个简单而有效的方式，用于批量下载音频文件并追踪下载的成功和失败情况。

2. 设置权限并下载

```bash
chmod +x downloadArchive_mp3.sh             # 添加执行权限

bash downloadArchive_mp3.sh                 # 运行音频下载脚本
```









