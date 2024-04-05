# 1. 项目功能

自动从github上下载本仓库，在ubuntu系统中一键安装图床，并初始化相关参数和设置权限

# 2. 文件结构

- 只需要在 `home/01_html` 路径下运行 `08_gitDownload.sh` bash脚本即可，自动从github上下载本仓库，根据提示初始化相关参数，设置相应权限

- 安装后在 `home/01_html` 路径下的相关文件结构如下所示

```
├── 08_gitDownload.sh
├── 08_x
│   └── image
│       ├── 01_imageHost
│       └── 02_imageTransfer
├── 08_x_imageGallery.php
├── 08_x_imageTransferName.txt
├── 08_x_mvImageServer.sh
├── 08_x-picPasteUpload.php
├── 08_x-serverImageHost.php
├── 08_x_serverImageTransfer.php
├── 08_x_simpleGallery.php
```

# 3. 环境配置

### 1. bash脚本的总体思路

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

### 2. 08_gitDownload.sh 代码

注意事项：

1. 上述脚本运行过程会提示输入两个参数，一个是自定义的项目路径，例如 `"/home/01_html/08_gitDownTest"`，只需要更改 `08_gitDownTest` 部分即可

2. 提示输入域名或者ip地址，如 `http://120.46.81.41` 或者 `https://domain.com`

3. `nginx.conf`和`php.ini`中关于上传图片的大小限制需要手动修改

4. 关于文件的限制访问，请手动添加

代码：

```sh
#!/bin/bash

# 提示用户输入一个目录
read -p "请输入一个目录（例如 /home/01_html/08_gitDownTest）: " dir1
if [ -d "$dir1" ]; then
    echo "目录 $dir1 已存在，请使用其他目录。"
    exit 1
fi

# 下载GitHub仓库
dir2="$dir1/03_Python-PHP"
git clone https://github.com/Yiwei666/03_Python-PHP.git "$dir2"

# 创建目录和处理文件
mkdir -p "$dir1/06_imageHost"
mv "$dir2/08_pictureEdit/06_imageHost"/* "$dir1/06_imageHost/"
rm -rf "$dir1/06_imageHost/v1"
rm -rf "$dir2"

# 创建更多目录并设置权限
dir3=$(basename "$dir1")
dir4="$dir1/image/01_imageHost"
dir5="$dir1/image/02_imageTransfer"
mkdir -p "$dir4" "$dir5"
chmod 777 "$dir4" "$dir5"

# 复制文件
cp "$dir1/06_imageHost/03_picPasteUpload.php" "/home/01_html/$dir3-picPasteUpload.php"
cp "$dir1/06_imageHost/03_serverImageHost.php" "/home/01_html/$dir3-serverImageHost.php"

# 文本替换
sed -i "s/03_serverImageHost.php/$dir3-serverImageHost.php/" "/home/01_html/$dir3-picPasteUpload.php"

# 提示用户输入域名或网址
read -p "请输入一个域名或网址（例如 http://120.46.81.41）: " ipname

# 更多文本替换
sed -i "s|/home/01_html/02_LAS1109/35_imageHost/|$dir4/|g" "/home/01_html/$dir3-serverImageHost.php"
sed -i "s|http://120.46.81.41|$ipname|g" "/home/01_html/$dir3-serverImageHost.php"

# 创建文件和设置权限
echo "创建和设置文件权限"
touch "/home/01_html/${dir3}_imageTransferName.txt"
chmod 666 "/home/01_html/${dir3}_imageTransferName.txt"
chown www-data:www-data "/home/01_html/${dir3}_imageTransferName.txt"

# 复制更多文件
cp "$dir1/06_imageHost/05_imageGallery.php" "/home/01_html/${dir3}_imageGallery.php"
cp "$dir1/06_imageHost/05_serverImageTransfer.php" "/home/01_html/${dir3}_serverImageTransfer.php"
cp "$dir1/06_imageHost/05_mvImageServer.sh" "/home/01_html/${dir3}_mvImageServer.sh"
cp "$dir1/06_imageHost/05_simpleGallery.php" "/home/01_html/${dir3}_simpleGallery.php"

# 执行更多文本替换
sed -i "s|http://120.46.81.41/02_LAS1109/35_imageHost/|$ipname/$dir3/image/01_imageHost/|g" "/home/01_html/${dir3}_imageGallery.php"
sed -i "s|/home/01_html/02_LAS1109/35_imageHost/|$dir4/|g" "/home/01_html/${dir3}_imageGallery.php"
sed -i "s|05_serverImageTransfer.php|${dir3}_serverImageTransfer.php|g" "/home/01_html/${dir3}_imageGallery.php"
sed -i "s|/home/01_html/05_imageTransferName.txt|/home/01_html/${dir3}_imageTransferName.txt|g" "/home/01_html/${dir3}_imageGallery.php"

# 更多文本替换
sed -i "s|/home/01_html/05_imageTransferName.txt|/home/01_html/${dir3}_imageTransferName.txt|g" "/home/01_html/${dir3}_serverImageTransfer.php"

sed -i "s|/home/01_html/05_imageTransferName.txt|/home/01_html/${dir3}_imageTransferName.txt|g" "/home/01_html/${dir3}_mvImageServer.sh"
sed -i "s|/home/01_html/02_LAS1109/35_imageHost/|$dir4/|g" "/home/01_html/${dir3}_mvImageServer.sh"
sed -i "s|/home/01_html/02_LAS1109/35_imageTransfer/|$dir5/|g" "/home/01_html/${dir3}_mvImageServer.sh"

# 给脚本添加可执行权限
chmod +x "/home/01_html/${dir3}_mvImageServer.sh"

# 设置cron任务
(crontab -l 2>/dev/null; echo "*/1 * * * * /usr/bin/bash '/home/01_html/${dir3}_mvImageServer.sh'") | crontab -

# 最后的文本替换
sed -i "s|http://120.46.81.41/02_LAS1109/35_imageTransfer/|$ipname/$dir3/image/02_imageTransfer/|g" "/home/01_html/${dir3}_simpleGallery.php"
sed -i "s|/home/01_html/02_LAS1109/35_imageTransfer/|$dir5/|g" "/home/01_html/${dir3}_simpleGallery.php"

# 删除目录
rm -rf "$dir1/06_imageHost"

echo "执行完毕！"
```










