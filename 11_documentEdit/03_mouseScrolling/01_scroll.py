import pyautogui
import time

# 设置滚动间隔时间（以秒为单位）
scroll_interval = 3  # 每0.1秒滚动一次
scroll_duration = 3000   # 总滚动时间为300秒

# 每次滑动的距离（正数向上滑动，负数向下滑动）
scroll_amount = -100

# 模拟滚动
start_time = time.time()
while time.time() - start_time < scroll_duration:
    pyautogui.scroll(scroll_amount)  # 滚动指定的距离
    time.sleep(scroll_interval)  # 等待 scroll_interval 秒

print("Scrolling completed.")
