# 功能

- 下载youtube上空中英语教室的音频

- 这段代码使用Pytube库下载指定YouTube视频的最高质量MP3音频，并在下载完成后显示音频文件的大小（以MB为单位）。用户需要输入视频链接，然后脚本会自动完成下载和显示大小。


# 文件结构

```
ytbAudio_dwn.py              # 下载youtube视频中的的音频，使用视频默认文件名命名，使用本地代理          

ytbAudio_dwn_name.py         # 下载youtube视频中的的音频，升级版本，替换掉名字中的非法字符，避免下载失败

dateNameWrite.py             # 将同级目录下的所有mp3音频文件名和创建日期写入到 mp3_list.txt 文件中，作为存档

nameAudioDate.py             # 对同级目录下所有mp3音频进行重命名，使用创建日期作为文件名

```
