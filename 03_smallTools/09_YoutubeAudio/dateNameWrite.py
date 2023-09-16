import os
from datetime import datetime

# 获取当前目录下所有的mp3文件
mp3_files = [file for file in os.listdir() if file.endswith(".mp3")]

# 打开一个文本文件来保存结果，使用UTF-8编码
with open("mp3_list.txt", "w", encoding="utf-8") as txt_file:
    for mp3_file in mp3_files:
        # 获取文件的创建日期
        create_time = datetime.fromtimestamp(os.path.getctime(mp3_file))
        
        # 将日期和文件名以tab分隔的形式写入文本文件
        txt_file.write(f"{create_time}\t{mp3_file}\n")

print("完成！已将文件名和创建日期写入mp3_list.txt文件。")
