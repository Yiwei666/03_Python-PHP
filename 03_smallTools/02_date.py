# -*- coding: utf-8 -*-

import datetime

# 获取当前日期和时间
current_datetime = datetime.datetime.now()

# 构建文件路径
file_path = "/home/01_html/05_douyinDownload/date.txt"

# 以追加模式打开文件，并写入当前日期和时间
with open(file_path, "a") as file:
    file.write(str(current_datetime) + "\n")
