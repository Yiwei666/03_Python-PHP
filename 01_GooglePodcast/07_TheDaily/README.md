# 1. 项目功能

- 下载Google Podcast中的 The Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs

# 2. 文件结构

```
source.sh                              # 将指定文件夹下的文件名写入到脚本同级目录下的source.txt文件中        
source_move_to_target.sh               # 将source.txt中记录的文件从一个目录转移到另外一个目录中
```

# 3. 环境配置

### 1. 音频下载

```
curl -o homepage.html https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs
```


### 2. 文件转移

由于云服务器硬盘容量有限，需要将部分已下载的音频上传至onedrive云端中，因此需要将已下载的音频从下载文件夹转移到上传文件夹中

1. source.sh

```sh
#!/bin/bash

# 获取脚本所在目录
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# 指定目录
directory="/home/01_html/42_TheDaily/01_audio"

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









