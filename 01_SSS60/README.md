# 项目功能

使用python和bash脚本下载 scientific American podcasts 音频

# 项目结构

```
.
├── sss.html                    # 临时文件，内含多个音频文件，大概有300页
├── parse_html.py               # 解析sss.html文件，将音频文件名和链接写入 audio_url.txt 文件
├── audio_url.txt               # 临时文件，存储每一页中的音频链接
├── total_audio_url.txt         # 储存所有的音频链接和音频文件名
├── download_audio.sh           # 下载audio_url.txt中重定向后的音频文件
├── total_control.sh            # 控制单页的音频链接下载
├── page_range_control.sh       # 控制指定页码范围内的音频链接下载
├── 01_audio                    # 存储音频的文件夹
└── sync_audio.sh               # 同步音频到其他云服务器


total_control.sh 脚本内容：

1. 首先执行 rm -rf  sss.html  audio_url.txt
2. 再执行 curl -o sss.html https://www.scientificamerican.com/podcasts/?page=2
3. 然后执行python脚本 parse_html.py，相应脚本运行结束后
4. 接着 运行bash脚本 download_audio.sh
5. 再接着执行 cat audio_url.txt >> total_audio_url.txt
6. 再执行 ls -l   /home/01_html/04_sss60/01_audio   | grep "^-" | wc -l

```
