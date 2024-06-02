#!/bin/bash

# 提示用户输入磁力链接
echo "请输入磁力链接:"
read M

# 使用输入的磁力链接执行 transmission-remote 命令
transmission-remote -n 'transmission:123456' -a "$M"

# 打印命令执行后返回的信息
echo "命令已执行，返回的信息如上。"
