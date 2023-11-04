# 项目功能

- 下载空中英语教室出品的 listen and talk 博客中的音频

- 官网：https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zb3VuZG9uLmZtL3BvZGNhc3RzLzA5MWVhMzM3LTkwZTItNDAzZC04YzcwLTg2OGFlZTRiMDAyMy54bWw?sa=X&ved=0CAIQ9sEGahcKEwjgyM7j_qmCAxUAAAAAHQAAAAAQEQ


# 文件结构

```
.
├── /01_audio/              # 存放音频的文件夹
├── homepage.html           # 下载的podcast首页
├── nameURL_extract.py      # 提取homepage.html首页中的音频链接
├── nameURL.txt             # 提取到的文件名和链接储存到nameURL.txt文件中
└── download_mp3.sh         # 基于nameURL.txt文件中的链接下载音频
```


# 环境配置

1.下载相应podcast主页面为 homepage.html

```
curl -o homepage.html  https://podcasts.google.com/feed/aHR0cHM6Ly9mZWVkcy5zb3VuZG9uLmZtL3BvZGNhc3RzLzA5MWVhMzM3LTkwZTItNDAzZC04YzcwLTg2OGFlZTRiMDAyMy54bWw?sa=X&ved=0CAIQ9sEGahgKEwjIjuDN6qmCAxUAAAAAHQAAAAAQjgk
```









# 参考资料

- 零代码编程：用ChatGPT批量下载谷歌podcast上的播客音频：https://zhuanlan.zhihu.com/p/661487722

