# 1. 项目功能

- 下载Google Podcast中的 Planet Money 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5ucHIub3JnLzUxMDI4OS9wb2RjYXN0LnhtbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQxxU

# 2. 文件结构

```
.
├── 01_PlanetMoney_execute_tasks.sh              # 下载流程自动化脚本
├── 01_audio                                     # 存储下载音频的文件夹，无需提前创建
├── 83_syn_azure2-1_to_AECS_PlanetMoney.sh       # 同步脚本
├── download_Random_mp3.py                       # 基于nameURL.txt链接下载音频的python脚本
├── homepage.html                                # google podcast中 Planet Money 页面
├── logDown.txt                                  # 下载日志
├── nameURL.txt                                  # 存储音频链接的文本
└── nameURL_extract.py                           # 提取音频链接的脚本
```

# 3. 环境配置

1. 下载含有所有音频链接的html

```
curl -o homepage.html https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5ucHIub3JnLzUxMDI4OS9wb2RjYXN0LnhtbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQxxU
```

2. 提取音频链接，得到 nameURL.txt 

```
python nameURL_extract.py 
```

3. 配置音频下载脚本download_Random_mp3.py

```python
import os
import random
import time
import requests
from urllib.parse import urlparse

input_file = "/home/01_html/41_PlanetMoney/nameURL.txt"
output_path = "/home/01_html/41_PlanetMoney/01_audio"
download_limit = 6
log_file = "/home/01_html/41_PlanetMoney/logDown.txt"

# 检查日志文件是否存在，不存在则创建
if not os.path.exists(log_file):
    open(log_file, 'w').close()

# 读取文件内容
with open(input_file, 'r') as file:
    lines = file.readlines()

# 随机打乱数组
random.shuffle(lines)

# 迭代前 download_limit 行（或更少）
for line in lines[:download_limit]:
    # 提取文件名和URL
    filename, url = map(str.strip, line.split(','))

    # 获取当前时间（北京时间）
    current_time = time.strftime("%Y%m%d-%H%M%S", time.localtime())

    # 检查文件是否已存在
    if os.path.exists(os.path.join(output_path, f"{filename}.mp3")):
        with open(log_file, 'a') as log:
            log.write(f"{current_time},{filename},{url}: 文件 {filename}.mp3 已存在于 {output_path}。跳过...\n")
        print(f"文件 {filename}.mp3 已存在于 {output_path}。跳过...")
    else:
        # 使用requests获取重定向后的mp3链接
        redirected_url = requests.head(url, allow_redirects=True).url

        # 使用requests下载mp3音频，并以文件名命名，输出到指定路径
        response = requests.get(redirected_url)
        with open(os.path.join(output_path, f"{filename}.mp3"), 'wb') as audio_file:
            audio_file.write(response.content)

        with open(log_file, 'a') as log:
            log.write(f"{current_time},{filename},{url}\n")
        print(f"{current_time},{filename},{url} 下载完成并保存到 {output_path}")

    # 添加5秒的延迟
    time.sleep(5)
```

主要修改如下部分，注意绝对路径以及随机下载的音频数量

```python
input_file = "/home/01_html/41_PlanetMoney/nameURL.txt"
output_path = "/home/01_html/41_PlanetMoney/01_audio"
download_limit = 6
log_file = "/home/01_html/41_PlanetMoney/logDown.txt"
```

4. 























