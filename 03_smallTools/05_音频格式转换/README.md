### 项目结构
---
1. 对音频文件进行重命名，对特殊字符用 “_” 替代   
2. 用ffmpeg将同级目录下的.m4a和.flac音频文件转换为.mp3文件，文件名不变   
3. 同级目录下的.m4a和.flac音频文件全部删除   
4. 对mp3文件构造访问链接   


### 项目功能
---

- collect_audio.sh  

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


- mp3_URL_extract.sh

写个bash脚本，将同级目录下所有mp3格式音频的文件名，写入到txt文件中，注意每一个文件名前要添加 “https://domain.com/music/周杰伦/01_补充/” 构造下载链接

```
mp3_URL_extract.sh    # 基于mp3文件名生成链接，使用时需要修改 "https://domain.com/music/周杰伦/01_补充/" 这一部分

mp3_files.txt         # 运行上述脚本，同级目录下会产生该txt文件

```


- mp3_download.sh

有一个mp3_files.txt文件，里面有多个音频链接，音频格式为mp3，能否写个bash脚本将这些音频下载到同级目录下，注意使用 curl下载，将下载失败的链接保存到error_log文件中

```
mp3_files.txt         # 存储mp3音频链接的txt文件

mp3_download.sh       # 下载mp3_files.txt文件中的mp3音频，使用时不需要修改

```


- rename_files_all.sh

写个bash脚本，将同级目录下所有文件名中的 空格，中英文括号，"(", ")", "（","）", 都用下划线"_"替代，并进行重命名。在上述代码的基础上，继续添加功能，将"《","》","[","]","：","!" 替换为下划线。


- generate_mp3_paths.sh

当前目录 /home/01_html/12_music/ 下有多个子文件夹（子文件夹中可能还有子孙文件夹），每个子/子孙文件夹中有多个mp3音频，能否写个bash脚本，将所有mp3格式音频的  路径+文件名   写入到txt文件中，注意写入的时候，将路径中的 "/home/01_html/"  部分替换为 “https://domain.com/"  构造访问链接，因为"/home/01_html/"  部分被设置为了web服务器的根目录。

```
generate_mp3_paths.sh      # 产生同级目录下所有目录中mp3文件名的链接，注意使用时修改脚本，将根目录换成域名

mp3_paths.txt              # 运行上述脚本，同级目录下会产生该txt文件

```


- url_build.sh

以markdown链接格式打印当前目录下mp3的音频url和文件名，使用时注意音频链接构造过程中域名和文件夹的修改




