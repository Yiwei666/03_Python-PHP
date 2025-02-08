# 1. 项目功能

使用php、python、bash等编写的运行在云服务器和客户端的常用小工具


# 2. 文件结构

### 1. 常用脚本

```
infoo.php, paragraph.php, question.php, washbrain.php           # 总体上使用的是同一个模板， paragraph.php 显示方式略有不同
lsfileTime.php, siteCollect.php, siteName.php                   # 显示网站链接，架构完全不同
login.php, logout.php                                           # 登入、登出脚本，还有基于哈希函数的版本
hashConvert.php               # 将字符串转换为哈希值
uname.php                     # 生成html和markdown格式的链接
mysqldict.php                 # 基于mysql数据库的在线词典
pdfcat.php                    # 在线查看pdf文件
markdown.php                  # 在线显示markdown文件
kkmusic.php                   # 每天更新可可英语的听歌学英语音频
google.php                    # 搜索指定网站关键词脚本 
mgugesrch.php                 # 辅助生成google.php网站指定代码
s1_clock.php                  # 倒计时跳转到指定网站
loopAudio.php                 # 循环播放指定链接音频

11_wordsmart.php
12_EssentialWord.php
13_PlanetEarth.php
```

### 2. 其他项目

1. cookie相关：
   - [https://github.com/Yiwei666/10_private_code/tree/main/06_smallTools/01_loginCookie](https://github.com/Yiwei666/10_private_code/tree/main/06_smallTools/01_loginCookie)
   - [https://github.com/Yiwei666/03_Python-PHP/tree/main/08_pictureEdit/06_imageHost/04_cookie](https://github.com/Yiwei666/03_Python-PHP/tree/main/08_pictureEdit/06_imageHost/04_cookie)


2. 电影下载和在线观看：
   - [https://github.com/Yiwei666/03_Python-PHP/tree/main/13_bitTorrent](https://github.com/Yiwei666/03_Python-PHP/tree/main/13_bitTorrent)


3. Python虚拟环境创建和管理
   - [https://github.com/Yiwei666/12_blog/blob/main/888/8-008.md](https://github.com/Yiwei666/12_blog/blob/main/888/8-008.md)


4. 记录自己在技术上的点滴成长和进步
   - [https://github.com/aweill/note/blob/master/01_milestone.md](https://github.com/aweill/note/blob/master/01_milestone.md)


5. 到期服务器备份与服务器新域名更换
   - [https://github.com/Yiwei666/10_private_code/blob/main/03_crontab/README.md](https://github.com/Yiwei666/10_private_code/blob/main/03_crontab/README.md)



# 3. 环境配置


- **02_创建缩略图.py**
```
读取指定目录下的多个视频文件，然后截取每个视频的第10秒作为缩略图。
```


- **02_parser.py**
```
html文本解析
```


- **03_douYinLink**
```
运行在本地的脚本，输入一个抖音短视频分享链接，获取该视频无水印下载链接和视频，视频用日期命名，下载视频到同级目录下
03_douYinLink 是基于 01_htmldown.py 和 02_parser.py 整合而来的
```


- **03_douyinVideo_vps.py**
```
运行在vps上的抖音视频下载脚本，下载视频到指定目录
```



- **douyVideo.php**
```
浏览器中加载播放vps指定目录下的mp4视频

更改视频路径，每行显示的视频数量，视频尺寸大小时，需要更改如下代码

$videosPerRow = 3; // 可以根据需要更改每行显示的视频数量
$videoPath = '/home/01_html/02_douyVideo/';
$videoUrl = $domain . '/02_douyVideo/' . $videoName;
echo '<video controls width="300" height="400" onended="playNextVideo(this)">'; // 添加onended事件

```


- **ytbVideo.php**
```
720P youtube视频播放脚本

以下是代码的简要总结：

    该网页使用PHP代码来获取指定目录下的所有MP4视频文件。
    它使用CSS样式来布局和美化视频播放器。
    PHP代码将视频文件分配到每一行中，每行显示指定数量的视频。
    每个视频都包含一个视频播放器和视频文件名。
    JavaScript代码为视频播放器添加了一个事件处理函数，当一个视频播放结束时，它会自动播放下一个视频。
    如果一行的所有视频都播放完毕，它会继续播放下一行的第一个视频。
```
视频下载脚本链接：https://github.com/Yiwei666/05_C_programing/blob/main/video/download_youtube_vps.py


- **ytbVideo-HQ.php**
```
1080P HQ youtube视频播放脚本
```
通过第三方网站解析获得高清视频链接：https://youtube4kdownloader.com/   
curl  -o  name.mp4    https://s20.youtube4kdownloader.com/download7/hd5/zhwr5y5fm8/v0/av/c2/cdb18/cd18   


### siteCollect.php

```
1. 首先，代码检查用户是否已经登录。如果用户没有登录，则会将用户重定向到登录页面。
2. 如果用户已经登录，则会显示一个表单，用户可以在表单中输入网站名称和网址，并将其保存到一个名为 siteCollectUrl.txt 的文件中。
3. 如果用户输入的网址已经存在于文件中，则会弹出一个提示框，告诉用户该网址已经存在。
4. 如果用户输入的网址不存在于文件中，则会将网站名称和网址写入文件中，并在页面上显示一个表格，其中包含所有已保存的网站链接。
5. 页面上还有一个“Logout”链接，当用户单击该链接时，会将用户重定向到 siteCollect.php 文件，并且在 URL 中添加了查询参数 logout=true。
6. 在 siteCollect.php 文件中，如果检测到 logout=true，则会销毁当前用户的会话数据，并将用户重定向到登录页面 
```


### question.php
```
代码的功能如下：
1. 验证用户是否已登录，如果未登录，则重定向到登录页面。
2. 如果用户单击注销链接，则注销并重定向到登录页面。
3. 显示一个表单，允许用户输入问题数据并将其提交到服务器。
4. 显示一个按钮，允许用户隐藏页面内容。
5. 如果用户提交了问题数据，则将其写入名为“questiondata.txt”的文件中。
6. 如果用户请求显示最新内容，则从“questiondata.txt”文件中读取内容并将其显示在页面上。
7. 显示一个成功登录的消息和一个注销链接。
8. 显示一个版权信息 
```

1. 需要进行web读写的脚本权限和用户组设置

注意：对应的txt文件，如 `questiondata.txt`的权限和所属用户和组需要进行设置，否则php在web端无法对txt文件进行写入

```
-rw-rw-rw-  1 www-data www-data       # questiondata.txt，对应ubuntu系统
-rw-rw-rw-  1 nginx nginx             # questiondata.txt，对应centos系统
```

- 权限设置命令

```
chmod 666 questiondata.txt
chown www-data:www-data questiondata.txt
```

- 下面几个脚本的数据文本权限设置同上

```
infoo.php
paragraph.php
washbrain.php
siteCollect.php
siteName.php
```


2. 将`在只读文本区域中显示内容` 中的中文设置为微软雅黑字体，英文和数字设置为 Arial字体

```css
<!-- 在 head 部分添加以下样式 -->
<style>
    textarea[readonly] {
      display: block;
      margin: 0 auto;
      text-align: center;
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用微软雅黑字体作为首选字体 */
    }
</style>
```

3. 能否修改上述代码，将 `输入字典数据的文本区域`  中的 中文设置为 微软雅黑字体，英文和数字设置为 Arial字体

```css
<!-- 在 head 部分添加以下样式 -->
<style>
    #questiondata {
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用微软雅黑字体作为中文首选字体，英文和数字使用Arial字体 */
    }
</style>
```

4. 添加深色主题，只需要在css style 部分进行如下设置

```css
body {
  background-color: #333; /* Dark gray background */
  color: #eee; /* Light white text color */
}

a {
  color: #00bcd4; /* Blue-green color for links */
}

textarea {
  background-color: #333; /* Dark gray background for textarea */
  color: #eee; /* Light white text color for textarea */
}
```



