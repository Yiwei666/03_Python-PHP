# 1. 项目功能

- 下载Google Podcast中的 Simple English News Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5idXp6c3Byb3V0LmNvbS85Njg5ODMucnNz?sa=X&ved=0CAcQrrcFahgKEwjYqeCCrLyEAxUAAAAAHQAAAAAQ9Tc
  
# 2. 文件结构


# 3. 环境配置

1. 下载相应podcast主页面为 homepage.html

```bash
curl -o homepage.html https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5idXp6c3Byb3V0LmNvbS85Njg5ODMucnNz?sa=X&ved=0CAcQrrcFahgKEwjYqeCCrLyEAxUAAAAAHQAAAAAQ9Tc
```

判断 homepage.html 中是否存在listitem标签

```bash
grep 'listitem' homepage.html
```

2. 提取homepage.html文件中的文件名和音频链接，文件名中仅包含中文汉字、英文字母以及阿拉伯数字

在该步骤中，只需要执行以下命令即可

```python
python nameURL_extract.py
```


