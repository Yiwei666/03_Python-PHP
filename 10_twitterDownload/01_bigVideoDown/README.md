# 1. 项目功能

借助第三方解析网站，实现对twitter上大视频的在线异步下载

# 2. 文件结构

### 1. 文件结构

```
.
├── 05_twitter_bigVideo_download.php       # 获取用户在网页提交的twitter链接
├── 05_twitter_bigfile                     # 项目文件夹
│   ├── 01_url.txt                         # 存储前端写入的链接
│   └── 05_bashDownTwitterBigVideo.sh      # 后端处理脚本
├── 05_twitter_video                       # 储存视频文件夹
│   ├── 1234.mp4                           # 视频
│   ├── ...
```


# 3. 环境配置


### 1. 定时脚本 `05_bashDownTwitterBigVideo.sh`

1. 总体思路

```
编写一个bash脚本完成如下任务
1.检查 /home/01_html/05_twitter_bigfile 目录下是否有 01_url.txt文件，如果没有该文件，则创建该文件。
2. 判断01_url.txt是否为空，如果为空，则结束运行。
3. 如果不为空，则读取其内容，将其作为网址进行访问，如果访问不响应，则退出脚本，并清空01_url.txt文件中的内容
4. 如果访问网址出现响应，则下载该网址对应的mp4视频到/home/01_html/05_twitter_video 文件夹，如果是跳转下载，则相应跳转再下载，并并清空01_url.txt文件中的内容
5. 将下载的mp4命令为 年月日-时分秒-11位随机数字，如：20230722-215223-269940798.mp4
```

2. 权限和定时设置

```sh
chmod +x /home/01_html/05_twitter_bigfile/05_bashDownTwitterBigVideo.sh
* * * * * /usr/bin/bash /home/01_html/05_twitter_bigfile/05_bashDownTwitterBigVideo.sh
```




```sh
chown www-data:www-data /home/01_html/05_twitter_bigfile/01_url.txt

chmod 644 /home/01_html/05_twitter_bigfile/01_url.txt
```









