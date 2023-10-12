
# 项目功能

异步下载抖音视频，不需要等待单个视频下载完成再提交下个视频链接，可以连续写入多个链接，存储到txt文件中，每隔固定时间读取下载


# 项目架构

- **架构**


- **项目文件组成**

```
.
├── 01_url_get.php
├── 02_douyinDown.py
├── 03_add_3_to_2.sh
├── 04_2_subtract_4.py
├── 2.txt
├── 3_failure.txt
└── 4_success.txt

.
├── 02_douyVideo                     # 存储视频的文件夹
│   ├── 20231012-031208.mp4
│   ├── 20231012-031409.mp4
│   ├── 20231012-031608.mp4
│   ├── 20231012-031811.mp4
│   ├── 20231012-032009.mp4
│   ├── ...
```

- **文件权限**

```
-rw-r--r-- 1 root  root  2052 Oct 11 20:32 01_url_get.php
-rwxr-xr-x 1 root  root  2967 Oct 11 21:13 02_douyinDown.py
-rwxr-xr-x 1 root  root   515 Oct 11 21:30 03_add_3_to_2.sh
-rwxr-xr-x 1 root  root   674 Oct 11 21:29 04_2_subtract_4.py
-rw-rw-rw- 1 nginx nginx    0 Oct 12 05:00 2.txt
-rw-rw-rw- 1 root  root     0 Oct 12 05:10 3_failure.txt
-rw-rw-rw- 1 root  root  4867 Oct 12 03:20 4_success.txt

drwxrwxr-x  2 nginx nginx    65536 Oct 12 03:20 02_douyVideo
drwxr-xr-x  2 root  root       157 Oct 11 21:29 05_douyinAsynDload
```


# 环境配置














