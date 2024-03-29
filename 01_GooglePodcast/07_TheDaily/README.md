# 1. 项目功能

- 下载Google Podcast中的 The Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs

# 2. 文件结构

```
source.sh                              # 将指定文件夹下的文件名写入到脚本同级目录下的source.txt文件中        
source_move_to_target.sh               # 将source.txt中记录的文件从一个目录转移到另外一个目录中
rclone_limitFileSize.sh                # 自动化执行文件的转移和上传
replace_directory.py                   # 将指定脚本中的A字符串替换为B字符串
file_diff_checker.py                   # 比较两分代码的不同，打印出不同的行
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

# 指定目录路径，判断该目录是否小于指定大小
directory="/home/01_html/45_TodayExplained/01_audio"

# 判断目录大小是否小于12GB
size=$(du -s "$directory" | awk '{print $1}')
limit=8000000  # 8GB的大小限制，阈值通常需要小于可用内存的一半

if [ $size -lt $limit ]; then
  echo "目录大小小于12GB，退出脚本"
  exit 0
fi

# 目录大于等于15GB的情况
echo "目录大小大于等于12GB，执行操作"

# 删除文件 /home/01_html/45_TodayExplained/source.txt
rm -f "/home/01_html/45_TodayExplained/source.txt"  && sleep 3

# 运行脚本 /usr/bin/bash /home/01_html/45_TodayExplained/source.sh
/usr/bin/bash "/home/01_html/45_TodayExplained/source.sh"

# 等待30秒
sleep 30

# 删除目录 /home/01_html/45_TodayExplained/02_audio
rm -rf "/home/01_html/45_TodayExplained/02_audio"  && sleep 3

# 运行脚本 /usr/bin/bash /home/01_html/45_TodayExplained/source_move_to_target.sh
/usr/bin/bash "/home/01_html/45_TodayExplained/source_move_to_target.sh"  && sleep 3

# 执行 rclone 命令，onedrive上该目录需要提前创建，等待时间需要保证rclone上传完毕，同时新下载的文件大小小于阈值
/usr/bin/rclone copy "/home/01_html/45_TodayExplained/02_audio" "do1-1:do1-1/45_TodayExplained/01_audio"  && sleep 600

# 删除目录，释放硬盘空间 /home/01_html/45_TodayExplained/02_audio
rm -rf "/home/01_html/45_TodayExplained/02_audio"
```

运行上述代码之前，需要注意以下几方面

- 需要在云服务器的项目文件夹中先创建 `01_audio` 文件夹，用于保存 `download_mp3.sh` 脚本下载的音频， `02_audio` 不需要提前创建

- 注意onedrive远程目录`/45_TodayExplained/01_audio`需要提前创建，脚本中远程标签`do1-1:do1-1`要写对，否则rclone上传时会报错

```sh
/usr/bin/rclone copy "/home/01_html/45_TodayExplained/02_audio" "do1-1:do1-1/45_TodayExplained/01_audio"
```

- 结合实际情况，注意替换目录`45_TodayExplained`为相应值，可以采用 `replace_directory.py` 脚本批量转换

- 注意设置rclone的上传时间，该时间在满足完全上传的要求外尽可能小，过了该时间将删除`02_audio`文件夹，考虑服务器上传带宽，digitalocean 对于 8 GB上传一般在15分钟内完成

- 指定执行转移文件的目录大小阈值，如 8 GB，通常设置为可用内存的一半，必须满足在rclone上传期间内，下载量不会达到该阈值

- crontab定时任务，每分钟执行一次，小于设定的内存阈值，则退出脚本，注意更换路径

```crontab
* * * * * /usr/bin/bash /home/01_html/45_TodayExplained/rclone_limitFileSize.sh
```

- 后台运行 `download_mp3.sh` 脚本

```sh
nohup bash download_mp3.sh > output.txt 2>&1 &
```

- 最后不满设置的目录大小阈值的文件需要手动上传，完成之后别忘了核对云端的文件数量以及删除`01_audio`目录释放硬盘容量

```sh
rclone copy "/home/01_html/45_TodayExplained/01_audio" "do1-1:do1-1/45_TodayExplained/01_audio"
```

- 确定所有文件都已上传，并且释放了 `01_audio` 文件夹的内存占用后，取消`rclone_limitFileSize.sh`脚本的crontab定时任务，可以减少cpu占用以及方便管理

```
# * * * * * /usr/bin/bash /home/01_html/45_TodayExplained/rclone_limitFileSize.sh
```


### 4. replace_directory.py  


这段Python代码通过遍历多个Shell脚本文件，实现了批量替换指定字符串的功能，提供了一个映射关系字典来定义替换规则。替换后的内容被写回原文件，实现了自动化的批量字符串替换操作。

运行前需要初始化`replacement_mapping`需要替换的字符串，以及需要处理的脚本名`script_files`


```py
import os

# 定义字符串替换的映射关系
replacement_mapping = {
    "45_TodayExplained": "46_AllEarsEnglishPodcast",
    # 在这里添加更多的映射关系
}

# 获取当前脚本所在目录
current_directory = os.path.dirname(os.path.abspath(__file__))

# 定义要处理的脚本文件列表
script_files = [
    "source.sh",
    "source_move_to_target.sh",
    "rclone_limitFileSize.sh",
    "download_mp3.sh",
]

# 遍历每个脚本文件
for script_file in script_files:
    script_path = os.path.join(current_directory, script_file)
    
    # 打印当前脚本文件名
    print(f"处理脚本文件: {script_file}")
    
    # 打开脚本文件，以二进制模式读取并使用utf-8编码
    with open(script_path, 'rb') as file:
        lines = file.readlines()
        
        for index, line in enumerate(lines):
            # 遍历替换映射关系
            for key, value in replacement_mapping.items():
                if key.encode('utf-8') in line:
                    # 替换字符串并打印替换前后的内容
                    new_line = line.replace(key.encode('utf-8'), value.encode('utf-8'))
                    print(f"  行 {index + 1} 替换前：{line.strip().decode('utf-8', 'ignore')}")
                    print(f"     替换后：{new_line.strip().decode('utf-8', 'ignore')}")
                    # 更新替换后的行
                    lines[index] = new_line
                    
    # 将替换后的内容写回文件
    with open(script_path, 'wb') as file:
        file.writelines(lines)

    print("完成替换\n")
```


### 5. file_diff_checker.py

该Python脚本实现了在用户输入的文件路径下比较两个文件的内容，如果不同，则输出具体不同的行及其内容。

```py
import os

def list_files_in_directory():
    try:
        current_directory = os.getcwd()
        files = [f for f in os.listdir(current_directory) if os.path.isfile(os.path.join(current_directory, f))]
        print("当前目录下的文件:")
        for file in files:
            print(file)
    except Exception as e:
        print(f"发生错误：{e}")

def compare_files(file1_path, file2_path):
    try:
        with open(file1_path, 'r', encoding='utf-8') as file1, open(file2_path, 'r', encoding='utf-8') as file2:
            lines1 = file1.readlines()
            lines2 = file2.readlines()

            # 比较每一行
            for i, (line1, line2) in enumerate(zip(lines1, lines2), start=1):
                if line1 != line2:
                    print(f"第 {i} 行不同:")
                    print(f"文件1: {line1.strip()}")
                    print(f"文件2: {line2.strip()}")
            
            if lines1 == lines2:
                print("两个文件的内容相同")
            else:
                print("两个文件的内容不同")
    except FileNotFoundError:
        print("文件未找到")
    except Exception as e:
        print(f"发生错误：{e}")

# 显示当前目录下的文件
list_files_in_directory()

# 提示用户输入文件路径
file1_name = input("请输入第一个文件的名称：")
file2_name = input("请输入第二个文件的名称：")

# 构建完整的文件路径
file1_path = os.path.join(os.getcwd(), file1_name)
file2_path = os.path.join(os.getcwd(), file2_name)

# 比较文件内容
compare_files(file1_path, file2_path)
```




















