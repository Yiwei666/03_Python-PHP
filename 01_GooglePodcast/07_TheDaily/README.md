# 1. 项目功能

- 下载Google Podcast中的 The Daily 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs

# 2. 文件结构

```
source.sh                              # 将指定文件夹下的文件名写入到脚本同级目录下的source.txt文件中        
source_move_to_target.sh               # 将source.txt中记录的文件从一个目录转移到另外一个目录中
```

# 3. 环境配置


```
curl -o homepage.html https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zaW1wbGVjYXN0LmNvbS81NG5BR2NJbA?sa=X&ved=0CAcQrrcFahgKEwj45J2XsdyDAxUAAAAAHQAAAAAQnxs
```

