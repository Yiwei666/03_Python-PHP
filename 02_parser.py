# -*- coding: utf-8 -*-
"""
Created on Sat May 20 19:38:53 2023

@author: 31598
"""

from bs4 import BeautifulSoup

# 读取HTML文件
with open("page.html", "r", encoding="utf-8") as file:
    content = file.read()

# 解析HTML
soup = BeautifulSoup(content, "html.parser")

# 查找第一个source标签
source_tag = soup.find("source")

if source_tag:
    src = source_tag.get("src")
    src = src.replace("amp;", "")  # 删除字符串中的 "amp;"
    print("提取的链接：", src)
else:
    print("未找到source标签")
