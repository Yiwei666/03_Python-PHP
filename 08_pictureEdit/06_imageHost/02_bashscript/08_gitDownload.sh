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
