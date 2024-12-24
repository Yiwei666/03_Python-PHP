# 1. 项目功能

1. 将谷歌学术页面及crossRef API返回的论文元数据写入到云服务器的mysql数据库中
2. web在线管理论文分类标签（增删查改）和论文访问


# 2. 文件结构

```php
# 1. 功能模块
08_db_config.php
08_category_operations.php

# 2. web交互
08_webAccessPaper.php

```


# 3. 数据库和表

### 1. 数据库构建思路

1. 目标：构建一个期刊论文数据库，储存多篇论文的元数据，包括每篇论文 标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商。每条数据在mysql数据库中占据一行，大概有几万条数据。同时还需要对每篇论文进行分类管理。

2. 方案：使用三个表来规范化数据，Papers 表存储论文信息，Categories 表存储分类信息，PaperCategories 表存储论文与分类的关联。
    - Papers 表存储每篇论文的基本信息。
    - Categories 表存储所有可能的分类。
    - PaperCategories 表实现了 Papers 与 Categories 之间的多对多关系，每条记录表示一篇论文属于一个分类。



### 2. 创建数据库和表

1. 创建名为 paper_db 的数据库：

```sql
CREATE DATABASE paper_db;
```


2. 创建表 papers

```sql
CREATE TABLE papers (
    paperID INT AUTO_INCREMENT PRIMARY KEY, -- 自增主键
    title VARCHAR(255) NOT NULL,            -- 论文标题
    authors TEXT NOT NULL,                  -- 作者列表
    journal_name VARCHAR(255) NOT NULL,     -- 期刊名称
    publication_year INT NOT NULL,          -- 出版年份
    volume VARCHAR(50),                     -- 卷号
    issue VARCHAR(50),                      -- 期号
    pages VARCHAR(50),                      -- 页码范围
    article_number VARCHAR(50),             -- 文章编号
    doi VARCHAR(100),                       -- DOI号
    issn VARCHAR(50),                       -- 期刊ISSN
    publisher VARCHAR(255)                  -- 出版商
);
```


3. 创建 categories 表

```sql
CREATE TABLE categories (
    categoryID INT AUTO_INCREMENT PRIMARY KEY, -- 分类ID，自增主键
    category_name VARCHAR(255) NOT NULL       -- 分类名称
);
```



4. 创建 paperCategories 表

```sql
CREATE TABLE paperCategories (
    paperID INT NOT NULL,         -- 论文ID
    categoryID INT NOT NULL,      -- 分类ID
    PRIMARY KEY (paperID, categoryID),  -- 组合主键
    FOREIGN KEY (paperID) REFERENCES papers(paperID) ON DELETE CASCADE, -- 外键关联 papers 表
    FOREIGN KEY (categoryID) REFERENCES categories(categoryID) ON DELETE CASCADE -- 外键关联 categories 表
);
```

paperCategories 表用于实现 papers 和 categories 表之间的多对多关系。在这种情况下，通常不需要设置自增主键，而是通过组合主键（paperID 和 categoryID）来唯一标识每条记录。

paperID 和 categoryID：
  - 定义为 NOT NULL，确保不能插入空值。

`PRIMARY KEY (paperID, categoryID)`：
  - 使用组合主键，确保每对 paperID 和 categoryID 的组合是唯一的。

`FOREIGN KEY`：
  - 设置外键约束：
    - paperID 引用 papers 表的 paperID 列。
    - categoryID 引用 categories 表的 categoryID 列。
  - `ON DELETE CASCADE`：当 papers 或 categories 表中的相关记录被删除时，paperCategories 表中的对应记录会自动删除。


### 3. 表结构

上述sql命令创建的表结构如下所示

1. `paper_db`数据库中的表

```sql
mysql> show tables;
+--------------------+
| Tables_in_paper_db |
+--------------------+
| categories         |
| paperCategories    |
| papers             |
+--------------------+
3 rows in set (0.00 sec)
```

2. `categories` 表
```sql
mysql> describe categories;
+---------------+--------------+------+-----+---------+----------------+
| Field         | Type         | Null | Key | Default | Extra          |
+---------------+--------------+------+-----+---------+----------------+
| categoryID    | int          | NO   | PRI | NULL    | auto_increment |
| category_name | varchar(255) | NO   |     | NULL    |                |
+---------------+--------------+------+-----+---------+----------------+
2 rows in set (0.01 sec)
```

3. `paperCategories` 表

```sql
mysql> describe paperCategories;
+------------+------+------+-----+---------+-------+
| Field      | Type | Null | Key | Default | Extra |
+------------+------+------+-----+---------+-------+
| paperID    | int  | NO   | PRI | NULL    |       |
| categoryID | int  | NO   | PRI | NULL    |       |
+------------+------+------+-----+---------+-------+
2 rows in set (0.00 sec)
```

4. `papers` 表

```sql
mysql> describe papers;
+------------------+--------------+------+-----+---------+----------------+
| Field            | Type         | Null | Key | Default | Extra          |
+------------------+--------------+------+-----+---------+----------------+
| paperID          | int          | NO   | PRI | NULL    | auto_increment |
| title            | varchar(255) | NO   |     | NULL    |                |
| authors          | text         | NO   |     | NULL    |                |
| journal_name     | varchar(255) | NO   |     | NULL    |                |
| publication_year | int          | NO   |     | NULL    |                |
| volume           | varchar(50)  | YES  |     | NULL    |                |
| issue            | varchar(50)  | YES  |     | NULL    |                |
| pages            | varchar(50)  | YES  |     | NULL    |                |
| article_number   | varchar(50)  | YES  |     | NULL    |                |
| doi              | varchar(100) | YES  |     | NULL    |                |
| issn             | varchar(50)  | YES  |     | NULL    |                |
| publisher        | varchar(255) | YES  |     | NULL    |                |
+------------------+--------------+------+-----+---------+----------------+
12 rows in set (0.00 sec)
```


### 4. 数据库查询


1. 查询所有论文及其分类：

```sql
SELECT p.title, c.category_name 
FROM papers p
JOIN paperCategories pc ON p.paperID = pc.paperID
JOIN categories c ON pc.categoryID = c.categoryID;
```




# 4. php功能模块

## 1. `08_db_config.php`



## 2. `08_category_operations.php`





# 5. web交互脚本

## 1. `08_webAccessPaper.php`

### 1. 编程思路

注意，能否编写一个php脚本，运行在云服务器中，在web页面上访问时可以显示目前已有的分类，调用上述 `08_category_operations.php` 模块来实现。具体要求如下：

1. 左侧显示一个容器，容器的宽度约为页面宽度的25%，其中包含一个3列多行的表格，表格的行数由 categories 表中的分类标签数决定。

2. 在表格的上方显示三个标签管理选项，依次为创建标签，删除标签和修改标签。
    - 创建标签选项下方显示一个输入框即可，点击创建即可在 categories 表中创建表中未存在的标签，如果已存在该标签则给出提示。
    - 删除标签选项下方显示一个输入框即可，点击删除即可在 categories 表中删除表中已存在的标签，如果不存在该标签则给出提示。
    - 修改标签选项下方显示2个输入框即可，分别为原标签和新标签，点击修改即可在 categories 表中修改表中已存在的标签，如果不存在该标签则给出提示。

3. 右侧约75的页面宽度用于显示每个标签下的论文标题、作者、出版年份和期刊名。点击左侧表格中的标签，右侧页面会相应显示相应标签下的论文标题和doi号。每行显示一个论文标题，论文标题下方用小号字体显示作者、出版年和期刊名。论文标题对应一个超链接，点击标题能够在新页面打开 `"https://doi.org/"+doi` 链接



### 2. 环境变量

1. 根据实际修改脚本名 `08_webAccessPaper.php`

```php
// 处理完 POST 请求后刷新页面并显示消息
header("Location: 08_webAccessPaper.php?message=" . urlencode($message));
exit();
```


















