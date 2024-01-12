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
