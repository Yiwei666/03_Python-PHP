# -*- coding: utf-8 -*-
"""
Created on Sun Apr  2 21:45:10 2023

@author: sun78
"""

import re
from bs4 import BeautifulSoup
import chardet


url_01 = '/home/01_html/04_kekemusic/latest.html'

url_02 = '/home/01_html/04_kekemusic/musicUrl.txt'       # 存储所有子页面链接


# 读取HTML文件并检测编码
with open(url_01, 'rb') as f:
    result = chardet.detect(f.read())
    encoding = result['encoding']

# 解析HTML文件
with open(url_01, 'r', encoding=encoding) as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

# 按照要求逐级细化查找
a_tags = soup.find('div', class_='box').find('div', class_='lastPage_left')\
           .find('div', class_='list_box_2').find('ul', id='menu-list').find_all('li')
           


# 遍历每个字符串并提取链接和标题

urlList = []
for i in a_tags:
    print(i.find('h2').find_all('a')[1],"\n")
    urlList.append(str(i.find('h2').find_all('a')[1]))

# print(urlList,"\n")

pattern = r'<a href="([^"]*)" target="_blank" title="([^"]*)">([^<]*)</a>'
for s in urlList:
    match = re.search(pattern, s)
    if match:
        link = match.group(1)        # link
        title = match.group(2)
        print(f"Link: {link}, Title: {title}")   # title
        
        # 将链接保存到文件中
        with open( url_02 , 'a+') as f:
            f.seek(0)
            if link in f.read():
                print("Link already exists in file.")
            elif not link.startswith('http://www.kekenet.com/song/'):           # 判断格式不规范的链接
                print("Invalid link format.")
            else:
                f.write(link + '\n')
