#!/bin/bash

# 提示用户输入目录
read -p "请输入一个目录: " dir1
if [ -d "$dir1" ]; then
    echo "目录 $dir1 已存在."
    exit 1
fi

# 下载GitHub仓库
dir2="$dir1/03_Python-PHP"
git clone https://github.com/Yiwei666/03_Python-PHP.git "$dir2"

# 创建目录并移动文件
mkdir -p "$dir1/06_imageHost"
mv "$dir2/08_pictureEdit/06_imageHost/"* "$dir1/06_imageHost/"

# 删除子文件夹和dir2目录
rm -rf "$dir1/06_imageHost/v1"
rm -rf "$dir2"

# 创建目录并设置权限
dir3=$(basename "$dir1")
dir4="$dir1/image/01_imageHost"
dir5="$dir1/image/02_imageTransfer"
mkdir -p "$dir4" "$dir5"
chmod 777 "$dir4" "$dir5"

# 复制文件
cp "$dir1/06_imageHost/03_picPasteUpload.php" "/home/01_html/${dir3}-picPasteUpload.php"
cp "$dir1/06_imageHost/03_serverImageHost.php" "/home/01_html/${dir3}-serverImageHost.php"

# 替换字符串
sed -i "s/03_serverImageHost.php/${dir3}-serverImageHost.php/g" "/home/01_html/${dir3}-picPasteUpload.php"

# 提示用户输入域名或网址
read -p "请输入一个域名或网址: " ipname

# 替换脚本中的字符串
sed -i "s|/home/01_html/02_LAS1109/35_imageHost/|$dir4/|g" "/home/01_html/${dir3}-serverImageHost.php"
sed -i "s|http://120.46.81.41|$ipname|g" "/home/01_html/${dir3}-serverImageHost.php"

# 删除目录
rm -rf "$dir1/06_imageHost"

echo "操作完成。"
