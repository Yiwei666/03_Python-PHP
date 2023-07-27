### 项目功能
---

- 01_jpg_To_png.py

能否写个python脚本，将同级目录下的jpg图片转换为png图片，文件名不变

- imageCompression.py

jpg和png图片压缩

项目地址：https://github.com/liangxiaobo/imageCompression/tree/master

命令执行
```
$ python imageCompression.py -h


test.py [-i <imgs>] [-q <quality>] [-s <subsampling>] [-j <jpga>] [-d <dir>]
     -i, --imgs 需要压缩的图片，多个图片以逗号分隔 "a.jpg,b.jpg
     -q, --quality 默认压缩的图片质量为15，可以调整0-95 
     -j, --jpga 为1时设置将图片统计转换成.jpg格式，默认为0 
     -d, --dir 设置一个目录，压缩指定目录下的图片 
     -s, subsampling 设置编码器的子采样 默认-1 
                     -1: equivalent to keep 
                      0: equivalent to 4:4:4 
                      1: equivalent to 4:2:2 
                      2: equivalent to 4:2:0 


命令示例：python test.py -i a.jpg,b.jpg -q 20
```
