# 1. 项目功能

在ubuntu系统中一键安装图床脚本

# 2. 文件结构

只需要在 `home/01_html` 路径下运行 `08_gitDownload.sh` bash脚本即可，自动从github上下载本仓库，根据提示初始化相关参数，设置相应权限

# 3. 环境配置


```
############

写个bash脚本完成如下任务
提示用户输入一个目录，例如 dir1="/home/01_html/08_gitDownTest"，脚本要在屏幕给出提示。如果该目录已经存在，则给出提示并退出脚本
下载 https://github.com/Yiwei666/03_Python-PHP.git 仓库到 dir2="dir1/03_Python-PHP"文件夹，
创建"dir1/06_imageHost"目录
然后将dir2/08_pictureEdit/06_imageHost下的所有文件移动到 dir1/06_imageHost目录下
删除dir1/06_imageHost/v1 子文件夹
删除dir2目录

###

dir3=$(basename "$dir1")
创建目录 dir4="dir1/image/01_imageHost"
创建目录 dir5="dir1/image/02_imageTransfer"
并给dir4和dir5设置 777 权限

复制 "$dir1/06_imageHost/03_picPasteUpload.php" 文件为 "/home/01_html/$dir3-picPasteUpload.php"
复制 "$dir1/06_imageHost/03_serverImageHost.php" 文件为 "/home/01_html/$dir3-serverImageHost.php"

将"/home/01_html/$dir3-picPasteUpload.php"脚本中的"03_serverImageHost.php"字符串替换为 "$dir3-serverImageHost.php"

提示用户输入一个域名或网址，例如 ipname="http://120.46.81.41"，脚本要在屏幕给出提示

将  "/home/01_html/$dir3-serverImageHost.php" 脚本中的"/home/01_html/02_LAS1109/35_imageHost/"字符串替换为 "$dir4/"
将  "/home/01_html/$dir3-serverImageHost.php" 脚本中的"http://120.46.81.41"字符串替换为 "$ipname"

### 

创建 /home/01_html/$dir3_imageTransferName.txt 文件

给  /home/01_html/$dir3_imageTransferName.txt 文件 设置666权限，还设置 chown www-data:www-data 所属组

复制 "$dir1/06_imageHost/05_imageGallery.php" 文件为 "/home/01_html/$dir3_imageGallery.php"
复制 "$dir1/06_imageHost/05_serverImageTransfer.php" 文件为"/home/01_html/$dir3_serverImageTransfer.php"
复制 "$dir1/06_imageHost/05_mvImageServer.sh" 文件为"/home/01_html/$dir3_mvImageServer.sh"
复制 "$dir1/06_imageHost/05_simpleGallery.php" 文件为"/home/01_html/$dir3_simpleGallery.php"



将"/home/01_html/$dir3_imageGallery.php"脚本中的"http://120.46.81.41/02_LAS1109/35_imageHost/"字符串替换为 "$ipname/$dir3/image/01_imageHost/"
将"/home/01_html/$dir3_imageGallery.php"脚本中的"/home/01_html/02_LAS1109/35_imageHost/"字符串替换为  "$dir4/"
将"/home/01_html/$dir3_imageGallery.php"脚本中的"05_serverImageTransfer.php"字符串替换为  "$dir3_serverImageTransfer.php"
将"/home/01_html/$dir3_imageGallery.php"脚本中的"/home/01_html/05_imageTransferName.txt"字符串替换为  "/home/01_html/$dir3_imageTransferName.txt"


将"/home/01_html/$dir3_serverImageTransfer.php"脚本中的"/home/01_html/05_imageTransferName.txt"字符串替换为  "/home/01_html/$dir3_imageTransferName.txt"


将"/home/01_html/$dir3_mvImageServer.sh"脚本中的"/home/01_html/05_imageTransferName.txt"字符串替换为  "/home/01_html/$dir3_imageTransferName.txt"
将"/home/01_html/$dir3_mvImageServer.sh"脚本中的"/home/01_html/02_LAS1109/35_imageHost/"字符串替换为  "$dir4/"
将"/home/01_html/$dir3_mvImageServer.sh"脚本中的"/home/01_html/02_LAS1109/35_imageTransfer/"字符串替换为  "$dir5/"


给 "/home/01_html/$dir3_mvImageServer.sh" 添加可执行权限 

chmod +x "/home/01_html/$dir3_mvImageServer.sh" 

追加crontab定时任务，每分钟执行一次 "/home/01_html/$dir3_mvImageServer.sh" 脚本

*/1 * * * * /usr/bin/bash   "/home/01_html/$dir3_mvImageServer.sh"

将"/home/01_html/$dir3_simpleGallery.php"脚本中的"http://120.46.81.41/02_LAS1109/35_imageTransfer/"字符串替换为  "$ipname/$dir3/image/02_imageTransfer/"
将"/home/01_html/$dir3_simpleGallery.php"脚本中的"/home/01_html/02_LAS1109/35_imageTransfer/"字符串替换为  "$dir5/"

删除"dir1/06_imageHost"目录


######################
```












