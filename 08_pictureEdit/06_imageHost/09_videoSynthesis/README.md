# 1. 项目功能

基于png图片和mp3音频合成mp4视频

# 2. 文件结构

```
creat_video.sh          # 基于png图片和mp3音频合成mp4视频
06_video_list.php       # 列出指定目录下的所有MP4文件名
06_videoPlayer.php      # 跳转播放 06_video_list.php 中点击的MP4文件
```



# 3. 环境配置

### 1. 安装ffmpeg和imagemagick

```bash
sudo apt-get update
sudo apt-get install ffmpeg
sudo apt-get install imagemagick
sudo apt install mpg123
```

### 2. `creat_video.sh`

- 源码：[creat_video.sh](creat_video.sh)

1. 环境变量

```bash
image_dir="/home/01_html/06_videoSynthesis/pic"
music_dir="/home/01_html/06_videoSynthesis/music"
```

2. 查看mp3视频时长

```bash
mpg123 -t filename.mp3
```


💎 **功能**

这段 Bash 脚本的功能是从指定目录中随机选择一定数量的图片和一个音频文件，然后使用这些图片和音频文件制作一个视频。具体的步骤和功能包括：

1. 设置目录：定义`存储图片`和`音频`文件的目录路径。
2. 获取文件列表：从指定目录中读取所有的 `.png` 图片文件和 `.mp3` 音频文件。
3. 用户输入：脚本会提示用户输入要随机选择的图片数量，并且允许用户从列出的音频文件中选择一个。
4. 文件选择和验证：随机选择用户指定数量的图片，并检查用户选择的音频文件是否存在。
5. 获取音频时长：使用 `ffmpeg` 获取选定音频文件的时长，并将其转换为秒。
6. 计算显示时间：根据音频时长和图片数量计算每张图片的显示时间。
7. 图片处理：将选定的图片处理成`统一的分辨率`，并保存到一个临时目录。
8. 创建视频输入文件：生成一个文本文件，指定 ffmpeg 如何合并图片和音频。
9. 视频制作：使用 ffmpeg 根据上述生成的输入文件和音频文件生成视频。
10. 清理和输出：输出视频文件的路径，并清理临时文件。

这个脚本的输出是一个视频文件，其中包含按照音频长度均匀分配的随机选择的图片，配合用户选定的音频背景。


### 3. MP4在线播放

1. `06_video_list.php`

环境变量

```php
$dir = "/home/01_html/06_videoSynthesis";

// 指定传递参数的php脚本名
echo "<a href='06_videoPlayer.php?video=$videoEncoded' target='_blank'>$video</a><br />";
```

2. `06_videoPlayer.php`

环境变量

```php
// 构建视频URL
$videoUrl = "https://chaye.one/06_videoSynthesis/" . urlencode($videoName) . ".mp4";
// 构建字幕URL
$srtUrl = "https://chaye.one/06_videoSynthesis/" . urlencode($videoName) . ".srt";
```

