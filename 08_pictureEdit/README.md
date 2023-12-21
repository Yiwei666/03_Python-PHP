# 1. 项目功能

1. 上传及查看图片
2. 将图片白色背景转为透明
3. 图片格式转换及压缩
4. 数学公式图片图像识别获取latex公式
5. 本地定时截图
6. 图片几何变换



# 2. 项目结构

```
-rw-r--r--  1 root  root   picture_main.php             # 上传图片及查看当天上传的图片
-rw-r--r--  1 root  root   picture_pastls.php           # 查看过去上传的图片
drwxrwxrwx 14 root  root   01_pic                       # 存储图片的文件夹，下面有很多以日期命名的子文件夹，例如20230703

```


# 3. 安装配置

- picture_main.php脚本需要更改的环境变量如下所示

```php
$target_dir = "/home/01_html/01_pic/" . $today_folder;
```
