import os
import shutil

# X目录和Y目录的路径
x_directory = '/home/01_html/04_sss60/01_audio'
y_directory = '/home/01_html/04_sss60/02_zeroSizeList_Audio'

# 列出X目录中大小为0的MP3文件
zero_size_mp3_files = [f for f in os.listdir(x_directory) if f.endswith('.mp3') and os.path.getsize(os.path.join(x_directory, f)) == 0]

# 创建cut_paste.txt文件以写入剪切成功的文件名
with open('cut_paste.txt', 'w') as cut_paste_file:
    # 遍历X目录中的0 KB MP3文件
    for zero_size_mp3_file in zero_size_mp3_files:
        # 构造同名文件在Y目录中的路径
        y_mp3_file = os.path.join(y_directory, zero_size_mp3_file)

        # 如果在Y目录中找到同名非零MP3文件，则剪切到X目录
        if os.path.exists(y_mp3_file) and os.path.getsize(y_mp3_file) > 0:
            shutil.move(y_mp3_file, os.path.join(x_directory, zero_size_mp3_file))
            cut_paste_file.write(zero_size_mp3_file + '\n')

print("剪切成功的MP3文件名已写入 cut_paste.txt 文件。")
