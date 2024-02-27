# 1. 项目功能


```
01 收集四六级，托福，雅思等词汇
02 基于mysql数据库和php脚本在web上显示词汇
03 基于python和php脚本的在线单词学习

```


# 2. 项目结构


```
mysqlTest.php                # 用于测试能否连接到mysql数据库
mysqldict.php                # 脚本连接到mysql数据库，打印数据库中的单词数据，包含托福，雅思，GRE等数据
mysqldict_random.php         # 按照一定规律，部分显示mysql表中数据
mysqldict_random_darkTheme.php     # mysqldict_random.php 深色主题版本

01_word-Python-PHP           # 是一个独立但相关的项目

word                         # 所有单词的txt文件，mysql数据库中的单词来源

```

# 3. 环境配置

- mysqldict_random.php 

现在需要修改`mysqldict.php`代码，使得每天只显示每个表格中的部分数据。要求如下：首先计算当前年份的总天数M，以及当前日期是当年的第N天，然后分别计算每个表格的长度L，然后将每个表格中的数据按照默认顺序分成M组，每组的长度是X，X=L/M，如果X是小数，可以向下取整，然后分别显示每个表格中的 第（N-1)*X+1 到 N*X 个数据

