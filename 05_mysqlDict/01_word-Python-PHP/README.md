### 项目功能
```
浏览器中运行php和python代码，输出单词到web页面。该版本的python后端代码没有经过优化，优化后的见 07_wordStudy仓库
```


- **/home/01_html/15_pythonword目录权限设置**
```
drwxrwxr-x             # chmod 775
```


- **09N_单词数据库.json文件权限设置**
```
chown nginx:nginx /home/01_html/15_pythonword/09N_单词数据库.json
chmod 775 /home/01_html/15_pythonword/09N_单词数据库.json                 # 666权限已足够，对应-rw-rw-rw- 1 nginx nginx
```

### 注意事项
```
php脚本和python脚本中需要修改相应文件路径

09N_背单词.py 代码运行速度太慢，性能需要优化
```
