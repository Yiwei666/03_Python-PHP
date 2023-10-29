# 项目功能



# 文件结构

```
.
├── sss.html                    # 临时文件，内含多个音频文件，大概有300页
├── parse_html.py               # 解析sss.html文件，将音频文件名和链接写入 audio_url.txt 文件
├── audio_url.txt               # 临时文件，存储每一页中的音频链接
├── total_audio_url.txt         # 储存所有的音频链接和音频文件名
├── download_audio.sh           # 下载audio_url.txt中重定向后的音频文件
├── total_control.sh            # 控制单页的音频链接下载
├── page_range_control.sh       # 控制指定页码范围内的音频链接下载
├── /01_audio/                    # 存储音频的文件夹
└── sync_audio.sh               # 同步音频到其他云服务器


.

├── 02_zeroSizeList_Audio

├── check_mp3.sh


├── download_audio_zeroSizeList.sh

├── zeroNameUrl_extract.py
├── pre_downloadUrl.txt
├── cut_pastAudio.py
├── cut_paste.txt

├── total_audio_url.txt

├── zero-size.txt

└── zeroSize_audioName_find.py



```
