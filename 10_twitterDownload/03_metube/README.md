# 1. 项目功能

1. 在云服务器上部署`metube` docker容器，在网页上解析twitter、youtube等视频链接，下载视频到服务器并返回下载后的视频链接

2. `metube`介绍：MeTube是一个自托管的YouTube下载器，提供了`youtube-dl和yt-dlp`工具的Web用户界面。用户可以通过它从YouTube及其他多个站点直接下载视频到本地存储。MeTube设计为在Docker环境中运行，易于设置和扩展。它支持通过环境变量自定义下载路径、用户权限和文件命名规则等设置。此外，它还支持浏览器扩展和iOS快捷方式，提高了访问性和易用性。该项目采用AGPL-3.0许可证，鼓励社区贡献和修改。

3. `yt-dlp`介绍：yt-dlp 是一个命令行程序，用于从YouTube和其他视频网站下载视频。它是youtube-dl的一个分支，添加了许多改进和新特性，比如更快的下载速度、更多的视频网站支持和频繁的更新。用户可以通过yt-dlp下载高清视频，甚至包括4K和8K视频，还支持下载字幕、播放列表和用户频道。它适用于多种操作系统，如Windows、macOS和Linux，是一种非常灵活和强大的媒体下载工具。

# 2. 文件结构

```
05_ffmpeg_tool.sh          # 剪辑视频、提取音频、转移文件、重命名文件等
```


# 3. metube安装和配置

### 1. 启动容器

```bash
# docker run -d -p 8081:8081 -v /path/to/downloads:/downloads ghcr.io/alexta69/metube
docker run -d -p 8081:8081 -v /home/01_html/05_temp_ffmpeg:/downloads ghcr.io/alexta69/metube
```

:star: 项目地址`metube`：https://github.com/alexta69/metube  
:star: 相关项目`yt-dlp`：https://github.com/yt-dlp/yt-dlp  


### 2. 将 Docker 容器的端口映射限制为仅对 `localhost（127.0.0.1）`可见

1. 停止并移除现有容器（如果它已经在运行）：

```bash
docker stop [容器ID或名称]
docker rm [容器ID或名称]
```

您可以使用 `docker ps` 查找正在运行的容器的 `ID 或名称`。docker命令[参考](https://github.com/Yiwei666/03_Python-PHP/wiki/06_docker%E5%91%BD%E4%BB%A4)


```sh
docker ps                              # 列出当前正在运行的 Docker 容器

docker ps -a                           # 该命令将显示所有容器的列表，包括正在运行的容器和已经停止的容器。每个容器的信息包括容器的 ID、镜像名称、状态、创建时间等。

docker-compose up -d                   # 运行命令,让容器在后台运行，
                                       # 命令会根据当前目录下的 docker-compose.yml 文件的定义，启动指定的服务。
                                       # -d 标志表示以守护进程（后台）模式运行服务，即服务将在后台继续运行而不会占用当前终端。

docker restart <容器名称或容器ID>       # 重启容器

docker logs -f <container_name>        # 命令用于查看指定 Docker 容器的日志输出。在这个命令中，-f 参数表示 "follow"，即实时跟踪日志输出的更新。

docker rm -f <container_name>          # 命令用于强制删除指定的 Docker 容器。

docker stop <container_name>           # 命令停止正在运行的容器。
```


2. 使用更新的端口映射重新启动容器：

```bash
docker run -d -p 127.0.0.1:8081:8081 -v /home/01_html/05_temp_ffmpeg:/downloads ghcr.io/alexta69/metube
```

这条命令将容器的 8081 端口仅映射到本地环境，外部无法访问。


3. nginx反向代理

```nginx
location /metube/ {
        proxy_pass http://127.0.0.1:8081/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
}
```

4. htpasswd

- 创建密码文件

```bash
htpasswd -c /path/to/.htpasswd username
```

- 修改nginx配置文件

```nginx
location /metube/ {
        auth_basic "Restricted Access";
        auth_basic_user_file /home/02_htpasswd/.htpasswd;

        proxy_pass http://127.0.0.1:8081/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
}
```


参考博客：[htpasswd 身份验证](https://github.com/Yiwei666/12_blog/blob/main/004/004.md)



# 4. `yt-dlp`安装和配置

### 1. `yt-dlp`下载安装

- 执行文件下载到`/usr/local/bin`目录下

```bash
curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp
chmod a+rx /usr/local/bin/yt-dlp
```

参考资料：https://github.com/yt-dlp/yt-dlp/wiki/Installation

- 判断是否安装成功

```bash
yt-dlp --version
```

- 更新

```bash
yt-dlp -U
```

### 2. 命令行

```bash
yt-dlp [视频URL]                         # 下载视频
yt-dlp -F [视频URL]                      # 列出所有可用的格式
yt-dlp -f [format_code] [视频URL]        # 下载特定格式视频
```


# 4. `ffmpeg` 视频截取和音频提取

### 1. ffmpeg安装

- `ffmpeg` 是一个非常强大的开源工具，用于录制、转换和流式处理音频和视频。它支持几乎所有类型的媒体文件格式，并提供了大量的编解码库，使得用户可以在不同格式之间转换媒体文件。除了基本的转换功能，ffmpeg 还可以用来调整媒体文件的各种参数，如分辨率、比特率等，并支持复杂的视频处理任务，如视频剪辑和效果应用。

- `mediainfo` 是一个轻量级的工具，用于显示多媒体文件的技术信息和标签数据。它可以提供有关音频和视频文件的详细信息，如码率、持续时间、音视频格式、分辨率等。mediainfo 支持多种输出格式，包括文本、HTML 或XML，并且能够与许多GUI（图形用户界面）前端集成，使其易于使用。该工具非常有用，尤其是在需要了解文件编码细节以进行兼容性或质量分析时。

```bash
sudo apt update
sudo apt install ffmpeg
sudo apt-get install mediainfo
```


### 2. `19_ffmpeg_tool.sh`

- 首先提示用户选择功能：
1. 实现mp4音频提取。将脚本的执行目录切换到当前目录下，然后提示用户输入一个mp4视频的文件名，判断视频是否存在，然后再提取该视频的mp3音频，提取后的音频文件名与视频文件名一样，但是后缀不一样，最后打印音频总时长以及音频大小，使用MB作为单位。

2. 截取mp4视频片段。将脚本的执行目录切换到当前目录下，然后提示用户输入一个mp4视频的文件名，判断视频是否存在，然后打印视频的总时长。再提示用户输入需要截取的视频的时段范围，精确到秒，例如 `1:20:30-2:5:1`  即提取时段 1小时20分30秒到2小时5分1秒之间的视频片段，视频片段命名为当前时间 “年月日-时分秒-12位随机大小英文字母数字”组合，例如 `20240127-222114-xqc2warlhhAB.mp4`，最后打印截取的视频时长以及大小，使用MB作为单位


- 功能5的另外一种实现

```sh
    5)
        read -p "请输入MP4视频文件名: " filename
        if [[ -f "$filename" ]]; then
            # 获取视频总时长
            video_duration=$(mediainfo --Inform="General;%Duration%" "$filename")
            video_duration_sec=$(echo "$video_duration/1000" | bc)
            echo "视频总时长: ${video_duration_sec} 秒"

            # 输入截取范围
            read -p "请输入需要截取的视频时段范围（多个时段用逗号分隔，如 01:02:30-01:36:00,02:05:00-03:38:00）: " time_ranges
            IFS=',' read -r -a ranges <<< "$time_ranges"
            filter_complex=""
            concat_input=""
            index=0

            # 为每个片段生成过滤器描述
            for range in "${ranges[@]}"; do
                start_time=$(echo $range | cut -d '-' -f 1)
                end_time=$(echo $range | cut -d '-' -f 2)
                # 注意：这里每个片段都视为独立的输入
                filter_complex+="[$index:v] [$index:a] "
                concat_input+="-ss $start_time -to $end_time -i \"$filename\" "
                ((index++))
            done

            # 连接所有过滤器输入
            filter_complex+="concat=n=$index:v=1:a=1 [v] [a]"

            # 生成最终输出文件名
            output=$(date +"%Y%m%d-%H%M%S")-$(tr -dc 'a-zA-Z0-9' </dev/urandom | head -c 12).mp4
            
            # 执行ffmpeg命令，合并视频
            ffmpeg_cmd="ffmpeg $concat_input -filter_complex \"$filter_complex\" -map \"[v]\" -map \"[a]\" -y \"$output\""
            eval $ffmpeg_cmd

            # 获取合并视频的时长和大小
            merged_duration=$(mediainfo --Inform="General;%Duration%" "$output")
            merged_duration_sec=$(echo "$merged_duration/1000" | bc)
            merged_size=$(mediainfo --Inform="General;%FileSize%" "$output")
            merged_size_mb=$(echo "scale=2; $merged_size/1048576" | bc)
            echo "合并的视频总时长: ${merged_duration_sec} 秒"
            echo "合并的视频大小: ${merged_size_mb} MB"
        else
            echo "文件不存在！"
        fi
        ;;
```



### 3. 环境配置

需要提前安装`ffmpeg和mediainfo`，除此之外，部分功能需要初始化以下参数。

- 功能2：

```sh
save_path="/home/01_html/05_twitter_video/$output"
```

- 功能3：

```sh
mv "$filename" /home/01_html/05_twitter_video/
echo "文件已成功转移到 /home/01_html/05_twitter_video/ 目录"
```

