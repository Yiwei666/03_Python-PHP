import os

# 指定目录
directory = '/home/01_html/04_sss60/01_audio'  # 将此路径替换为实际的目录路径

# 列出目录中的所有文件
files = os.listdir(directory)

# 打开 zero-size.txt 文件以写入文件名
with open('zero-size.txt', 'w') as output_file:
    # 遍历目录中的文件
    for filename in files:
        file_path = os.path.join(directory, filename)
        # 检查文件是否为0字节
        if os.path.isfile(file_path) and os.path.getsize(file_path) == 0 and filename.endswith('.mp3'):
            output_file.write(filename + '\n')

print("文件名已写入 zero-size.txt 文件。")
