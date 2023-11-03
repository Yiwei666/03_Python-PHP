# 项目功能


# 文件结构

```
.
├── audioUrl.txt
├── bbc6min.html
├── homeUrl.txt
├── parser_bbc6Min_html.py
├── parser_subPage_html.py
└── subPage.html
```

# 环境配置

1. 下载主页面

```bash
curl -o bbc6min.html https://www.bbc.co.uk/learningenglish/english/features/6-minute-english
```

2. 解析主页面，获取所有主链接

使用python读取同级目录下的bbc6min.html文件，然后进行解析，在class = "threecol"的ul标签中，查找所有class="course-content-item active" 的li 标签，在每个li标签中查找class="text"的div标签，然后在该div标签下查找h2标签，在h2标签中获取href值，并且在href值前添加 "https://www.bbc.co.uk" 字符串，然后写入到 homeUrl.txt 文件中


**parser_bbc6Min_html.py**

```python
from bs4 import BeautifulSoup

# 读取bbc6min.html文件
with open('bbc6min.html', 'r', encoding='utf-8') as file:
    html = file.read()

# 使用Beautiful Soup解析HTML
soup = BeautifulSoup(html, 'html.parser')

# 查找所有class="course-content-item active"的li标签
li_tags = soup.find_all('li', class_='course-content-item active')

# 打开homeUrl.txt文件以便写入结果
with open('homeUrl.txt', 'w', encoding='utf-8') as output_file:
    # 遍历每个li标签
    for li_tag in li_tags:
        # 在li标签中查找class="text"的div标签
        div_tag = li_tag.find('div', class_='text')

        # 在div标签下查找h2标签
        h2_tag = div_tag.find('h2')

        # 获取h2标签中的href值
        href = h2_tag.a['href']

        # 将"https://www.bbc.co.uk"添加到href值前面
        full_url = 'https://www.bbc.co.uk' + href

        # 写入到homeUrl.txt文件中
        output_file.write(full_url + '\n')

print('URLs已写入到homeUrl.txt文件中')

```

3. 下载子页面，获取每个子页面中的音频链接

```
curl -o subPage.html https://www.bbc.co.uk/learningenglish/english/features/6-minute-english_2023/ep-231026
```

使用python读取同级目录下的subPage.html文件，然后进行解析，在class="download bbcle-download-extension-mp3"的a标签中，获取href值，然后以追加的方式写入到 audioUrl.txt 文件中，如果该文件中已经存在该href值，则进行提示并忽略该次写入

**parser_subPage_html.py**

```python
from bs4 import BeautifulSoup

# 读取subPage.html文件
with open('subPage.html', 'r', encoding='utf-8') as file:
    html = file.read()

# 使用Beautiful Soup解析HTML
soup = BeautifulSoup(html, 'html.parser')

# 查找所有class="download bbcle-download-extension-mp3"的a标签
a_tags = soup.find_all('a', class_='download bbcle-download-extension-mp3')

# 打开audioUrl.txt文件以便追加结果
with open('audioUrl.txt', 'a', encoding='utf-8') as output_file:
    # 遍历每个a标签
    for a_tag in a_tags:
        # 获取a标签的href值
        href = a_tag['href']

        # 检查是否已经存在于audioUrl.txt中
        with open('audioUrl.txt', 'r', encoding='utf-8') as existing_file:
            existing_urls = existing_file.read().splitlines()
            if href not in existing_urls:
                # 写入到audioUrl.txt文件中
                output_file.write(href + '\n')
                print(f'已写入URL: {href}')
            else:
                print(f'URL已存在: {href}')

# print('URLs已追加到audioUrl.txt文件中')

```

4. bash脚本循环下载每个子页面，获取所有音频下载链接

有一个homeUrl.txt文件，里面每一行是一个链接，针对每一个链接，写个bash脚本进行如下操作，
首先下载链接，并命名为 subPage.html 文件，每次下载需要为覆盖下载
然后运行parser_subPage_html.py脚本
然后等待1.5秒钟，处理下一个链接


**loopDownUrl.sh**

```bash
#!/bin/bash

# 检查homeUrl.txt文件是否存在
if [ ! -f "homeUrl.txt" ]; then
    echo "homeUrl.txt文件不存在"
    exit 1
fi

# 逐行读取homeUrl.txt文件中的链接
while IFS= read -r url
do
    # 下载链接并命名为subPage.html，强制覆盖已有文件
    wget -O subPage.html -N "$url"
    
    # 检查下载是否成功
    if [ $? -eq 0 ]; then
        # 运行parser_subPage_html.py脚本
        python parser_subPage_html.py

        # 等待1.5秒钟
        sleep 1.5
    else
        echo "链接下载失败: $url"
    fi
done < homeUrl.txt

```




















