# 1. 项目功能

- 下载Google Podcast中的 The Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs

# 2. 文件结构

```
source.sh                              # 将指定文件夹下的文件名写入到脚本同级目录下的source.txt文件中        
source_move_to_target.sh               # 将source.txt中记录的文件从一个目录转移到另外一个目录中
rclone_limitFileSize.sh                # 自动化执行文件的转移和上传
```

# 3. 环境配置

### 1. 音频下载

```
curl -o homepage.html https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs
```


### 2. 文件转移和上传

由于云服务器硬盘容量有限，需要将部分已下载的音频上传至onedrive云端中，因此需要将已下载的音频从下载文件夹转移到上传文件夹中

1. source.sh

```sh
#!/bin/bash

# 获取脚本所在目录
# script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
script_dir="/home/01_html/45_TodayExplained"

# 指定目录
directory="/home/01_html/45_TodayExplained/01_audio"

# 检查目录是否存在
if [ ! -d "$directory" ]; then
  echo "指定的目录不存在"
  exit 1
fi

# 切换到目录
cd "$directory" || exit 1

# 获取所有文件名并写入source.txt
ls -1 > "$script_dir/source.txt"

echo "文件名已写入到 source.txt 中"
```

2. source_move_to_target.sh

转移过程中会检查source.txt中的文件是否存在于源目录和目标目录，并给出对应提示

```sh
#!/bin/bash

# 指定目录A和目录B
directory_a="/home/01_html/42_TheDaily/01_audio"
directory_b="/home/01_html/42_TheDaily/02_audio"

# 指定源文件路径
source_file="/home/01_html/42_TheDaily/source.txt"

# 检查目录A是否存在
if [ ! -d "$directory_a" ]; then
  echo "目录A不存在"
  exit 1
fi

# 检查目录B是否存在，如果不存在则创建
if [ ! -d "$directory_b" ]; then
  mkdir -p "$directory_b"
fi

# 读取txt文件中的文件名并逐行处理
while IFS= read -r filename; do
  # 检查文件是否存在于目录A中
  if [ -e "$directory_a/$filename" ]; then
    # 检查文件是否已经存在于目录B中
    if [ -e "$directory_b/$filename" ]; then
      echo "警告：目录B中已存在文件 $filename"
    else
      # 移动文件到目录B
      mv "$directory_a/$filename" "$directory_b/"
      echo "文件 $filename 移动成功"
    fi
  else
    echo "警告：目录A中不存在文件 $filename"
  fi
done < "$source_file"
```


3. rclone_limitFileSize.sh

- 功能如下

```
1. 判断指定目录的大小 "/home/01_html/45_TodayExplained/01_audio"  是否小于15GB，如果小于，则退出脚本运行
2. 如果大于等于15GB，则 
首先删除掉 /home/01_html/45_TodayExplained/source.txt   文件，避免重复写入
然后运行/usr/bin/bash  /home/01_html/45_TodayExplained/source.sh ，扫描目录，将文件名写入source.txt文件
然后等待60秒钟，再执行如下命令
删除目录 /home/01_html/45_TodayExplained/02_audio ，避免初次使用该目录存在其他文件
然后执行 /usr/bin/bash  /home/01_html/45_TodayExplained/source_move_to_target.sh   转移文件到指定目录，该目录若不存在会自动创建
然后执行 /usr/bin/rclone copy /home/01_html/45_TodayExplained/02_audio do1-1:do1-1/45_TodayExplained/01_audio       rclone长传到onedrive
最后删除目录 /home/01_html/45_TodayExplained/02_audio           释放内存
```

- 源代码

```sh
#!/bin/bash

# 指定下载目录，判断该目录是否小于指定大小
directory="/home/01_html/45_TodayExplained/01_audio"

# 判断目录大小是否小于12GB
size=$(du -s "$directory" | awk '{print $1}')
limit=12000000  # 12GB的大小限制

if [ $size -lt $limit ]; then
  echo "目录大小小于12GB，退出脚本"
  exit 0
fi

# 目录大于等于15GB的情况
echo "目录大小大于等于12GB，执行操作"

# 删除文件 /home/01_html/45_TodayExplained/source.txt
rm -f "/home/01_html/45_TodayExplained/source.txt"

# 运行脚本 /usr/bin/bash /home/01_html/45_TodayExplained/source.sh
/usr/bin/bash "/home/01_html/45_TodayExplained/source.sh"

# 等待30秒
sleep 30

# 删除目录 /home/01_html/45_TodayExplained/02_audio
rm -rf "/home/01_html/45_TodayExplained/02_audio"

# 运行脚本 /usr/bin/bash /home/01_html/45_TodayExplained/source_move_to_target.sh
/usr/bin/bash "/home/01_html/45_TodayExplained/source_move_to_target.sh"

# 执行 rclone 命令，onedrive上该目录需要提前创建
/usr/bin/rclone copy "/home/01_html/45_TodayExplained/02_audio" "do1-1:do1-1/45_TodayExplained/01_audio"

# 等待30秒
sleep 30

# 删除目录，释放硬盘空间 /home/01_html/45_TodayExplained/02_audio
rm -rf "/home/01_html/45_TodayExplained/02_audio"
```

运行上述代码之前，需要注意以下几方面

- 注意远程目录需要提前创建，远程标签要写对

```sh
/usr/bin/rclone copy "/home/01_html/45_TodayExplained/02_audio" "do1-1:do1-1/45_TodayExplained/01_audio"
```

- 注意替换目录`45_TodayExplained`

- 指定执行转移文件的目录大小阈值，如12GB

- 定时任务

```crontab
* * * * * /usr/bin/bash /home/01_html/45_TodayExplained/rclone_limitFileSize.sh
```

- 最后不满设置的目录大小阈值的文件需要手动上传，完成之后别忘了核对云端的文件数量以及删除`01_audio`目录

```sh
rclone copy "/home/01_html/45_TodayExplained/01_audio" "do1-1:do1-1/45_TodayExplained/01_audio"
```




