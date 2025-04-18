# 1. 项目功能

```
01 支持本地运行和随机输出单词到web页面，记录打卡天数
02 根据单词选意思，根据意思选单词
03 支持单词发音，朗诵
04 支持造句和近义词
05 支持错题收录
06 词库包括四六级，托福，雅思，GRE，SAT等，数据库见文件夹 03_Python-PHP/05_mysqlDict/word/
```

# 2. 文件结构

```
├── pythonWord.php                      # -rw-r--r--  1 root  root
├── 15_pythonword                       # drwxrwxr-x  2 root  root        
│   ├── 09N_单词数据库.json             # -rw-rw-rw- 1 nginx nginx 
│   └── 09N_背单词.py                   # -rw-r--r-- 1 root  root

注意：当你执行一个脚本时，操作系统会检查脚本文件的权限位，如果父文件夹具有可执行权限并且脚本文件本身有读取权限，那么你可以直接执行该脚本。
```



**09N_背单词(alpha).py**
```
导入了自然语言处理模块，需要语料库，对环境要求高，本地运行
支持单词朗诵
def wordToSentence(wordInput) 函数支持近义词查找和造句

```


**09N_背单词(Beta).py**
```
没有导入自然语言处理模块，不需要语料库，对环境要求低，本地运行
支持单词朗诵

```


注意：alpha和beta版本都是在本地运行的数据库，09N_背单词.py中记得更改json文件路径

**09N_背单词.py**
```
服务器上运行，有很多无效的冗杂代码，web响应时间很慢
```

**09N_背单词_vps优化.py**
```
在vps上运行、经过优化后的代码，运行速度和web响应时间略有提升
```


# 3. 环境配置

```bash
# 将目录及其内容的所属组更改为 'root'
sudo chown -R :root 15_pythonword

# 将目录权限设置为 drwxrwxr-x (775)
sudo chmod 775 15_pythonword

# 将 09N_单词数据库.json 文件的权限设置为 -rw-rw-rw- (666)
sudo chmod 666 15_pythonword/09N_单词数据库.json

# 将 09N_单词数据库.json 文件的所有者更改为 nginx:nginx
sudo chown nginx:nginx 15_pythonword/09N_单词数据库.json

# 将 09N_背单词.py 文件的权限设置为 -rw-r--r-- (644)
sudo chmod 644 15_pythonword/09N_背单词.py

# 将 09N_背单词.py 文件的所有者更改为 root:root
sudo chown root:root 15_pythonword/09N_背单词.py
```

