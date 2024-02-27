
# 项目功能

异步下载抖音视频，不需要等待单个视频下载完成再提交下个视频链接，可以连续写入多个链接，存储到txt文件中，每隔固定时间读取下载


# 项目架构

- **架构**

<p align="center">
  <img src="image/structure.png" alt="Image Description" width="700">
</p>

注意：当txt2中的链接x第一次下载失败后，链接x会储存到txt3中，但是如果第二次x下载成功后，x会被追加到txt4中，这个时候，链接x会同时存在于txt2，txt3，和txt4中，在5点时会删除txt2中已经存在于txt4中的链接x，然后5点10分时，txt3中的链接会追加到txt2中，然后txt3清空且不含x链接，此时txt2和txt4中含有链接，且txt2中的x链接不会再进行下载，直到第二天5点的时候会从txt2中删除，最后x链接只会存在于txt4中。如果x链接一直都无法实现正确解析下载（比如抖音原视频被删除），那么x链接永远不会写入到txt4中，且在每一个24小时周期中都会写入到txt2中，最后txt2中会存在多个相同的x链接。

- **项目文件组成**

```
├── 05_douyinAsynDload                   # 文件夹，存储该项目所有核心脚本
    ├── 01_url_get.php                   # web页面上提醒输入链接，写入到2.txt中，提供查看日志按钮，需要指定日志查看脚本名称
    ├── 01_view_log.php                  # 查看写入日志，环境变量设置需要指定日志文件路径
    ├── 02_douyinDown.py                 # 筛选2.txt中存在，4_success.txt中不存在的链接进行下载，定时每2分钟下载一次
    ├── 03_add_3_to_2.sh                 # 凌晨5.10分将3_failure.txt中的链接追加到2.txt中，并清空3_failure.txt
    ├── 04_2_subtract_4.py               # 凌晨5点，筛选2.txt中的链接，保存不存在于4_success.txt中的链接
    ├── 2.txt                            # 保存所有待下载的链接。该文件需要提前创建，并设置所属组和权限
    ├── 2_addTotalLog.txt                # 日志文件，同步记录2.txt中按照时间顺序追加的所有链接，包含时间戳。该文件需要提前创建，并设置所属组和权限
    ├── 3_failure.txt                    # 保存下载失败的链接，定期追加到2.txt中，然后清空
    ├── 4_success.txt                    # 存储所有下载成功的链接，保证不重复下载
    └── 5_totalSuccessLog.txt            # 日志文件，记录返回状态码为200或下载成功的视频文件名和对应的抖音url。该文件需要提前创建


.
├── 02_douyVideo                         # 存储视频的文件夹
│   ├── 20231012-031208.mp4
│   ├── 20231012-031409.mp4
│   ├── 20231012-031608.mp4
│   ├── 20231012-031811.mp4
│   ├── 20231012-032009.mp4
│   ├── ...
```

注意：使用时仅需访问 `01_url_get.php` 脚本即可

- **文件权限**

```
-rw-r--r-- 1 root  root  2052 Oct 11 20:32 01_url_get.php
-rw-r--r-- 1 root  root   1003 Dec 20 21:54 01_view_log.php
-rwxr-xr-x 1 root  root  2967 Oct 11 21:13 02_douyinDown.py
-rwxr-xr-x 1 root  root   515 Oct 11 21:30 03_add_3_to_2.sh
-rwxr-xr-x 1 root  root   674 Oct 11 21:29 04_2_subtract_4.py
-rw-rw-rw- 1 nginx nginx    0 Oct 12 05:00 2.txt
-rw-rw-rw- 1 nginx nginx   255 Dec 20 17:37 2_addTotalLog.txt
-rw-rw-rw- 1 root  root     0 Oct 12 05:10 3_failure.txt
-rw-rw-rw- 1 root  root  4867 Oct 12 03:20 4_success.txt
-rw-r--r-- 1 root  root    204 Dec 20 19:30 5_totalSuccessLog.txt

drwxrwxr-x  2 nginx nginx    65536 Oct 12 03:20 02_douyVideo             # 存储视频的文件夹
drwxr-xr-x  2 root  root       157 Oct 11 21:29 05_douyinAsynDload       # 存储核心脚本的文件夹
```

权限设置命令

```sh
chmod +x 02_douyinDown.py 03_add_3_to_2.sh 04_2_subtract_4.py

chown www-data:www-data 2.txt
chown www-data:www-data 2_addTotalLog.txt

chmod 666 2.txt 2_addTotalLog.txt 3_failure.txt 4_success.txt

chown www-data:www-data 02_douyVideo
chmod 755 02_douyVideo
```




# 环境配置

### 1. crontab定时任务

```
*/2 * * * * /home/00_software/01_Anaconda/bin/python /home/01_html/05_douyinAsynDload/02_douyinDown.py

0 5 * * * /home/00_software/01_Anaconda/bin/python /home/01_html/05_douyinAsynDload/04_2_subtract_4.py

10 5 * * * /usr/bin/bash /home/01_html/05_douyinAsynDload/03_add_3_to_2.sh
```

说明：

1. 使用crontab写个定时任务，每隔2分钟执行一次 /home/01_html/05_douyinAsynDload/02_douyinDown.py，python路径为 /home/00_software/01_Anaconda/bin/python

2. 使用crontab写个定时任务，每天5点的时候执行 /home/01_html/05_douyinAsynDload/04_2_subtract_4.py，python路径为 /home/00_software/01_Anaconda/bin/python

3. 使用crontab写个定时任务，每天5点10分的时候执行 /home/01_html/05_douyinAsynDload/03_add_3_to_2.sh，bash路径为 /usr/bin/bash


- rclone将视频从云服务器上传至onedrive

```crontab
0 * * * * rclone copy --ignore-existing /home/01_html/02_douyVideo cc1-2:do1-2/01_html/02_douyVideo
```

- rclone将视频从onedrive下载至云服务器

```
30 * * * * rclone copy --ignore-existing HW-1012:do1-2/01_html/02_douyVideo /home/01_html/01_tecent1017/25_film_videos
```


### 2. 抖音url写入 01_url_get.php

1. 能否写一个php脚本，在web页面访问该php脚本的时候显示一个输入框，提示输入保存字符串，用户输入字符串并点击输入保存按钮后，程序会提取该字符串中的 https链接，并将该链接以追加的方式写入到 2.txt文件和2_addTotalLog.txt文件中。
下面是一个字符串例子，字符串通常是如下格式 “......”，只需要提取“https://v.douyin.com/abcdef/” 部分链接即可。

```php
<body>
    <?php
    date_default_timezone_set('Asia/Shanghai');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userInput = $_POST["input"];
    
        preg_match_all('/https:\/\/[^ ]+/', $userInput, $matches);
    
        $links = $matches[0];
    
        $filePath = '/home/01_html/05_douyinAsynDload/2.txt';
        $filePathLog = '/home/01_html/05_douyinAsynDload/2_addTotalLog.txt';
    
        if (!empty($links)) {
            $file = fopen($filePath, "a");
            $logFile = fopen($filePathLog, "a");
    
            foreach ($links as $link) {
                $timestamp = date('Y-m-d H:i:s');
                fwrite($file, $link . PHP_EOL);
                fwrite($logFile, $link . ',' . $timestamp . PHP_EOL);
            }
    
            fclose($file);
            fclose($logFile);
    
            echo "<div id='output'>链接已成功保存到 $filePath 和 $filePathLog 文件中！</div>";
        } else {
            echo "<div id='output'>未找到有效的链接，请重新输入。</div>";
        }
    }
    ?>

    <form id="inputForm" method="POST">
        <textarea id="inputText" name="input" rows="5" cols="50" placeholder="请输入字符串"></textarea>
        <br>
        <input id="saveButton" type="submit" value="保存并执行">
        <br>
        <br>
        <br>
        <button id="visitButton" onclick="visitUrl()">刷新</button>
        <br>
        <br>
        <br>
        <button id="viewButton" onclick="viewLog()">查看</button>
    </form>

    <script>
        function visitUrl() {
            window.location.href = "https://domain.com/05_douyinAsynDload/01_url_get.php";
        }

        function viewLog() {
            window.open("01_view_log.php", "_blank");
        }
    </script>
</body>
```

2. 注意：
  - 部署的时候注意修改跳转链接以及icon对应的域名domain.com
  - `2.txt`和`2_addTotalLog.txt`需要提前使用touch命令创建并设置权限和所属组，不然web无法写入
  - 下面参数赋值需要注意
```php
<link rel="shortcut icon" href="https://domain.com/00_logo/download.png">           # 域名

$filePath = '/home/01_html/05_douyinAsynDload/2.txt';
$filePathLog = '/home/01_html/05_douyinAsynDload/2_addTotalLog.txt';

window.location.href = "https://domain.com/05_douyinAsynDload/01_url_get.php";    # 域名和 01_url_get.php脚本

window.open("01_view_log.php", "_blank");          # 01_view_log.php 脚本
```

### 3. 抖音写入日志查看 01_view_log.php

```php
<?php
$filePathLog = '/home/01_html/05_douyinAsynDload/2_addTotalLog.txt';

// 读取文件的最后两行
$logContent = shell_exec("tail -n 2 $filePathLog");

// 输出 HTML 头部
echo "<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>抖音日志最后两行</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    .centered-container {
      position: absolute;
      top: 20%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    h2 {
      font-size: 24px;
    }

    pre {
      text-align: center;
      font-size: 18px;
      line-height: 5em;
    }
  </style>
</head>
<body>";

// 输出内容
echo "<div class='centered-container'>
        <h2>日志最后两行</h2>
        <pre>$logContent</pre>
      </div>";

// 输出 HTML 尾部
echo "</body>
</html>";
?>

```

注意：需要指定读取的文件路径

```php
$filePathLog = '/home/01_html/05_douyinAsynDload/2_addTotalLog.txt';
```



### 4. 抖音视频下载 02_douyinDown.py

1. 在 /home/01_html/05_douyinAsynDload/2.txt 中每一行可能有一个https链接，在/home/01_html/05_douyinAsynDload/4_success.txt  中每一行可能也有一个https链接，二者也有可能都是空的，现在需要筛选出 在2.txt中有的链接，同时在4_success.txt中没有的链接，并且从筛选出来的链接数组中随机抽取一个链接 赋值为 encoded_url。

```python
# 从文件中读取2.txt中的链接
with open("/home/01_html/05_douyinAsynDload/2.txt", "r") as file:
    links_2 = [line.strip() for line in file.readlines()]

# 从文件中读取4_success.txt中的链接
with open("/home/01_html/05_douyinAsynDload/4_success.txt", "r") as file:
    links_4_success = [line.strip() for line in file.readlines()]

# 找到2.txt中有但4_success.txt中没有的链接
filtered_links = list(set(links_2) - set(links_4_success))

# 随机选择一个链接作为encoded_url
if filtered_links:
    encoded_url = random.choice(filtered_links)
    
    url1 = "https://dlpanda.com/zh-CN/?url="
    url = url1 + encoded_url + "&token=G7eRpMaa"
```

2. 继续修改上述代码，将下载成功的 encoded_url 追加到 /home/01_html/05_douyinAsynDload/4_success.txt中，下载失败的 encoded_url 追加到 3_failure.txt 中。


3. 定义文件路径变量

```python
# 定义文件路径变量
links_2_path = "/home/01_html/05_douyinAsynDload/2.txt"
links_4_success_path = "/home/01_html/05_douyinAsynDload/4_success.txt"
failure_log_path = "/home/01_html/05_douyinAsynDload/3_failure.txt"
success_log_path = "/home/01_html/05_douyinAsynDload/5_totalSuccessLog.txt"
download_dir = "/home/01_html/02_douyVideo/"
```

### 5. 03_add_3_to_2.sh

说明：写一个bash脚本，将3_failure.txt中的内容追加到2.txt文件中，追加后清空3_failure.txt中的内容。

```bash
#!/bin/bash

# 定义文件名变量
failure_file="/home/01_html/05_douyinAsynDload/3_failure.txt"
success_file="/home/01_html/05_douyinAsynDload/2.txt"

# 检查文件是否存在
if [ -e "$failure_file" ] && [ -e "$success_file" ]; then
    # 追加内容到4_success.txt
    cat "$failure_file" >> "$success_file"

    # 清空3_failure.txt
    > "$failure_file"

    echo "内容已成功追加到$success_file并清空了$failure_file"
else
    echo "文件不存在，请检查文件路径或创建文件"
fi
```

### 6. 04_2_subtract_4.py

说明：2.txt文件和4_success.txt中 每一行都有可能有一个https链接，现在需要写一个python脚本，删除2.txt文件中已经存在于4_success.txt中的链接，保留剩余的链接到原2.txt文件中。

```python
# 文件名变量
file_2 = '/home/01_html/05_douyinAsynDload/2.txt'
file_4_success = '/home/01_html/05_douyinAsynDload/4_success.txt'

# 读取4_success.txt中的链接
with open(file_4_success, 'r') as success_file:
    success_links = set(line.strip() for line in success_file)

# 读取2.txt文件中的链接，并保留不在success_links中的链接
with open(file_2, 'r') as input_file:
    remaining_links = [line.strip() for line in input_file if line.strip() not in success_links]

# 将剩余的链接写回2.txt文件中
with open(file_2, 'w') as output_file:
    output_file.write('\n'.join(remaining_links))

print("链接已成功处理！")
```





