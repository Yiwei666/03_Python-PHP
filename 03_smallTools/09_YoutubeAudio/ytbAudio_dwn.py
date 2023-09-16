import socks
import socket
from pytube import YouTube
import os

# 设置SOCKS5代理
socks.set_default_proxy(socks.SOCKS5, "127.0.0.1", 1080)
socket.socket = socks.socksocket

def download_highest_quality_audio(url):
    try:
        # 创建YouTube对象
        yt = YouTube(url)
        
        # 获取所有音频流
        audio_streams = yt.streams.filter(only_audio=True).order_by('abr').desc()
        
        # 选择最高比特率的音频流（通常是第一个）
        highest_quality_audio = audio_streams[0]
        
        # 下载音频流并保存为MP3，使用视频的默认文件名，并添加.mp3扩展名
        output_path = '.'
        output_filename = yt.title + '.mp3'
        highest_quality_audio.download(output_path=output_path, filename=output_filename)
        
        # 获取下载文件的大小（以字节为单位）
        file_size_bytes = os.path.getsize(os.path.join(output_path, output_filename))
        
        # 转换文件大小为MB
        file_size_mb = file_size_bytes / (1024 * 1024)
        
        print(f"最高质量音频下载完成！保存为 '{output_filename}'，大小：{file_size_mb:.2f} MB")

    except Exception as e:
        print("下载失败:", str(e))

# 提示用户输入要下载的YouTube视频的URL
video_url = input("请输入要下载的YouTube视频链接：")

# 调用下载最高质量音频函数
download_highest_quality_audio(video_url)
