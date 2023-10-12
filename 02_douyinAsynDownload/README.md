
# 项目功能

异步下载抖音视频，不需要等待单个视频下载完成再提交下个视频链接，可以连续写入多个链接，存储到txt文件中，每隔固定时间读取下载


# 项目架构

- **架构**

<p align="center">
  <img src="image/structure.png" alt="Image Description" width="700">
</p>

- **项目文件组成**

```
.
├── 01_url_get.php                   # web页面上提醒输入链接，写入到2.txt中
├── 02_douyinDown.py                 # 筛选2.txt中存在，4_success.txt中不存在的链接进行下载，定时每2分钟下载一次
├── 03_add_3_to_2.sh                 # 凌晨5.10分将3_failure.txt中的链接追加到2.txt中，并清空3_failure.txt
├── 04_2_subtract_4.py               # 凌晨5点，筛选2.txt中的链接，保存不存在于4_success.txt中的链接
├── 2.txt                            # 保存所有待下载的链接
├── 3_failure.txt                    # 保存下载失败的链接，定期追加到2.txt中，然后清空
└── 4_success.txt                    # 存储所有下载成功的链接，保证不重复下载

.
├── 02_douyVideo                     # 存储视频的文件夹
│   ├── 20231012-031208.mp4
│   ├── 20231012-031409.mp4
│   ├── 20231012-031608.mp4
│   ├── 20231012-031811.mp4
│   ├── 20231012-032009.mp4
│   ├── ...
```

- **文件权限**

```
-rw-r--r-- 1 root  root  2052 Oct 11 20:32 01_url_get.php
-rwxr-xr-x 1 root  root  2967 Oct 11 21:13 02_douyinDown.py
-rwxr-xr-x 1 root  root   515 Oct 11 21:30 03_add_3_to_2.sh
-rwxr-xr-x 1 root  root   674 Oct 11 21:29 04_2_subtract_4.py
-rw-rw-rw- 1 nginx nginx    0 Oct 12 05:00 2.txt
-rw-rw-rw- 1 root  root     0 Oct 12 05:10 3_failure.txt
-rw-rw-rw- 1 root  root  4867 Oct 12 03:20 4_success.txt

drwxrwxr-x  2 nginx nginx    65536 Oct 12 03:20 02_douyVideo
drwxr-xr-x  2 root  root       157 Oct 11 21:29 05_douyinAsynDload
```


# 环境配置

- **crontab定时任务**

```
*/2 * * * * /home/00_software/01_Anaconda/bin/python /home/01_html/05_douyinAsynDload/02_douyinDown.py

0 5 * * * /home/00_software/01_Anaconda/bin/python /home/01_html/05_douyinAsynDload/04_2_subtract_4.py

10 5 * * * /usr/bin/bash /home/01_html/05_douyinAsynDload/03_add_3_to_2.sh
```

说明：

1. 使用crontab写个定时任务，每隔2分钟执行一次 /home/01_html/05_douyinAsynDload/02_douyinDown.py，python路径为 /home/00_software/01_Anaconda/bin/python

2. 使用crontab写个定时任务，每天5点的时候执行 /home/01_html/05_douyinAsynDload/04_2_subtract_4.py，python路径为 /home/00_software/01_Anaconda/bin/python

3. 使用crontab写个定时任务，每天5点10分的时候执行 /home/01_html/05_douyinAsynDload/03_add_3_to_2.sh，bash路径为 /usr/bin/bash


- **01_url_get.php**

能否写一个php脚本，在web页面访问该php脚本的时候显示一个输入框，提示输入保存字符串，用户输入字符串并点击输入保存按钮后，程序会提取该字符串中的 https链接，并将该链接以追加的方式写入到 2.txt文件中。
下面是一个字符串例子，字符串通常是如下格式 “......”，只需要提取“https://v.douyin.com/abcdef/” 部分链接即可。


- **02_douyinDown.py**

1. 在 /home/01_html/05_douyinAsynDload/2.txt 中每一行可能有一个https链接，在/home/01_html/05_douyinAsynDload/4_success.txt  中每一行可能也有一个https链接，二者也有可能都是空的，现在需要筛选出 在2.txt中有的链接，同时在4_success.txt中没有的链接，并且从筛选出来的链接数组中随机抽取一个链接 赋值为 encoded_url。

```python
# 从文件中读取2.txt中的链接
with open("/home/01_html/05_douyinAsynDload/2.txt", "r") as file:
    links_2 = [line.strip() for line in file.readlines()]

# 从文件中读取4_success.txt中的链接
with open("/home/01_html/05_douyinAsynDload/4_success.txt", "r") as file:
    links_4_success = [line.strip() for line in file.readlines()]

# 找到2.txt中有但4_success.txt中没有的链接
filtered_links = list(set(links_2) - set(links_4_success))

# 随机选择一个链接作为encoded_url
if filtered_links:
    encoded_url = random.choice(filtered_links)
    
    url1 = "https://dlpanda.com/zh-CN/?url="
    url = url1 + encoded_url + "&token=G7eRpMaa"
```

2. 继续修改上述代码，将下载成功的 encoded_url 追加到 /home/01_html/05_douyinAsynDload/4_success.txt中，下载失败的 encoded_url 追加到 3_failure.txt 中。


- **03_add_3_to_2.sh**

说明：写一个bash脚本，将3_failure.txt中的内容追加到2.txt文件中，追加后清空3_failure.txt中的内容。

```bash
#!/bin/bash

# 定义文件名变量
failure_file="/home/01_html/05_douyinAsynDload/3_failure.txt"
success_file="/home/01_html/05_douyinAsynDload/2.txt"

# 检查文件是否存在
if [ -e "$failure_file" ] && [ -e "$success_file" ]; then
    # 追加内容到4_success.txt
    cat "$failure_file" >> "$success_file"

    # 清空3_failure.txt
    > "$failure_file"

    echo "内容已成功追加到$success_file并清空了$failure_file"
else
    echo "文件不存在，请检查文件路径或创建文件"
fi
```

- **04_2_subtract_4.py**

说明：2.txt文件和4_success.txt中 每一行都有可能有一个https链接，现在需要写一个python脚本，删除2.txt文件中已经存在于4_success.txt中的链接，保留剩余的链接到原2.txt文件中。

```python
# 文件名变量
file_2 = '/home/01_html/05_douyinAsynDload/2.txt'
file_4_success = '/home/01_html/05_douyinAsynDload/4_success.txt'

# 读取4_success.txt中的链接
with open(file_4_success, 'r') as success_file:
    success_links = set(line.strip() for line in success_file)

# 读取2.txt文件中的链接，并保留不在success_links中的链接
with open(file_2, 'r') as input_file:
    remaining_links = [line.strip() for line in input_file if line.strip() not in success_links]

# 将剩余的链接写回2.txt文件中
with open(file_2, 'w') as output_file:
    output_file.write('\n'.join(remaining_links))

print("链接已成功处理！")
```





