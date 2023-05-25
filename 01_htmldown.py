# -*- coding: utf-8 -*-
"""
Created on Sat May 20 18:25:58 2023

@author: 31598
"""

import requests

from urllib.parse import quote


import re

def extract_links(text):
    pattern = r"(https?://\S+)"
    links = re.findall(pattern, text)
    return links

text = input("请输入字符串: ")
print("输入的字符串是:", text)

# text = '6.69 lcA:/ 复制打开抖音，看看【铁铁的作品】主打一个真实！# 原相机 # 辣妹穿搭  https://v.douyin.com/UhUSHTt/'

video_url = extract_links(text)[0]


print(video_url)

# video_url = "https://v.douyin.com/UhUSHTt/"

encoded_url = quote(video_url, safe="")
print(encoded_url)

url1 = "https://dlpanda.com/zh-CN/?url="

url =  url1 + encoded_url + "&token=G7eRpMaa"

print(url)

# url = "https://dlpanda.com/zh-CN/?url=https%3A%2F%2Fv.douyin.com%2FUhU9QK7%2F&token=G7eRpMaa"
 

headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    "Referer": "https://dlpanda.com/",
}

response = requests.get(url, headers=headers)
content = response.text

# 保存为HTML文件
with open("page.html", "w", encoding="utf-8") as file:
    file.write(content)

print("页面已保存为 page.html 文件")


