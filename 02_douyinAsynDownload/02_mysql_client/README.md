# 1. 项目功能

国内云服务器同步、处理抖音视频

1. 将onedrive中的mp4文件名同步到本地数据库中
2. 定时更新服务器中的mp4文件

# 2. 文件结构

### 1. 客户端视频定时更新

```
├── 03_mysql_douyin
│   ├── 03_db_config.php                 // 连接到数据库，创建 $mysql 连接对象
│   ├── 03_tk_video_check.php            // 对比服务器和数据库中的视频信息，更新存在状态和创建时间等信息
│   ├── 03_copy_remote_to_local.php      // 基于rclone将onedrive中的视频信息同步到服务器的数据库中
│   └── 03_random_replace_video.php      // 从服务器中删除部分视频，从onedrive中下载部分视频，实现视频更新
```


### 2. 客户端视频播放

```php
25_douyinVideo_page.php                        // 分页展示视频，按照数据库的默认顺序显示

25_douyin_likes_operation.php                  // 功能模块，获取数据库中每条视频的likes数，对likes数进行加减操作
25_douyinVideo_page_likes_orderDate.php        // 分页展示视频，按照数据库的默认顺序显示，支持对每条视频的likes数进行加减操作，调用 25_douyin_likes_operation.php 模块
25_douyinVideo_page_likesOrder.php             // 分页展示视频，按照likes数降低的顺序显示，支持对每条视频的likes数进行加减操作，调用 25_douyin_likes_operation.php 模块

25_douyinVideo_random.php                      // 随机播放视频
25_douyinVideo_random_preload.php              // 随机播放视频并预加载下一条视频
25_douyinVideo_random_preload_likes.php        // 随机播放likes数大于0的视频并预加载下一条视频
```




# 3. 环境配置

## 1. 客户端视频定时更新

### 1. `03_db_config.php`

1. 数据库

- 创建一个mysql数据库 tiktok_db，这个数据库有3列，第一列为mp4视频名字 video_name；第三列为视频的创建时间 creat_time；第三列为视频的存在状态 exist_status，0代表不存在，1代表存在；如下所示

```
mysql> describe tk_videos;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| video_name   | varchar(255) | NO   |     | NULL    |                |
| create_time  | datetime     | NO   |     | NULL    |                |
| exist_status | tinyint      | NO   |     | NULL    |                |
| likes        | int unsigned | NO   |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+
5 rows in set (0.01 sec)
```

2. 环境变量

```php
$host = 'localhost'; // 通常是 'localhost' 或一个IP地址
$username = 'root'; // 数据库用户名
$password = '123456'; // 数据库密码
$dbname = 'tiktok_db'; // 数据库名称
```

3. 导出/备份数据库文件命令

```sh
alias sbt='mysqldump -p tiktok_db > /home/01_html/backup_tiktok_db_$(date +%Y%m%d_%H%M%S).sql'
```



### 2. `03_tk_video_check.php`

🟢 同步本地视频文件和数据库记录，通过检查、更新和统计本地视频文件的存在状态和数据库中的相应记录

1. 功能

- 检查和打开目录：验证指定目录（存放视频文件）是否存在并可读，然后打开这个目录。
- 遍历目录中的视频文件：遍历指定目录下的所有文件，对于每个 .mp4 视频文件，检查数据库中是否已存在该视频文件的记录。
- 数据库操作：
  - 如果视频文件在数据库中不存在，将其信息（文件名和创建时间）插入数据库，并设置其存在状态为 1。
  - 遍历数据库中的视频记录，检查每个视频文件是否存在于本地目录。如果文件存在但数据库标记为不存在（exist_status = 0），则更新状态为存在（exist_status = 1）并更新创建时间。如果文件不存在，则更新状态为不存在（exist_status = 0）。
  - 统计和打印信息：计算并打印数据库中所有视频的总数，以及存在状态为 1 和 0 的视频数量。
- 关闭数据库连接：在操作完成后关闭数据库连接。

2. 环境变量

```php
// 引入数据库配置和连接
include '03_db_config.php';

// 指定视频文件所在目录
$video_dir = "/home/01_html/01_tecent1017/25_film_videos";
```

### 3. `03_copy_remote_to_local.php`

🟢 这段代码使用 rclone 从远程目录获取 `.mp4` 格式的视频文件名，检查数据库中的记录，并为数据库中不存在的视频文件插入新记录。

1. 功能

- 使用 rclone 获取远程目录的视频文件：通过 rclone 命令获取远程目录中所有 .mp4 格式的视频文件名。
- 数据库操作：
  - 遍历获取到的视频文件名，检查数据库中是否已存在这些视频文件的记录。
  - 如果视频文件在数据库中不存在，则将其插入数据库，并设置其创建时间为当前时间，存在状态为 0。
  - 文件复制：代码中有注释掉的部分，本意是使用 rclone 复制不存在于数据库中的视频文件到本地目录，但这部分代码在实际运行中被注释掉了。
- 统计和打印信息：计算并打印出新插入数据库的视频文件数量。
- 关闭数据库连接：在操作完成后关闭数据库连接。

2. 环境变量

```php
// 引入数据库配置和连接
include '03_db_config.php';

// 远程目录
$remote_dir = "HW-1012:do1-2/01_html/02_douyVideo";
// 本地目录
$local_dir = "/home/01_html/01_tecent1017/25_film_videos";
```


### 4. `03_random_replace_video.php`

🟢 这段代码主要实现了从本地删除随机选定的视频文件，并从远程目录复制数据库中记录的但本地缺失的视频文件到本地。

1. 功能

- 引入数据库配置并连接数据库。
- 执行一个 PHP 脚本以执行特定的操作（`03_copy_remote_to_local.php`）。
- 定义本地和远程目录，用于存储和操作视频文件。
- 从本地目录读取所有 MP4 视频文件名，并存入数组 A。
- 从数据库中查询所有视频文件名，并存入数组 B。
- 从数组 A 中随机选取 N（默认为 50）个视频，并从本地目录删除这些视频。
- 计算数组 B 和 A 的差集（即存在于 B 中但不在 A 中的视频），然后从这个差集中随机选取 M（默认为 50）个视频。
- 将这 M 个视频从远程目录复制到本地目录。
- 关闭数据库连接。
- 执行另一个 PHP 脚本进行特定的后续检查（`03_tk_video_check.php`）。
- 输出整个过程完成的信息。

2. 环境变量

```php
// 引入数据库配置和连接
include '03_db_config.php'; // 根据实际路径调整

exec('php /home/01_html/03_mysql_douyin/03_copy_remote_to_local.php');

// 定义本地和远程目录
$local_dir = "/home/01_html/01_tecent1017/25_film_videos";
$remote_dir = "HW-1012:do1-2/01_html/02_douyVideo";

exec('php /home/01_html/03_mysql_douyin/03_tk_video_check.php');
```

3. 定时任务

每小时的第 30 分钟 执行一次 PHP 脚本

```bash
30 * * * * /usr/bin/php /home/01_html/03_mysql_douyin/03_random_replace_video.php
```


## 2. 客户端视频播放


### 1. 25_douyinVideo_page.php

2. 环境变量

```php
    $domain = 'http://domain.com';
    $videoPath = '/home/01_html/03_douyVideoLocal/';

    $videosPerRow = 2;
    $videosPerPage = 60;

    $videoUrl = $domain . '/03_douyVideoLocal/' . $videoName;
```


### 2. 25_douyin_likes_operation.php

2. 环境变量

```php

```


### 3. 25_douyinVideo_page_likes_orderDate.php

2. 环境变量

```php

```



### 4. 25_douyinVideo_page_likesOrder.php

2. 环境变量

```php

```



### 5. 25_douyinVideo_random.php

2. 环境变量

```php

```


### 6. 25_douyinVideo_random_preload.php

2. 环境变量

```php

```



### 7. 25_douyinVideo_random_preload_likes.php

2. 环境变量

```php

```





# 参考资料

1. [MySQL安装及数据迁移](https://github.com/Yiwei666/12_blog/blob/main/002/002.md)






