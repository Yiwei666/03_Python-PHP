# 1. 项目功能

1. 将指定文件夹下的图片名称全部写入到mysql数据库中
2. 用户可以在web页面进行点赞，并写入到数据库中以及在页面即时显示点赞数。
3. 通过👍和👎图标计数likes和dislikes的数量，二者差值代表总喜欢数。基于总喜欢数排序显示图片。


# 2. 文件结构

```
08_db_config.php                           # 通常包含数据库连接信息如服务器地址、用户名、密码等
08_image_management.php                    # 用于响应用户对图片进行喜欢或不喜欢操作的后端服务，通过更新数据库并实时反馈结果到前端用户界面

08_picDisplay_mysql.php                    # 点赞图标位于图片外右侧居中
08_picDisplay_mysql_inRight.php            # 点赞图标位于图片内右侧居中
08_picDisplay_mysql_inRigTra.php           # 点赞图标位于图片内右侧居中，点赞图标所在方框设置为透明
08_picDisplay_order.php                    # 基于总点赞数排序显示图片
08_picDisplay_mysql_gallery.php            # 显示数据库中所有图片，添加分页、侧边栏、localStorage
08_picDisplay_mysql_order.php              # 显示数据库中所有图片，按照总点赞数由多到少排序，添加分页、侧边栏、localStorage
```

# 3. 环境配置

### 1. `08_picDisplay_mysql.php`

1. 用户认证：检查用户是否已经登录，如果未登录则重定向到登录页面。
2. 图片管理：从特定目录获取所有PNG格式的图片，检查这些图片是否已经存入数据库中。如果没有，则将其添加到数据库。
3. 图片展示：从数据库中随机选取指定数量的图片（在此脚本中设置为3张），然后在网页上显示。
4. 互动功能：用户可以点击喜欢或不喜欢的按钮来更新图片的喜欢和不喜欢的数量。

此外，该脚本还调用了以下外部脚本或文件：

```
08_db_config.php             # 包含数据库连接的配置信息。
08_image_management.php      # 处理图片的喜欢和不喜欢的更新请求。
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

```
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

3. 创建用户并授权

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


6. 数据筛选

```
SELECT COUNT(*) FROM images;                              # 查询一个表中的数据条数
SELECT COUNT(*) FROM images WHERE likes > 100;            # 如果你想知道哪些图片的点赞数超过100
```





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











