# 1. 项目功能

利用rclone从onedrive拷贝下载文件到云服务器

# 2. 文件结构

```
rclone_random_downmp3.sh          # 随机下载10个mp3音频到云服务器
```


# 3. 环境配置

### 1. rclone_random_downmp3.sh

1. 脚本功能

```
1. 删除目录 /home/01_html/37_Economist/01_audio 
2. 创建目录 /home/01_html/37_Economist/01_audio 
3. 使用rclone读取远程位置 AECS-1109:AECS-1109/37_Economist/01_audio  下的所有文件名到一个数组中
4. 从上述数组中随机选取10个后缀名为mp3的文件名，下载到指定目录 /home/01_html/37_Economist/01_audio
```

2. 设置定时任务

每天 13:10 crontab定时执行如下命令

```
rclone copy AECS-1109:AECS-1109/37_Economist/01_audio/file  /home/01_html/37_Economist/01_audio
```




# 参考资料

1. rclone连接到onedrive：https://github.com/Yiwei666/12_blog/wiki/08_rclone%E8%BF%9E%E6%8E%A5%E5%88%B0OneDrive#2-%E4%B8%8A%E4%BC%A0%E5%92%8C%E4%B8%8B%E8%BD%BD%E6%B5%8B%E8%AF%95
