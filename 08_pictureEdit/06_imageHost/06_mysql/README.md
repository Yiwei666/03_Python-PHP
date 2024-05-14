# 1. 项目功能

1. 将指定文件夹下的图片名称全部写入到mysql数据库中
2. 用户可以在web页面进行点赞，并写入到数据库中以及在页面即时显示点赞数。
3. 通过👍和👎图标计数likes和dislikes的数量，二者差值代表总喜欢数。基于总喜欢数排序显示图片。


# 2. 文件结构

```
08_db_config.php                           # 通常包含数据库连接信息如服务器地址、用户名、密码等
08_db_sync_images.php                      # 图片目录与数据库同步功能模块
08_db_image_status.php                     # 该功能模块将项目文件夹下已删除的图片在数据库中image_exists赋值为0，存在则赋值为1，注意项目文件夹中图片信息是数据库图片信息的子集

08_image_management.php                    # 用于响应用户对图片进行喜欢或不喜欢操作的后端服务，通过更新数据库并实时反馈结果到前端用户界面
08_image_likes_manager.php                 # 后台控制（增加或减少）数据库中的likes和dislikes数量变化
08_image_dislikes_delete.php               # 后台控制（增加或减少）数据库中的likes和dislikes数量变化，功能4能够删除图片文件夹中dislikes数在某个范围内的图片，删除前需rclone备份至onedrive

08_picDisplay_mysql.php                    # 点赞图标位于图片外右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRight.php            # 点赞图标位于图片内右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRigTra.php           # 点赞图标位于图片内右侧居中，点赞图标所在方框设置为透明，能够写入图片名到数据库

08_picDisplay_order.php                    # 基于总点赞数排序显示有限张图片，例如50张图片
08_picDisplay_mysql_gallery.php            # 显示数据库中所有图片，添加分页、侧边栏、localStorage，按照文件名默认排序
08_picDisplay_mysql_order.php              # 显示数据库中所有图片，按照总点赞数由多到少排序，添加分页、侧边栏、localStorage

08_picDisplay_mysql_orderExist.php         # 基于数据库中的图片信息显示图片文件夹中所有图片，按照图片数据库中 likes-dislikes 的值降序显示，不显示数据库中已删除的图片，不显示已删除图片导致的空白页
08_picDisplay_mysql_galleryExist.php       # 基于数据库中的图片信息显示图片文件夹中所有图片，不显示数据库中已删除的图片，不显示已删除图片导致的空白页，按照文件名默认排序
```

# 3. php功能模块

### 1. `08_db_config.php` 数据库连接

通过mysqli对象实现与数据库的连接，并检查连接是否成功。

```php
<?php
$host = 'localhost'; // 通常是 'localhost' 或一个IP地址
$username = 'root'; // 数据库用户名
$password = '123456789'; // 数据库密码
$dbname = 'image_db'; // 数据库名称

// 创建数据库连接
$mysqli = new mysqli($host, $username, $password, $dbname);

// 检查连接
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>
```

- 需要初始化的参数包括用户名、密码、数据库名称

```php
$username = 'root'; // 数据库用户名
$password = '123456789'; // 数据库密码
$dbname = 'image_db'; // 数据库名称
```

### 2. `08_db_sync_images.php` 数据库同步图片信息

1. 将图片目录与数据库同步的功能独立成一个可重用的 PHP 脚本模块。
2. 图片目录与数据库同步：代码首先从指定目录中读取所有 PNG 格式的图片，然后检查这些图片是否已经存储在数据库中。未记录在数据库的图片将被添加到数据库。

```php
<?php
include '08_db_config.php'; // 包含数据库连接信息

function syncImages($directory) {
    global $mysqli;

    $imagesInDirectory = glob($directory . "/*.png"); // 获取所有 png 图片
    $existingImages = [];

    // 获取数据库中已存在的图片
    $result = $mysqli->query("SELECT image_name FROM images");
    while ($row = $result->fetch_assoc()) {
        $existingImages[] = $row['image_name'];
    }

    // 检查目录中的图片是否已在数据库中
    foreach ($imagesInDirectory as $filePath) {
        $imageName = basename($filePath);
        if (!in_array($imageName, $existingImages)) {
            // 如果图片不在数据库中，则添加
            $stmt = $mysqli->prepare("INSERT INTO images (image_name, likes, dislikes) VALUES (?, 0, 0)");
            $stmt->bind_param("s", $imageName);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// 可以根据需要在这个脚本中直接调用 syncImages 函数或在其他文件中调用
?>
```

- 可以在任何 PHP 脚本中通过以下方式调用此功能：

```php
include '08_db_sync_images.php';
syncImages("/home/01_html/08_x/image/01_imageHost"); // 调用函数并提供图片存储目录
// syncImages($dir4);
```


### 3. `08_image_management.php` 图像点赞/反对

1. 功能分析：

- 首先引入数据库配置文件 `08_db_config.php` 以获取数据库连接。
- 检查当前请求是否为 `POST` 方法。
- 从 POST 请求中获取 `imageId` 和 `action` 两个参数：
  - `imageId`：图像的唯一标识符。
  - `action`：用户操作，可能为`like`（点赞）或`dislike`（反对）。
- 根据 action 的值执行不同的 SQL 查询：
  - like：点赞计数加一。
  - dislike：反对计数加一。
- 使用 mysqli 对象执行 SQL 查询并更新数据库。
- 返回更新后的点赞和反对数，以 `JSON` 格式输出。


```php
<?php
// 引入数据库配置文件
include '08_db_config.php';

// 确保是 POST 请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取 POST 数据
    $imageId = isset($_POST['imageId']) ? intval($_POST['imageId']) : null;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // 根据 action 更新数据库
    if ($imageId && ($action === 'like' || $action === 'dislike')) {
        if ($action === 'like') {
            $query = "UPDATE images SET likes = likes + 1 WHERE id = ?";
        } elseif ($action === 'dislike') {
            $query = "UPDATE images SET dislikes = dislikes + 1 WHERE id = ?";  // 注意此处也改为加一
        }

        // 准备和执行 SQL 语句
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("i", $imageId);
            $stmt->execute();
            $stmt->close();

            // 获取更新后的值
            $result = $mysqli->query("SELECT likes, dislikes FROM images WHERE id = $imageId");
            $row = $result->fetch_assoc();

            // 返回 JSON 数据
            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'Failed to prepare statement']);
        }
    } else {
        echo json_encode(['error' => 'Invalid input']);
    }
} else {
    // 非 POST 请求处理
    echo json_encode(['error' => 'Invalid request method']);
}

?>
```

环境变量中唯一需要注意的初始化参数是`08_db_config.php`，获取数据库连接

```php
include '08_db_config.php';
```

2. 注意点：

- 确保数据库中已经有 images 表，并且包含 likes 和 dislikes 字段。
- 防止 SQL 注入攻击：使用准备好的 SQL 语句进行查询。

这些代码实现了数据库连接配置和简单的图像点赞/反对功能。

3. 创建后的表结构：

```
mysql> describe images
    -> ;
+------------+--------------+------+-----+---------+----------------+
| Field      | Type         | Null | Key | Default | Extra          |
+------------+--------------+------+-----+---------+----------------+
| id         | int          | NO   | PRI | NULL    | auto_increment |
| image_name | varchar(255) | NO   |     | NULL    |                |
| likes      | int          | YES  |     | 0       |                |
| dislikes   | int          | YES  |     | 0       |                |
+------------+--------------+------+-----+---------+----------------+
```

4. 导出`image_db`数据库为`08_image_backup_02.sql`。在终端中输入如下命令，然后输入mysql的root密码即可。

```bash
mysqldump -p image_db  > 08_image_backup_02.sql

alias sbp='mysqldump -p image_db > /home/01_html/08_image_backup_$(date +%Y%m%d_%H%M%S).sql'
```

### 4. `08_db_image_status.php`判断图片是否删除

1. 该功能模块将项目文件夹下已删除的图片在数据库中`image_exists`赋值为0，存在则赋值为1，注意项目文件夹中图片信息是数据库图片信息的子集
2. 运行该脚本前需要在数据库`images`表中新增`image_exists`一列

```sql
ALTER TABLE images ADD COLUMN image_exists TINYINT DEFAULT 0;
```

3. 环境变量

```php
// 引入数据库配置文件
include '08_db_config.php';

// 定义图片存储目录
$imagesDirectory = '/home/01_html/08_x/image/01_imageHost';
```

4. `08_db_image_status.php`功能模块调用方式

```php
include '08_db_image_status.php';                    // 判断数据库中所有图片的存在状态
```


# 4. 后台管理脚本


### 1. `08_image_likes_manager.php`

```
通过引入`08_image_management.php` 文件，现在能不能编写一个脚本，实现以下需求
1. 提醒用户输入三个整数，分别为a，b和x，这三个数都可以为正整数或负整数或0，但是需要满足a小于b。不满足要求则给出提示并退出。
2. 显示3个选项，分别对应3个功能，大概描述每个功能的含义，提示输入1对应功能1，输入2对应功能2，输入3对应功能3
3. 功能1：打印数据库中 likes 在[a，b] 之间的图片数量，并将对应图片的 likes 统一都加上x个，并打印数据库中图片的总数量以及对应操作信息
4. 功能2：打印数据库中 dislikes 在[a，b] 之间的图片数量，将其likes统一都加上x个，并打印数据库中图片的总数量以及对应操作信息
5. 功能3：打印数据库中 likes 在[a，b] 之间的图片数量，以及数据库中图片的总数量
```

- 该脚本中需要初始化的参数如下所示

```php
include '08_db_config.php';                                      // 包含数据库连接的配置信息
```



### 2. `08_image_dislikes_delete.php`

`08_image_dislikes_delete.php` 是 `08_image_likes_manager.php` 升级版本，

1. 新增功能4：统计 dislikes 在 [a, b] 范围内的图片数量，并将云服务器项目文件夹中dislikes 在 [a, b] 范围的这些对应的图片都删除掉，删除前打印出这些文件的名称，提醒用户确认，最后打印删除后的项目文件中图片总数量。

2. 新增功能5：创建的数据库连接对象 $mysqli 中有一列是 image_exists，image_exists列表示数据库中每张图片的存储状态，0表示不存在，1表示存在。功能5就是：对于所有 image_exists为1的图片，分别查找likes和dislikes在 [a, b] 区间内的数量并打印出来。除此之外，还打印出数据库中图片总数，image_exists为0和为1的数量。


- 该脚本中需要初始化的参数如下所示

```php
include '08_db_config.php';                          // 创建数据库连接对象 $mysqli

include '08_db_sync_images.php';                     // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");    // 调用函数并提供图片存储目录

include '08_db_image_status.php';                    // 判断数据库中所有图片的存在状态

$project_folder = '/home/01_html/08_x/image/01_imageHost/';      // 替换为项目文件夹的路径
```




# 5. web交互脚本

### 1. `08_picDisplay_mysql.php`

1. 用户认证：检查用户是否已经登录，如果未登录则重定向到登录页面。
2. 图片管理：从特定目录获取所有PNG格式的图片，检查这些图片是否已经存入数据库中。如果没有，则将其添加到数据库。
3. 图片展示：从数据库中随机选取指定数量的图片（在此脚本中设置为3张），然后在网页上显示。
4. 互动功能：用户可以点击喜欢或不喜欢的按钮来更新图片的喜欢和不喜欢的数量。
5. 终端识别：能够根据客户端类型（手机/电脑）自适应图片宽度

此外，该脚本还调用了以下外部脚本或文件：

```
08_db_config.php             # 包含数据库连接的配置信息。
08_image_management.php      # 处理图片的喜欢和不喜欢的更新请求。
```

- 环境变量配置

```php
include '08_db_config.php';  // 包含数据库连接信息

$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://19640810.xyz"; // 域名网址
$picnumber = 3; // 设置需要显示的图片数量

fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})
```

注意：从数据库中随机选取图片名称，然后通过构造链接进行访问，但是并未考虑到项目文件夹中图片已经删除，但是数据库中仍保留其信息。因此，对于部分已删除图片显示的是空白。


🟢 note: 下面3个脚本的环境配置都是一样的，参考上述 `08_picDisplay_mysql.php`，区别在于 点赞/踩 图标的样式有一些区别

```
08_picDisplay_mysql.php                    # 点赞图标位于图片外右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRight.php            # 点赞图标位于图片内右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRigTra.php           # 点赞图标位于图片内右侧居中，点赞图标所在方框设置为透明，能够写入图片名到数据库
```

### 2. `08_picDisplay_order.php`

1. 用户验证：检查用户是否登录，若未登录，则重定向到登录页面。
2. 登出操作：若用户点击了登出链接，注销用户会话并重定向到登录页面。
3. 数据库连接：通过包含的数据库配置文件建立与数据库的连接。
4. 图片读取：从数据库中读取图片名称，**按照点赞数减去踩数的差值降序排序，并限制显示的图片数量**。
5. 图片展示：在网页上展示选定数量的图片，并通过设备类型自动调整图片宽度。
6. 刷新按钮：提供一个按钮，用户点击后刷新页面，以重新显示图片。

- 环境变量

```php
include '08_db_config.php';  // 包含数据库连接信息

$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://19640810.xyz"; // 域名网址
$picnumber = 50; // 设置需要显示的图片数量
```


# 4. ubuntu系统安装MySQL

### 1. 安装mysql

在 Ubuntu 云服务器上安装 MySQL 也是类似的过程，下面是详细的步骤：

1. **更新软件包列表**： 首先，更新服务器上的软件包列表：

```
sudo apt update
```

2. **安装 MySQL 服务器**： 运行以下命令以安装 MySQL 服务器：

```
sudo apt install mysql-server
```

3. **设置 MySQL 密码**： 在安装过程中，您可能会被要求设置 MySQL root 用户的密码。请记住您设置的密码，因为您在之后访问 MySQL 数据库时需要用到它。Ubuntu安装mysql默认此步骤跳过。

4. **检查 MySQL 服务器状态**： 安装完成后，MySQL 服务器将自动启动。您可以运行以下命令检查 MySQL 服务器状态：

```
sudo systemctl status mysql
```

5. **设置 MySQL 自启动**： 若要确保 MySQL 服务器在系统启动时自动启动，可以运行以下命令：

```
sudo systemctl enable mysql
```

6. **登录到 MySQL**： 使用以下命令登录到 MySQL 数据库：

使用以下命令登录到 MySQL 数据库：

```
sudo mysql -u root
```

如果没有要求输入密码，而是直接进入了 MySQL 提示符，那么很可能没有设置密码。您可以尝试运行以下查询来查看 MySQL 用户和权限信息：

```sql
SELECT User, Host, plugin FROM mysql.user;
```

在查询结果中，找到 User 列为 `root` 的那行。在同一行中，查看 plugin 列的值。如果 `plugin` 列的值为 `auth_socket`，则表示使用了操作系统身份验证，而不是密码验证。

输出示例

```
mysql> SELECT User, Host, plugin FROM mysql.user;
+------------------+-----------+-----------------------+
| User             | Host      | plugin                |
+------------------+-----------+-----------------------+
| debian-sys-maint | localhost | caching_sha2_password |
| mysql.infoschema | localhost | caching_sha2_password |
| mysql.session    | localhost | caching_sha2_password |
| mysql.sys        | localhost | caching_sha2_password |
| root             | localhost | auth_socket           |
+------------------+-----------+-----------------------+
5 rows in set (0.01 sec)
```

7. **修改root用户的认证插件**:

由于root用户的认证插件是auth_socket，您可以将其更改为mysql_native_password，以便您可以使用密码进行登录。打开MySQL命令行或任何MySQL管理工具，并执行以下命令：

```mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH 'mysql_native_password' BY 'your_password';
FLUSH PRIVILEGES;
```

将 `your_password` 替换为您想要设置的密码。

下面是更改后的输出，可以看到root的认证方式已经变为mysql_native_password

```
mysql> SELECT User, Host, plugin FROM mysql.user;
+------------------+-----------+-----------------------+
| User             | Host      | plugin                |
+------------------+-----------+-----------------------+
| debian-sys-maint | localhost | caching_sha2_password |
| mysql.infoschema | localhost | caching_sha2_password |
| mysql.session    | localhost | caching_sha2_password |
| mysql.sys        | localhost | caching_sha2_password |
| phpmyadmin       | localhost | caching_sha2_password |
| root             | localhost | mysql_native_password |
| wordpressuser    | localhost | caching_sha2_password |
+------------------+-----------+-----------------------+
7 rows in set (0.00 sec)
```

判断脚本能否正确读取数据库：https://github.com/Yiwei666/03_Python-PHP/blob/main/05_mysqlDict/mysqlTest.php


### 2. 创建数据库和表结构

1. 登录 MySQL：

使用命令行客户端登录 MySQL，你可能需要使用 root 账户：

```mysql
sudo mysql -u root -p
```


2. 创建新的数据库

```mysql
CREATE DATABASE your_database_name;
```

3. 创建用户并授权（root用户可跳过）

```mysql
CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

更改`your_username`、`your_password`、`your_database_name`三个参数

注意：上述命令不要对于root用户执行。root 用户在 MySQL 中默认已经拥有对所有数据库的全部权限。这意味着 root 用户通常不需要额外的权限授予来访问或管理特定的数据库。


4. 选择数据库：

```mysql
USE your_database_name;
```


5. 创建表：

```mysql
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_name VARCHAR(255) NOT NULL,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0
);
```

- `id`：一个自增的整数，用作主键。
- `image_name`：一个字符串字段，用来存储图片的名称。
- `likes`：一个整数字段，用来存储图片的喜欢次数。
- `dislikes`：一个整数字段，用来存储图片的不喜欢次数。

创建后的表结构：

```
mysql> describe images
    -> ;
+------------+--------------+------+-----+---------+----------------+
| Field      | Type         | Null | Key | Default | Extra          |
+------------+--------------+------+-----+---------+----------------+
| id         | int          | NO   | PRI | NULL    | auto_increment |
| image_name | varchar(255) | NO   |     | NULL    |                |
| likes      | int          | YES  |     | 0       |                |
| dislikes   | int          | YES  |     | 0       |                |
+------------+--------------+------+-----+---------+----------------+
```


6. 数据筛选

```sql
SELECT COUNT(*) FROM images;                              # 查询一个表中的数据条数
SELECT COUNT(*) FROM images WHERE likes > 100;            # 如果你想知道哪些图片的点赞数超过100
```

7. 数据查询


- 要查看MySQL数据库中一个表的前10行数据，可以使用以下命令：

```sql
SELECT * FROM images
ORDER BY id
LIMIT 10;
```

- 对于查看表的后10行数据，可以使用以下命令：

```sql
SELECT * FROM images
ORDER BY id DESC
LIMIT 10;
```

- 这条命令将数据按照id降序排列，从而使得最新的记录排在前面，然后通过LIMIT 10返回最后10行。如果你需要它们按原始顺序展示，可以对结果再次使用ORDER BY id ASC：

```sql
SELECT * FROM (
    SELECT * FROM images
    ORDER BY id DESC
    LIMIT 10
) AS last_ten
ORDER BY id ASC;
```
这里，我们使用了一个子查询来首先获取最后10行，然后在外层查询中对这些结果按id进行升序排序，以返回按原始顺序的记录。



### 3. mysql常用命令

```mysql
CREATE DATABASE dbname;          # 创建数据库
SHOW DATABASES;                  # 查看 MySQL 服务器上存在哪些数据库
 
# 修改数据库名称
USE old_database_name;           # 切换到要查看的数据库
ALTER DATABASE old_database_name RENAME TO new_database_name; # 修改数据库名字
 
DROP DATABASE database_name;     # 删除数据库
 
SHOW TABLES;                     # 显示数据库中的所有表
 
SHOW VARIABLES LIKE 'secure_file_priv'; # 这个命令将显示MySQL服务器允许加载数据文件的目录。
 
DESCRIBE tablename;              # 查看表的构成
 
DROP TABLE table_name;            # 删除表
 
SELECT id, word FROM GREtable;    # 这将返回表格中的id列和word列的内容。
 
SELECT * FROM tablename;         # 查看所有行和列
 
SELECT meaning FROM GREtable WHERE ID = 2;  # 查看指定ID的meaning
SELECT meaning FROM GREtable WHERE ID IN (2, 3, 5);  # 查看多个ID的meaning，可以使用IN关键字
 
SELECT * FROM SATtable LIMIT 10;  # 查看前10行数据；
 
SHOW VARIABLES LIKE 'character\_set\_database';  # 查看数据库的编码方式
```





# 5. to do list

1. rclone onedrive 图片备份
2. mysql 数据库备份




# 参考资料

1. 可可英语：https://github.com/Yiwei666/03_Python-PHP/tree/main/01_kekemusic
2. 在线词典：https://github.com/Yiwei666/03_Python-PHP/tree/main/05_mysqlDict
3. mysql数据库博客：https://github.com/Yiwei666/12_blog/blob/main/002/002.md






