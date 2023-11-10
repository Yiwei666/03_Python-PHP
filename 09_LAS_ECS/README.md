### 项目功能
---
- 用于音视频、文档、日志等处理的小脚本


### 项目结构
---
- uname.php

输入网站名和网址，生成 markdown 和 html 格式的链接

- 11_musicMix.php

通过构造音频链接，随机播放指定文件夹下的mp3音频

- 11_musicMixTxt.php

它的主要功能是从一个包含MP3音频链接的文件中读取链接，并以随机的方式播放这些音频文件。代码会自动加载页面并播放一首随机选取的歌曲，然后在歌曲播放完毕后选择下一首随机歌曲继续播放。页面上会显示一个标题 "MusicMix" 和一个包含播放器控件的区域，用于播放音频文件。

- lsfile_js.php

代码将会显示具有.html、.php和.js扩展名的文件列表。

`lsfile_darkTheme.php`在`lsfile_js.php`基础上新增深色主题功能。

- 13_MixPlayers.php (聚合在线音频播放器) 

```
├── 13_MixPlayers_codeGenerate.php        # 输入路径生成相应代码，添加到13_MixPlayers.php文件中
├── 13_MixPlayers.php                     # 生成多个在线播放器，每个播放器对应一个音频文件夹

```

- serialNumberGenerate.php

生成指定位数的序列号，序列号包含大小写英文字符和数字的组合

### 部署环境
---




