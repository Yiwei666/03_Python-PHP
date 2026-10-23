# 1. 项目功能

1. 统计指定路径下各一级子目录的总大小，以易读的单位按大小降序展示，帮助快速定位占用空间较大的目录。


# 2. 文件结构

```py
01_folder_sizes.py         # 统计指定路径下各一级子目录的总大小，以易读的单位按大小降序展示，帮助快速定位占用空间较大的目录。
```


# 3. 环境配置

##  1. `01_folder_sizes.py`

**1. 功能**

1. 统计指定路径下各一级子目录的总大小，以易读的单位按大小降序展示，帮助快速定位占用空间较大的目录。

2. 该脚本可以与 [基于目录联接的跨卷存储迁移](https://github.com/Yiwei666/12_blog/blob/main/888/8-023.md) 搭配使用，以便清理C盘存储空间。


**2. 编程思路**

能不能编写一个python脚本，实现如下需求：
1. 提示用户输入一个目录路径，例如 `C:\Users\31598\AppData\Local`
2. 计算该路径下一级各个目录的大小，方便用户统计各个目录占用的空间
3. 打印出各个目录的大小，以方便用户阅读的形式给出。不满1MB，使用KB作为单位，保留2位小数；不满1GB，使用MB作为单位，保留1位小数。
4. 最核心的目的就是方便用户确定哪些目录比较大，后续可能会进行清理。


**3. 环境变量**

1. 无需安装第三方库。
2. 通过终端交互输入需要统计的指定路径，无需脚本内硬编码。


**4. 测试日志**

```
大小（降序）       目录名称 / 状态
----------------------------------------------------------------
         7.08 GB  Microsoft  [跳过 28 个链接/重解析点]
         1.03 GB  Mozilla
        759.4 MB  Temp  [统计不完整：1 处读取失败; 跳过 1 个链接/重解析点]
        661.8 MB  OpenAI
        534.5 MB  Packages
        509.5 MB  Tencent
        457.9 MB  Programs  [跳过 1 个链接/重解析点]
        207.4 MB  adspower_global-updater
        159.4 MB  pip
        122.4 MB  cursor-updater
        121.1 MB  Codex
        116.0 MB  qq-chat-updater
         57.0 MB  BsccCloud
         56.5 MB  wvpn-updater
         53.7 MB  Downloaded Installations
         47.8 MB  AMD
         27.0 MB  ConnectedDevicesPlatform
         20.6 MB  Comms
         16.5 MB  Sublime Text 3
         15.8 MB  nwjs
         13.1 MB  xwalk
          7.3 MB  D3DSCache
          6.8 MB  VirtualStore
          6.1 MB  xweb
          5.4 MB  Adobe
          5.0 MB  ESHOW
          2.8 MB  Publishers
          2.1 MB  fontconfig
       810.13 KB  UCSF
       682.45 KB  Lenovo
       665.59 KB  UWST
       314.20 KB  OneDrive
       212.06 KB  OriginLab
       168.62 KB  Lantern
       135.00 KB  Spyder
       129.25 KB  cache
       123.52 KB  Windows Master Store
        52.00 KB  kingsoft
        38.68 KB  PlaceholderTileLogoFolder
        31.18 KB  __SHARED
        20.88 KB  CMakeTools
        13.72 KB  FastStone
         2.66 KB  Notepad
         1.84 KB  speech
         1.23 KB  ToastNotificationManagerCompat
         0.74 KB  HP
         0.05 KB  testcpp3
         0.01 KB  Backup
         0.00 KB  CEF
         0.00 KB  conda
         0.00 KB  CrashRpt
         0.00 KB  Diagnostics
         0.00 KB  ElevatedDiagnostics  [统计不完整：1 处读取失败]
         0.00 KB  Netease
         0.00 KB  xweb_webview
         0.00 KB  Yarn
----------------------------------------------------------------
已读取的子目录文件合计：12.01 GB
一级子目录数：56；耗时：5.1 秒
一级目录中已跳过 4 个链接/重解析点。

说明：1 KB = 1024 字节；父目录直接包含的文件不计入上述合计。
显示文件逻辑大小，可能与磁盘实际占用不同；硬链接按路径重复计数。
链接和 Windows 重解析点不跟随；读取失败时，显示的大小可能偏小。
本脚本只读取和统计文件，不执行清理或删除。
```
