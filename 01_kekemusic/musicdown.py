# -*- coding: utf-8 -*-
"""
Created on Tue Apr 11 21:19:39 2023

@author: sun78
"""
   
import requests
import time

from bs4 import BeautifulSoup
import chardet

url_01 = '/home/01_html/04_kekemusic/musicUrl.txt'        # 保存有所有子页面链接
url_02 = '/home/01_html/04_kekemusic/music.html'          # 下载的子页面html文件，覆盖保存
url_03 = '/home/01_html/04_kekemusic/finalmusic.txt'      # 音频链接

with open( url_01 , 'r') as f:   # musicUrl.txt 为保存所有音频链接的文本
    links = f.readlines()

for i, link in enumerate(links):
    if i <= 50:
        try:
            
            link = link.strip()  # 去掉链接两端的空格和换行符
            response = requests.get(link)
            with open(url_02, 'w', encoding='utf-8') as f:
                f.write(response.text)
            time.sleep(600)  # 等待2分钟 /600
            print("已完成单个链接下载")
        
        
            # 读取HTML文件并检测编码
            with open(url_02, 'rb') as f:
                result = chardet.detect(f.read())
                encoding = result['encoding']
                
                
            # 打开 HTML 文件
            with open(url_02, 'r', encoding=encoding) as f:
                html = f.read()
        
            # 解析 HTML 文件
            soup = BeautifulSoup(html, 'html.parser')
        
            # 查找所有的 audio 标签
            audios = soup.find_all('audio')
            time_tag = soup.find('div', class_='e_title').find('time').text[7:]
            # 遍历每个 audio 标签并提取链接
            for audio in audios:
                source = audio.find('source')
                if source:
                    link = source.get('src')
                    print(link)
                    # 将链接保存到文件中
                    with open( url_03 , 'a+') as f:
                        f.seek(0)
                        if link in f.read():
                            print("Link already exists in file.")
                        else:
                            f.write(time_tag +','+ link + '\n')
        except Exception as e:
            print(f"解析链接出现错误：{str(e)}")
                    
    else:
        print("链接序号已超过36，不予解析！")
                    
