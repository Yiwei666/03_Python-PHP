import sys
from pytube import YouTube

def log_print(log_file_path, *args, **kwargs):
    with open(log_file_path, 'a') as log_file:
        print(*args, **kwargs, file=log_file)

def clear_log_file(log_file_path):
    with open(log_file_path, 'w') as log_file:
        pass

def download_video(url, save_path, video_name, log_file_path):
    log_print(log_file_path, 'youtube链接：' + url)
    log_print(log_file_path, '视频命名：' + video_name)
    try:
        # 创建YouTube对象
        yt = YouTube(url)
        
        # 获取视频的所有可用格式
        available_streams = list(yt.streams.filter(progressive=True).all())
        
        # 选择最高质量的视频格式
        video = available_streams[-1]
        
        # 下载视频到指定路径
        video.download(output_path=save_path, filename=video_name)
        
        log_print(log_file_path, "视频下载完成！")
        
    except Exception as e:
        error_message = "下载失败: " + str(e)
        log_print(log_file_path, error_message)

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

# 日志文件路径
log_file_path = '/home/01_html/06_youtubeDownload/02_ytbVideo_log.txt'

# 清空日志文件
clear_log_file(log_file_path)

# 调用下载函数并传递URL、保存路径、视频名和日志文件路径
download_video(video_url, save_path, video_name, log_file_path)
