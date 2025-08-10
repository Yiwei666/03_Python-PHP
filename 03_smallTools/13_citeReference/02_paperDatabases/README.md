# 1. 项目功能

1. 将谷歌学术页面及crossRef API返回的论文元数据写入到云服务器的mysql数据库中
2. web在线管理论文分类标签（增删查改）和论文访问


# 2. 文件结构

### 1. 文件目录

```php
# 1. 功能模块
08_db_config.php                          # 创建数据库连接对象
08_category_operations.php                # php模块，实现了对论文及其分类信息的创建、查询、更新和删除（CRUD）功能，并管理了论文与分类之间的关联关系
08_api_auth.php                           # php模块，后端API中调用，用于统一处理 API 密钥认证逻辑
08_tm_add_paper.php                       # 基于油猴脚本传递的论文元数据，检查数据库中是否存在相同doi，插入论文数据，并分配默认分类
08_tm_get_categories.php                  # 返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名
08_tm_get_paper_categories.php            # 基于doi查找论文的paperID，基于paperID查找论文所属分类
08_tm_update_paper_categories.php         # 基于doi查找论文的paperID，基于paperID更新论文所属分类

08_web_Base32.php                  # Base32类，模块，在 08_webAccessPaper.php 中调用，用于doi号编码，构建论文查看链接
08_web_update_paper_status.php     # 接收前端发送的 DOI 和新的论文状态这两个参数，然后根据这两个参数去数据库更新对应论文的状态，并将更新结果以 JSON 格式返回给前端。
08_web_update_rating.php           # 根据 DOI 查询论文当前的评分（rating），也能在收到合法的 0–10 整数评分时将其写入数据库。

# 2. web交互
08_webAccessPaper.php              # 在线管理论文分类（创建、删除、修改分类标签），在线更改论文所属分类，在线更改论文所属状态码（下载/删除/查看等）
08_base32_tool.php                 # base32在线编码和解码，主要用于doi编码
08_web_crossRef_query.php          # 在web页面上查询显示论文的元数据（展示crossRef API返回的多条结果），能够将元数据写入到数据库并进行分类，功能类似 08_tm_paperManagement.js

# 3. 油猴脚本
08_tm_paperManagement.js           # 油猴脚本（基于01_GBT_api_items.js扩展），通过在谷歌学术页面提取参考文献，结合crossRef API查询论文的元数据，并将论文元数据写入到mysql数据库中（可选），还能够给论文进行在线新增/取消/更改分类（可选），能够复制doi的base32编码。

# 4. 服务器端脚本
08_server_update_paper_status.php                # 更新数据库中论文状态码、基于论文状态码执行下载、删除等操作，可用于cron定时执行
08_server_paper_management.php                   # 对 papers 表进行管理操作，包括查询、筛选、统计、展示表结构以及修改记录或表结构等多种功能
08_server_update_citation_all_random.php         # 更新论文引用数，从所有具有标准doi值的行中，随机选取一行更新引用数，不限制引用数是否为0。适合不断更新论文的引用情况，需较长时间。
08_server_update_citation_topN_random.php        # 更新论文引用数，按照paperID降序，从引用数为0的前N行中随机选取一行更新引用数，注意前N行引用数均为0的情况。适合更新最新导入数据库的论文。
08_server_insert_paper_doi_defined.php           # 手动插入论文信息到数据库中，支持 json 格式输入，尤其是没有 doi 号的论文 ，默认分类到 categoryID = 1，可选分类到 123。

# 5. 客户端脚本
08_client_doi_base32_scidownl.py          # 在windows客户端上输入doi号，下载对应pdf论文，使用doi号的base32编码进行命名
```

### 2. 项目思路

1. 首先在谷歌学术页面中检索文献，然后点击相应文献的引用按钮，再通过油猴脚本获取引用中的论文标题
2. 在油猴脚本中基于crossRef API检索论文标题，查找相应文献，获取相应的元数据，包括doi
3. 如果基于论文标题检索的论文标题不准确，则需要在web脚本 `08_web_crossRef_query.php` 基于doi号进行检索
4. 下载相应的pdf论文，使用base32编码后的doi号进行命名，放到onedrive相应文件夹下；使用 rclone 可以将 onedrive 中的论文同步到 google drive 中，以便在 gemini 中导入。

```sh
# 将onedrive中的论文数据库同步到google drive中 
*/5 * * * * /usr/bin/rclone sync 'rc4:/3图书/13_paperRemoteStorage/' 'gd1:/13_paperRemoteStorage/' --transfers=16
```

5. 通过 `08_webAccessPaper.php` 在线访问论文数据库，包括创建/更改论文分类，在线查看pdf论文等。点击论文标题可以跳转到相关的论文网页（通过doi链接）。


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



3. 将 `title` 列的长度从 `VARCHAR(255)` 增加到 `VARCHAR(355)`

```sql
ALTER TABLE papers MODIFY title VARCHAR(355) NOT NULL;
```


4. 可以将 title 列更改为 TEXT 类型：适用于存储变长且可能非常长的文本数据（本项目未采用）

```sql
ALTER TABLE papers MODIFY title TEXT NOT NULL;
```

- 使用 `TEXT` 类型的场景
  - 当 `VARCHAR` 不足以满足需求时，可以考虑使用 `TEXT` 类型
  - `TINYTEXT`：最多 `255` 字节。
  - `TEXT`：最多 `65,535` 字节（约 64 KB）。
  - `MEDIUMTEXT`：最多 `16,777,215` 字节（约 16 MB）。
  - `LONGTEXT`：最多 `4,294,967,295` 字节（约 4 GB）。

- 特点：
  - 不支持默认值：与 VARCHAR 不同，TEXT 类型的列不能有默认值。
  - 索引限制：只能对 TEXT 列的前缀进行索引，无法对整个列进行索引。
  - 存储方式：TEXT 类型的列通常存储在表外，访问时可能会有性能开销。


5. 新增 `rating` 列，取值为大于等于0的整数，默认值为0。

```sql
ALTER TABLE papers
ADD COLUMN rating INT UNSIGNED NOT NULL DEFAULT 0;
```


6. 新增 `doi_type` 列和 `citation_count` 列
   - 新增 `doi_type` 列， 默认值为 NULL，取值为 T 和 F，分别代表标准类型的doi和非标准类型的doi。
   - 新增 `citation_count` 列，默认值为 0，取值为大于等于0的整数。

```sql
ALTER TABLE papers
ADD COLUMN doi_type ENUM('T', 'F') DEFAULT NULL;

ALTER TABLE papers
ADD COLUMN citation_count INT UNSIGNED DEFAULT 0;
```



7. 添加 `status`, `rating`, `doi_type` 和 `citation_count` 列后的`papers` 表结构

```
mysql> describe papers;
+------------------+----------------------------------+------+-----+---------+----------------+
| Field            | Type                             | Null | Key | Default | Extra          |
+------------------+----------------------------------+------+-----+---------+----------------+
| paperID          | int                              | NO   | PRI | NULL    | auto_increment |
| title            | varchar(355)                     | YES  |     | NULL    |                |
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
| rating           | int unsigned                     | NO   |     | 0       |                |
| doi_type         | enum('T','F')                    | YES  |     | NULL    |                |
| citation_count   | int unsigned                     | YES  |     | 0       |                |
+------------------+----------------------------------+------+-----+---------+----------------+
16 rows in set (0.01 sec)
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


4. 修改表中现有记录的值

```sql
UPDATE papers
SET title = 'Molecular Dynamics Analysis of the Microstructure of the CaO-P<sub>2</sub>O<sub>5</sub>-SiO<sub>2</sub> Slag System with Varying P<sub>2</sub>O<sub>5</sub>/SiO<sub>2</sub> Ratios'
WHERE paperID = 345;
```


5. 查找 `doi` 值相同（重复）的行

```sql
SELECT * FROM papers  
WHERE doi IN (  
    SELECT doi  
    FROM papers  
    WHERE doi IS NOT NULL AND doi <> ''  
    GROUP BY doi  
    HAVING COUNT(*) > 1  
)  
ORDER BY doi;
```

- `GROUP BY doi`：按 doi 分组。
- `COUNT(*) > 1`：筛选出 doi 出现次数大于 1 的行，即重复的 doi。
- `WHERE doi IS NOT NULL AND doi <> ''`：排除 NULL 和空字符串的 doi 值，以免影响结果。



### 2. 别名alias

1. 备份数据库

```sh
alias dpaper='mysqldump -p paper_db > /home/01_html/08_paper_db_backup_$(date +%Y%m%d_%H%M%S).sql'
```



# 4. php功能模块

## 1.1 `08_db_config.php`

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



## 1.2 `08_api_auth.php`

### 1. 编程思路

1. 编写一个API认证php模块（08_api_auth.php），确保只有拥有有效API密钥的请求才能访问和操作您的后端API。该模块在相应的后端api中进行调用。
2. 为了配合后端的API密钥验证，前端油猴脚本需要在所有向后端API发送的请求中添加正确的头部。


### 2. 功能和环境变量


1. 功能：
   - 提供 `checkApiKey()` 函数，验证请求头 `X-Api-Key` 是否与服务器预设的密钥一致。
   - 若认证失败，则直接返回 `HTTP 401` 并终止后续逻辑。


2. 代码实现

```php
<?php
// 08_api_auth.php

/**
 * 检查请求头中的 API Key 是否有效。
 * 如果无效，则返回 401 并终止执行。
 */
function checkApiKey() {
    // 从请求头获取全部 Header
    $headers = getallheaders();

    // 这里设置服务器端预设的有效 API Key （生产环境建议更安全的存储方式）
    $validKey = 'YOUR_API_KEY_HERE';

    // 判断是否存在 X-Api-Key 且是否与预设的 validKey 匹配
    if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== $validKey) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401); // 未授权
        echo json_encode(['success' => false, 'message' => 'Invalid or missing API key']);
        exit();
    }
}
```


3. 环境变量

生产环境中请将 `YOUR_API_KEY_HERE` 替换为真正的密钥，并确保不要将其暴露在公共仓库中。

```php
// 这里设置服务器端预设的有效 API Key （生产环境建议更安全的存储方式）
$validKey = 'YOUR_API_KEY_HERE';
```


### 3. 模块调用

1. 后端被调用的api脚本中使用如下代码进行验证：

```php
// [MODIFIED] 引入 API 认证
require_once '08_api_auth.php';
checkApiKey();
```

2. 本项目中调用 `08_api_auth.php` 的后端脚本如下

```php
08_tm_add_paper.php                       # 基于油猴脚本传递的论文元数据，检查数据库中是否存在相同doi，插入论文数据，并分配默认分类
08_tm_get_categories.php                  # 返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名
08_tm_get_paper_categories.php            # 基于doi查找论文的paperID，基于paperID查找论文所属分类
08_tm_update_paper_categories.php         # 基于doi查找论文的paperID，基于paperID更新论文所属分类

08_web_update_paper_status.php     # 接收前端发送的 DOI 和新的论文状态这两个参数，然后根据这两个参数去数据库更新对应论文的状态，并将更新结果以 JSON 格式返回给前端。
```

3. 本项目中以下前端脚本在调用api时，需要在所有向后端API发送的请求中添加正确的头部（包含`API_KEY`，后端进行验证）。

```
08_webAccessPaper.php
08_web_crossRef_query.php
08_tm_paperManagement.js 
```

- 下面是 `08_webAccessPaper.php` 前端脚本中包含 `API_KEY` 的后端api请求示例

```js
        // 获取当前论文已勾选的分类（通过后端API，如果你有相应的php接口文件）
        function fetchPaperCategories(doi) {
            // [MODIFIED] 在请求头中添加 X-Api-Key
            fetch('08_tm_get_paper_categories.php?doi=' + encodeURIComponent(doi), {
                headers: {
                    'X-Api-Key': API_KEY
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 渲染分类复选框，并根据当前论文的分类勾选
                    renderCategoryCheckboxes(allCategories, data.categoryIDs);
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('获取论文分类时出现错误。');
            });
        }
```



## 2. `08_category_operations.php`

- 功能概述：实现了对论文及其分类信息的创建、查询、更新和删除（CRUD）功能，并管理了论文与分类之间的关联关系

### 1. 函数功能

1. `getCategories($mysqli)`
   - 功能：从数据库中获取所有分类的详细信息
   - 描述：从 `categories` 表中获取所有分类信息，并按分类名称升序排列返回一个包含分类数据的数组，或在失败时返回错误信息。

2. `addCategory($mysqli, $categoryName)`
   - 功能：新增一个分类
   - 描述：向 `categories` 表中插入一个新的分类名，返回操作成功的消息或错误信息。

3. `deleteCategory($mysqli, $categoryID)`
   - 功能：删除指定的分类
   - 描述：根据提供的分类ID删除 `categories` 表中的对应分类，返回成功消息或错误提示。

4. `updateCategoryName($mysqli, $categoryID, $newCategoryName)`
   - 功能：修改分类的名称
   - 描述：更新指定分类ID对应的分类名称为新的名称，返回成功消息或错误提示。

5. `getPapersByCategory($mysqli, $categoryID, $sort = 'paperID_desc')`
   - 功能：获取特定分类下的所有论文，支持可选的排序方式。
   - 描述：查询某一分类下的所有论文信息，可通过`$sort`参数指定排序方式，如按`paperID、publication_year、status、journal_name、authors或title`升序或降序排序。

6. `getPaperByDOI($mysqli, $doi)`
   - 功能：通过DOI获取特定的论文信息
   - 描述：根据论文的DOI从 papers 表中获取其完整信息，返回包含论文信息的数组，或在失败时返回 false。

7. `insertPaper($mysqli, $title, $authors, $journal_name, $publication_year, $volume, $issue, $pages, $article_number, $doi, $issn, $publisher)`
   - 功能：插入一篇新的论文记录
   - 描述：向 `papers` 表中插入新的论文信息，包括标题、作者、出版年份等，返回成功标志和新论文的ID，或在失败时返回错误信息。

8. `getCategoriesByPaperID($mysqli, $paperID)`
   - 功能：获取指定论文的所有分类ID
   - 描述：根据`$paperID`从`paperCategories`关联表中查询所有关联的`categoryID`。成功时返回`categoryID`的数组，失败时返回false。
   - 新增：在 `getPapersByCategory()` 的 `switch` 中新增 `rating_asc` 与 `rating_desc` 两个分支，使用 `ORDER BY p.rating ASC|DESC, p.paperID DESC`。

9. `updatePaperCategories($mysqli, $paperID, $categoryIDs)`
   - 功能：更新指定论文的分类
   - 描述：开始一个数据库事务。删除当前论文`$paperID`在`paperCategories`表中的所有现有分类关联。插入新的分类关联，使用提供的`$categoryIDs`数组。

10. `assignAllPapersCategory($mysqli, $paperID)`
    - 功能：为新插入的论文分配`“0 All papers”`分类。
    - 描述：假设`“0 All papers”`分类的`categoryID`为1（根据代码中设置）。向`paperCategories`表插入一条记录，将论文`$paperID`与`categoryID为1`的分类关联。

11. `updatePaperStatus($mysqli, $paperID, $newStatus)`
    - 功能：根据论文ID更新论文的状态。
    - 描述：更新`papers`表中指定`$paperID`的论文记录，将其`status`字段更新为`$newStatus`。




### 2. 后端接口函数调用

```php
# 1. 08_tm_add_paper.php                       # 基于油猴脚本传递的论文元数据，检查数据库中是否存在相同doi，插入论文数据，并分配默认分类
checkApiKey()                                                        # 执行 API Key 检查 
getPaperByDOI($mysqli, $doi)                                         # 检查数据库中是否已经存在具有指定 DOI 的论文。
insertPaper($mysqli, $title, $authors, $journal_name, $publication_year, $volume, $issue, $pages, $article_number, $doi, $issn, $publisher)
                                                                     # 将新的论文信息插入到数据库中。
assignAllPapersCategory($mysqli, $paperID)                           # 将新插入的论文分配到默认的 "0 All papers" 分类中。


# 2. 08_tm_get_categories.php                  # 返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名
checkApiKey()                                                        # 执行 API Key 检查 
getCategories($mysqli)                 


# 3. 08_tm_get_paper_categories.php            # 基于doi查找论文的paperID，基于paperID查找论文所属分类
checkApiKey()                                                        # 执行 API Key 检查 
getPaperByDOI($mysqli, $doi)                                         # 通过提供的 DOI（数字对象标识符）从数据库中检索对应的论文记录。
getCategoriesByPaperID($mysqli, $paperID)                            # 根据论文的 paperID 获取该论文所属的所有分类 ID。


# 4. 08_tm_update_paper_categories.php         # 基于doi查找论文的paperID，基于paperID更新论文所属分类
checkApiKey()                                                        # 执行 API Key 检查 
getPaperByDOI($mysqli, $doi)                                         # 通过提供的 DOI（数字对象标识符）从数据库中检索对应的论文记录。
updatePaperCategories($mysqli, $paperID, $categoryIDs)               # 更新指定论文的分类。


# 5. 08_web_update_paper_status.php            # 通过 DOI 确认论文存在，并获取其唯一标识 paperID。根据提供的 paperID 更新论文的 status 字段。
checkApiKey()                                                        # 执行 API Key 检查 
getPaperByDOI($mysqli, $doi)                                         # 通过提供的 DOI（数字对象标识符）从数据库中检索对应的论文记录。
updatePaperStatus($mysqli, $paperID, $newStatus)                     # 根据论文的 paperID 更新其 status 字段。

# 6. 08_web_update_rating.php                  # 基于doi查询/更新rating值
checkApiKey()
getPaperByDOI($mysqli, $doi)
```



## 3.1 `08_tm_add_paper.php`

### 1. 功能

基于油猴脚本传递的论文元数据，调用`08_category_operations.php`模块中的函数，检查数据库中是否已存在相同doi（`getPaperByDOI`函数），插入论文元数据（`insertPaper`函数）以及分配默认分类（`assignAllPapersCategory`函数）。


- 设置响应头：配置返回类型为JSON，并允许跨域POST请求，确保客户端能够正确调用API。

- **加载模块**：引入数据库配置模块 (`08_db_config.php`) 和分类操作模块 (`08_category_operations.php`)，提供数据库连接和分类管理功能。执行 API Key 检查

```php
// 引入数据库配置、API认证和操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- 接收请求数据：通过 `php://input` 获取POST请求中的原始JSON数据，并解析为PHP数组，用于后续操作。

- 验证数据：检查请求数据的有效性，确保 doi 不为空，否则返回错误信息并终止流程。

- **检查重复**：调用 `getPaperByDOI` 函数检查数据库中是否已存在相同DOI的论文，若存在则直接返回已有的 paperID，避免重复插入。

- 提取字段：从请求数据中提取论文信息（如标题、作者、期刊名、出版年份等），若字段缺失则设置为 null，保证数据完整性。

- **插入论文**：调用 `insertPaper` 函数，将论文数据写入数据库，若成功则获取新生成的 paperID，失败则返回错误信息。

- **分配默认分类**：调用 `assignAllPapersCategory` 函数，将新论文分配到默认分类 "All papers"，并根据分配结果返回成功或部分成功的响应信息。

- 返回响应：通过JSON格式返回操作结果，包括成功标志（success）、论文ID（paperID）或失败原因（message），确保客户端能够处理相应的结果。






## 3.2 `08_tm_get_categories.php`

### 1. 功能

调用`08_category_operations.php`模块中的 `getCategories`函数，返回数据库中的所有`categoryID` 和 `categoryName` 分类ID及分类名。


- 设置响应头信息：配置返回类型为JSON，允许跨域访问和GET请求，以确保客户端能够正确访问API并解析返回数据。

- 加载必要模块：引入数据库配置模块提供数据库连接对象 $mysqli，以及分类操作模块包含获取分类的函数 getCategories。执行 API Key 检查

```php
// 引入数据库配置、API认证和操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- **获取分类数据**：调用 `getCategories` 函数从数据库查询所有分类信息，返回包含 `categoryID` 和 `categoryName` 的分类数组，如果查询失败则返回 false。

- 判断并返回响应：检查获取的分类数据是否为数组，若成功则返回包含分类信息的JSON响应，若失败则返回包含错误信息的失败响应，保证客户端得到明确的操作结果。



## 3.3 `08_tm_get_paper_categories.php`

### 1. 功能

调用`08_category_operations.php`模块中的函数，基于doi查找论文的paperID（ `getPaperByDOI`函数），通过 `paperID` 查询论文所属分类的ID列表（`getCategoriesByPaperID` 函数）。

- 设置响应头信息：配置返回数据格式为JSON，允许跨域GET请求，确保客户端能够正确访问API并解析返回的数据。

- 加载必要模块：引入数据库配置模块提供数据库连接对象 $mysqli，以及分类操作模块包含查询论文和分类的函数，如 getPaperByDOI 和 getCategoriesByPaperID。执行 API Key 检查

```php
// 引入数据库配置、API认证和操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- 获取并验证DOI参数：通过GET参数获取DOI，去除空格后验证其有效性，若为空则返回错误信息 "DOI不能为空"，并终止流程。

- **查询论文信息**：调用 `getPaperByDOI` 函数，通过DOI从数据库获取对应论文的 `paperID`，若未找到论文则返回错误信息 "未找到对应的论文"。

- **查询分类信息**：调用 `getCategoriesByPaperID` 函数，通过 `paperID` 查询论文所属分类的ID列表，若查询成功则返回分类数组，失败则返回错误信息 "获取分类失败"。

- 返回响应结果：根据查询结果生成JSON响应，包含成功标志、分类ID数组或错误信息，确保客户端能清晰了解操作结果。



## 3.4 `08_tm_update_paper_categories.php`

### 1. 功能

调用`08_category_operations.php`模块中的函数，基于doi查找论文的paperID（ `getPaperByDOI`函数），调用 `updatePaperCategories` 函数，删除论文的旧分类并插入新的分类列表。


- 设置响应头信息：配置API返回的数据格式为JSON，允许跨域POST请求，并确保请求头和方法符合规范，支持接收客户端发送的JSON数据。

- 加载必要模块：引入数据库配置模块提供数据库连接对象 $mysqli，以及分类操作模块用于处理论文的查询和分类更新操作。执行 API Key 检查

```php
// 引入数据库配置、API认证和操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';
```

- 获取并验证POST数据：通过POST请求获取DOI和分类ID数组，确保DOI非空且分类ID为数组格式，若验证失败返回明确的错误信息。

- **查询论文信息**：调用 `getPaperByDOI` 函数，通过DOI查询对应的论文ID（`paperID`），若未找到论文，则返回错误信息 "未找到对应的论文"。

- 确保默认分类存在：检查分类ID数组是否包含默认分类 "All papers" 的 `categoryID = 1`，若不存在则强制加入，确保论文始终属于默认分类。

- **更新论文分类**：调用 `updatePaperCategories` 函数，删除论文的旧分类并插入新的分类列表，若更新成功返回成功消息，若失败则返回具体的错误信息。

- 返回JSON响应：根据操作结果返回明确的JSON响应，包括操作成功与否的标志、成功消息或错误提示，确保客户端能够清晰了解执行结果。



## 4.1 `08_web_Base32.php`              

### 1. 功能

Base32类，模块，在 08_webAccessPaper.php 中调用，用于doi号编码，构建论文查看链接




## 4.2 `08_web_update_paper_status.php`     

### 1. 功能
 
接收前端发送的 DOI（论文唯一标识）和新的论文状态这两个参数，然后根据这两个参数去数据库更新对应论文的状态，并将更新结果以 JSON 格式返回给前端。


1. 引入外部文件

```php
require_once '08_api_auth.php';            // [MODIFIED] 引入 API 认证
require_once '08_db_config.php';           // 数据库连接
require_once '08_category_operations.php'; // 内含 getPaperByDOI() 和 updatePaperStatus()
```

2. 调用 `getPaperByDOI($mysqli, $doi)` 函数，去数据库查找对应 DOI 的论文。

3. 获取查询到的 `$paper` 的 ID（这里假设字段是 `paperID`），然后调用 `updatePaperStatus($mysqli, $paperID, $newStatus)` 进行数据库更新操作。

核心流程：前端发送带有 doi 和 status 的 JSON 请求 → 服务器获取这两个参数 → 根据 doi 查找对应论文 → 将论文状态更新为新的状态 → 将更新结果以 JSON 格式返回给前端。




## 5. `08_web_update_rating.php`

### 1. 功能

一个带 API Key 鉴权的 JSON 接口，能根据 DOI 查询或更新 0–10 整数评分，并统一返回 `{success,message?,rating?}`。该模块在 `08_webAccessPaper.php` 中被调用。


1. CORS 与预检

- 允许跨域请求（`Access-Control-Allow-Origin: *`）。
- 对 OPTIONS 预检请求直接返回 204，不做后续处理。


2. API Key 鉴权

- 引入 `08_api_auth.php` 并调用 `checkApiKey()`，确保每次请求都带有有效的 `X-Api-Key`。


3. 统一响应格式

定义了 respond($success, $message, $rating) 函数，统一以 JSON 返回：

```json
{
  "success": true|false,
  "message": "...",     // 可选
  "rating": 0–10        // 可选
}
```


4. 参数读取与校验

- 从请求体中解析 JSON，必须包含非空的 doi 字段。
- 可选地包含 rating 字段，区分“仅查询”与“更新”两种流程。


5. 查询现有评分

- 若没有显式传入 rating，脚本立即返回该 DOI 对应论文的当前 rating（若未设置则默认为 0）。


6. 更新评分

- 若提交了 rating，先验证它是 0–10 范围内的整数。
- 然后执行 `UPDATE papers SET rating = ? WHERE paperID = ?`，并返回更新后的新值。


7. 错误处理

- 对缺少参数、找不到论文、验证失败、数据库预处理或执行错误，都通过 `respond(false, "...")` 返回对应错误信息。




### 2. 环境变量

```php
// 引入 API 认证、数据库与操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 执行 API Key 检查
checkApiKey();
```




# 5. web交互脚本

## 1. `08_webAccessPaper.php`

📁 **文件结构**

```php
08_webAccessPaper.php
    # 1. 模块调用
        08_db_config.php
        08_category_operations.php
        08_web_Base32.php
    # 2. 后端API调用
        08_tm_get_categories.php
            08_api_auth.php
            08_db_config.php
            08_category_operations.php
        08_tm_get_paper_categories.php
            08_api_auth.php
            08_db_config.php
            08_category_operations.php
        08_tm_update_paper_categories.php
            08_api_auth.php
            08_db_config.php
            08_category_operations.php
        08_web_update_paper_status.php
            08_api_auth.php
            08_db_config.php
            08_category_operations.php
        08_web_update_rating.php                # 将用户输入的评分值传递给后端api
            08_api_auth.php
            08_db_config.php
            08_category_operations.php
```



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

```
状态码       操作按钮                  显示按钮  
--------------------------------------------------
 C          下载 (C to DW )
 DW                                  下载中 (DW)
 CL         删除 (CL to DL)           查看  (CL)
 DL                                  删除中 (DL)
 L                                    查看  (L)
 N                                   无显示 (N)
```


上述“标签”旁新增按钮和提示的样式和“标签”一致（包括字体大小、颜色、样式等）

注意：上述需求的实现可能需要调用 `08_db_config.php`、`08_category_operations.php` 模块，可能需要新增一些函数

保持上述 `08_webAccessPaper.php` 代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。

```
B           A
onedrive    服务器      status
1           1           CL   DL (to C)
1           0           C    DW (to CL)
0           1           L
0           0           N
```



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



💡 **5. 新增思路**

上述 `08_webAccessPaper.php` 代码能够很好的满足我的需求。现在我有一个新的需求：

1. 运行上述代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），这些论文默认是按照 paperID 的降序 进行排列的(基于 `getPapersByCategory` 函数，定义在 `08_category_operations.php` 模块中，下面有相应函数和模块)。

2. 我的需求是，在网页的右上角显示一个 `“工具”` 按钮（样式参考`“标签”`按钮），点击该按钮弹出几个选项，可以点击这些选项来重新排列右侧当前分类标签下的论文显示顺序，选项分别是 `“论文ID升序”、“发表年降序”、“发表年升序”、“状态码降序”、“状态码升序”、“期刊名升序”、“期刊名降序”、“作者名升序”、“作者名降序”、“标题升序”、“标题降序”`。在排序的过程中，对于字符串来说，字母按照A到Z是升序，反之是降序。比较过程中，如果当前字母相同，则比较下一个字母，以此类推。

保持上述 `08_webAccessPaper.php` 代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。如果需要对 调用的 `08_category_operations.php` 模块中的函数进行修改和新增，也一并输出。



💡 **6. 新增思路**

上述`08_webAccessPaper.php`代码是正常工作的。现在我有一个新的需求，具体分析和要求如下：

1. 运行上述代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），在右侧页面上方的 `“论文列表 No. 数字”` 旁边会显示一个`“工具”`按钮，用来调整页面中论文的排序方式。

2. 我的需求是，在 `“工具”` 按钮旁边依次新增 `“全部下载”` 和 `“全部删除”` 两个按钮，这两个按钮的样式和`“工具”`一致（包括字体大小、颜色、样式等）。

3. 点击 `“全部下载”` 时，会核查并修改当前分类标签下所有论文的 `status`，如果 `status` 为 `C` ，则修改为 `DW`，后端会有定时脚本基于`DW`状态码执行下载操作。对于status为其他值的，则不需要修改。

4. 点击 `“全部删除”` 时，会核查并修改当前分类标签下所有论文的 `status`，如果 status 为 `CL`，则修改为 `DL`，后端会有定时脚本基于`DL`状态码执行删除操作。对于status为其他值的，则不需要修改。

保持上述 `08_category_operations.php` 代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。如果需要对调用的 `08_web_update_paper_status.php、08_category_operations.php`中的代码进行修改和新增，也一并输出。



💡 **7. 新增思路**

上述 `08_webAccessPaper.php` 代码是可以正常工作的，但是有一个地方需要优化，分析和需求如下：

1. 上述代码显示的页面中，左侧的上下依次包含两部分，分别是`"分类管理"和"现有分类"`。`"分类管理"`包含创建分类、删除分类和修改分类。`"现有分类"`中则列出了数据库中的所有分类标签，点击其中任一标签，页面右侧会显示当前标签下的所有论文信息。

2. 页面左侧的`"分类管理"`在大部分时候是用不上的，因此为了减少对左侧空间的占用，将`"分类管理"`这部分页面放到一个新窗口中，通过点击 `"分类管理"` 按钮来触发，同时在 `"现有分类"` 文字的旁边添加一个 `"分类管理"` 按钮（按钮字体的大小、颜色、样式等参考页面右侧上方`"论文列表 No. 数字"`旁边的 `"工具" "全部下载" "全部删除"`按钮）。

3. 点击`"分类管理"`按钮，在页面中弹出一个新窗口，在窗口中进行分类管理，包括 创建分类、删除分类和修改分类，窗口中的 分类管理样式可以与上述代码中已有的 `"分类管理"` 界面一样，包括提示词、输入框、字体大小、窗口布局等等，功能也要完全一样。这种实现与点击右侧论文的`"标签"`按钮弹出新窗口并进行标签管理类似。

4. 通过引入`"分类管理"`按钮在新窗口中进行分类管理，可以为左侧节省一部分空间，同时，`"现有分类"`的相关表格展示也可以上移。

5. 把 `"现有分类"` 设置为h2标题级别，点击`"分类管理"`按钮出现的新窗口设置 `top 10%, left 35%, width 30%`。

保持上述 `08_webAccessPaper.php` 代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。



💡 **8. 新增思路**

上述 `08_webAccessPaper.php` 代码是正常工作的，但是有一个地方需要优化，分析和需求如下：

1. 运行上述代码，网页右侧显示左侧相应分类标签下的所有论文信息，每条论文信息目前包含3行，第1行是标题，第2行是出版年、期刊名和作者等，第3行是`"标签"、"下载"、"复制DOI"`等按钮。

2. 我的需求是对于每条论文信息再新增一行，即第4行，显示当前论文所属的所有分类标签（字体颜色 #777，字体大小为 11px）。该功能的实现建议通过直接调用 `08_category_operations.php` 模块中的函数来实现，比如 `getPaperByDOI($mysqli, $doi)`、`getCategoriesByPaperID($mysqli, $paperID)`函数等，相比于使用后端api，模块中的函数调用响应时间更快。

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。




💡 **9. 新增思路**

运行上述 `08_webAccessPaper.php` 代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），每篇论文下方还会显示"标签  删除  查看 复制DOI 复制编码DOI"等按钮，现在我的需求如下：

1. 当用户点击页面中论文的标题、"复制DOI"按钮 或 "复制编码DOI"按钮时，标题或者按钮的字体颜色要变成"#c58af9"，方便用户识别点击过哪些按钮或者标题。

2. 在实现上述需求的过程中，最好通过新增或者调整几行代码来实现，避免大幅修改代码，其余代码不要发生变动，哪怕是新增空格或者修改注释等。我最后会进行代码审查和测试，观察是否实现需求且没有变动其他代码。

输出修改后的完整代码。



💡 **10. 新增思路**


运行上述 `08_webAccessPaper.php` 代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），每篇论文下方还会显示"标签 删除 查看 复制DOI 复制编码DOI"等按钮；最下面的一行是 "分类标签："，包含该论文所属的分类。现在我的需求如下：

1. 编写一个新的模块 `08_web_update_rating.php`，接收前端发送的 DOI（论文唯一标识）和新的`rating`数值这两个参数（rating参数可选，非必需，通过用户输入，如果该参数没有赋值，即代表仅从数据库中查询该doi论文的rating值，不涉及更新），然后根据这两个参数去数据库Papers表更新/查询对应论文的rating值，并将更新/查询结果以 JSON 格式返回给前端。尽量把该模块设计的标准化一些，后续可能还会在其他地方调用。

2. 修改 `08_webAccessPaper.php` 代码，在每篇论文的"标签 删除 查看 复制DOI 复制编码DOI"等按钮所在行新增一个按钮 "评分"，大小、字体等格式与其他按钮保持一致即可。点击该按钮，会弹出一个小窗口，小窗口中有一个输入框，提示用户输入具体的 rating 值，取值在0-10，必须是整数（确保没有非法输入）。用户输入rating值并选择保存(或者取消)后，该rating值会被模块 `08_web_update_rating.php` 更新到数据库的Papers表中。同时页面中关于rating的显示信息也同步更新。小窗口显示在页面中心，高度6cm，宽度8cm左右，窗口右上角需包含窗口关闭图标×。保存或者取消按钮之间需要有适当间距，位于窗口中的下方。

3. 修改 `08_webAccessPaper.php` 代码，在每篇论文的 "分类标签："行下方新增一行，该行显示相应论文的 rating 信息。具体包括：首先显示5个小五角星，最后一个五角星间隔适当空格后是一个具体的rating数值（包含1位小数，取值在0-10，均为整数，与最后一个星星有一个空格间隔，通过数据库的Papers表获取）。星星的填充颜色包括两种橙色 `#f90` 和灰色`#999`。假如数据库中的rating值是10，则5个星星全是橙色；如果rating值是5，则前2.5个星星是橙色，后2.5个星星是灰色，即第3个星星左半边是橙色，右半边是灰色；如果rating是8，则前4个星星是橙色，最后1个星星是灰色；如果rating是0，则所有星星均为灰色；即填充橙色星星的数量为 `rating/2`，填充灰色星星的数量为 `5-rating/2`。rating数字字体颜色为`#eca334`，字号为12px，字体为 `Helvetica,Arial,sans-serif`。rating的数值通过模块 `08_web_update_rating.php`从数据库的Papers表中获取。星星的高度和rating字体高度须保持一致。星星的实现和样式可以参考豆瓣网站（douban.com）中评分星星的设计。注意：用户在2中保存rating值时，页面中的rating数值和五角星填充需要同步更新。


结合上述需求，请输出完整的 `08_web_update_rating.php` 模块，并同步修改  `08_webAccessPaper.php` 代码，然后完整输出。对于 `08_webAccessPaper.php` 代码修改，尽量通过增加/调整少量代码行来实现，其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。




💡 **11. 新增思路**

上述新输出的模块 `08_web_update_rating.php` 以及修改后的 `08_webAccessPaper.php` 代码很好的实现了关于评分写入和显示的需求。现在还还有一个额外的新需求，如下：

1. 在 `08_web_update_rating.php` 显示的右侧页面顶部，点击"工具"按钮，目前支持按照 论文ID、发表年、状态码、期刊名、作者名、标题等对相应分类下的论文进行升序/降序排序。现在需要新增按照 rating 值对论文升序和降序排序两个选项。rating值可以基于 `08_web_update_rating.php` 从数据库的 papers 表中获取。

2. 上述排序须仅通过修改上述 `08_webAccessPaper.php` 代码来实现，可以调用但不要改变 模块 `08_web_update_rating.php` 代码。对于 `08_webAccessPaper.php` 代码修改，尽量通过增加/调整少量代码行来实现，其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。





💡 **12. 新增思路**


上述修改后的 `08_webAccessPaper.php` 代码很好的满足了在工具选项下新增按照评分升序和降序的需求。但是注意到你提到可以将“评分排序”也纳入 `getPapersByCategory()` 的 switch 中，我认为这是一个非常好的提议，不仅可以减少 `08_webAccessPaper.php` 代码篇幅，还可以实现代码的模块化和功能化。

```php
if ($sort === 'rating_asc' || $sort === 'rating_desc') {
    $order = ($sort === 'rating_asc') ? 'ASC' : 'DESC';
    $query = "
    SELECT 
        p.paperID, p.title, p.authors, p.publication_year, 
        p.journal_name, p.doi, p.status
    FROM papers p
    JOIN paperCategories pc ON p.paperID = pc.paperID
    WHERE pc.categoryID = ?
    ORDER BY p.rating $order, p.paperID DESC
    ";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $selectedCategoryID);
        $stmt->execute();
        $papers = $stmt->get_result();
        $stmt->close();
    } else {
        $papers = null;
    }
} else {
    $papers = getPapersByCategory($mysqli, $selectedCategoryID, $sort);
}
```

现在请将上述 `08_webAccessPaper.php` 中关于 rating 查询的代码放到 `getPapersByCategory()` 的 switch 中，输出修改后的完整  `08_webAccessPaper.php，08_category_operations.php，08_web_update_rating.php`，其中 `08_webAccessPaper.php` 尽量只删减代码行，`08_category_operations.php` 中仅新增相关代码行，`08_web_update_rating.php` 中如果没有必要可以不同修改。其余代码行不要改动，方便后续review。






💡 **13. 新增思路**


运行上述 `08_webAccessPaper.php` 代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），每篇论文下方还会显示`"标签 删除 查看 复制DOI 复制编码DOI"`等按钮；再下面的一行是 `"分类标签："`，包含该论文所属的分类；最下面一行是论文的 `rating` 显示（包括星星填充显示和具体的 `rating` 值）。现在我的需求如下：

1. 在每篇论文`rating`所在行、具体的`rating`值后面显示每篇论文的被引数，可以通过查询 `papers` 表中的 `citation_count` 值来获取。显示格式为 `被引数：citation_count`，`被引数`和具体的`rating`值之间需要有适当的间隔。`被引数：citation_count`的字体样式为`font-family: Arial, sans-serif`，字体大小为12，颜色为 `#777`。

2. 对于 `工具` 按钮下的排序选项，新增两项：`被引数升序`和`被引数降序`。

2. 上述需求的实现，可能涉及到 `08_webAccessPaper.php`、`08_category_operations.php`等脚本的修改。 可以通过对 `08_category_operations.php` 等模块函数的修改或新增，来实现对被引数值查询的需求；对于 08_webAccessPaper.php 代码修改，尽量通过增加/调整少量代码行来实现。其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。

对于代码有改动的脚本，输出修改后的完整代码。




💡 **14. 新增思路**

运行上述 `08_webAccessPaper.php` 代码，网页右侧显示左侧相应分类标签下的所有论文（包含标题、作者、期刊名等信息），每篇论文下方还会显示`"标签 删除 查看 复制DOI 复制编码DOI"`等按钮；再下面的一行是 `"分类标签："`，包含该论文所属的分类；最下面一行是论文的 `rating` 显示（包括星星填充显示和具体的 `rating` 值）和被引数显示。现在我有一个新的需求，如下：

1. 当某一分类下的论文比较多的时候，页面中 `rating` 信息（包括评级星星和 `rating` 值）需要加载1-2秒才能够显示（用户能够明显感觉到等待的时间），但是论文标题、作者、期刊名、分类标签及引用数等却能够瞬时显示。出现这种问题的原因，猜想可能是由于页面中 `rating` 的数据库查询依赖于 后端api的调用，可能需要一些响应时间；但是论文标题、作者、期刊名、分类标签及引用数等信息似乎是`08_webAccessPaper.php` 直接调用 `08_category_operations.php` 模块中的函数从数据库中获取的，因此耗时特别短。

2. 现在需要优化 `08_webAccessPaper.php` 页面中 rating 信息显示的加载时间，可以参考页面中 论文标题、作者、期刊名、分类标签及引用数等信息 快速查询显示的实现，直接通过模块调用可能更高效。

3. 上述需求的实现，可能涉及到 `08_webAccessPaper.php`、`08_category_operations.php`等脚本的修改。 可以通过对 `08_category_operations.php` 等模块函数的修改或新增，来实现对被引数值查询的需求；对于 `08_webAccessPaper.php` 代码修改，尽量通过增加/调整少量代码行来实现。其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。

对于代码有改动的脚本，输出修改后的完整  `08_webAccessPaper.php` 和 `08_category_operations.php`代码，不要有任何代码省略（此处强调），以便我能够直接复制替换，否则会增加我review和修改代码的负担。



💡 **15. 新增思路**


a. 最新问题

- 优化后的`08_webAccessPaper.php` 脚本通过调用 `08_category_operations.php` 模块中的 `getPapersByCategory()` 函数来获取的 `rating` 信息，这与 页面其他内容（标题、作者、期刊、分类标签、引用数）等其他信息似乎都是同时获取的，为什么初始代码中不能同时显示呢？

- 页面其他内容（标题、作者、期刊、分类标签、引用数）等其他信息为什么在 `html` 的首次渲染中就能够显示呢？

- 能不能不修改 `08_category_operations.php` 模块内容，在 `08_webAccessPaper.php` 脚本中也采用 `PHP 在生成 HTML 时直接写进了页面标记里，浏览器拿到响应后就能立刻渲染` 来处理 rating信息的显示（包括星星的填充和rating数值显示），避免纯前端的循环 DOM 操作。

- 上述代码似乎仅能显示整颗填充的星星，不能显示半颗填充的星星，例如 `rating` 为 5 时，仅显示两颗橙色填充星星，其余均为灰色填充星星。理论上应该是，第三颗星星左半边应为橙色填充，右半边为灰色填充。上述代码该如何修改呢？

- 使用类似 `github split` 格式列出 `08_category_operations.php` 和  `08_webAccessPaper.php` 代码修改前后的差异




b. 解决方案

- 最新版代码的核心优化思路是将原本由前端JavaScript负责的评分星星渲染工作，交给后端PHP在服务器上完成。这样做主要为了解决第旧版代码中可能存在的 `“内容闪烁”或加载延迟` 问题。

- 因为在旧版中，浏览器必须等待HTML加载完毕、再执行JavaScript后才能画出星星。通过在服务器端直接生成完整的星星HTML，最新版代码确保了用户在打开页面的瞬间就能看到包含评分的最终内容，显著提升了页面的初始加载速度和视觉稳定性。

- 这样即可让评分星星与数值在服务器端生成，页面首次渲染时就立即可见，无需前端批量 DOM 操作。

- 通过后端 api 响应获取 `rating` 数据，页面加载耗时大概 `5-6` 秒；通过 `08_category_operations.php` 模块中的 `getPapersByCategory()` 函数直接返回 `rating` 数值，然后在页面中执行 js 操作，页面响应时间降至 `2-2.5` 秒；如果直接在服务器中基于 php 生成 `rating` 相关信息的 html 代码，响应时间几乎为0。








### 2. 模块、函数和后端接口

1. PHP模块

```php
08_db_config.php
08_category_operations.php
08_web_Base32.php
```


2. 后端接口

```php
08_tm_get_categories.php
08_tm_get_paper_categories.php
08_tm_update_paper_categories.php
08_web_update_paper_status.php
08_web_update_rating.php
```


3. PHP函数

```php
getCategories($mysqli)
addCategory($mysqli, $categoryName)
deleteCategory($mysqli, $categoryID)
updateCategoryName($mysqli, $categoryID, $newCategoryName)
getPaperByDOI($mysqli, $doi)
insertPaper($mysqli, $title, $authors, $journal_name, $publication_year, $volume, $issue, $pages, $article_number, $doi, $issn, $publisher)
assignAllPapersCategory($mysqli, $paperID)
getPapersByCategory($mysqli, $selectedCategoryID, $sort)
```




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
// [MODIFIED] 定义 API_KEY 常量
const API_KEY = 'YOUR_API_KEY_HERE'; // 与后端 08_api_auth.php 中保持一致

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

```html
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


7. 更新/查询数据库中论文 rating 数值

```js
// ====== [NEW CODE] 评分：保存 ======
fetch('08_web_update_rating.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Api-Key': API_KEY
    },
    body: JSON.stringify({
        doi: currentRatingDOI,
        rating: num
    })
})


// ====== [NEW CODE] 页面加载后，为每篇论文拉取评分并渲染 ======
fetch('08_web_update_rating.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Api-Key': API_KEY
    },
    body: JSON.stringify({ doi })
})
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


📁 **文件结构**

```php
08_web_crossRef_query.php
    08_tm_add_paper.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
    08_tm_get_categories.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
    08_tm_get_paper_categories.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
    08_tm_update_paper_categories.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
```



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




💡 **4. 新增思路**

上述 `08_web_crossRef_query.php` 脚本是正常工作的，现在我有一个新的需求，分析和要求如下：

1. 在输入框输入相关检索信息，在页面中点击 `"查询"` 按钮之后，页面能够基于 `crossRef API` 返回的信息显示该篇论文的一些元数据信息，并且还会显示 `"复制 Base32"` 按钮和 `"标签"` 按钮。

2. 点击 `"标签"` 按钮会弹出一个新的窗口页面，在这个小窗口中可以为这篇论文分配所在分类，这些功能都是正常工作的。

3. 但是上述`"标签"`按钮打开的新的窗口中，所有的论文分类名称仅显示为1列。显示为1列的话，对于整个屏幕的空间并未充分利用。因此，我觉得可以显示为5列，按照`categoryID`的顺序从左到右，从上到下显示为5列，显示的行数根据分类名称的数量来确定，当分类的行数很多，并超过了窗口的高度时，则出现纵向滚动轴。可以将窗口的宽度设置为页面宽度的80%。

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。





💡 **5. 新增思路**


上述 `08_web_crossRef_query.php` 显示的页面支持两种查询方式：基于 `title` 和 `doi` 查询，通过 `crossref api` 返回的数据提取相应论文的部分元信息：doi、出版年、期刊、卷号、期号、页码等，然后将相应的信息写入到数据 `paper_db` 数据库的 `papers` 表中。现在我有一个新的需求，如下：

1. 在请求 `crossref api` 返回的响应数据中，有一个字段是 `is-referenced-by-count` 其值对应的是 被引数，能否修改 `08_web_crossRef_query.php` 代码，使得页面中的检索结果中显示`被引数`这一信息（无论是基于 `title` 检索，还是基于 `doi` 检索的结果）。除此之外，可能还需要修改后端调用的 api，使得用户在将其他论文元信息写入到 `papers` 表时，被引数也需要相应写入（`is-referenced-by-count` 的值存在且不为空时）到 `papers` 表的 `citation_count` 列。

2. 对于上述相关代码修改，尽量通过增加/调整少量代码行来实现。其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。


3. 可以使用类似 github split 格式列出 `08_web_crossRef_query.php` 和 其他脚本代码修改前后的差异


- 参考上面的修改建议，修改后的代码如上所示，请检查是否与你的建议完全一致。运行上述代码后，通过 `title` 和 `doi` 检索，页面中均能显示正确的被引数信息，但是在向 `papers` 的 `citation_count` 列中写入的值却是 NULL 。表明代码不能够正确地将 被引数 写入到数据库中，另外，当api返回的 被引数 不存在或者 为空时，请将数据库的 `citation_count` 写为 0 而不是写为 `NULL`。注意，每次写入都采取更新的方式，因为api返回的被引数可能随时间是变化的。





### 2. 环境变量

1. 注意模块调用和API礼貌池

```php
const API_BASE_URL = 'https://domain.com/'; // 与原油猴脚本保持一致，php模块调用

// [MODIFIED] 定义 API_KEY 常量
const API_KEY = 'YOUR_API_KEY_HERE'; // 请与后端保持一致的密钥

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


3. 注意doi号查询时如何使用礼貌池

```
# 使用礼貌池
https://api.crossref.org/works/{DOI}?mailto=your-email@example.com
https://api.crossref.org/works/10.1038/nature12373?mailto=xiaoming@example.com
```



# 6. 服务器端脚本

## 1. `08_server_update_paper_status.php`

功能概述：从数据库中读取论文信息，并将 DOI 转换为 Base32 编码的文件名，再通过扫描本地和远程目录来判断文件是否存在，进而自动执行下载或删除操作，并更新数据库中的状态。

### 1. 编程思路

💡 **1. 初始思路**

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


💡 **2. 新增思路**

1. 初始代码适用于所有远程pdf文件均位于 `$remote_dir  = 'rc4:/3图书/13_paperRemoteStorage';`  目录下
2. 假如 `'rc4:/3图书/13_paperRemoteStorage'` 目录下新增若干子目录，pdf 文件均分散位于这些子目录中，上述代码在获取远程存储的 PDF 文件时如何获取到所有这些子目录中的文件呢？使用 rclone copy 将远程 PDF 下载到本地时，如何确定相应pdf文件位于哪个对应子目录呢？
3. 由于现在我想要将 `'rc4:/3图书/13_paperRemoteStorage'` 目录下的所有pdf文件均分散存储到该路径下的子目录中，需要考虑上面这些问题。

请保持上述代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。



### 2. 环境变量

```php
// 配置部分
$db_host     = 'localhost';          // 数据库主机
$db_name     = 'paper_db';           // 数据库名称
$db_user     = 'root';               // 数据库用户名
$db_password = '123456';           // 数据库密码

// 本地目录(A)
$local_dir   = '/home/01_html/08_paperLocalStorage';
// 远程目录(B)
$remote_dir  = 'rc4:/3图书/13_paperRemoteStorage';
```



## 2. `08_server_paper_management.php` 

功能概述：在终端交互界面下对 papers 表进行管理操作，包括查询、筛选、统计、展示表结构以及修改记录或表结构等多种功能。它允许用户通过输入不同的功能编号来选择执行具体的数据库操作，例如打印所有数据、检测重复的 doi、更新字段内容等。

### 1. 编程思路

💡 **1. 初始思路**

数据库连接脚本以及papers表如上 `08_db_config.php` 所示，请编写一个php脚本（在服务器终端界面上运行），实现几个功能，用户通过输入序号来进行功能选择，需要实现的功能如下所示：

1. 打印出papers表中的所有数据
2. 打印出最后写入的10条完整数据
3. 查找doi重复的行
4. 提示用户输入doi号，然后打印包含该doi号的所有行
5. 在页面上打印出表格中所有 status = "N" 行对应的doi号。
6. 打印出数据库中 status 为`'CL','C','L','N','DW','DL'`各个值的数据条数，以及数据总条数。
7. 列出所有field关键词，用户通过输入关键词的序号来选择需要打印的关键词项，列出每一条数据中对应所选关键词的值
8. 打印出 papers 的表结构信息，对应mysql的 `describe papers;`命令
9. 修改表中`title`的varchar的最大存储长度。先打印当前title的最大长度值，提示用户输入想要设定的值并检查该值是否合法（提示推荐值为355），询问用户是否需要用新的最大长度值替换旧值？输入y确认，输入n或其他值表示取消
10. 提示用户输入doi，然后打印出对应该doi的title值，提示用户输入新的title值，然后打印出输入的新的title值，询问用户是否需要用新的title值替换旧值？输入y确认，输入n或其他值表示取消


功能7：列出所有field关键词，用户通过输入多个关键词的序号（使用空格分隔）来选择需要打印的关键词项，列出每一条数据中对应所选关键词的值
上述代码的功能7是否支持用户输入多个序号进行多个field选择？多个序号之间是否支持使用空格分隔？如果不支持，请修改对应部分代码，其余部分代码不要变。


💡 **2. 新增思路**

`papers`表结构，数据库连接脚本 `08_db_config.php`，以及 `08_server_paper_management.php` 脚本如上所示，现在需要修改 `08_server_paper_management.php` 脚本，新增功能11，需求如下：

1. 提示用户输入doi，然后打印出对应该doi的 `journal_name` 值，提示用户输入新的 `journal_name` 值，然后打印出输入的新的 `journal_name` 值，询问用户是否需要用新的 `journal_name` 值替换旧值？输入y确认，输入n或其他值表示取消。类似功能10，区别在于将 `title` 更新换成 `journal_name` 。

2. 对于 `08_server_paper_management.php` 代码修改，尽量通过增加/调整少量代码行来实现，其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。

输出修改后的完整 `08_server_paper_management.php` 代码。



💡 **3. 新增思路**

请修改上述php脚本，新增功能12，需求如下：

1. 提示用户输入`doi`，然后打印出 `papers` 表中对应该 `doi` 的论文信息，即该doi所在行的所有列值，打印时以 json 格式输出（打印格式要方便用户审阅）。

2. 打印出 `papers` 表中的所有列名，并且要有序号。询问用户是否需要手动修改该doi号所在行的某一列的值，如果需要请输入该列名对应的序号，不需要的话则输入q结束程序运行，输入除了q和列序号之外的其他值均为非法输入，提示用户重新输入。注意：不允许用户修改 `paperID` 和 `doi` 列的值，即输入该列对应的序号也是非法的。因为该值被数据库中的多个表公用，是论文在数据库中的唯一标识。`doi` 值也不允许被修改，因为其 base32 编码是相应pdf的文件名，修改 `doi` 会使得二者不一致。

3. 提示用户输入新的列值，然后打印出该列的原值和新值供用户审查。请用户确认是否需要更新，如果需要则输入 y，不需要则输入 n，结束运行则输入 q，输入其他值为非法值，提示用户重新输入。

4. 对于 `08_server_paper_management.php` 代码修改，尽量通过增加/调整少量代码行来实现，其余部分代码行不要变动，哪怕是增加空格或者修改注释都不行，确保所有的代码修改均与上述需求的实现有关，因为无关的改动会增加我review代码的工作量。

输出修改后的完整 `08_server_paper_management.php` 代码。不要使用 canvas。




### 2. 环境变量


```php
require_once '08_db_config.php'; // 引入数据库连接配置
```




## 3. `08_server_update_citation_all_random.php`

- 从所有具有标准doi值的行中，随机选取一行更新引用数，不限制引用数是否为0。适合不断更新论文的引用情况，需较长时间。


### 1. 编程思路

💡 **1. 初始思路**

现在需要编写一个 `08_server_update_citation_all_random.php` 脚本实现以下功能：

1. 通过调用 `08_db_config.php` 模块连接到数据库，然后读取 `papers` 表格。

2. 按照 `papers` 表中 `paperID` 降序的顺序，从其中随机选取 `doi_type` 不为 `F` 或者 `doi_type` 为空（`NULL`）的行，读取该行的 `doi` 值，然后将基于该 doi 请求如下地址： 

```
https://api.crossref.org/works/{doi}?mailto=fangxy@qq.com

# 例如，当doi=10.1021/ja01056a002时

https://api.crossref.org/works/10.1063/1.5017661?mailto=fangxy@qq.com
```

3. 如果请求正确响应，并返回 json 格式的数据，解析其中 `is-referenced-by-count` 字段对应的值，如果该字段存在，且字段对应的值不为空、对应的值是大于等于0的整数，则将该值更新为上述 `doi` 所在行的 `citation_count` 列值。


输出满足上述需求的完整 `08_server_update_citation_all_random.php` 代码




### 2. 环境变量

1. 参数实例化

```php
// 1. 包含数据库配置文件并连接数据库
require_once '08_db_config.php';

// 3. 构建 CrossRef API 请求 URL
// 使用 urlencode() 对 DOI 进行编码，防止 DOI 中的特殊字符导致 URL 格式错误
$mailto = 'fangxy@qq.com'; // 礼貌地在请求中带上邮箱
$apiUrl = "https://api.crossref.org/works/" . urlencode($doi) . "?mailto=" . $mailto;
```


2. 安装 cURL 扩展

```sh
# 安装与您的 PHP 版本匹配的 cURL 扩展（以 PHP 8.1 为例）
apt install php8.1-curl
```

不安装扩展会出现如下报错：

```
PHP Fatal error: Uncaught Error: Call to undefined function curl_init()
```


3. cron定时

```sh
# 从所有具有标准doi值的行中，随机选取一行更新引用数，不限制引用数是否为0。适合不断更新论文的引用情况，需较长时间。
*/5 * * * * /usr/bin/php /home/01_html/08_server_update_citation_all_random.php > /dev/null 2>&1
```




## 4. `08_server_update_citation_topN_random.php`

- 按照paperID降序，从引用数为0的前N行中随机选取一行更新引用数，注意前N行引用数均为0的情况。适合更新最新导入数据库的论文。

### 1. 编程思路

💡 **1. 初始思路**

上述修改后的代码很好的满足了我的需求，但是现在我的需求有一些调整（调整集中在对doi所在行的选取上），如下：

1. 通过调用 `08_db_config.php` 模块连接到数据库，然后读取 `papers` 表格。

2. 首先确定 `doi_type` 不为 `F` 或者 `doi_type` 为空（`NULL`）的行，然后再从这些行中筛选出 `citation_count` 为 0 的行，按照 papers 表中 `paperID` 降序的顺序对这些行进行排列，再由高到低确定前 N 行（例如N = 10，如果实际满足要求的为 M 行， 且M大于等于1小于10，则后续操作基于这 M 行，即`N=M`；如果满足要求的为 0 行，则结束程序运行），从这 N 行中随机抽取一行，读取该行的 doi 值，然后将基于该 doi 请求如下地址： 

```
https://api.crossref.org/works/{doi}?mailto=fangxy@qq.com

# 例如，当doi=10.1021/ja01056a002时

https://api.crossref.org/works/10.1063/1.5017661?mailto=fangxy@qq.com
```

3. 如果请求正确响应，并返回 json 格式的数据，解析其中 `is-referenced-by-count` 字段对应的值，如果该字段存在，且字段对应的值不为空、对应的值是大于等于0的整数，则将该值更新为上述 `doi` 所在行的 `citation_count` 列值。


输出满足上述需求的完整 `08_server_update_citation_topN_random.php` 代码



### 2. 环境变量

1. 参数实例化

```php
// 1. 包含数据库配置文件并连接数据库
require_once '08_db_config.php';

// 定义从最新的多少条记录中进行随机选取
$topN = 300;

// 3. 构建 CrossRef API 请求 URL
// 使用 urlencode() 对 DOI 进行编码，防止 DOI 中的特殊字符导致 URL 格式错误
$mailto = 'fangxy@qq.com'; // 礼貌地在请求中带上邮箱
$apiUrl = "https://api.crossref.org/works/" . urlencode($doi) . "?mailto=" . $mailto;
```

注意修改可调参数 `topN ` （定义从最新的多少条记录中进行随机选取）。



2. 安装 cURL 扩展

```sh
# 安装与您的 PHP 版本匹配的 cURL 扩展（以 PHP 8.1 为例）
apt install php8.1-curl
```

不安装扩展会出现如下报错：

```
PHP Fatal error: Uncaught Error: Call to undefined function curl_init()
```



3. cron定时

```sh
# 按照paperID降序，从引用数为0的前N行中随机选取一行更新引用数，注意前N行引用数均为0的情况。适合更新最新导入数据库的论文。
*/2 * * * * /usr/bin/php /home/01_html/08_server_update_citation_topN_random.php > /dev/null 2>&1
```



## 5. `08_server_insert_paper_doi_defined.php`

功能：针对没有 doi 号的论文，手动插入论文信息到数据库中，支持 json 格式输入


### 1. 编程思路

💡 **1. 初始思路**

基于上述 paper_db 数据库中几个表的结构，现在需要编写一个php脚本实现手动录入论文信息，具体需求如下：

1. 首先提示用户输入论文元数据，输入格式为 json 格式 （一个 `{...}`字符串，可能会写成一行，也可能会换行），参考示例如下。程序首先检查用户输入的json格式字符串是否合法，例如是否缺少逗号，冒号，引号等，是否有重复相同的键等，避免由于格式错误造成后续写入出现问题。如果字符串非法，提示用户重新输入。

- 示例字符串格式1（所有键值对均位于同一行）：

```json
{
  "title": "Predicting ternary activities using binary data",  "authors": "Toop G W",  "journal_name": "Trans. TMS-AIME",  "publication_year": 1965,  "volume": "223",  "pages": "850-855"}
```

- 示例字符串格式2（每一个键值对占据一行，有换行符）：

```json
{
  "title": "Joint learning of temporal models to handle imbalanced data for human activity recognition",
  "authors": "Hamad R A, Yang L, Woo W L, et al.",
  "journal_name": "Applied Sciences",
  "publication_year": 2020,
  "volume": "10",
  "issue": "15",
  "article_number": "5293",
  "doi": "10.1051/jcp/1975720083",
  "doi_type":"F",
  "citation_count":"100"
}
```

2. 核查用户输入的json字符串中是否包含 `doi` 项，且该项的值非空，如果不存在或者值为空，需要给出提示，提示用户重新输入。`doi` 项不能为空的是因为部分后端api调用时依赖 `doi` 值作为唯一识别符，缺少会造成部分功能不能正常运行。

3. 确认 `papers` 表中有哪些列，然后从输入的json字符串中来匹配这些列的值，如果json字符串中某些列名称不存在或者列值为空，则 `papers` 表中该列值采用表中默认值。

4. 对比用户输入字符串中的以及 `papers` 表中的 `doi` 值和 `title` 值，核查 `papers` 表中是否已经存在相同的 `doi` 值和 `title` 值，避免重复写入，对比核查过程中可忽略 `doi` 值和 `title` 值的大小写。核查完成后，需打印核查结果。如果存在相同的 `doi` 或者 `title` 值，提示用户是否重新输入字符串，输入 y 代表重新输入，输入 n 代表仍使用该字符串（即写入重复的信息，特殊情况下可能需要），输入 q 代表结束程序运行。除了三种输入外其余均为非法输入，并提示用户输入合法的操作码。如果不存在相同的  `doi` 或者 `title`，则继续执行程序。

5. 以json格式打印出 `papers` 表中待新增的该条论文信息（打印格式要方便用户审阅，包含表中所有列），打印值包括用户输入的和缺失项数据库默认的值，然后提示用户进行审核、确认。输入y即同意向 `papers` 表中插入该条论文信息，执行新增插入操作；输入q代表取消新增，并结束程序运行；输入n代表不采用该条论文信息，并提示用户输入新的字符串，即重新执行上述步骤1、2、3和4（重新输入、核查、匹配、打印）；除了三种输入外其余均为非法输入，并提示用户输入合法的操作码。

6. 在 `papers` 表中完成论文信息新增写入后，打印出 `categories` 表中 `categoryID` 为 1 对应的 `category_name`。由于 `categoryID` 为 1 的分类标签覆盖所有论文，因此新增论文的 `paperID` 和 `categoryID = 1` 的匹配关系需要写入到 `paperCategories` 表中。完成写入后给出提示。

7. 打印出 `categories` 表中 `categoryID` 为 123 对应的 `category_name`。上述新增论文的 `paperID` 可能属于 `categories` 表中 `categoryID = 123` 的分类，询问用户是否需要写入该分类关系到 `paperCategories` 表中，用户输入 y 代表确认新增该分类关系；输入 n 代表不新增，并结束程序运行。除了这两种输入外其余均为非法输入，并提示用户输入合法的操作码。


请输出满足上述要求的 `08_server_insert_paper_doi_defined.php` 脚本。不要使用canvas



### 2. 环境变量

1. 模块调用

```php
require '08_db_config.php';
```


2. 注意如下代码会插入 `categoryID = 1` 的关联，可选插入 `categoryID = 123` 的关联，即，默认分类到 `categoryID = 1`，可选分类到 `categoryID = 123`。

```php
// 插入 categoryID = 1 的关联
$row = $mysqli->query("SELECT category_name FROM categories WHERE categoryID = 1")->fetch_assoc();
echo "分类 1 对应 name：", $row['category_name'] ?? '不存在', "\n";
$stmt2 = $mysqli->prepare("INSERT INTO paperCategories (paperID, categoryID) VALUES (?, 1)");
$stmt2->bind_param('i', $paperID);
$stmt2->execute();
echo "已添加关联 (paperID={$paperID}, categoryID=1)\n";



// 可选插入 categoryID = 123 的关联
$row123 = $mysqli->query("SELECT category_name FROM categories WHERE categoryID = 123")->fetch_assoc();
if ($row123) {
    echo "分类 123 对应 name：", $row123['category_name'], "\n";
    while (true) {
        echo "是否添加 (paperID={$paperID}, categoryID=123) 关联？(y/n): ";
        $c = trim(fgets(STDIN));
        if ($c === 'y') {
            $stmt3 = $mysqli->prepare("INSERT INTO paperCategories (paperID, categoryID) VALUES (?, 123)");
            $stmt3->bind_param('i', $paperID);
            $stmt3->execute();
            echo "已添加关联 (paperID={$paperID}, categoryID=123)\n";
            break;
        } elseif ($c === 'n') {
            echo "未添加该关联，程序结束。\n";
            break;
        } else {
            echo "非法输入，请输入 y 或 n。\n";
        }
    }
} else {
    echo "分类 123 不存在，程序结束。\n";
}
```




# 7. tampermonkey 脚本


## 1. `08_tm_paperManagement.js`

📁 **文件结构**

```js
08_tm_paperManagement.js
    08_tm_add_paper.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
    08_tm_get_categories.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
    08_tm_get_paper_categories.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
    08_tm_update_paper_categories.php
        08_api_auth.php
        08_db_config.php
        08_category_operations.php
```



### 1. 编程思路

💡 **1. 初始思路**

上述油猴脚本能够基于 crossRef API 返回的信息显示该篇论文标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商 这些信息（用户点击“提取内容并查询 DOI”按钮之后页面会获取上述论文的元信息），我想要把这些信息写入到云服务器 paper_db 数据库中的 papers 表格中并对该篇论文进行分类。我的需求如下：

1. 在油猴脚本中再新增一个按钮“标签”，点击标签按钮之后会把  crossRef API 返回的、在页面显示的 论文标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商 这些信息 都写入到papers 表格中，如果crossRef API返回的上述信息中部分缺失，则缺失项不用写入，维持表格的默认值。期刊ISSN只写入印刷版即可。另外，在写入该条论文数据前，请将该论文的doi与papers 表格中已有的doi进行比对，如果已经存在，则不用写入该条论文的元数据。

2. 点击“标签”按钮的同时还需要显示 categories 表中的所有分类标签，每个标签前面显示一个小的正方框，如果该篇论文属于某个标签，则对应方框中会显示一个对号。用户可以点击方框来添加或者取消对号。需要通过paperCategories 表该操作来实现论文分类。

3. categories 表中有一个标签是"0 All papers"，默认给所有的论文都添加该标签，且页面中无法取消该方框前的对号

如果上述需求的实现需要在服务器中引入新的php模块，可请编写相关模块，目前已有的模块 `08_db_config.php`、`08_category_operations.php` 也可以调用。


💡 **2. 新增思路**

运行上述油猴脚本，点击`“提取内容并查询 DOI”`按钮之后，页面能够基于 `crossRef API` 返回的信息显示该篇论文的一些元数据信息（如：`标题，作者，期刊名，出版年，卷，期，页码，文章编号，doi号，期刊ISSN 和 出版商` 等），但是这些信息中可能有部分缺失项。当页面中显示的信息缺失满足以下任意一种情况时，都需要在页面中出现一个小弹窗提示用户（满足多种情况时，只弹出一次即可）。

1. `期刊名、出版年、作者、出版商`，其中任意一项或者几项缺失时要弹窗提醒。
2. `卷号和期号`都缺失时要提醒
3. `页码和文章号`都缺失时要提醒

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。


💡 **3. 新增思路**

上述修改后的代码满足了我的要求，现在我有一个新的需求，分析和要求如下：
1. 运行上述油猴脚本，点击 `"提取内容并查询 DOI"` 按钮之后，页面能够基于 crossRef API 返回的信息显示该篇论文的一些元数据信息，并且还会显示 `"复制 Base32"` 按钮和 `"标签"` 按钮。
2. 点击 `"标签"` 按钮会弹出一个新的窗口页面，在这个小窗口中可以为这篇论文分配所在分类，这些功能都是正常工作的。
3. 但是该窗口只有 `"保存分类"` 按钮，窗口右上角没有 关闭按钮（叉符号），也没有 `"取消"` 按钮（即放弃保存分类）

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。


💡 **4. 新增思路**

任何人运行上述油猴脚本都可以将论文元数据写入到数据库，并且修改论文分类。现在我的需求如下：
1. 编写一个API认证php模块（`08_api_auth.php`），确保只有拥有有效API密钥的请求才能访问和操作您的后端API。该模块在相应的后端api中进行调用。
2. 为了配合后端的API密钥验证，前端油猴脚本需要在所有向后端API发送的请求中添加正确的头部。

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。


💡 **5. 新增思路**

上述油猴脚本是正常工作的，现在我有一个新的需求，分析和要求如下：
1. 运行上述油猴脚本，点击 `"提取内容并查询 DOI"` 按钮之后，页面能够基于 `crossRef API` 返回的信息显示该篇论文的一些元数据信息，并且还会显示 `"复制 Base32"` 按钮和 `"标签"` 按钮。
2. 点击 `"标签"` 按钮会弹出一个新的窗口页面，在这个小窗口中可以为这篇论文分配所在分类，这些功能都是正常工作的。
3. 但是上述`"标签"`按钮打开的新的窗口中，所有的论文分类名称仅显示为1列。显示为1列的话，对于整个屏幕的空间并未充分利用。因此，我觉得可以显示为5列，按照`categoryID`的顺序从左到右，从上到下显示为5列，显示的行数根据分类名称的数量来确定，当分类的行数很多，并超过了窗口的高度时，则出现纵向滚动轴。可以将窗口的宽度设置为页面宽度的80%。

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。


💡 **6. 新增思路**

上述油猴脚本是正常工作的，现在我有一个新的需求，分析和要求如下：
1. 运行上述油猴脚本，点击 `"提取内容并查询 DOI"` 按钮之后，页面能够基于 crossRef API 返回的信息显示该篇论文的一些元数据信息，并且还会显示 `"复制 Base32"` 按钮和 `"标签"` 按钮，各按钮都是正常工作的。
2. 现在我需要新增一个 `"复制doi"` 按钮，用来复制窗口中显示的未编码doi（crossRef API 返回的doi），按钮的样式（字体大小、颜色等）与`"复制 Base32"` 按钮保持一致即可。

保持上述代码的界面UI设计、代码逻辑和功能不变，仅对上述提到的需求进行修改。请输出修改后的完整代码。



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

// [MODIFIED] 在脚本中添加 API_KEY 用于后端认证，与后端API中的密钥一致
const API_KEY = 'YOUR_API_KEY_HERE';

// 使用礼貌池提升查询性能
const apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(reference)}&mailto=GroovyBib@example.org`;

url: API_BASE_URL + '08_tm_add_paper.php',

url: API_BASE_URL + '08_tm_get_categories.php',

url: API_BASE_URL + `08_tm_get_paper_categories.php?doi=${encodeURIComponent(doi)}`,

url: API_BASE_URL + '08_tm_update_paper_categories.php',

```

1. `@connect` 指令用于声明脚本可以进行跨域请求的目标域。这是 Tampermonkey 的安全机制，确保脚本只能与指定的域进行通信。


# 8. 客户端脚本

## 1. `08_client_doi_base32_scidownl.py`

### 1. 编程思路

1. 请编写一个python脚本，提示用户输入一个论文doi号，例如：`"10.1063/1.446740"`，然后使用scidownl的如下下载命令下载论文至  `"C:\Users\sun78\下载_chrome"` 路径下。注意，下载的pdf 命名使用 doi 号的base32编码，例如 `"10.1063/1.446740"` base32 编码后是`"GEYC4MJQGYZS6MJOGQ2DMNZUGA======"`，下载命令如下：

```py
scidownl download --doi 10.1063/1.446740 --out "C:\Users\sun78\下载_chrome\GEYC4MJQGYZS6MJOGQ2DMNZUGA======.pdf"
```

2. base32编码的实现规则请参考如下php代码（需要使用python来实现），输出修改后的完整代码。



### 2. 环境变量

```py
# 拼接出最终的保存路径（Windows 环境下注意转义反斜杠）
# save_path = f"C:\\Users\\sun78\\下载_chrome\\{encoded_doi}.pdf"
save_path = f"C:\\Users\\sun78\\Desktop\\Al_rdf\\{encoded_doi}.pdf"
```

### 3. scidownl模块

- scidownl 文档：https://pypi.org/project/scidownl/

- Quick usage

```py
# Download with a DOI and filenmae is the paper's title.
$ scidownl download --doi https://doi.org/10.1145/3375633

# Download with a PMID and a user-defined filepath
$ scidownl download --pmid 31395057 --out ./paper/paper-1.pdf

# Download with a title
$ scidownl download --title "ImageNet Classification with Deep Convolutional Neural Networks" --out ./paper/paper-1.pdf

# Download with a proxy: SCHEME=PROXY_ADDRESS 
$ scidownl download --pmid 31395057 --out ./paper/paper-1.pdf --proxy http=socks5://127.0.0.1:7890
```




# 参考资料




