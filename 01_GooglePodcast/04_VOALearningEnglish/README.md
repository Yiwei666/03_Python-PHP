# 1. 项目功能

- 下载Google Podcast中空中英语教室出品的 VOA Learning English 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9sZWFybmluZ2VuZ2xpc2gudm9hbmV3cy5jb20vcG9kY2FzdC8_em9uZUlkPTE2ODk


# 2. 文件结构

```
.
├── 01_VOA_execute_tasks.sh              # 任务自动执行脚本
├── 01_audio                             # 存放音频的文件夹
├── 83_syn_azure2-1_to_AECS_VOA.sh       # 文件夹同步脚本
├── download_Random_mp3.sh               # 基于nameURL.txt文件中的链接下载音频
├── homepage.html                        # 下载的podcast首页
├── nameURL.txt                          # 提取到的文件名和链接储存到nameURL.txt文件中
└── nameURL_extract.py                   # 提取homepage.html首页中的音频链接
```


# 3. 环境配置

### 1. 下载相应podcast主页面为 homepage.html

```bash
curl -o homepage.html  https://podcasts.google.com/feed/aHR0cHM6Ly9sZWFybmluZ2VuZ2xpc2gudm9hbmV3cy5jb20vcG9kY2FzdC8_em9uZUlkPTE2ODk
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

- 使用python解析homepage.html文件，首先定位所有`role="listitem"`的a标签，然后在每一个a标签中定位`class="LTUrYb"`的div 标签，提取该div标签中的文本作为标题；
- 然后在a标签中定位`jsname="fvi9Ef"`的div 标签，提取该div标签中的`jsdata`属性值，截取该属性值中两个“;”之间的内容作为链接；
- 请将以上标题和链接依次写入到 nameURL.txt文本中，使用英文逗号进行分隔。注意在写入标题前应该检查该标文本题中存在的各类字符，除了中文汉字，英文字母以及阿拉伯数字外的字符，其余字符全部使用"-"替代。

**注意：不需要修改`nameURL_extract.py`中的任何参数**

nameURL_extract.py

```python
from bs4 import BeautifulSoup
import re

# 读取homepage.html文件内容
with open('homepage.html', 'r', encoding='utf-8') as file:
    html_content = file.read()

# 使用BeautifulSoup解析HTML
soup = BeautifulSoup(html_content, 'html.parser')

# 定位所有role="listitem"的a标签
listitem_a_tags = soup.find_all('a', role='listitem')

# 打开nameURL.txt文件，准备写入内容
with open('nameURL.txt', 'w', encoding='utf-8') as output_file:
    for a_tag in listitem_a_tags:
        # 在a标签中定位class="LTUrYb"的div标签，提取文本作为标题
        title_div = a_tag.find('div', class_='LTUrYb')
        title = re.sub(r'[^\u4e00-\u9fa5A-Za-z0-9]', '-', title_div.get_text()) if title_div else ''

        # 在a标签中定位jsname="fvi9Ef"的div标签，提取jsdata属性值
        jsdata_div = a_tag.find('div', jsname='fvi9Ef')
        jsdata = jsdata_div['jsdata'] if jsdata_div and 'jsdata' in jsdata_div.attrs else ''
        
        # 截取jsdata属性值中两个“;”之间的内容作为链接
        link = re.search(r';(.*?);', jsdata).group(1) if jsdata else ''

        # 将标题和链接写入nameURL.txt文件，使用英文逗号进行分隔
        output_file.write(f'{title},{link}\n')

print('Extraction and writing to nameURL.txt completed.')
```

### 3. 基于nameURL.txt中的文件名和链接下载音频

避免重复下载，默认下载间隔为5秒钟，注意创建和修改保存音频文件的路径

```bash
mkdir 01_audio                         # 创建文件夹

chmod +x download_Random_mp3.sh               # 添加执行权限

nohup bash download_Random_mp3.sh &           # 后台运行bash脚本
```


- download_Random_mp3.sh：随机下载nameURL.txt中的5个音频

```sh
#!/bin/bash

input_file="nameURL.txt"
output_path="/home/01_html/09_VOALearningEnglish/01_audio"
download_limit=5

# 将所有行读入数组
mapfile -t lines < "$input_file"

# 随机打乱数组
shuf_lines=($(shuf -e "${lines[@]}"))

# 迭代前 download_limit 行（或更少）
for ((i=0; i<download_limit && i<${#shuf_lines[@]}; i++)); do
    line="${shuf_lines[$i]}"
    
    # 提取文件名和URL
    IFS=, read -r filename url <<< "$line"
    
    # 去除文件名和URL中的空格
    filename=$(echo "$filename" | tr -d ' ')
    url=$(echo "$url" | tr -d ' ')

    # 检查文件是否已存在
    if [ -e "$output_path/$filename.mp3" ]; then
        echo "文件 $filename.mp3 已存在于 $output_path。跳过..."
    else
        # 使用curl获取重定向后的mp3链接
        redirected_url=$(curl -s -L -o /dev/null -w '%{url_effective}' "$url")

        # 使用wget下载mp3音频，并以文件名命名，输出到指定路径
        wget -O "$output_path/$filename.mp3" "$redirected_url"

        echo "已从 $redirected_url 下载 $filename.mp3 到 $output_path"
    fi

    # 添加5秒的延迟
    sleep 5
done
```


### 4. 设置自动化任务脚本

```
1. 先删除如下文件夹
/home/01_html/09_VOALearningEnglish/01_audio
2. 然后创建文件夹
/home/01_html/09_VOALearningEnglish/01_audio
3. 然后运行如下脚本
/usr/bin/bash  /home/01_html/09_VOALearningEnglish/download_Random_mp3.sh
4. 然后等待10分钟
5. 最后运行如下脚本
/usr/bin/bash  /home/01_html/09_VOALearningEnglish/83_syn_azure2-1_to_AECS_VOA.sh


设置cron定时任务，每天凌晨1点执行如下命令
/usr/bin/bash  /home/01_html/09_VOALearningEnglish/01_VOA_execute_tasks.sh
```


对应脚本 01_VOA_execute_tasks.sh

```sh
#!/bin/bash

# Step 1: 删除文件夹
rm -r /home/01_html/09_VOALearningEnglish/01_audio

# Step 2: 创建文件夹
mkdir -p /home/01_html/09_VOALearningEnglish/01_audio

# Step 3: 运行第一个下载脚本
/usr/bin/bash /home/01_html/09_VOALearningEnglish/download_Random_mp3.sh

# Step 4: 等待10分钟
sleep 600  # 10分钟 = 10 * 60 秒

# Step 5: 运行第二个同步脚本
/usr/bin/bash /home/01_html/09_VOALearningEnglish/83_syn_azure2-1_to_AECS_VOA.sh

```

注意：同步脚本如下所示

### 5. 同步到境内云服务器并设置在线播放脚本


83_syn_azure2-1_to_AECS_VOA.sh

```bash
#!/bin/bash

# 定义源目录和目标服务器信息
source_dir="/home/01_html/09_VOALearningEnglish/"
target_server="root@39.106.188.186"
target_dir="/home/01_html/39_VOALearningEnglish/"

# 执行rsync命令
rsync -avz "$source_dir" "$target_server:$target_dir"
```

### 6. 设置目标服务器定时删除文件夹任务

目的：避免目标文件夹中积累的音频文件过多，每天凌晨1点定时删除掉文件夹

```cron
0 1 * * * rm -r /home/01_html/39_VOALearningEnglish/01_audio
```




# 参考资料

- 零代码编程：用ChatGPT批量下载谷歌podcast上的播客音频：https://zhuanlan.zhihu.com/p/661487722

