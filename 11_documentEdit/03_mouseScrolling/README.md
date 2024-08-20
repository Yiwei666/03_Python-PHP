# 1. 项目功能


# 2. 文件结构



# 3. 环境配置

### 1. 

1. 安装`PyAutoGUI`库：

```
pip install pyautogui
```

2. 更新`numpy`库

```
pip install --upgrade numpy
```


### 2. `01_scroll.py`

通过使用 `pyautogui` 模块实现了模拟鼠标滚轮的滚动功能。具体来说，它会每隔3秒向下滚动一次，每次滚动的距离为100个单位，总共持续3000秒（约50分钟）。在此期间，程序会不断地向下滚动，直到达到设置的总滚动时间。

```py
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
```

注意：
- 这个脚本直接模拟键盘按键，因此你需要确保运行脚本时，PDF窗口是活动窗口。
- 如果需要更平滑的滚动，可以减小`scroll_interval`的值，或者通过更复杂的逻辑控制滚动速度。
