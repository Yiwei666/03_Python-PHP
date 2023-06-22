import requests
from urllib.parse import quote
import re
from bs4 import BeautifulSoup
from datetime import datetime
import os

# 从文件中读取字符串
with open("/home/01_html/05_douyinDownload/douyin_url.txt", "r") as file:
    text = file.read().strip()

# 创建日志文件
log_file = open("/home/01_html/05_douyinDownload/douyin_log.txt", "w")

# 将print语句输出到日志文件
def log_print(*args, **kwargs):
    print(*args, **kwargs)
    print(*args, **kwargs, file=log_file)

log_print("读取的字符串是:", text)

def extract_links(text):
    pattern = r"(https?://\S+)"
    links = re.findall(pattern, text)
    return links

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
    log_print("提取的链接：", src)

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
        log_print("下载完成！保存为", file_name)
else:
    log_print("未找到source标签")

# 关闭日志文件
log_file.close()
