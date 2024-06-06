# 1. 项目功能

1. 使用 `BitTorrent` 客户端下载视频，如电影等，相关磁力链接可从 `The Pirate Bay`获取
2. 通过web服务器在线观看电影视频



# 2. 文件结构

```
├── 19_torrent_down.sh             # 获取并执行磁力下载·
├── 19_transfer_mp4_srt.sh         # 对mp4和srt文件重命名
├── 19_video_list.php              # 列出指定目录下所有mp4文件
├── 19_videoPlayer.php             # 播放mp4视频

```


# 3. Transmission-daemon安装和配置

### 1. 介绍

`Transmission-daemon` 是一个流行的 `BitTorrent` 客户端的后台运行版本，属于 `Transmission` 项目的一部分。这个客户端允许用户通过 BitTorrent 协议下载和上传文件。`Transmission-daemon` 是一个轻量级的程序，专为`服务器`和 `NAS（网络附加存储）`设备设计，可以在没有图形用户界面的环境中运行。

`Transmission-daemon` 与 Torrent 的关系：

- `BitTorrent` 协议：Torrent 文件是指使用 BitTorrent 协议进行点对点文件共享的文件的元数据。这些文件包含了文件的信息（如文件名、大小和结构）以及用于找到文件分布的计算机（即种子服务器）的信息。

- `Transmission-daemon` 作用：Transmission-daemon 作为一个 BitTorrent 客户端，使用 Torrent 文件中的信息来下载或上传文件。用户可以通过 torrent 文件或磁力链接来启动下载任务。

- 操作方式：与 `Transmission` 的桌面应用相比，Transmission-daemon 通常通过网络接口进行控制，例如使用网页界面或通过第三方应用与其 API 交互。这使得它非常适合安装在远程系统或无头服务器上，用户可以在任何有网络连接的地方管理它。

- 功能：Transmission-daemon 提供了大多数 BitTorrent 客户端的标准功能，包括队列管理、文件选择下载、带宽调整、定时任务等。



### 2. 安装

```bash
sudo apt update
sudo apt install transmission-daemon
```

### 3. 常用命令以及alias

- alias

```bash
alias trl="transmission-remote -n 'transmission:123456' -l"
alias trp="service transmission-daemon stop"
alias trs="service transmission-daemon start"
alias lsd="ls /var/lib/transmission-daemon/downloads"
alias cdd='cd /var/lib/transmission-daemon/downloads'
alias lsj="cat /etc/transmission-daemon/settings.json"
alias tns='bash /home/01_html/19_transfer_mp4_srt.sh'
alias rvs="transmission-remote -n 'transmission:123456' -t all --remove-and-delete"
alias dwn='bash /home/01_html/19_torrent_down.sh'
```

- 命令行

```bash
transmission-remote -t all --remove-and-delete
transmission-remote -n 'transmission:123456' -t all --remove-and-delete   # 删除所有的种子
transmission-remote -t all --remove
transmission-remote -t 2 --remove-and-delete
transmission-remote -n 'transmission:123456' -t 1 --remove-and-delete
transmission-remote -n 'username:password' -t 2 --remove-and-delete
```

### 4. 更改配置

需要先stop，修改完成后再start，running过程中修改配置无效。

```bash
sudo service transmission-daemon stop

sudo service transmission-daemon start
```

⭐ 重点修改参数

```json
"rpc-password": "{8916cecbd4d607ecc2e4bd842e3fdb9f54b3918bOZtGPoh0",     // 初始化密码，设置明文，start后自动加密
"rpc-whitelist-enabled": false,                                          // 由true改为false之后在网页上是可以访问的， 不推荐

"rpc-whitelist": "127.0.0.1",                                            // 白名单中只有本地回环地址
"rpc-whitelist-enabled": true,                                           // 开启白名单，只允许本地回环地址以及限定ip通过web界面访问
```


- Transmission-daemon 配位置文件 `/etc/transmission-daemon/settings.json`


```json
{
    "alt-speed-down": 50,
    "alt-speed-enabled": false,
    "alt-speed-time-begin": 540,
    "alt-speed-time-day": 127,
    "alt-speed-time-enabled": false,
    "alt-speed-time-end": 1020,
    "alt-speed-up": 50,
    "bind-address-ipv4": "0.0.0.0",
    "bind-address-ipv6": "::",
    "blocklist-enabled": false,
    "blocklist-url": "http://www.example.com/blocklist",
    "cache-size-mb": 4,
    "dht-enabled": true,
    "download-dir": "/var/lib/transmission-daemon/downloads",
    "download-limit": 100,
    "download-limit-enabled": 0,
    "download-queue-enabled": true,
    "download-queue-size": 5,
    "encryption": 1,
    "idle-seeding-limit": 30,
    "idle-seeding-limit-enabled": false,
    "incomplete-dir": "/var/lib/transmission-daemon/Downloads",
    "incomplete-dir-enabled": false,
    "lpd-enabled": false,
    "max-peers-global": 200,
    "message-level": 1,
    "peer-congestion-algorithm": "",
    "peer-id-ttl-hours": 6,
    "peer-limit-global": 200,
    "peer-limit-per-torrent": 50,
    "peer-port": 51413,
    "peer-port-random-high": 65535,
    "peer-port-random-low": 49152,
    "peer-port-random-on-start": false,
    "peer-socket-tos": "default",
    "pex-enabled": true,
    "port-forwarding-enabled": false,
    "preallocation": 1,
    "prefetch-enabled": true,
    "queue-stalled-enabled": true,
    "queue-stalled-minutes": 30,
    "ratio-limit": 2,
    "ratio-limit-enabled": false,
    "rename-partial-files": true,
    "rpc-authentication-required": true,
    "rpc-bind-address": "0.0.0.0",
    "rpc-enabled": true,
    "rpc-host-whitelist": "",
    "rpc-host-whitelist-enabled": true,
    "rpc-password": "{8916cecbd4d607ecc2e4bd842e3fdb9f54b3918bOZtGPoh0",
    "rpc-port": 9091,
    "rpc-url": "/transmission/",
    "rpc-username": "transmission",
    "rpc-whitelist": "127.0.0.1",
    "rpc-whitelist-enabled": false,
    "scrape-paused-torrents-enabled": true,
    "script-torrent-done-enabled": false,
    "script-torrent-done-filename": "",
    "seed-queue-enabled": false,
    "seed-queue-size": 10,
    "speed-limit-down": 100,
    "speed-limit-down-enabled": false,
    "speed-limit-up": 100,
    "speed-limit-up-enabled": false,
    "start-added-torrents": true,
    "trash-original-torrent-files": false,
    "umask": 18,
    "upload-limit": 100,
    "upload-limit-enabled": 0,
    "upload-slots-per-torrent": 14,
    "utp-enabled": true
}
```


# 4. 视频截取和音频提取

### 1. ffmpeg安装

- `ffmpeg` 是一个非常强大的开源工具，用于录制、转换和流式处理音频和视频。它支持几乎所有类型的媒体文件格式，并提供了大量的编解码库，使得用户可以在不同格式之间转换媒体文件。除了基本的转换功能，ffmpeg 还可以用来调整媒体文件的各种参数，如分辨率、比特率等，并支持复杂的视频处理任务，如视频剪辑和效果应用。

- `mediainfo` 是一个轻量级的工具，用于显示多媒体文件的技术信息和标签数据。它可以提供有关音频和视频文件的详细信息，如码率、持续时间、音视频格式、分辨率等。mediainfo 支持多种输出格式，包括文本、HTML 或XML，并且能够与许多GUI（图形用户界面）前端集成，使其易于使用。该工具非常有用，尤其是在需要了解文件编码细节以进行兼容性或质量分析时。

```bash
sudo apt update
sudo apt install ffmpeg
sudo apt-get install mediainfo
```














