# 1. 项目功能

1. 利用rclone从onedrive拷贝下载文件到云服务器
2. 利用rclone将远程目录中的文件名写入到txt文本中


# 2. 文件结构

```
rclone_random_downmp3.sh          # 随机下载10个mp3音频到云服务器
remote_filename_save.sh           # 使用rclone将远程目录下指定文件夹中的所有文件名写入到remote_filename.txt文件中

download_checker.py               # 基于文本nameURL.txt和文本remote_filename.txt提取未下载的音频文件名和链接到undownload_mp3.txt文本中
download_checker.sh               # bash实现
download_checker.php              # php实现

analyze_filenames.py              # 分析 nameURL.txt 文件中是否有重复的文件名，以及重复文件名出现的次数
```


# 3. 环境配置

### 1. rclone_random_downmp3.sh

1. 脚本功能

```
1. 删除目录 /home/01_html/37_Economist/01_audio 
2. 创建目录 /home/01_html/37_Economist/01_audio 
3. 使用rclone读取远程位置 AECS-1109:AECS-1109/37_Economist/01_audio  下的所有文件名到一个数组中
4. 从上述数组中随机选取10个后缀名为mp3的文件名，下载到指定目录 /home/01_html/37_Economist/01_audio
```

- 源代码

```sh
#!/bin/bash

# 远程路径
remote_path="AECS-1109:AECS-1109/37_Economist/01_audio"

# 本地路径
local_path="/home/01_html/37_Economist/01_audio"

# 随机选取的文件数量
random_files_count=10

# 1. 删除目录 $local_path
rm -rf "$local_path"

# 2. 创建目录 $local_path
mkdir -p "$local_path"

# 3. 使用rclone读取远程位置 $remote_path 下的所有文件名到一个数组中
file_list=($(rclone ls "$remote_path" | awk '{print $NF}' | shuf))

# 4. 从上述数组中随机选取 $random_files_count 个后缀名为mp3的文件名，下载到指定目录 $local_path
count=0

for file in "${file_list[@]}"; do
    if [[ $file == *.mp3 ]]; then
        if [ $count -lt $random_files_count ]; then
            rclone copy "$remote_path/$file" "$local_path"
            ((count++))
        else
            break
        fi
    fi
done

echo "任务完成"
```


2. 设置定时任务

每天 13:10 crontab定时执行如下命令

```sh
10 13 * * * /usr/bin/bash /home/01_html/37_Economist/rclone_random_downmp3.sh
```


### 2. remote_filename_save.sh

使用rclone将远程目录下指定文件夹中的所有文件名写入到txt文件中

```sh
#!/bin/bash

# 远程路径
remote_path="do1-1:do1-1/45_TodayExplained/01_audio"

# 1. 使用rclone读取远程位置 $remote_path 下的所有文件名，并将其存储到 remote_filename.txt 文件中
rclone ls "$remote_path" | awk '{print $NF}' > remote_filename.txt

echo "远程文件名已保存到 remote_filename.txt 文件中"
```


### 3. download_checker.py

这段代码实现了以下功能：

1. 从名为 'nameURL.txt' 的文件中读取文件名和对应的链接，以及从 'remote_filename.txt' 文件中读取已下载的文件名列表（去除后缀）。

2. 遍历 'nameURL.txt' 中的每一行，检查文件名是否在已下载文件列表中，如果不在则将未下载的文件名和链接写入 'undownload_mp3.txt' 文件。

注意如何正确去除文件名的后缀，使用`strip('.mp3')`函数存在潜在的问题，因为该函数会移除字符串两侧任何位置的` 'm', 'p', '3' `这三个字符，而不仅仅是作为后缀的情况。所以在一些情况下可能不是一个安全的方式去除文件扩展名。

```py
import os

# 读取 nameURL.txt 中的文件名和链接
with open('nameURL.txt', 'r', encoding='utf-8') as file:
    name_url_lines = file.readlines()

# 读取 remote_filename.txt 中已下载的文件名（去除后缀）
with open('remote_filename.txt', 'r', encoding='utf-8') as file:
    downloaded_files = [os.path.splitext(line.strip())[0] for line in file.read().splitlines()]

# 遍历每一行，检查文件名是否在已下载文件列表中，如果不在则写入 undownload_mp3.txt
with open('undownload_mp3.txt', 'w', encoding='utf-8') as file:
    for line in name_url_lines:
        filename, url = line.split(',')
        if os.path.splitext(filename.strip())[0] not in downloaded_files:
            file.write(f'{filename.strip()},{url.strip()}\n')

print('未下载的文件名和链接已写入 undownload_mp3.txt 文件。')
```










# 参考资料

1. rclone连接到onedrive：https://github.com/Yiwei666/12_blog/wiki/08_rclone%E8%BF%9E%E6%8E%A5%E5%88%B0OneDrive#2-%E4%B8%8A%E4%BC%A0%E5%92%8C%E4%B8%8B%E8%BD%BD%E6%B5%8B%E8%AF%95
