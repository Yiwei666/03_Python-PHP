import os
import requests
from PIL import Image
from io import BytesIO
import socks
import socket
import string
import random
from datetime import datetime

# 设置代理
socks.set_default_proxy(socks.SOCKS5, "localhost", 1080)
socket.socket = socks.socksocket

# 文件路径和保存目录
url_file = '03_pic_url.txt'
save_directory = r'D:\software\27_nodejs\海外风景'

# 确保保存目录存在
if not os.path.exists(save_directory):
    os.makedirs(save_directory)

def generate_random_string(length=9):
    characters = string.ascii_letters + string.digits
    return ''.join(random.choice(characters) for i in range(length))

def generate_file_name():
    current_time = datetime.now().strftime('%Y%m%d-%H%M%S')
    random_string = generate_random_string()
    return f"{current_time}-{random_string}.png"

# 读取URL并下载图片
with open(url_file, 'r') as file:
    urls = file.readlines()

for url in urls:
    url = url.strip()
    if url:
        try:
            response = requests.get(url)
            if response.status_code == 200:
                img = Image.open(BytesIO(response.content))
                img = img.convert('RGB')  # 确保转换为PNG兼容格式
                file_name = generate_file_name()
                file_path = os.path.join(save_directory, file_name)
                img.save(file_path, 'PNG')
                print(f"Downloaded and saved: {file_path}")
            else:
                print(f"Failed to download {url}, status code: {response.status_code}")
        except Exception as e:
            print(f"Error downloading {url}: {e}")
