# 1. 项目功能

每隔若干分钟截屏一次，存储到指定目录


# 2. 文件结构

```py
import time
from PIL import ImageGrab
import os

# 指定截图保存的文件夹路径
save_folder = r"D:\onedrive\图片\06_github_picture\printscreen\tmp_printscreen"

# 创建保存文件夹（如果不存在）
if not os.path.exists(save_folder):
    os.makedirs(save_folder)

# 循环截图
while True:
    try:
        # 获取当前时间并格式化为字符串（精确到秒，带分隔符）
        current_time = time.strftime("%Y-%m-%d_%H%M%S")
        
        # 截图并保存到文件夹，使用时间作为文件名
        screenshot = ImageGrab.grab()
        screenshot.save(os.path.join(save_folder, f"screenshot_{current_time}.png"))
        
        print(f"截图已保存: screenshot_{current_time}.png")
        
        # 每隔15分钟截图一次
        time.sleep(120)  # 900秒 = 15分钟
    except KeyboardInterrupt:
        # 如果用户按下Ctrl+C，退出循环
        break
```
