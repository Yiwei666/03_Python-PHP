# 项目结构

```
├── 01keke.py              # 解析 mainpage 的python脚本，链接写入到homePageUrl.txt文件中
├── latest.html            # 临时文件
├── homePageUrl.txt        # 储存mainpage中的所有url
├── musicdown.py           # 提取music.html中的url，储存到audioUrl.txt文件中
├── audioUrl.txt           # 储存音频链接的文件
├── insert_unique_urls.sh  # 将audioUrl.txt中的链接写入到mysql数据库中的脚本            
└── music.html             # 临时文件，含有音频链接


├── 01keke.py
├── audioUrl.txt
├── homePageUrl.txt
├── insert_unique_urls.sh
├── latest.html
├── musicdown.py
├── music.html
├── myscript_loop.sh
└── myscript.sh


```





# mysql数据库操作

- 创建表格 voaspecialTABLE

```mysql
CREATE TABLE voaspecialTABLE (
    datetime DATETIME,
    url VARCHAR(255)
);
```



- 数据库访问

```mysql
SHOW DATABASES;
USE kkmusicdb;
SHOW TABLES;
DESCRIBE voaspecialTABLE;
SELECT datetime,url FROM voaspecialTABLE;
```
