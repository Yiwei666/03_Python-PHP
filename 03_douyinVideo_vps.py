import requests
from urllib.parse import quote
import re
from bs4 import BeautifulSoup
from datetime import datetime
import os

def extract_links(text):
    pattern = r"(https?://\S+)"
    links = re.findall(pattern, text)
    return links

text = input("请输入字符串: ")
print("输入的字符串是:", text)

video_url = extract_links(text)[0]
encoded_url = quote(video_url, safe="")
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
    src = src.replace("amp;", "")
    print("提取的链接：", src)

    # Download the content from the extracted URL
    response = requests.get(src)
    content = response.content

    # Generate the file name based on the current date and time
    now = datetime.now()
    file_name = now.strftime("%Y%m%d-%H%M%S") + ".mp4"

    # Change the current working directory to the desired directory
    os.chdir("/home/01_html/02_douyVideo")

    # Save the content to a file with the generated file name
    with open(file_name, "wb") as file:
        file.write(content)
        print("下载完成！保存为", file_name)
else:
    print("未找到source标签")
