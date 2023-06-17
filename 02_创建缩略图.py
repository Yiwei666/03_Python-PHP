import os
from moviepy.video.io.ffmpeg_tools import ffmpeg_extract_subclip
from moviepy.video.io.VideoFileClip import VideoFileClip
import imageio

# 定义视频目录和缩略图目录的路径
video_dir = '/home/01_html/01_yiGongZi/'
thumbnail_dir = '/home/01_html/01_yiGongZi/thumbnails/'

# 获取视频目录下所有的mp4文件
video_files = [file for file in os.listdir(video_dir) if file.endswith('.mp4')]

# 遍历每个视频文件
for video_file in video_files:
    # 构建视频文件的完整路径
    video_path = os.path.join(video_dir, video_file)

    # 创建缩略图文件名
    thumbnail_name = os.path.splitext(video_file)[0] + '.jpg'
    
    # 构建缩略图文件的完整路径
    thumbnail_path = os.path.join(thumbnail_dir, thumbnail_name)

    # 使用moviepy加载视频文件
    video = VideoFileClip(video_path)
    
    # 提取视频的第10秒作为缩略图
    thumbnail = video.get_frame(10)
    
    # 保存缩略图为图像文件
    imageio.imwrite(thumbnail_path, thumbnail)
    
    # 关闭视频文件
    video.close()

    print(f"缩略图 {thumbnail_name} 已创建.")

print("所有视频的缩略图已创建完成.")
