# 1. 项目功能

- 下载Google Podcast中的 Simple English News Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5idXp6c3Byb3V0LmNvbS85Njg5ODMucnNz?sa=X&ved=0CAcQrrcFahgKEwjYqeCCrLyEAxUAAAAAHQAAAAAQ9Tc
  
# 2. 文件结构

1. 从google podcast下载音频及上传到onedrive的文件结构

```
├── 01_audio            # 存储音频的文件夹
├── homepage.html
├── nameURL_extract.py
├── nameURL.txt
├── analyze_filenames.py
├── download_mp3.sh
├── rclone_limitFileSize.sh
├── source.sh
├── source_move_to_target.sh
└── output.txt
```

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

在该步骤中，只需要执行以下命令即可生成 `nameURL.txt` 文件

```python
python nameURL_extract.py
```

note：记得将生成的 `nameURL.txt ` 文件上传至github

### 3. 分析 nameURL.txt 文件中是否有重复的文件名，以及重复文件名出现的次数

```python
python analyze_filenames.py
```


### 4. 在 windows 运行`replace_directory.py`脚本

```py
python replace_directory.py
```

```py
import os

# 定义字符串替换的映射关系
replacement_mapping = {
    #"45_TodayExplained": "46_AllEarsEnglishPodcast",
    # "46_AllEarsEnglishPodcast": "47_StuffYouShouldKnow"
    # "47_StuffYouShouldKnow": "48_EspressoEnglish"
    # "48_EspressoEnglish": "49_CoffeeBreakEnglish"
    # "49_CoffeeBreakEnglish": "50_TheEnglishWeSpeak"
    "50_TheEnglishWeSpeak": "51_SEND7"
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

运行前需要修改下面部分的代码

```py
replacement_mapping = {
    #"45_TodayExplained": "46_AllEarsEnglishPodcast",
    # "46_AllEarsEnglishPodcast": "47_StuffYouShouldKnow"
    # "47_StuffYouShouldKnow": "48_EspressoEnglish"
    # "48_EspressoEnglish": "49_CoffeeBreakEnglish"
    # "49_CoffeeBreakEnglish": "50_TheEnglishWeSpeak"
    "50_TheEnglishWeSpeak": "51_SEND7"
    # 在这里添加更多的映射关系 
}
```



上述python脚本将自动配置如下脚本的路径

```
.
├── download_mp3.sh
├── rclone_limitFileSize.sh
├── source.sh                        # 将指定文件夹下的文件名写入到脚本同级目录下的source.txt文件中
└── source_move_to_target.sh         # 将source.txt中记录的文件从一个目录转移到另外一个目录中
```

将上述脚本中的`"50_TheEnglishWeSpeak"`字符串更改为`"51_SEND7"`

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

```sh
mkdir 01_audio
```

- 注意onedrive远程目录`/51_SEND7/01_audio`需要提前创建
```sh
rclone mkdir do1-1:do1-1/51_SEND7/01_audio
```


- `rclone_limitFileSize.sh`脚本中远程标签`do1-1:do1-1`要写对，否则rclone上传时会报错

```sh
/usr/bin/rclone copy "/home/01_html/51_SEND7/02_audio" "do1-1:do1-1/51_SEND7/01_audio"  && sleep 1200
```


### 6. 设置`rclone_limitFileSize.sh`脚本参数

- 注意设置rclone的上传时间，该时间在满足完全上传的要求外尽可能小，过了该时间将删除`02_audio`文件夹，考虑服务器上传带宽，digitalocean 对于 8 GB上传一般在15分钟内完成

- 指定执行转移文件的目录大小阈值，如 8 GB，通常设置为可用内存的一半，必须满足在rclone上传期间内，下载量不会达到该阈值

```sh
# 执行 rclone 命令，onedrive上该目录需要提前创建，等待时间需要保证rclone上传完毕，同时新下载的文件大小小于阈值
/usr/bin/rclone copy "/home/01_html/55_HubermanLab/02_audio" "do1-1:do1-1/51_SEND7/01_audio"  && sleep 20

# 删除目录，释放硬盘空间 /home/01_html/51_SEND7/02_audio
rm -rf "/home/01_html/51_SEND7/02_audio"
```

注意上述代码中，`sleep`命令会在`rclone copy`命令复制完成之后再执行，所以`sleep`后面的暂停时间可以设置的小一些，但是不能够没有`sleep`命令，因为没有的话会在复制还未完成之后，就会执行下一条删除命令

### 7. 创建`rclone_limitFileSize.sh`相关定时任务

- crontab定时任务，每分钟执行一次，小于设定的内存阈值，则退出脚本，注意更换路径

```crontab
* * * * * /usr/bin/bash /home/01_html/51_SEND7/rclone_limitFileSize.sh
```


### 8. 后台运行音频下载脚本

- 后台运行 `download_mp3.sh` 脚本，在项目文件夹下执行如下命令

```sh
nohup bash download_mp3.sh > output.txt 2>&1 &
```

- 实时监视下载信息

```sh
tail -f output.txt
```

- 要取消在 Ubuntu 中运行的 `nohup` 命令，您可以使用 ps 命令查找相关的进程 `ID（PID）`，然后使用 `kill` 命令终止该进程。以下是具体步骤：

1. 使用以下命令查找与您的 `download_mp3.sh` 脚本相关的进程 `ID（PID）`：

```sh
ps aux | grep "download_mp3.sh"
```

2. 在输出中找到与您的脚本相关的 `PID`，然后使用 `kill` 命令终止该进程。假设 PID 为 `<your_pid>`，则执行以下命令：

```sh
kill <your_pid>
```

3. 暂停进程

```sh
kill -STOP pid
```

执行完后之后，下面的T代表暂停

```
root     2577942  0.0  0.3   7368  3092 pts/3    T    20:35   0:00 bash /home/01_html/55_HubermanLab/download_mp3.sh
```

4. 继续执行脚本

```sh
kill -CONT pid
```



### 9. 释放云服务器存储

- 最后不满设置的目录大小阈值的文件需要手动上传

```sh
rclone copy "/home/01_html/51_SEND7/01_audio" "do1-1:do1-1/51_SEND7/01_audio"
```

- rclone上传除音频文件夹外其余的文件，不覆盖已存在的目标文件

```sh
rclone copy --ignore-existing "/home/01_html/51_SEND7" "do1-1:do1-1/51_SEND7"
```

- 完成之后别忘了核对onedrive云端的文件数量以及删除`01_audio`目录释放硬盘容量

```sh
rclone size  do1-1:do1-1/51_SEND7/01_audio
```

可以通过以下命令判断rclone进程是否结束

```sh
ps aux | grep rclone
```


- 确定所有文件都已上传，并且释放了 `01_audio` 文件夹的内存占用后，取消`rclone_limitFileSize.sh`脚本的crontab定时任务，可以减少cpu占用以及方便管理

```
# * * * * * /usr/bin/bash /home/01_html/45_TodayExplained/rclone_limitFileSize.sh
```


### 10. 设置音频播放脚本和音频下载脚本

- 更改音频播放脚本中`51_SEND7.php`中的路径变量

- 更改音频下载脚本中`rclone_51_SEND7.sh`中的路径变量，从onedrive中下载指定数量的音频

```
1. 删除目录 /home/01_html/51_SEND7/01_audio 
2. 创建目录 /home/01_html/51_SEND7/01_audio 
3. 使用rclone读取远程位置 AECS-1109:AECS-1109/51_SEND7/01_audio  下的所有文件名到一个数组中
4. 从上述数组中随机选取10个后缀名为mp3的文件名，下载到指定目录 /home/01_html/51_SEND7/01_audio
```

### 11. 设置crontab定时任务，定时执行`rclone_51_SEND7.sh`脚本下载指定数量音频

```crontab
5 23 * * * /usr/bin/bash /home/01_html/51_SEND7/rclone_51_SEND7.sh
```


# 参考资料

rclone上传和下载测试：https://github.com/Yiwei666/12_blog/wiki/08_rclone%E8%BF%9E%E6%8E%A5%E5%88%B0OneDrive#2-%E4%B8%8A%E4%BC%A0%E5%92%8C%E4%B8%8B%E8%BD%BD%E6%B5%8B%E8%AF%95














