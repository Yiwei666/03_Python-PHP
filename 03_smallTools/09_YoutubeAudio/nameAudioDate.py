import os
from datetime import datetime

# 获取当前目录下所有的mp3文件
mp3_files = [file for file in os.listdir() if file.endswith(".mp3")]

for mp3_file in mp3_files:
    try:
        # 获取文件的创建日期
        create_time = datetime.fromtimestamp(os.path.getctime(mp3_file))

        # 格式化日期为指定格式
        formatted_date = create_time.strftime("%Y%m%d-%H-%M-%S")

        # 构建新的文件名
        new_filename = f"{formatted_date}.mp3"

        # 重命名文件
        os.rename(mp3_file, new_filename)

        print(f"已重命名 {mp3_file} 为 {new_filename}")
    except Exception as e:
        print(f"无法处理文件 {mp3_file}: {str(e)}")

print("完成！已对MP3文件进行命名。")
