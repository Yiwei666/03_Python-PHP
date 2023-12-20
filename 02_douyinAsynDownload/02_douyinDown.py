import requests
from urllib.parse import quote
from bs4 import BeautifulSoup
from datetime import datetime
import os
import random

# 定义文件路径变量
links_2_path = "/home/01_html/05_douyinAsynDload/2.txt"
links_4_success_path = "/home/01_html/05_douyinAsynDload/4_success.txt"
failure_log_path = "/home/01_html/05_douyinAsynDload/3_failure.txt"
success_log_path = "/home/01_html/05_douyinAsynDload/5_totalSuccessLog.txt"
download_dir = "/home/01_html/02_douyVideo/"

# 从2.txt读取链接
with open(links_2_path, "r") as file:
    links_2 = [line.strip() for line in file.readlines()]

# 从4_success.txt读取链接
with open(links_4_success_path, "r") as file:
    links_4_success = [line.strip() for line in file.readlines()]

# 找到2.txt中有但4_success.txt中没有的链接
filtered_links = list(set(links_2) - set(links_4_success))

# 随机选择一个链接作为encoded_url
if filtered_links:
    encoded_url = random.choice(filtered_links)
    
    url1 = "https://dlpanda.com/zh-CN/?url="
    url = url1 + encoded_url + "&token=G7eRpMaa"

    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Referer": "https://dlpanda.com/",
    }

    response = requests.get(url, headers=headers)
    content = response.text

    soup = BeautifulSoup(content, "html.parser")
    source_tag = soup.find("source")

    if source_tag:
        src = source_tag.get("src")
        src = "https:" + src.replace("amp;", "")
        print("提取的链接：", src)

        # 从提取的URL下载内容
        response = requests.get(src)

        if response.status_code == 200:
            # 将成功下载的链接追加到4_success.txt
            with open(links_4_success_path, "a") as success_file:
                success_file.write(encoded_url + "\n")
                print("状态码200，写入4_success.txt")

            # 根据当前日期和时间生成文件名
            now = datetime.now()
            file_name = now.strftime("%Y%m%d-%H%M%S") + ".mp4"

            # 将工作目录更改为所需目录
            os.chdir(download_dir)

            # 将内容保存到以生成的文件名命名的文件中
            with open(file_name, "wb") as file:
                file.write(response.content)
                print("下载完成！保存为", file_name)

            # 将下载详细信息追加到5_totalSuccessLog.txt
            with open(success_log_path, "a") as log_file:
                log_file.write(file_name + "," + encoded_url + "\n")
                print("状态码200，写入5_totalSuccessLog.txt")
        else:
            # 将下载失败的链接追加到3_failure.txt
            with open(failure_log_path, "a") as failure_file:
                failure_file.write(encoded_url + "\n")
            print("下载失败！状态码非200，写入3_failure.txt")
    else:
        print("未找到source标签")
        # 将下载失败的链接追加到3_failure.txt
        with open(failure_log_path, "a") as failure_file:
            failure_file.write(encoded_url + "\n")
        print("下载失败！写入3_failure.txt")
else:
    print("所有链接均在4_success.txt中存在。")
