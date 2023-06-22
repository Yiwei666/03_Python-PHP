# import socks
# import socket
from pytube import YouTube
import os

# 设置SOCKS5代理
# socks.set_default_proxy(socks.SOCKS5, "127.0.0.1", 1080)
# socket.socket = socks.socksocket

def download_video(url, save_path, video_name):
    try:
        # 创建YouTube对象
        yt = YouTube(url)
        
        # 获取视频的所有可用格式
        available_streams = yt.streams.filter(progressive=True).all()
        
        # 选择最高质量的视频格式
        video = available_streams[-1]
        
        # 下载视频到指定路径
        video.download(output_path=save_path, filename=video_name)
        
        print("视频下载完成！")
        
    except Exception as e:
        print("下载失败:", str(e))

# 读取txt文件
txt_file_path = '/home/01_html/06_youtubeDownload/01_name+url.txt'

with open(txt_file_path, 'r') as file:
    lines = file.readlines()
    # 获取视频URL
    video_url = lines[0].strip()
    # 获取视频名称
    video_name = lines[1].strip()

# 提供保存视频的路径
save_path = '/home/01_html/01_yiGongZi'

# 调用下载函数并传递URL、保存路径和视频名
download_video(video_url, save_path, video_name)
