# 1. 项目功能

1. 将谷歌学术页面及crossRef API返回的论文元数据写入到云服务器的mysql数据库中
2. web在线管理论文分类标签（增删查改）和论文访问


# 2. 文件结构

```php
# 1. 功能模块
08_db_config.php
08_category_operations.php
08_tm_add_paper.php                       # 基于油猴脚本传递的论文元数据，检查数据库中是否存在相同doi，插入论文数据，并进行默认分类
08_tm_get_categories.php                  # 返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名
08_tm_get_paper_categories.php            # 基于doi查找论文的paperID，基于paperID查找论文所属分类
08_tm_update_paper_categories.php

# 2. web交互
08_webAccessPaper.php


# 3. 油猴脚本
08_tm_paperManagement.js

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

2. 显示某几列、删除指定行

```sql
# 删除 paperID 为 10 的那一行：
DELETE FROM papers WHERE paperID = 2;

# 返回 papers 表中的 title 和 paperID 两列的数据
SELECT paperID, title FROM papers;
```


### 5. alias

```sh
alias dpaper='mysqldump -p paper_db > /home/01_html/08_paper_db_backup_$(date +%Y%m%d_%H%M%S).sql'
```



# 4. php功能模块

## 1. `08_db_config.php`



## 2. `08_category_operations.php`




## 3. `08_tm_add_paper.php`

### 1. 功能

基于油猴脚本传递的论文元数据，调用`08_category_operations.php`模块中的函数，检查数据库中是否已存在相同doi（`getPaperByDOI`函数），插入论文元数据（`insertPaper`函数）以及分配默认分类（`assignAllPapersCategory`函数）。


- 设置响应头：配置返回类型为JSON，并允许跨域POST请求，确保客户端能够正确调用API。

- **加载模块**：引入数据库配置模块 (`08_db_config.php`) 和分类操作模块 (`08_category_operations.php`)，提供数据库连接和分类管理功能。

- 接收请求数据：通过 `php://input` 获取POST请求中的原始JSON数据，并解析为PHP数组，用于后续操作。

- 验证数据：检查请求数据的有效性，确保 doi 不为空，否则返回错误信息并终止流程。

- **检查重复**：调用 `getPaperByDOI` 函数检查数据库中是否已存在相同DOI的论文，若存在则直接返回已有的 paperID，避免重复插入。

- 提取字段：从请求数据中提取论文信息（如标题、作者、期刊名、出版年份等），若字段缺失则设置为 null，保证数据完整性。

- **插入论文**：调用 `insertPaper` 函数，将论文数据写入数据库，若成功则获取新生成的 paperID，失败则返回错误信息。

- **分配默认分类**：调用 `assignAllPapersCategory` 函数，将新论文分配到默认分类 "All papers"，并根据分配结果返回成功或部分成功的响应信息。

- 返回响应：通过JSON格式返回操作结果，包括成功标志（success）、论文ID（paperID）或失败原因（message），确保客户端能够处理相应的结果。



## 4. `08_tm_get_categories.php`

### 1. 功能

调用`08_category_operations.php`模块中的 `getCategories`函数，返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名。


- 设置响应头信息：配置返回类型为JSON，允许跨域访问和GET请求，以确保客户端能够正确访问API并解析返回数据。

- 加载必要模块：引入数据库配置模块提供数据库连接对象 $mysqli，以及分类操作模块包含获取分类的函数 getCategories。

```php
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- **获取分类数据**：调用 `getCategories` 函数从数据库查询所有分类信息，返回包含 `categoryID` 和 `categoryName` 的分类数组，如果查询失败则返回 false。

- 判断并返回响应：检查获取的分类数据是否为数组，若成功则返回包含分类信息的JSON响应，若失败则返回包含错误信息的失败响应，保证客户端得到明确的操作结果。



## 5. `08_tm_get_paper_categories.php`

### 1. 功能

调用`08_category_operations.php`模块中的函数，基于doi查找论文的paperID（ `getPaperByDOI`函数），通过 `paperID` 查询论文所属分类的ID列表（`getCategoriesByPaperID` 函数）。

- 设置响应头信息：配置返回数据格式为JSON，允许跨域GET请求，确保客户端能够正确访问API并解析返回的数据。

- 加载必要模块：引入数据库配置模块提供数据库连接对象 $mysqli，以及分类操作模块包含查询论文和分类的函数，如 getPaperByDOI 和 getCategoriesByPaperID。

```php
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- 获取并验证DOI参数：通过GET参数获取DOI，去除空格后验证其有效性，若为空则返回错误信息 "DOI不能为空"，并终止流程。

- **查询论文信息**：调用 `getPaperByDOI` 函数，通过DOI从数据库获取对应论文的 `paperID`，若未找到论文则返回错误信息 "未找到对应的论文"。

- **查询分类信息**：调用 `getCategoriesByPaperID` 函数，通过 `paperID` 查询论文所属分类的ID列表，若查询成功则返回分类数组，失败则返回错误信息 "获取分类失败"。

- 返回响应结果：根据查询结果生成JSON响应，包含成功标志、分类ID数组或错误信息，确保客户端能清晰了解操作结果。



## 6. `08_tm_update_paper_categories.php`






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


# 6. tampermonkey 脚本



### 1. 编程思路

上述油猴脚本能够基于 crossRef API 返回的信息显示该篇论文标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商 这些信息（用户点击“提取内容并查询 DOI”按钮之后页面会获取上述论文的元信息），我想要把这些信息写入到云服务器 paper_db 数据库中的 papers 表格中并对该篇论文进行分类。我的需求如下：

1. 在油猴脚本中再新增一个按钮“标签”，点击标签按钮之后会把  crossRef API 返回的、在页面显示的 论文标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商 这些信息 都写入到papers 表格中，如果crossRef API返回的上述信息中部分缺失，则缺失项不用写入，维持表格的默认值。期刊ISSN只写入印刷版即可。另外，在写入该条论文数据前，请将该论文的doi与papers 表格中已有的doi进行比对，如果已经存在，则不用写入该条论文的元数据。

2. 点击“标签”按钮的同时还需要显示 categories 表中的所有分类标签，每个标签前面显示一个小的正方框，如果该篇论文属于某个标签，则对应方框中会显示一个对号。用户可以点击方框来添加或者取消对号。需要通过paperCategories 表该操作来实现论文分类。

3. categories 表中有一个标签是"0 All papers"，默认给所有的论文都添加该标签，且页面中无法取消该方框前的对号

如果上述需求的实现需要在服务器中引入新的php模块，可请编写相关模块，目前已有的模块 08_db_config.php、08_category_operations.php 也可以调用。














