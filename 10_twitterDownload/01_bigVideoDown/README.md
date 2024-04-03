# 1. 项目功能

借助第三方解析网站，实现对twitter上大视频的在线异步下载

# 2. 文件结构

### 1. 文件结构

```
.
├── 05_twitter_bigVideo_download.php       # 获取用户在网页提交的twitter链接
├── 05_twitter_bigfile                     # 项目文件夹
│   ├── 01_url.txt                         # 存储前端写入的链接
│   └── 05_bashDownTwitterBigVideo.sh      # 后端处理脚本
├── 05_twitter_video                       # 储存视频文件夹
│   ├── 1234.mp4                           # 视频
│   ├── ...
```


# 3. 环境配置


### 1. 定时脚本 `05_bashDownTwitterBigVideo.sh`

1. 总体思路

```
编写一个bash脚本完成如下任务
1.检查 /home/01_html/05_twitter_bigfile 目录下是否有 01_url.txt文件，如果没有该文件，则创建该文件。
2. 判断01_url.txt是否为空，如果为空，则结束运行。
3. 如果不为空，则读取其内容，将其作为网址进行访问，如果访问不响应，则退出脚本，并清空01_url.txt文件中的内容
4. 如果访问网址出现响应，则下载该网址对应的mp4视频到/home/01_html/05_twitter_video 文件夹，如果是跳转下载，则相应跳转再下载，并并清空01_url.txt文件中的内容
5. 将下载的mp4命令为 年月日-时分秒-11位随机数字，如：20230722-215223-269940798.mp4
```

2. 权限和定时设置

```sh
chmod +x /home/01_html/05_twitter_bigfile/05_bashDownTwitterBigVideo.sh
* * * * * /usr/bin/bash /home/01_html/05_twitter_bigfile/05_bashDownTwitterBigVideo.sh
```


3. `05_bashDownTwitterBigVideo.sh` 代码

```sh
#!/bin/bash

# 目标目录和文件定义
DIR="/home/01_html/05_twitter_bigfile"
FILE="$DIR/01_url.txt"
VIDEO_DIR="/home/01_html/05_twitter_video"

# 检查01_url.txt文件是否存在，如果不存在则创建
if [ ! -f "$FILE" ]; then
    touch "$FILE"
fi

# 检查文件是否为空
if [ ! -s "$FILE" ]; then
    echo "01_url.txt is empty. Exiting..."
    exit 0
fi

# 读取文件中的URL
URL=$(cat "$FILE")

# 尝试访问URL
if ! curl --output /dev/null --silent --head --fail "$URL"; then
    echo "URL is not responding. Exiting and clearing 01_url.txt..."
    > "$FILE"
    exit 1
fi

# 如果URL响应，则下载视频
# 生成目标文件名
TARGET_NAME=$(date "+%Y%m%d-%H%M%S")-$(tr -dc '0-9' < /dev/urandom | fold -w 11 | head -n 1).mp4
TARGET_PATH="$VIDEO_DIR/$TARGET_NAME"

# 使用wget下载视频，处理可能的重定向
wget -O "$TARGET_PATH" "$URL"

# 清空01_url.txt文件内容
> "$FILE"
```

### 2. `01_url.txt`组和权限设置


```sh
chown www-data:www-data /home/01_html/05_twitter_bigfile/01_url.txt

chmod 644 /home/01_html/05_twitter_bigfile/01_url.txt
```

### 3. 前端获取网址脚本 `05_twitter_bigVideo_download.php`







