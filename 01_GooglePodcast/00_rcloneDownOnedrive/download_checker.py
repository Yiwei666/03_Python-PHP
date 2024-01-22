import os

# 读取 nameURL.txt 中的文件名和链接
with open('nameURL.txt', 'r', encoding='utf-8') as file:
    name_url_lines = file.readlines()

# 读取 remote_filename.txt 中已下载的文件名（去除后缀）
with open('remote_filename.txt', 'r', encoding='utf-8') as file:
    downloaded_files = [os.path.splitext(line.strip())[0] for line in file.read().splitlines()]

# 遍历每一行，检查文件名是否在已下载文件列表中，如果不在则写入 undownload_mp3.txt
with open('undownload_mp3.txt', 'w', encoding='utf-8') as file:
    for line in name_url_lines:
        filename, url = line.split(',')
        if os.path.splitext(filename.strip())[0] not in downloaded_files:
            file.write(f'{filename.strip()},{url.strip()}\n')

print('未下载的文件名和链接已写入 undownload_mp3.txt 文件。')
