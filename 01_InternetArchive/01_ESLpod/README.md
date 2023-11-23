# 项目功能

下载 English as a Second Language (ESL) Podcast 音频

Internet Archive网址： https://archive.org/details/ipodcast

# 文件结构

- 官网一级目录

```
a-day-in-the-life-of-jeff/	          09-Sep-2022 23:36	-
a-day-in-the-life-of-lucy/	          10-Sep-2022 04:22	-
cultural-english/	                    10-Sep-2022 17:06	-
daily-english/	                      10-Sep-2022 17:13	-
english-for-business-meetings/       	10-Sep-2022 02:08	-
history/	                            14-Dec-2019 11:38	-
interview-questions-answered/	        10-Sep-2022 02:36	-
introduction-to-the-united-states/	  10-Sep-2022 04:55	-
using-english-at-work/	              09-Sep-2022 21:37	-
ESLPod.jpg	                          08-Dec-2019 14:55	152.0K
ESLPod_thumb.jpg	                    08-Dec-2019 15:47	3.6K
__ia_thumb.jpg	                      12-Feb-2021 00:38	8.8K
eslpod.xml	                          14-Dec-2019 11:38	1.1M
ipodcast_archive.torrent	            06-Jun-2022 09:27	2.5M
ipodcast_files.xml	                  10-Sep-2022 17:13	3.5M
ipodcast_meta.sqlite	                14-Dec-2019 11:38	4.3M
ipodcast_meta.xml	                    10-Sep-2022 14:36	1.1K
```

1. 上面一级目录下的每个子文件夹是一个主题的音频，需要从子文件夹中获取音频真正的下载链接
2. 主页中显示的下载链接需要重定向，每个主题中音频下载链接的前缀往往都不同
```
https://archive.org/details/ipodcast/a-day-in-the-life-of-lucy/dill10.mp3                         # 主页音频下载链接，基于该链接下载的文件大小一样，且都无法播放

https://ia902808.us.archive.org/35/items/ipodcast/a-day-in-the-life-of-lucy/dill10.mp3            # a-day-in-the-life-of-lucy/ 子目录中的音频链接，该链接可用于音频下载
https://ia802808.us.archive.org/35/items/ipodcast/cultural-english/ce001.mp3                      # cultural-english/	子目录中的音频链接，该链接可用于音频下载
```



- 项目目录

```
.
├── 01_audio                         # 保存音频的文件夹
├── 01_success.txt                   # 保存下载成功的音频网址
└── downloadArchive_mp3.sh           # 下载音频的bash脚本
```

# 环境配置



