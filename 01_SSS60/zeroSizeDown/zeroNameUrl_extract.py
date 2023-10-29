# 打开A文件以读取内容，包含所有文件名和链接
with open('total_audio_url.txt', 'r') as a_file:
    a_lines = a_file.readlines()

# 打开B文件以读取内容，仅空文件名
with open('zero-size.txt', 'r') as b_file:
    b_lines = b_file.read().splitlines()

# 打开C文件以写入内容
with open('pre_downloadUrl.txt', 'w') as c_file:
    for line in a_lines:
        parts = line.strip().split(',')
        if len(parts) == 2:
            filename, link = parts
            if filename+".mp3" in b_lines:
                c_file.write(f"{filename},{link}\n")

print("匹配的行已写入到 pre_downloadUrl.txt 文件中。")
