### 项目结构
```
服务器A
├── 02_flv
│   ├── 10_1_hale-herbicide.mp4
│   ├── 11_1_imbroglio-incubate.mp4
│    ....
│   ├── convert_videos.sh              # 将非标准mp4格式转换为标准mp4格式
│   ├── nohup.out
│   ├── obtain_mp4URL.sh               # 获取同级目录下所有mp4的文件名，构造下载链接
│   └── output.txt                     # 获取的下载链接txt文件


服务器B
├── 01_yubeiduan
│   ├── 9_3_graft-gregarious.mp4
│   ├── 9_4_gratuitous-gullible_standard.mp4
│    ....
│   ├── download_mp4_log.sh             # 基于output.txt文本中的下载链接下载视频的脚本
│   ├── download_mp4.sh                 # 基于output.txt文本中的下载链接下载视频的脚本
│   ├── failed_links.txt                # 视频下载过程的报错链接
│   └── output.txt


```


### 1. ubuntu中FFmpeg安装
---

要在Ubuntu系统中安装FFmpeg，可以按照以下步骤进行操作：

1. 打开终端（Terminal）。

2. 运行以下命令以更新软件包列表：

```
sudo apt update
```

3. 安装FFmpeg和相关依赖包。运行以下命令：

```
sudo apt install ffmpeg
```

这将安装FFmpeg及其所需的所有库和工具。

4. 系统将提示你输入管理员密码（sudo密码）。输入密码并按回车键。

等待安装完成。安装过程可能需要一些时间，具体取决于你的网络连接速度和系统性能。

5. 安装完成后，可以在终端中运行以下命令来验证安装是否成功：

```
ffmpeg -version
```
如果成功安装，将显示FFmpeg的版本信息。

现在你已经成功在Ubuntu系统中安装了FFmpeg。你可以使用FFmpeg进行音视频处理、转码、剪辑等操作。


### 2. FLV格式转为MP4格式
---

要将FLV格式视频转换为MP4格式，可以使用FFmpeg工具进行转码。请按照以下步骤进行操作：

1. 打开终端（Terminal）。

2. 进入包含要转换的FLV文件的目录。可以使用cd命令切换到相应的目录。例如：

```
cd /path/to/flv/videos
```
将/path/to/flv/videos替换为实际的FLV文件所在路径。

3. 运行以下命令以将FLV文件转换为MP4格式：

```
ffmpeg -i input.flv -c:v libx264 -c:a aac output.mp4
```
其中，input.flv是要转换的FLV文件名，output.mp4是转换后的MP4文件名。你可以根据需要修改这些文件名。

4. 等待转码过程完成。转码时间取决于视频的大小和你的系统性能。

5. 转码完成后，你可以在同一目录下找到生成的MP4文件。

请注意，上述命令使用了默认的视频编码器（libx264）和音频编码器（aac）。如果你希望使用其他编码器，可以根据需要进行修改。另外，FFmpeg还提供了许多参数和选项，可以进行更高级的转码设置，例如调整视频质量、分辨率、比特率等。如果需要进一步自定义转码设置，可以参考FFmpeg的文档或使用ffmpeg -h命令查看更多选项和使用示例。


### 3. 非标准MP4转标准MP4
---

1. 运行以下命令以将MP4文件转换为标准的MP4格式：

```
ffmpeg -i input.mp4 -c:v copy -c:a copy output.mp4
```

其中，input.mp4是要转换的MP4文件名，output.mp4是转换后的标准MP4文件名。你可以根据需要修改这些文件名。

2. 等待转换过程完成。由于这里使用的是直接拷贝（copy）的方式，不进行重新编码，因此转换过程会非常快速。

转换完成后，你可以在同一目录下找到生成的标准MP4文件。

3. 这里的命令中使用了-c:v copy和-c:a copy选项，它们指示FFmpeg直接拷贝视频和音频流，而不进行重新编码。这样可以快速地将视频转换为标准MP4格式，而不会改变原始视频和音频的编码方式和质量。


### 4. FLV批量转换bash脚本
---

Bash脚本来将当前目录下的所有FLV格式视频文件转换为MP4格式：

- **convert_videos.sh**
```
#!/bin/bash

for file in *.flv; do
    if [ -f "$file" ]; then
        filename="${file%.*}"
        output="${filename}.mp4"
        ffmpeg -i "$file" -c:v libx264 -c:a aac "$output"
    fi
done

```


1. 将上述脚本保存为一个名为convert_videos.sh的文件，并确保脚本具有可执行权限（使用chmod +x convert_videos.sh命令赋予执行权限）。然后，打开终端，进入包含该脚本的目录，并执行./convert_videos.sh来运行脚本。

```
./convert_videos.sh                       # 终端中运行脚本

nohup ./convert_videos.sh &               # 后台运行bash脚本

tail -f nohup.out                         # 实时查看脚本输出

ps -ef | grep convert_videos.sh           # 命令用于列出正在运行的进程，并通过过滤器 grep 来查找包含指定名称的进程。
```

2. 该脚本使用for循环遍历当前目录下的所有FLV文件。对于每个FLV文件，它提取文件名（不包括扩展名），然后将其作为输出MP4文件的文件名。接下来，它使用FFmpeg将FLV文件转换为MP4文件，使用libx264作为视频编码器，使用AAC作为音频编码器。

3. 转换后的MP4文件将与原始FLV文件位于相同的目录下，并具有相同的文件名，只是扩展名改为了.mp4。

4. 请注意，这个脚本假设FFmpeg已经正确安装并在系统的$PATH路径中。如果你的FFmpeg安装位置不在默认路径中，你可能需要修改脚本中的ffmpeg命令为完整的FFmpeg可执行文件路径。



### 5. 批量非标准MP4转标准MP4
---

Bash脚本来将当前目录下的所有非标准MP4格式视频文件转换为标准MP4格式：

- **convert_MP4.sh**
```
#!/bin/bash

for file in *.mp4; do
    if [ -f "$file" ]; then
        filename="${file%.*}"
        output="${filename}_standard.mp4"
        ffmpeg -i "$file" -c:v copy -c:a copy "$output"
    fi
done

```



### 6. 将视频文件名转换成下载链接
---
运行一个bash脚本来构建MP4视频文件的下载链接并将其保存到一个文本文件中，您可以尝试以下脚本：

- **obtain_mp4URL.sh**
```
#!/bin/bash

# 视频目录路径
video_directory="./"

# 保存链接的文本文件路径
output_file="./output.txt"

# 清空输出文件
> "$output_file"

# 遍历视频目录下的所有mp4文件
for file in "$video_directory"/*.mp4; do
    # 提取文件名
    filename=$(basename "$file")
    
    # 构建下载链接
    download_link="https://domain.com/01_TOEFL/01_flv_test/$filename"
    
    # 将链接追加到输出文件
    echo "$download_link" >> "$output_file"
done

echo "链接构建完成并保存到 $output_file"

```
脚本将在当前目录下生成名为output.txt的文本文件，并将下载链接追加到该文件中。请确保将脚本和视频文件放在同一目录下，并运行该脚本以生成链接文件。


### 7. MP4视频批量下载
---
以下是基于output.txt中的下载链接使用curl命令下载视频的示例脚本：

- **download_mp4.sh**
```
#!/bin/bash

# 保存链接的文本文件路径
link_file="./output.txt"

# 下载视频的目标目录
download_directory="./"

# 读取链接文件中的每一行
while IFS= read -r download_link; do
    # 提取文件名
    filename=$(basename "$download_link")
    
    # 使用curl命令下载视频文件
    curl -O "$download_link"
    
    # 移动文件到目标目录
    mv "$filename" "$download_directory"
    
    echo "已下载视频文件: $filename"
done < "$link_file"

echo "视频下载完成"

```

1. 请将脚本中的./output.txt替换为实际的output.txt文件路径。将./替换为您希望将视频下载到的目标目录路径。


2. 脚本将读取output.txt文件中的每个下载链接，并使用curl命令下载视频文件。然后，脚本将使用mv命令将下载的视频文件移动到目标目录。每次下载完成后，脚本会输出已下载的视频文件名。最后，当所有视频都下载完成后，脚本将输出"视频下载完成"。确保在运行该脚本时，您具有适当的写入权限，并且output.txt中的链接有效。


更新版本

- **download_mp4_log.sh**
```
#!/bin/bash

# 保存链接的文本文件路径
link_file="./output.txt"

# 下载视频的目标目录
download_directory="./"

# 保存下载失败的链接的文件路径
failed_links_file="./failed_links.txt"

# 清空保存失败链接的文件
> "$failed_links_file"

# 读取链接文件中的每一行
while IFS= read -r download_link; do
    # 提取文件名
    filename=$(basename "$download_link")
    
    # 使用curl命令下载视频文件
    if curl -O "$download_link"; then
        # 下载成功，移动文件到目标目录
        mv "$filename" "$download_directory"
        echo "已下载视频文件: $filename"
    else
        # 下载失败，将链接追加到失败链接文件中
        echo "$download_link" >> "$failed_links_file"
        echo "下载失败的链接: $download_link"
    fi
done < "$link_file"

echo "视频下载完成"

if [ -s "$failed_links_file" ]; then
    echo "以下链接下载失败，请检查链接的有效性:"
    cat "$failed_links_file"
fi

```
此更新的脚本添加了一个保存下载失败链接的文件(failed_links.txt)。脚本首先会清空该文件，然后在下载视频时，如果链接无效或下载失败，将链接追加到该文件中。在脚本执行完毕后，如果有下载失败的链接，将输出它们的列表。

请确保在运行该脚本时，具有适当的写入权限，并且output.txt中的链接有效。

