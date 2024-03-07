# 1. 项目功能

自动化配置网页播放音频的php脚本和定时下载音频的bash脚本

# 2. 文件结构

```
.
├── 511_inputAutoSetPodcast.sh           # 通过终端页面交互对自动化脚本进行参数初始化
├── 51_autoSetPodcast.sh                 # 需要手动参数初始化的自动化下载脚本
├── 51_SEND7.sh                          # 网页播放音频的php脚本，原本后缀是`.php`的，由于通过curl下载的`51_SEND7.php`不是源代码，而是被服务器解释并生成了HTML内容的文件，因此将后缀改成了`.sh`
└── rclone_51_SEND7.sh                   # 定时脚本，用于定时下载音频
```



# 3. 环境配置


### 1. 51_autoSetPodcast.sh 初级版本

需要手动对参数进行初始化

```sh

```








