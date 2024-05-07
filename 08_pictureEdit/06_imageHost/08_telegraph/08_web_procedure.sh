#!/bin/bash

# 提示用户输入网页链接
echo "请输入网页链接："
read web_url

# 下载网页内容并保存到指定文件路径
output_path="/home/01_html/08_x/image/05_webPicDownload/08_web_url.html"
curl -o "$output_path" "$web_url"

# 检查是否下载成功
if [ $? -eq 0 ]; then
  echo "网页已成功下载到 $output_path"
else
  echo "下载失败，请检查您的网络连接或网页链接是否正确。"
  exit 1
fi

# 运行指定的 Python 脚本
python_path="/home/00_software/miniconda/bin/python"
python_script="/home/01_html/08_x/image/05_webPicDownload/08_web_extract_url.py"

if [ -x "$python_path" ]; then
  "$python_path" "$python_script"
else
  echo "找不到指定的 Python 解释器，请确认路径是否正确。"
fi
