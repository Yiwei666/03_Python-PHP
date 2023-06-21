# Python-PHP

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

- **02_创建缩略图.py**

读取指定目录下的多个视频文件，然后截取每个视频的第10秒作为缩略图。






- **02_parser.py**

html文本解析


- **03_douYinLink**
```
输入一个抖音短视频分享链接，获取该视频无水印下载链接和视频，视频用日期命名
```


- **siteCollect.php**
```
1. 首先，代码检查用户是否已经登录。如果用户没有登录，则会将用户重定向到登录页面。
2. 如果用户已经登录，则会显示一个表单，用户可以在表单中输入网站名称和网址，并将其保存到一个名为 siteCollectUrl.txt 的文件中。
3. 如果用户输入的网址已经存在于文件中，则会弹出一个提示框，告诉用户该网址已经存在。
4. 如果用户输入的网址不存在于文件中，则会将网站名称和网址写入文件中，并在页面上显示一个表格，其中包含所有已保存的网站链接。
5. 页面上还有一个“Logout”链接，当用户单击该链接时，会将用户重定向到 siteCollect.php 文件，并且在 URL 中添加了查询参数 logout=true。
6. 在 siteCollect.php 文件中，如果检测到 logout=true，则会销毁当前用户的会话数据，并将用户重定向到登录页面 
```


- **question.php**
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
