# 项目功能

- 下载Google Podcast中空中英语教室出品的 VOA Learning English 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9sZWFybmluZ2VuZ2xpc2gudm9hbmV3cy5jb20vcG9kY2FzdC8_em9uZUlkPTE2ODk


# 文件结构

```
.
├── /01_audio/              # 存放音频的文件夹
├── homepage.html           # 下载的podcast首页
├── nameURL_extract.py      # 提取homepage.html首页中的音频链接
├── nameURL.txt             # 提取到的文件名和链接储存到nameURL.txt文件中
└── download_mp3.sh         # 基于nameURL.txt文件中的链接下载音频
```


# 环境配置

1. 下载相应podcast主页面为 homepage.html

```bash
curl -o homepage.html  https://podcasts.google.com/feed/aHR0cHM6Ly9sZWFybmluZ2VuZ2xpc2gudm9hbmV3cy5jb20vcG9kY2FzdC8_em9uZUlkPTE2ODk
```

判断 homepage.html 中是否存在listitem标签

```bash
grep 'listitem' homepage.html
```

2. 提取homepage.html文件中的文件名和音频链接，文件名中仅包含中文汉字、英文字母以及阿拉伯数字

在该步骤中，只需要执行以下命令即可

```python
python nameURL_extract.py
```

- 使用python解析homepage.html文件，首先定位所有`role="listitem"`的a标签，然后在每一个a标签中定位`class="LTUrYb"`的div 标签，提取该div标签中的文本作为标题；
- 然后在a标签中定位`jsname="fvi9Ef"`的div 标签，提取该div标签中的`jsdata`属性值，截取该属性值中两个“;”之间的内容作为链接；
- 请将以上标题和链接依次写入到 nameURL.txt文本中，使用英文逗号进行分隔。注意在写入标题前应该检查该标文本题中存在的各类字符，除了中文汉字，英文字母以及阿拉伯数字外的字符，其余字符全部使用"-"替代。

<p align="center">
  <br>
  <br>
  <img src="image/googlepodcast.png" alt="Image Description" width="800">
  <br>
  <br>
</p>

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

3. 基于nameURL.txt中的文件名和链接下载音频

避免重复下载，默认下载间隔为5秒钟，注意创建和修改保存音频文件的路径

```bash
mkdir 01_audio                         # 创建文件夹

chmod +x download_mp3.sh               # 添加执行权限

nohup bash download_mp3.sh &           # 后台运行bash脚本
```


download_mp3.sh

```
#!/bin/bash

input_file="nameURL.txt"
output_path="/home/01_html/07_listen_and_talk/01_audio"

# 读取每一行
while IFS=, read -r filename url; do
    # 移除文件名和网址中的空格
    filename=$(echo "$filename" | tr -d ' ')
    url=$(echo "$url" | tr -d ' ')

    # 检查文件是否已存在
    if [ -e "$output_path/$filename.mp3" ]; then
        echo "File $filename.mp3 already exists in $output_path. Skipping..."
    else
        # 使用curl获取重定向后的mp3链接
        redirected_url=$(curl -s -L -o /dev/null -w '%{url_effective}' "$url")

        # 使用wget下载mp3音频，并以文件名命名，输出到指定路径
        wget -O "$output_path/$filename.mp3" "$redirected_url"

        echo "Downloaded $filename.mp3 from $redirected_url to $output_path"
    fi

    # 添加5秒的延迟
    sleep 5
done < "$input_file"
```

4. 同步到境内云服务器并设置在线播放脚本


83_syn_azure2-1_to_HW.sh

```bash
#!/bin/bash

# 定义源目录和目标服务器信息
source_dir="/home/01_html/08_EngLearnCuriousMind/"
target_server="root@125.46.87.48"
target_dir="/home/01_html/36_EngLearnCuriousMind/"

# 执行rsync命令
rsync -avz "$source_dir" "$target_server:$target_dir"
```


# 参考资料

- 零代码编程：用ChatGPT批量下载谷歌podcast上的播客音频：https://zhuanlan.zhihu.com/p/661487722

