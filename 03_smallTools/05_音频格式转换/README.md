### 项目结构
```

```


### 项目功能
---

- convert_audio.sh  

centes系统中，当前目录下有多个子文件夹，每个子文件夹中有多个音频，能否写个bash脚本，将所有非mp3格式音频的文件名写入到txt文件中，txt文件名用子文件夹的名命名，每个子文件夹都要生成一个txt文件

- generate_links.sh

将同级目录下所有非mp3格式音频的文件名，写入到txt文件中，注意每一个文件名前要添加 “domain.com/music/周杰伦/01_补充/” 构造下载链接

- rename_files.sh

将同级目录下所有文件名中的空格用下划线"_"替代，并进行重命名

- download_audio.sh

有一个audio_files.txt文件，里面有多个音频链接，音频格式包括.flac和.m4a，能否写个bash脚本将这些音频下载到同级目录下，注意使用 curl

- audio_conversion.sh

能否用bash写个脚本，用ffmpeg将同级目录下的.m4a和.flac音频文件转换为.mp3文件，文件名不变

- delete_audio_files.sh

能否用bash写个脚本，将同级目录下的.m4a和.flac音频文件全部删除
