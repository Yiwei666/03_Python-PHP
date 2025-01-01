# 1. 项目功能

1. 将谷歌学术页面及crossRef API返回的论文元数据写入到云服务器的mysql数据库中
2. web在线管理论文分类标签（增删查改）和论文访问


# 2. 文件结构

```php
# 1. 功能模块
08_db_config.php
08_category_operations.php
08_tm_add_paper.php                       # 基于油猴脚本传递的论文元数据，检查数据库中是否存在相同doi，插入论文数据，并分配默认分类
08_tm_get_categories.php                  # 返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名
08_tm_get_paper_categories.php            # 基于doi查找论文的paperID，基于paperID查找论文所属分类
08_tm_update_paper_categories.php         # 基于doi查找论文的paperID，基于paperID更新论文所属分类

08_web_Base32.php                  # Base32类，模块，在 08_webAccessPaper.php 中调用，用于doi号编码，构建论文查看链接
08_web_update_paper_status.php     # 接收前端发送的 DOI 和新的论文状态这两个参数，然后根据这两个参数去数据库更新对应论文的状态，并将更新结果以 JSON 格式返回给前端。

# 2. web交互
08_webAccessPaper.php              # 在线管理论文分类（创建、删除、修改分类标签），在线更改论文所属分类，在线更改论文所属状态码（下载/删除/查看等）
08_base32_tool.php                 # base32在线编码和解码，主要用于doi编码
08_web_crossRef_query.php          # 在web页面上查询显示论文的元数据（展示crossRef API返回的多条结果），能够将元数据写入到数据库并进行分类，功能类似 08_tm_paperManagement.js

# 3. 油猴脚本
08_tm_paperManagement.js           # 油猴脚本（基于01_GBT_api_items.js扩展），通过在谷歌学术页面提取参考文献，结合crossRef API查询论文的元数据，并将论文元数据写入到mysql数据库中（可选），还能够给论文进行在线新增/取消/更改分类（可选），能够复制doi的base32编码。

# 4. 服务器端脚本
08_server_update_paper_status.php         # 更新数据库中论文状态码、基于论文状态码执行下载、删除等操作，可用于cron定时执行

```


# 3. 数据库和表

## 1. 数据库构建思路

1. 目标：构建一个期刊论文数据库，储存多篇论文的元数据，包括每篇论文 标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商。每条数据在mysql数据库中占据一行，大概有几万条数据。同时还需要对每篇论文进行分类管理。

2. 方案：使用三个表来规范化数据，Papers 表存储论文信息，Categories 表存储分类信息，PaperCategories 表存储论文与分类的关联。
    - Papers 表存储每篇论文的基本信息。
    - Categories 表存储所有可能的分类。
    - PaperCategories 表实现了 Papers 与 Categories 之间的多对多关系，每条记录表示一篇论文属于一个分类。



## 2. 创建数据库和表

### 1. 创建名为 paper_db 的数据库

1. 创建命令

```sql
CREATE DATABASE paper_db;
```


2. `paper_db`数据库中的表

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


### 2. 创建表 papers

1. 创建基础表

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

2. 在 papers 表中新增一个 `status` 列，并使用 ENUM 类型来限制其取值为您指定的六种状态。以下是具体的 SQL 语句：

```sql
ALTER TABLE papers 
ADD COLUMN status ENUM('CL', 'C', 'L', 'N', 'DW', 'DL') NOT NULL DEFAULT 'N';
```

- status，包含6种值，分别如下：
  - CL 表示该pdf论文同时存在于onedrive(cloud)和服务器本地(local)
  - C 表示仅存在于onedrive
  - L 表示仅存在于服务器本地
  - N 表示onedrive和服务器本地均不存在
  - DW 表示等待从onedrive下载到服务器本地
  - DL 表示等待从 服务器本地 删除

注意：status列默认值设置为 N


3. 添加status列后的`papers` 表

```sql
mysql> describe papers;
+------------------+----------------------------------+------+-----+---------+----------------+
| Field            | Type                             | Null | Key | Default | Extra          |
+------------------+----------------------------------+------+-----+---------+----------------+
| paperID          | int                              | NO   | PRI | NULL    | auto_increment |
| title            | varchar(255)                     | NO   |     | NULL    |                |
| authors          | text                             | NO   |     | NULL    |                |
| journal_name     | varchar(255)                     | NO   |     | NULL    |                |
| publication_year | int                              | NO   |     | NULL    |                |
| volume           | varchar(50)                      | YES  |     | NULL    |                |
| issue            | varchar(50)                      | YES  |     | NULL    |                |
| pages            | varchar(50)                      | YES  |     | NULL    |                |
| article_number   | varchar(50)                      | YES  |     | NULL    |                |
| doi              | varchar(100)                     | YES  |     | NULL    |                |
| issn             | varchar(50)                      | YES  |     | NULL    |                |
| publisher        | varchar(255)                     | YES  |     | NULL    |                |
| status           | enum('CL','C','L','N','DW','DL') | NO   |     | N       |                |
+------------------+----------------------------------+------+-----+---------+----------------+
13 rows in set (0.01 sec)
```



### 3. 创建 categories 表

1. 创建命令

```sql
CREATE TABLE categories (
    categoryID INT AUTO_INCREMENT PRIMARY KEY, -- 分类ID，自增主键
    category_name VARCHAR(255) NOT NULL       -- 分类名称
);
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




### 4. 创建 paperCategories 表

1. 创建命令

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



## 3. 数据库查询

### 1. 常用查询命令

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


3. 使用 `JOIN` 语句将 `paperCategories` 表与 `categories` 表关联，以获取对应的 `category_name`

```sql
SELECT 
    paperCategories.paperID, 
    paperCategories.categoryID, 
    categories.category_name
FROM 
    paperCategories
JOIN 
    categories 
ON 
    paperCategories.categoryID = categories.categoryID;
```

使用表别名简化查询（可选）：

```sql
SELECT 
    pc.paperID, 
    pc.categoryID, 
    c.category_name
FROM 
    paperCategories pc
JOIN 
    categories c 
ON 
    pc.categoryID = c.categoryID;
```



### 2. 别名alias

1. 备份数据库

```sh
alias dpaper='mysqldump -p paper_db > /home/01_html/08_paper_db_backup_$(date +%Y%m%d_%H%M%S).sql'
```



# 4. php功能模块

## 1. `08_db_config.php`

1. 功能：创建数据库连接对象

2. 代码

```php
<?php
$host = 'localhost'; // 通常是 'localhost' 或一个IP地址
$username = 'root'; // 数据库用户名
$password = '12345678'; // 数据库密码
$dbname = 'paper_db'; // 数据库名称

// 创建数据库连接
$mysqli = new mysqli($host, $username, $password, $dbname);

// 检查连接
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>
```

3. 环境变量：更改数据库密码和名称

```php
$password = '12345678'; // 数据库密码
$dbname = 'paper_db'; // 数据库名称
```


## 2. `08_category_operations.php`

### 1. 功能

- `getCategories`：从 `categories` 表中获取所有分类信息，并按分类名称升序排列返回一个包含分类数据的数组，或在失败时返回错误信息。

- `addCategory`：向 `categories` 表中插入一个新的分类名，返回操作成功的消息或错误信息。

- `deleteCategory`：根据提供的分类ID删除 `categories` 表中的对应分类，返回成功消息或错误提示。

- `updateCategoryName`：更新指定分类ID对应的分类名称为新的名称，返回成功消息或错误提示。

- `getPapersByCategory`：查询某一分类下的所有论文信息，按论文ID降序排序返回结果集，包含标题、作者、出版年份等论文信息。

- `assignAllPapersCategory`：为指定论文分配 "All papers" 分类（`categoryID = 1`），以确保每篇论文都属于默认分类，返回操作成功与否的标志。

- `getCategoriesByPaperID`：根据论文ID查询论文所属的所有分类ID，并返回分类ID的数组，或在失败时返回错误信息。

- `updatePaperCategories`：通过事务处理删除论文当前的分类并插入新的分类ID列表，确保论文分类更新的原子性，返回操作成功与否和错误提示。

- `getPaperByDOI`：根据论文的DOI从 papers 表中获取其完整信息，返回包含论文信息的数组，或在失败时返回 false。

- `insertPaper`：向 `papers` 表中插入新的论文信息，包括标题、作者、出版年份等，返回成功标志和新论文的ID，或在失败时返回错误信息。


### 2. 函数调用

```php
# 1. 08_tm_add_paper.php                       # 基于油猴脚本传递的论文元数据，检查数据库中是否存在相同doi，插入论文数据，并分配默认分类
getPaperByDOI
insertPaper
assignAllPapersCategory

# 2. 08_tm_get_categories.php                  # 返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名
getCategories

# 3. 08_tm_get_paper_categories.php            # 基于doi查找论文的paperID，基于paperID查找论文所属分类
getPaperByDOI
getCategoriesByPaperID

# 4. 08_tm_update_paper_categories.php         # 基于doi查找论文的paperID，基于paperID更新论文所属分类
getPaperByDOI
updatePaperCategories
```



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

### 1. 功能

调用`08_category_operations.php`模块中的函数，基于doi查找论文的paperID（ `getPaperByDOI`函数），调用 `updatePaperCategories` 函数，删除论文的旧分类并插入新的分类列表。


- 设置响应头信息：配置API返回的数据格式为JSON，允许跨域POST请求，并确保请求头和方法符合规范，支持接收客户端发送的JSON数据。

- 加载必要模块：引入数据库配置模块提供数据库连接对象 $mysqli，以及分类操作模块用于处理论文的查询和分类更新操作。

```php
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- 获取并验证POST数据：通过POST请求获取DOI和分类ID数组，确保DOI非空且分类ID为数组格式，若验证失败返回明确的错误信息。

- **查询论文信息**：调用 `getPaperByDOI` 函数，通过DOI查询对应的论文ID（`paperID`），若未找到论文，则返回错误信息 "未找到对应的论文"。

- 确保默认分类存在：检查分类ID数组是否包含默认分类 "All papers" 的 `categoryID = 1`，若不存在则强制加入，确保论文始终属于默认分类。

- **更新论文分类**：调用 `updatePaperCategories` 函数，删除论文的旧分类并插入新的分类列表，若更新成功返回成功消息，若失败则返回具体的错误信息。

- 返回JSON响应：根据操作结果返回明确的JSON响应，包括操作成功与否的标志、成功消息或错误提示，确保客户端能够清晰了解执行结果。



## 7. `08_web_Base32.php`              

### 1. 功能

Base32类，模块，在 08_webAccessPaper.php 中调用，用于doi号编码，构建论文查看链接




## 8. `08_web_update_paper_status.php`     

### 1. 功能
 
接收前端发送的 DOI（论文唯一标识）和新的论文状态这两个参数，然后根据这两个参数去数据库更新对应论文的状态，并将更新结果以 JSON 格式返回给前端。


1. 引入外部文件

```php
require_once '08_db_config.php';           // 数据库连接
require_once '08_category_operations.php'; // 内含 getPaperByDOI() 和 updatePaperStatus()
```

2. 调用 `getPaperByDOI($mysqli, $doi)` 函数，去数据库查找对应 DOI 的论文。

3. 获取查询到的 `$paper` 的 ID（这里假设字段是 `paperID`），然后调用 `updatePaperStatus($mysqli, $paperID, $newStatus)` 进行数据库更新操作。

核心流程：前端发送带有 doi 和 status 的 JSON 请求 → 服务器获取这两个参数 → 根据 doi 查找对应论文 → 将论文状态更新为新的状态 → 将更新结果以 JSON 格式返回给前端。


### 2. 环境变量



# 5. web交互脚本

## 1. `08_webAccessPaper.php`

### 1. 编程思路

💡 **1. 初始思路**

注意，能否编写一个php脚本，运行在云服务器中，在web页面上访问时可以显示目前已有的分类，调用上述 `08_category_operations.php` 模块来实现。具体要求如下：

1. 左侧显示一个容器，容器的宽度约为页面宽度的25%，其中包含一个3列多行的表格，表格的行数由 categories 表中的分类标签数决定。

2. 在表格的上方显示三个标签管理选项，依次为创建标签，删除标签和修改标签。
    - 创建标签选项下方显示一个输入框即可，点击创建即可在 categories 表中创建表中未存在的标签，如果已存在该标签则给出提示。
    - 删除标签选项下方显示一个输入框即可，点击删除即可在 categories 表中删除表中已存在的标签，如果不存在该标签则给出提示。
    - 修改标签选项下方显示2个输入框即可，分别为原标签和新标签，点击修改即可在 categories 表中修改表中已存在的标签，如果不存在该标签则给出提示。

3. 右侧约75%的页面宽度用于显示每个标签下的论文标题、作者、出版年份和期刊名。点击左侧表格中的标签，右侧页面会相应显示相应标签下的论文标题和doi号。每行显示一个论文标题，论文标题下方用小号字体显示作者、出版年和期刊名。论文标题对应一个超链接，点击标题能够在新页面打开 `"https://doi.org/"+doi` 链接



💡 **2. 新增思路**

请修改上述 `08_webAccessPaper.php` 代码，在右侧每个论文标题下面的“标签”开头行新增如下设置：

1. 如果 status 值为`CL`，则在“标签”旁显示一个 “删除”提示，点击“删除”后会将 `CL` 变成 `DL`；还显示一个 “查看” 提示，点击“查看”后，会在新的标签页打开链接 `"https://domain.com/08_paperLocalStorage/" + base32编码后的doi + ".pdf" 链接`（例如：`https://domain.com/08_paperLocalStorage/GEYC4MJQGA3S64ZRGE3DMMZNGAYTKLJQGM3TILJS.pdf`） base32编码方式参考 6. 附录
2. 如果 status 值为`DL`，则在“标签”旁显示一个 “删除中” 提示
3. 如果 status 值为`C`，则在“标签”旁显示一个 “下载”提示，点击“下载”后会将 `C` 变成 `DW`
4. 如果 status 值为`DW`，则在“标签”旁显示一个 “下载中” 提示
5. 如果 status 值为`L`，则在“标签”旁显示一个 “查看” 提示，点击“查看”后，会在新的标签页打开链接 `"https://domain.com/08_paperLocalStorage/" + base32编码后的doi + ".pdf"` 链接
6. 如果 status 值为`N`，则“标签”旁不用显示任何按钮和提示

上述“标签”旁新增按钮和提示的样式和“标签”一致（包括字体大小、颜色、样式等）

注意：上述需求的实现可能需要调用 `08_db_config.php`、`08_category_operations.php` 模块，可能需要新增一些函数

保持上述 `08_webAccessPaper.php` 代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。



💡 **3. 新增思路**

上述`08_webAccessPaper.php`代码是正常工作的，但是有一个地方需要优化，分析和需求如下：

1. 运行上述代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），每条论文信息下面还有“标签”按钮，点击该按钮会打开一个新的窗口，可以看到数据库中的所有论文分类名称以及当前论文所属的分类，还可以通过勾选来更改论文所属分类。还有“保存”和“取消”两个按钮来确认是否进行分类更改操作。这些功能都是正常工作的。

2. 但是上述“标签”按钮打开的新的窗口中，将所有的论文分类名称显示为1列，当分类非常多的时候，由于这个窗口高度有限，会使得最下方的分类名称以及“保存”和“取消”两个按钮均被覆盖，无法看到并进行相关操作。我认为最简单的解决方法就是添加一个纵向滚动条，对于超出窗口区域的部分通过滑动滚动条来查看。

3. 但我觉得上述方法不够完美，因为如果只显示为1列的话，对于整个屏幕的空间并未充分利用。因此，我觉得可以显示为4列，按照paperID的顺序从左到右，从上到下显示为4列，显示的行数根据分类名称的数量来确定，当分类的行数很多，并超过了窗口的高度时，则出现纵向滚动轴来解决分类名称和按钮被覆盖、看不到的问题。

4. 另外，可以在窗口的右上角添加一个叉，用来关闭“标签”按钮打开的窗口。


- 实际解决方案：
  - 弹窗 `#categoryModal` 被限制了最大高度 `max-height: 80%`，若内容超出则出现纵向滚动条。
  - 分类复选框通过 `display: grid; grid-template-columns: repeat(5, 1fr);` 分成了 5 列。
  - 在弹窗右上角添加了“X”按钮（`.close-btn`），点击即可关闭弹窗。



💡 **4. 新增思路**

上述`08_webAccessPaper.php`代码是正常工作的，但是有一个地方需要优化，分析和需求如下：

1. 运行上述代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），每条论文信息下面还有“标签”按钮，该行还有基于数据库中status状态码显示的其他按钮（查看、下载、删除、下载中、删除中等）

2. 现在我有一个新的需求，在该行再新增两个提示按钮，分别为 `“复制DOI”` 和 `“复制编码DOI”`，注意 `“复制编码DOI”` 指的是复制base32编码后的doi，点击这两个按钮可以复制相应内容，以便我在其他地方粘贴。由于数据库中存储了每篇论文的doi，而且上述代码中也有对doi进行base32编码的实现。因此，新需求的实现可以调用/复用已有的相关代码。

3. 上述新增按钮和提示的样式和“标签”一致（包括字体大小、颜色、样式等）

保持上述 `08_webAccessPaper.php` 代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。







### 3. 环境变量

1. `require_once` 语句引入了其他 PHP 模块，确保这些文件在新环境中的路径正确。

```php
// 引入数据库连接模块和分类操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 引入 Base32 编码类（请确保本地存在 08_web_Base32.php 并包含题目中的实现）
require_once '08_web_Base32.php';
```


2. 设置网站图标（favicon）的路径。

```html
<link rel="icon" href="https://domain.com/00_logo/endnote.png" type="image/png">
```


3. JavaScript 中的 AJAX 请求路径，这些脚本用于获取和更新分类及论文状态。

```js
// 获取所有分类
fetch('08_tm_get_categories.php')

// 获取当前论文已勾选的分类
fetch('08_tm_get_paper_categories.php?doi=' + encodeURIComponent(doi))

// 更新论文分类
fetch('08_tm_update_paper_categories.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ 
        doi: doi, 
        categoryIDs: categoryIDs 
    })
})

// 更新论文状态
fetch('08_web_update_paper_status.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ 
        doi: doi, 
        status: newStatus 
    })
})
```


4. 构建用于查看论文 PDF 的链接

```php
echo '<button type="button" onclick="window.open(\'https://domain.com/08_paperLocalStorage/' . urlencode($encodedDOI) . '.pdf\', \'_blank\')">查看</button>';
```


5. 将论文标题链接到 DOI 页面

```php
<a href="https://doi.org/<?= htmlspecialchars($paper['doi']) ?>" target="_blank">
    <?php echo $paper['title']; ?>
</a>
```


6. Header 重定向 URL，根据实际修改脚本名 `08_webAccessPaper.php`

```php
// 处理完 POST 请求后刷新页面并显示消息
header("Location: 08_webAccessPaper.php?message=" . urlencode($message));
exit();
```



## 2. `08_base32_tool.php`

功能：base32 在线编码和解码

### 1. 标准Base32

1. 字母表:
   - 使用标准的Base32字母表：A-Z 和 2-7，与RFC 4648定义的一致。

2. 编码过程:
   - 将输入数据转换为二进制表示，每个字符对应8位。
   - 将整个二进制字符串分割成5位一组，因为Base32每个字符表示5位数据。
   - 如果二进制长度不是5的倍数，则在末尾填充0，直到达到5的倍数。
   - 将每个5位块转换为对应的Base32字符。
   - 最终输出的Base32字符串长度应该是8的倍数，不足部分使用=进行填充。

3. 解码过程:
   - 移除输入中的填充字符=。
   - 将每个Base32字符转换回对应的5位二进制。
   - 将所有5位块连接成一个完整的二进制字符串。
   - 将二进制字符串每8位转换回原始字符，忽略不完整的字节。

4. 填充规则:
   - 根据输入的字节数，填充=字符以确保输出长度为8的倍数。具体来说：
   - 输入字节数模40（即每8个Base32字符）决定需要多少填充。

5. 错误处理:
   - 在解码过程中，如果遇到不在Base32字母表中的字符，应返回错误或false。



### 2. base32编码类实现


1. base32编码类实现代码

```php
// Base32编码和解码函数
class Base32
{
    private static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function encode($input)
    {
        if (empty($input)) return '';

        $binary = '';
        // 将每个字符转换为其二进制表示
        for ($i = 0; $i < strlen($input); $i++) {
            $binary .= str_pad(decbin(ord($input[$i])), 8, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串填充到5的倍数
        $binary = str_pad($binary, ceil(strlen($binary) / 5) * 5, '0', STR_PAD_RIGHT);

        $base32 = '';
        for ($i = 0; $i < strlen($binary); $i += 5) {
            $chunk = substr($binary, $i, 5);
            $index = bindec($chunk);
            $base32 .= self::$alphabet[$index];
        }

        // 添加填充
        $padding = strlen($base32) % 8;
        if ($padding !== 0) {
            $base32 .= str_repeat('=', 8 - $padding);
        }

        return $base32;
    }

    public static function decode($input)
    {
        if (empty($input)) return '';

        // 移除填充字符
        $input = strtoupper($input);
        $input = rtrim($input, '=');

        $binary = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            $index = strpos(self::$alphabet, $char);
            if ($index === false) {
                // 无效的Base32字符
                return false;
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串转换回原始字符串
        $decoded = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            if (strlen($byte) < 8) {
                // 忽略不完整的字节
                break;
            }
            $decoded .= chr(bindec($byte));
        }

        return $decoded;
    }
}
```


🔯 **2. 对比标准base32**

1. 字母表:

   - 用户代码使用的字母表与RFC 4648标准一致。

2. 编码过程:

   - 用户代码正确地将输入转换为二进制，并按5位分割。
   - 使用str_pad在二进制末尾填充0，使其长度为5的倍数，这与标准一致。
   - 将每个5位块映射到Base32字符。
   - 添加填充=以确保输出长度为8的倍数。

3. 解码过程:

   - 用户代码正确地移除填充字符并将输入转换为大写（标准是大小写不敏感的）。
   - 将每个Base32字符转换回5位二进制。
   - 将二进制字符串按8位分割，并转换回原始字符，忽略不完整的字节。

4. 填充规则:

   - 用户代码根据输出长度模8来决定填充数量，这与标准一致。

5. 错误处理:

   - 在解码过程中，如果遇到无效字符，函数返回false，符合预期的错误处理。




## 3. `08_web_crossRef_query.php`

### 1. 编程思路

💡 **1. 初始思路**

1. `08_tm_paperManagement.js`油猴脚本是可以正常工作的，包括利用谷歌学术页面提取的参考文献通过crossRef API获取论文的元数据（doi，论文标题，期刊名等），也能够对doi进行正确的base32编码，`“复制 Base32”` 按钮也能正常复制工作。`“标签”`按钮也能够正常工作，能够通过勾选方框或者取消对号实现对论文分类更新保存。
2. 但是上述`08_tm_paperManagement.js`油猴脚本有一个美中不足的地方，即油猴脚本是基于crossRef API返回的 `data.message.items[0]` 来获取论文元数据的，如果返回的 `items[0]` 不是正确的，或者是信息缺失的，就不能正常工作了。事实上，API会返回 20个items，第一个items通常都是最准确的，但往往也有例外，但即使第一个不完美，其他items中也能找到想要的那个。综上，我的需求如下：
   - 修改上述油猴脚本，使其变成一个php脚本，里面可以结合 javascript、php、html语言等等，运行在云服务器上，用户可以通过web页面访问。
   - 现有的功能保持不变，上述油猴脚本中是通过提取谷歌学术页面中的参考文献作为 crossRef API 查询输入，现在只需要在页面中显示一个输入框，用户通过输入框提交查询信息，作为api查询的输入，不在依赖于从谷歌学术页面提取api的查询信息。因此，油猴脚本中`“提取内容并查询 DOI”`这个按钮在新脚本中就变成了`“查询”`按钮了。
   - 上述油猴脚本中只显示api返回的`items[0]`的数据，并将其作为潜在的数据库写入信息。新脚本中需要在页面中显示api返回的所有items信息（有20条，items从0到19），当然每条items提取的还是油猴脚本在页面中显示的那些信息。然后在每条items信息的下方显示  `“复制 Base32”` 按钮和`“标签”`按钮，这两个按钮的功能务必与油猴脚本一致，不能更改。与油猴脚本的区别在于，油猴脚本中这两个按钮处理的数据是对应于`items[0]`，新脚本中的按钮处理的数据是对应于 当前items的。这样用户可以自由选择将哪一条items的信息写入到数据库了。
   - 请参考油猴脚本中的相关功能实现，对上述提到的需求进行代码实现，注意展示的网页要美观，尤其是对于多个items查询信息的展示（可参考谷歌检索结果页面）。请输出编写的完整代码。




💡 **2. 新增思路**

上述`08_web_crossRef_query.php`代码非常棒，可以正常工作，但是觉得有一个地方可以优化，如下：

1. 当api使用高峰期时，检索等待的时间可能较长，由于上述代码在检索过程中页面没有变化，用户不知道脚本是否已经在进行查询。

2. 我的需求是，在检索等待的过程中能否添加一些提示或者指示，例如一个进度条或者一个转圈的的圈圈等等，如果有更用户友好的图标或者设计，也请使用，方便用户无法判断是否仍然在检索。

实际解决方案：当用户点击「查询」按钮后，就会显示一个半透明遮罩与转圈图标，提示用户页面正在进行检索。一旦获取到数据或者出错，立刻隐藏转圈动画。




💡 **3. 新增思路**

上述`08_web_crossRef_query.php`代码中api请求链接只能用于查询 论文标题/参考文献等关键词，无法用于查询 doi 号。我的需求是：

1. 在搜索输入框下面的一行显示两个选项提示，分别是 title 和 doi，每个提示前显示一个选择符号（选中后里面会是实心的图标，否则是空心的），默认选择 title 。

2. 如果用户在查询前选择的是title，则认为用户在输入框中输入的是论文标题/参考文献等关键词，则使用url1= `https://api.crossref.org/works?query=${encodeURIComponent(query)}&rows=20` 作为api请求链接。

3. 如果用户在查询前选择的是doi，则认为用户在输入框中输入的是doi，则使用url2= `https://api.crossref.org/works/{DOI}`作为api请求链接。

4. 需要注意的是，url1返回的 `data.message.items`这个键的值是一个列表，包含查询的多个可能结果，上述代码是正确解析的，可以继续采用。但由于doi的唯一性，url2只会返回一个结果，只会返回 `data.message`，没有items这个键，这一点需要注意。url2返回的 message 包含的内容如下，与url1返回的 `data.message.items` 中的每一个结果结构类似。

```
indexed {…}
reference-count 43
publisher   "Elsevier BV"
license […]
funder  […]
content-domain  {…}
short-container-title   […]
published-print {…}
DOI "10.1016/j.cej.2024.150788"
type    "journal-article"
created {…}
page    "150788"
update-policy   "http://dx.doi.org/10.1016/elsevier_cm_policy"
source  "Crossref"
is-referenced-by-count  0
title   […]
prefix  "10.1016"
volume  "488"
author  […]
member  "78"
reference   […]
container-title […]
original-title  []
language    "en"
link    […]
deposited   {…}
score   1
resource    {…}
subtitle    []
short-title []
issued  {…}
references-count    43
alternative-id  […]
URL "https://doi.org/10.1016/j.cej.2024.150788"
relation    {}
ISSN    […]
issn-type   […]
subject []
published   {…}
assertion   […]
article-number  "150788"
```

总结：实现了「title/doi」双检索模式的完整需求，其他加载指示器、分类管理、复制 Base32 等均未改变。





### 2. 环境变量

1. 注意模块调用和API礼貌池

```php
const API_BASE_URL = 'https://domain.com/'; // 与原油猴脚本保持一致，php模块调用

// 可添加 mailto 参数使用API礼貌池提升查询性能
// apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(query)}&rows=20`;
apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(query)}&mailto=your-email@example.com&rows=20`;
apiUrl = `https://api.crossref.org/works/${encodeURIComponent(query)}`;

fetch(API_BASE_URL + '08_tm_add_paper.php',

fetch(API_BASE_URL + '08_tm_get_categories.php')

fetch(API_BASE_URL + `08_tm_get_paper_categories.php?doi=${encodeURIComponent(doi)}`)

fetch(API_BASE_URL + '08_tm_update_paper_categories.php', {
```


2. 确保 `0 All papers` (实际上 `categoryID=1`) 要强制选中

```js
        // 0 All papers (实际上 categoryID=1) 要强制选中
        if (catIDNum === 1) {
            checkbox.checked = true;
            checkbox.disabled = true;
        } else {
            if (numericPaperCategories.includes(catIDNum)) {
                checkbox.checked = true;
            }
        }
```



# 6. 服务器端脚本

## 1. `08_server_update_paper_status.php`

### 1. 编程思路

1. 获取paper_db数据库中papers表格中所有论文的doi和status，

2. 获取服务器 `$local_dir = "/home/01_html/08_paperLocalStorage"` 目录（A）下的所有pdf文件名，使用 rclone 获取onedrive指定 `$remote_dir = "rc4:/3图书/13_paperRemoteStorage"` 目录（B）中的所有pdf文件名，注意A和B目录下文件名都是base32编码后的文件名(如，`base32Filename.pdf`，其中`"base32Filename"`是已被base32编码部分)，`base32Filename.pdf` 格式如下所示

```
GEYC4MJQGA3S64ZRGE3DMMZNGAYTKLJQGM3TILJS.pdf
GEYC4MJQGE3C62ROM5RWCLRSGAYTSLRQG4XDAMZY.pdf
GEYC4MJQGM4C64ZUGE2TKNZNGAZDALJQGA2TSMRNPI======.pdf
```

3. 对数据库中每一个doi进行base32编码，并拼接`".pdf"`字符串，获取编码后的完整pdf文件名，对于每一个编码后的pdf文件名，进行以下操作：
   - 如果编码后的pdf文件名 在A和B目录同时存在，判断其status是否为`CL`或者`DL`，如果都不是，则需要将其赋值为`CL`。
   - 如果编码后的pdf文件名 在B目录存在，A目录不存在，则检查status是否为`C` 或者 `DW`，如果都不是，则需要将其赋值为`C`。
   - 如果编码后的pdf文件名 在B目录不存在，A目录存在，则检查status是否为`L`，如果不是，则需要将其赋值为`L`。
   - 如果编码后的pdf文件名 在A和B目录同时都不存在，则检查status是否为`N`，如果不是，则需要将其赋值为`N`。
   - 如果某个论文的status是`DW`，则执行rclone下载操作，下载成功后，将status由`DW`设置为`CL`。
   - 如果某个论文的status是`DL`，则执行本地删除，删除成功后，将status由DL设置为`C`。


```
B           A
onedrive    服务器      status
1           1           CL   DL (to C)
1           0           C    DW (to CL)
0           1           L
0           0           N
```


4. rclone下载操作可以参考如下部分

```php
$remote_file_path = $remote_dir . '/' . base32Filename.pdf;
$local_file_path = $local_dir;
$copy_command = "rclone copy '$remote_file_path' '$local_file_path'";
exec($copy_command, $copy_output, $copy_return_var);
if ($copy_return_var != 0) {
    echo "Failed to copy " . base32Filename.pdf . "\n";
} else {
    echo "Copied " . base32Filename.pdf . " successfully\n";
}
```


### 2. 环境变量





# 7. tampermonkey 脚本

## 1. `08_tm_paperManagement.js`

### 1. 编程思路

上述油猴脚本能够基于 crossRef API 返回的信息显示该篇论文标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商 这些信息（用户点击“提取内容并查询 DOI”按钮之后页面会获取上述论文的元信息），我想要把这些信息写入到云服务器 paper_db 数据库中的 papers 表格中并对该篇论文进行分类。我的需求如下：

1. 在油猴脚本中再新增一个按钮“标签”，点击标签按钮之后会把  crossRef API 返回的、在页面显示的 论文标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商 这些信息 都写入到papers 表格中，如果crossRef API返回的上述信息中部分缺失，则缺失项不用写入，维持表格的默认值。期刊ISSN只写入印刷版即可。另外，在写入该条论文数据前，请将该论文的doi与papers 表格中已有的doi进行比对，如果已经存在，则不用写入该条论文的元数据。

2. 点击“标签”按钮的同时还需要显示 categories 表中的所有分类标签，每个标签前面显示一个小的正方框，如果该篇论文属于某个标签，则对应方框中会显示一个对号。用户可以点击方框来添加或者取消对号。需要通过paperCategories 表该操作来实现论文分类。

3. categories 表中有一个标签是"0 All papers"，默认给所有的论文都添加该标签，且页面中无法取消该方框前的对号

如果上述需求的实现需要在服务器中引入新的php模块，可请编写相关模块，目前已有的模块 `08_db_config.php`、`08_category_operations.php` 也可以调用。



### 2. 函数调用及对应后端API

1. `sendPaperData(data)`

   - 调用的后端API：`08_tm_add_paper.php`
   - HTTP方法：POST
   - 目的：将提取的论文数据（包括标题、作者、期刊名、出版年、卷号、期号、页码、文章号、DOI、ISSN、出版商）发送到服务器，以便将论文信息添加到数据库中。如果论文已存在，返回已有的paperID，避免重复插入。


2. `fetchCategories()`

   - 调用的后端API：`08_tm_get_categories.php`
   - HTTP方法：GET
   - 目的：从服务器获取所有可用的分类信息（如“科学”、“技术”等），用于在前端展示分类选项，供用户选择或更新论文的分类标签。


3. `fetchPaperCategories(doi)`

   - 调用的后端API：`08_tm_get_paper_categories.php?doi=ENCODED_DOI`
   - HTTP方法：GET
   - 目的：根据论文的DOI从服务器获取该论文当前所属的分类ID数组，帮助前端展示论文已存在的分类标签，便于用户进行更新或修改。


4. `updatePaperCategories(doi, categoryIDs)`

   - 调用的后端API：`08_tm_update_paper_categories.php`
   - HTTP方法：POST
   - 目的：将用户选择或更新后的分类ID数组发送到服务器，更新数据库中该论文的分类信息，确保论文在不同分类中的正确归属。


### 3. 新增/取消分类实现

1. 分类选择界面：

   - 当用户点击“标签”按钮后，脚本会展示一个分类选择界面，列出所有可用的分类。

   - 默认分类（`categoryID = 1`，通常代表“所有论文”）始终被选中且不可取消，确保每篇论文至少属于这个默认分类。

   - 已有分类：如果论文已经属于某些分类，这些分类的复选框会默认被选中，用户可以选择保留或取消这些分类。

   - 新增分类：用户可以勾选尚未关联的分类，以将论文添加到这些新的分类中。


2. 收集用户选择：

   - 用户在分类选择界面中勾选或取消某些分类后，脚本会收集所有被选中的categoryID，确保默认分类`categoryID = 1`始终包含在内。

   - 例如，如果用户取消了一些旧分类并新增了一些新分类，最终发送的categoryIDs数组将仅包含用户当前选中的分类ID。


3. 事务管理：

   - 删除现有分类：首先，删除论文当前所有的分类关联，确保旧的分类被清除。

   - 插入新分类：然后，根据用户最新的选择，插入新的分类关联。这包括保留用户未取消的旧分类和新增的分类。

   - 提交或回滚事务：如果所有操作成功，提交事务，确保数据库中的分类信息与用户的最新选择一致；如果发生任何错误，回滚事务，保持数据库状态不变。



### 4. 环境变量

```js
// @connect      api.crossref.org
// @connect      domain.com

// 配置您的服务器API基础URL
const API_BASE_URL = 'https://domain.com/'; // 确保末尾有斜杠

// 使用礼貌池提升查询性能
const apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(reference)}&mailto=GroovyBib@example.org`;

url: API_BASE_URL + '08_tm_add_paper.php',

url: API_BASE_URL + '08_tm_get_categories.php',

url: API_BASE_URL + `08_tm_get_paper_categories.php?doi=${encodeURIComponent(doi)}`,

url: API_BASE_URL + '08_tm_update_paper_categories.php',

```

1. `@connect` 指令用于声明脚本可以进行跨域请求的目标域。这是 Tampermonkey 的安全机制，确保脚本只能与指定的域进行通信。










