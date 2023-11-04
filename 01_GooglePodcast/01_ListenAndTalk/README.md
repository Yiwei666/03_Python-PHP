# 项目功能

- 下载空中英语教室出品的 listen and talk 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zb3VuZG9uLmZtL3BvZGNhc3RzLzA5MWVhMzM3LTkwZTItNDAzZC04YzcwLTg2OGFlZTRiMDAyMy54bWw?sa=X&ved=0CAIQ9sEGahcKEwjgyM7j_qmCAxUAAAAAHQAAAAAQEQ


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
curl -o homepage.html  https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zb3VuZG9uLmZtL3BvZGNhc3RzLzA5MWVhMzM3LTkwZTItNDAzZC04YzcwLTg2OGFlZTRiMDAyMy54bWw?sa=X&ved=0CAIQ9sEGahgKEwjIjuDN6qmCAxUAAAAAHQAAAAAQjgk
```

判断 homepage.html 中是否存在listitem标签

```bash
grep 'listitem' homepage.html
```

2. 提取homepage.html文件中的文件名和音频链接，文件名中仅包含中文汉字、英文字母以及阿拉伯数字

- 使用python解析homepage.html文件，首先定位所有role="listitem"的a标签，然后在每一个a标签中定位class="LTUrYb"的div 标签，提取该div标签中的文本作为标题；
- 然后在a标签中定位jsname="fvi9Ef"的div 标签，提取该div标签中的jsdata属性值，截取该属性值中两个“;”之间的内容作为链接；
- 请将以上标题和链接依次写入到 nameURL.txt文本中，使用英文逗号进行分隔。注意在写入标题前应该检查该标文本题中存在的各类字符，除了中文汉字，英文字母以及阿拉伯数字外的字符，其余字符全部使用"-"替代。

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



# 参考资料

- 零代码编程：用ChatGPT批量下载谷歌podcast上的播客音频：https://zhuanlan.zhihu.com/p/661487722

