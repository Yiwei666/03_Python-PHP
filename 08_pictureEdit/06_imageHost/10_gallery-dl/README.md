# 1. 项目功能

使用本地 socks5 代理，基于 gallery-dl  能否下载x（前身twitter）指定账号在指定日期范围内的高清原图


# 2. 文件结构




# 3.  环境配置


## 1. gallery-dl 安装

### 1. 创建/激活虚拟环境

```sh
conda env list
conda activate iopaint_env
```


### 2. gallery-dl 安装

`gallery-dl` 是一个命令行工具，用于从各种图像托管网站（如 Pixiv、DeviantArt、Danbooru、Twitter、Instagram 等）下载图像及其相关元数据。它支持大量站点，能够自动解析网页内容、按作者、标签、专辑等结构化方式批量下载资源。

```sh
python -m pip install -U gallery-dl
python -m pip install PySocks
```


1. 核心依赖（通常已包含或自动安装）：

   - `Python 3.4+`: gallery-dl 运行所需的基础编程语言环境。
   - `Requests`: Python HTTP 库，用于发送网络请求并获取网页内容。


2. 可选依赖及其作用：

   - `yt-dlp` 或 `youtube-dl`：用于支持 HLS/DASH 视频下载，并提供 ytdl 集成。这意味着如果 gallery-dl 遇到视频内容，它可以使用这两个库来处理视频流的下载。yt-dlp 是 youtube-dl 的一个活跃维护的分支，通常推荐使用它。

   - `FFmpeg`：用于 Pixiv Ugoira 动画的转换。Ugoira 是 Pixiv 特有的一种动图格式，FFmpeg 可以将其转换为更常见的视频格式（如 WebM）。

   - `mkvmerge`：用于 精确的 Ugoira 帧时间码。与 FFmpeg 结合使用时，可以确保转换后的 Ugoira 视频具有更准确的帧时间信息。

   - `PySocks`：提供 SOCKS 代理支持。如果您需要通过 SOCKS 代理访问网络来下载内容，则需要安装此库。

   - `brotli` 或 `brotlicffi`：支持 Brotli 压缩。一些网站可能使用 Brotli 算法压缩其内容，安装这些库可以更好地处理此类压缩。

   - `zstandard`：支持 `Zstandard` 压缩。与 Brotli 类似，用于处理使用 Zstandard 算法压缩的内容。

   - `PyYAML`：提供 YAML 配置文件支持。虽然 gallery-dl 默认使用 JSON 格式的配置文件，但如果您希望使用 YAML 格式来编写配置文件，则需要此库。

   - `toml` (适用于 Python < 3.11)：提供 TOML 配置文件支持。如果您希望使用 TOML 格式来编写配置文件（在 Python 3.11 之前的版本中），则需要此库。

   - `SecretStorage`：用于 GNOME 密钥环密码，特别是当您使用 --cookies-from-browser 选项从浏览器中提取 cookie 时。在某些 Linux 环境下，浏览器将密码存储在密钥环中，此库可以帮助 gallery-dl 访问这些密码来解密 cookie。

   - `Psycopg`：提供 PostgreSQL 归档支持。如果您希望将下载记录或元数据存储到 PostgreSQL 数据库中进行管理，则需要此库。

总结来说，虽然 gallery-dl 自身可以独立运行完成基本下载任务，但安装这些可选库可以极大地扩展其功能，例如支持视频下载、处理特殊格式、使用代理、处理不同压缩格式以及更方便地管理认证和数据归档。






## 2. 导出 x 账号 cookie

Get cookies.txt LOCALLY 0.7.0 是一款开源的浏览器插件，专注于将 Cookie 导出为 Netscape 或 JSON 格式，适用于 Chrome 和 Firefox 等浏览器。






## 3. 下载命令（powershell）

powershell 执行如下命令


- 指定账号全部下载

```sh
gallery-dl   --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt"   --proxy "socks5://127.0.0.1:1080"     https://twitter.com/Japantravelco/media
```



- 指定时间范围

```sh
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "socks5://127.0.0.1:1080" --filter "date >= datetime(2025, 4, 12) and date < datetime(2025, 5, 29)"   https://twitter.com/Japantravelco/media



```


- 指定时间范围 & 输出日志
 
```sh
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "socks5://127.0.0.1:1080" --filter "date >= datetime(2025, 1, 27) and date < datetime(2025, 5, 28)" -v https://twitter.com/Japantravelco/media > download_log.txt 2>&1
```






# 参考资料

- [gallery-dl官方github项目](https://github.com/mikf/gallery-dl/tree/master)





