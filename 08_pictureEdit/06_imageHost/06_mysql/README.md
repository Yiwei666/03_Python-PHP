# 1. 项目功能

1. 将指定文件夹下的图片名称全部写入到`mysql`数据库中
2. 用户可以在web页面进行点赞，并写入到数据库中以及在页面同步显示点赞数。
3. 通过👍和👎图标计数`likes`和`dislikes`的数量，二者差值代表总喜欢数。基于总喜欢数排序显示图片。


# 2. 文件结构

## 1. 文件结构


```bash
# 1. 功能模块
08_db_config.php                               # 通常包含数据库连接信息如服务器地址、用户名、密码等
08_db_sync_images.php                          # 图片目录与数据库同步功能模块
08_db_image_status.php                         # 该功能模块将项目文件夹下已删除的图片在数据库中image_exists赋值为0，存在则赋值为1，注意项目文件夹中图片信息是数据库图片信息的子集
08_image_management.php                        # 用于响应用户对图片进行喜欢或不喜欢操作的后端服务，通过更新数据库并实时反馈结果到前端用户界面
08_image_leftRight_navigation.php              # 点击图片下方🔁按钮，打开该脚本，显示对应的图片，按照数据库默认或者likes降序排列，点击左右箭头实现图片顺序切换（已弃用，升级版本取代）
08_image_leftRight_navigation_voteStar.php     # 新增点赞/踩以及收藏功能，是 08_image_leftRight_navigation.php 升级版本
08_db_toggle_star.php                          # 根据图片的ID，查询该图片是否已被标记为“星标”（star），并在每次请求时切换其状态（从“标记”到“未标记”或反之），然后将新的状态更新到数据库并返回给前端。
08_image_web_category.php                      # 通过 AJAX 接口对图片的分类进行动态管理，包括获取所有分类、查询图片所属分类、更新图片的分类关联等，在 08_image_leftRight_navigation_starT.php 系列脚本中调用
08_image_leftRight_navigation_starT.php        # 相比于 08_image_leftRight_navigation_starF.php，新增图片分类按钮，在图片右上角显示当前图片所属分类，支持对于所选某一具体分类或者所有图片的且换导航


# 2. 后台管理
08_image_likes_manager.php                          # 后台控制（增加或减少）数据库中的likes和dislikes数量变化
08_image_dislikes_delete.php                        # 后台控制（增加或减少）数据库中的likes和dislikes数量变化，功能4能够删除图片文件夹中dislikes数在某个范围内的图片，删除前需rclone备份至onedrive
08_image_rclone_replace.php                         # 随机替换目录下的图片，确保目录下的总图片数为5000
08_server_manage_categories.php                     # 在后台中通过命令行对图片分类进行增删查改
08_server_update_unknowImage_picCategories.php      # 在后台中更新 "0.0 未知" 分类下的图片id，推荐cron定时更新
08_server_image_rclone_likesRange.php               # 后台下载指定likes值或范围内的图片（根据 image_exists=0来筛选）
08_server_filter_delete_images.php                  # 在后台中允许用户根据图片的多种条件（如 star、ID 范围、分类、likes、dislikes 等）从数据库中筛选图片，并选择性地删除指定目录下的对应图片文件，同时更新数据库状态
08_server_batch_categorize_images.php               # 基于图片命中的kindID字符串，在后台中批量给图片进行分类


# 3. web交互
08_picDisplay_mysql.php                    # 点赞图标位于图片外右侧居中，能够写入图片名到数据库，随机显示数据库中的 n 张图片
08_picDisplay_mysql_inRight.php            # 点赞图标位于图片内右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRigTra.php           # 点赞图标位于图片内右侧居中，点赞图标所在方框设置为透明，能够写入图片名到数据库

08_picDisplay_order.php                    # 基于总点赞数排序显示有限张图片，例如50张图片，未分页，显示为1列，只显示存在于服务器上的图片，通过SQL查询命令 WHERE image_exists = 1 来筛选
08_picDisplay_mysql_gallery.php            # 显示数据库中所有图片，添加分页、侧边栏、localStorage，按照文件名默认排序
08_picDisplay_mysql_order.php              # 显示数据库中所有图片，按照总点赞数由多到少排序，添加分页、侧边栏、localStorage

08_picDisplay_mysql_orderExist.php         # 基于数据库中的图片信息显示图片文件夹中所有图片，按照图片数据库中 likes-dislikes 的值降序显示，不显示数据库中已删除的图片，不显示已删除图片导致的空白页
08_picDisplay_mysql_galleryExist.php       # 基于数据库中的图片信息显示图片文件夹中所有图片，不显示数据库中已删除的图片，不显示已删除图片导致的空白页，按照文件名默认排序
08_picDisplay_mysql_orderExistTab.php          # 基于数据库中的图片信息显示图片文件夹中所有图片，按照图片数据库中 likes-dislikes 的值降序显示，不显示数据库中已删除的图片，显示在新标签页打开图片的图标（含左右切换导航），新增收藏/取消按钮等
08_picDisplay_mysql_galleryExistTab.php        # 基于数据库中的图片信息显示图片文件夹中所有图片，不显示数据库中已删除的图片，按照文件名默认排序，显示在新标签页打开图片的图标
08_picDisplay_mysql_orderExistTab_starT.php    # 显示收藏的图片，增加了分类选择弹窗，用户可点击按钮选择分类，并在分页、图片导航时保持筛选状态。
08_picDisplay_mysql_galleryExistTab_starT.php  # 功能与 08_picDisplay_mysql_orderExistTab_starT.php 几乎一样，是在其基础上进行修改的，唯一的区别是图片的排列顺序，按照默认顺序排列


# 4. 衍生脚本
08_picDisplay_mysql_galleryExistTab_starF.php      # 只显示服务器中star为0的图片，图片按照数据库默认排序显示
08_picDisplay_mysql_orderExistTab_starF.php        # 只显示服务器中star为0的图片，图片按照点赞数排序显示
08_image_leftRight_navigation_starF.php            # 对服务器中star为0的图片，支持两种切换算法：点赞数排序和默认排序

08_picDisplay_mysql_galleryExistTab_starT.php      # 只显示服务器中star为1的图片，图片按照数据库默认排序显示
08_picDisplay_mysql_orderExistTab_starT.php        # 只显示服务器中star为1的图片，图片按照点赞数排序显示
08_image_leftRight_navigation_starT.php            # 对服务器中star为1的图片，支持两种切换算法：点赞数排序和默认排序

08_image_rclone_top30.php                          # 从图片数据库中随机选取150张点赞数大于等于29的图片，进行下载
```

## 2. 数据库和表

### 1. `images` 父表

```
mysql> describe images;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| image_name   | varchar(255) | NO   |     | NULL    |                |
| likes        | int          | YES  |     | 0       |                |
| dislikes     | int          | YES  |     | 0       |                |
| image_exists | tinyint      | YES  |     | 0       |                |
| star         | tinyint(1)   | YES  |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+
6 rows in set (0.04 sec)
```

### 2. `Categories` 父表和`PicCategories` 子表

💡 **数据库构建思路**

如上所示，`image_db`图片数据库中有一个`images`表，里面存储了多张图片的元数据，包括每一张图片的id， 图片名，点赞数，点踩数，状态，受否被收藏等信息。每条数据在mysql数据库中占据一行，大概有几万条数据。现在需要对每张图片进行分类管理。下面是我的初步方案
- 方案：使用三个表来规范化数据，images表存储图片信息（已经创建并存有数据），Categories 表存储分类信息（未创建），PicCategories 表存储图片与分类的关联（未创建）。
- images 表存储每张图片的基本信息。
- Categories 表存储所有可能的分类。
- PicCategories 表实现 images 与 Categories 之间的多对多关系，每条记录表示一张图片属于一个分类。
- Categories 和 PicCategories 的操作不能够影响 images 表中的数据。

现在需要创建 Categories 和 PicCategories 表，请给出mysql操作命令。

```mysql
USE image_db;


-- 创建 Categories 表
CREATE TABLE Categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


-- 在 Categories 表中新增 kindID 列
ALTER TABLE Categories
ADD COLUMN kindID VARCHAR(255) DEFAULT NULL AFTER category_name,
ADD UNIQUE (kindID);


-- 创建 PicCategories 表，实现 images 和 Categories 的多对多关系，
-- 并在外键约束后加 ON DELETE CASCADE ON UPDATE CASCADE
-- 当父表记录被删除/更新时，子表自动执行相应操作
CREATE TABLE PicCategories (
  image_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (image_id, category_id),
  FOREIGN KEY (image_id) 
    REFERENCES images(id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  FOREIGN KEY (category_id) 
    REFERENCES Categories(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;
```


- `Categories` 和 `PicCategories` 表结构

```
mysql> describe Categories;
+---------------+--------------+------+-----+---------+----------------+
| Field         | Type         | Null | Key | Default | Extra          |
+---------------+--------------+------+-----+---------+----------------+
| id            | int          | NO   | PRI | NULL    | auto_increment |
| category_name | varchar(255) | NO   |     | NULL    |                |
| kindID        | varchar(255) | YES  | UNI | NULL    |                |
+---------------+--------------+------+-----+---------+----------------+
3 rows in set (0.01 sec)


mysql> describe PicCategories;
+-------------+------+------+-----+---------+-------+
| Field       | Type | Null | Key | Default | Extra |
+-------------+------+------+-----+---------+-------+
| image_id    | int  | NO   | PRI | NULL    |       |
| category_id | int  | NO   | PRI | NULL    |       |
+-------------+------+------+-----+---------+-------+
2 rows in set (0.01 sec)
```


# 3. php功能模块

## 1. `08_db_config.php` 数据库连接

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


## 2. `08_db_sync_images.php` 数据库同步图片信息

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


## 3. `08_image_management.php` 图像点赞/反对

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



## 4. `08_db_image_status.php` 判断图片是否删除

1. 该功能模块将项目文件夹下已删除的图片在数据库中`image_exists`赋值为0，存在则赋值为1，注意项目文件夹中图片信息是数据库图片信息的子集
2. 运行该脚本前需要在数据库`images`表中新增`image_exists`一列
3. 调用该模块前确保图片文件夹中的所有图片名均已经写入到mysql数据库中

```sql
ALTER TABLE images ADD COLUMN image_exists TINYINT DEFAULT 0;
```

- 新增`image_exists`列后的完整表格如下

```
mysql> describe images;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| image_name   | varchar(255) | NO   |     | NULL    |                |
| likes        | int          | YES  |     | 0       |                |
| dislikes     | int          | YES  |     | 0       |                |
| image_exists | tinyint      | YES  |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+
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



## 5. `08_image_leftRight_navigation.php` 图片顺序切换（已弃用）

### 1. 功能

功能：上述代码实现了一个图片浏览与切换功能的网页，其中包括图片的排序与导航。以下是具体功能概述：

- 图片排序：根据传递的 sort 参数，图片可以按照两种方式排序：
    - 排序1（sort=1）：按照 (likes - dislikes) 的差值进行降序排序。
    - 排序2（sort=2）：保持数据库中的默认排序（不做额外排序处理）。

- 图片导航：用户可以通过左右箭头按钮在图片之间切换：
    - 点击左箭头，会加载上一张图片。
    - 点击右箭头，会加载下一张图片。
    - 每次切换都会保持与当前排序方式一致。

- 传递参数：用户点击左右箭头时，页面会刷新，并传递当前图片的 `id` 和排序算法 `sort` 参数，保证图片切换时依然按照相应的排序方式进行。


### 2. 环境变量

```php
$key = 'signin-key-1'; // 应与加密时使用的密钥相同

// 引入数据库配置
include '08_db_config.php';

$domain = "https://19640810.xyz";
$dir5 = str_replace("/home/01_html", "", "/home/01_html/08_x/image/01_imageHost");

// 更换脚本名 08_image_leftRight_navigation.php
<button class="arrow arrow-left" onclick="window.location.href='08_image_leftRight_navigation.php?id=<?php echo $validImages[$prevIndex]['id']; ?>&sort=<?php echo $sortType; ?>'">←</button>
<button class="arrow arrow-right" onclick="window.location.href='08_image_leftRight_navigation.php?id=<?php echo $validImages[$nextIndex]['id']; ?>&sort=<?php echo $sortType; ?>'">→</button>
```


### 3. 模块调用

通常在 `08_picDisplay_mysql_galleryExistTab.php ` 和 `08_picDisplay_mysql_orderExistTab.php`中调用本模块，点击🔁按钮，传递`id和sort`参数给本脚本。调用示例如下所示，注意`sort`为1或者2，代表不同的排序算法。

```html
<button onclick="window.open('08_image_leftRight_navigation.php?id=<?php echo $image['id']; ?>&sort=2', '_blank')">🔁</button>
```

注意：该模块`08_image_leftRight_navigation.php`在实际生产中已弃用，由升级版本`08_image_leftRight_navigation_voteStar.php`取代。





## 6. `08_db_toggle_star.php` 图片收藏或取消

### 1. `images`表格新增列

1. 新增 star 列

在表 `images` 中增加一列 `star`，取值为 `0 或者 1`，并将默认值设置为 `0`，你可以使用以下 SQL 语句：

```sql
ALTER TABLE images
ADD COLUMN star TINYINT(1) DEFAULT 0;
```

2. 新的完整表格如下

```
mysql> describe images;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| image_name   | varchar(255) | NO   |     | NULL    |                |
| likes        | int          | YES  |     | 0       |                |
| dislikes     | int          | YES  |     | 0       |                |
| image_exists | tinyint      | YES  |     | 0       |                |
| star         | tinyint(1)   | YES  |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+
6 rows in set (0.00 sec)
```

### 2. `08_db_toggle_star.php` 功能

上述代码实现了一个用于切换数据库中某一图片的 "star" 状态的功能，具体描述如下：

- 引入数据库配置文件：`include '08_db_config.php';` 引入了包含数据库连接信息的配置文件。

- 处理POST请求：代码使用 `$_SERVER['REQUEST_METHOD'] === 'POST'` 来检查请求是否是一个POST请求。这意味着它期待通过POST方法发送的数据。

- 获取图片ID：通过 `$_POST['imageId']` 从请求体中获取 `imageId`，并将其转换为整数（`intval()`）。这个ID用于查找数据库中相应的图片记录。

- 查询图片的当前星标状态：
    - 使用 `SELECT star FROM images WHERE id = ?` 查询数据库中指定 `imageId` 的图片记录，并获取该图片的当前 `star` 值。
    - star 是一个二元值（0或1），表示图片是否被标记为“星标”（如收藏、加精等）。

- 切换星标状态：
    - 使用三元运算符 `($row['star'] == 1) ? 0 : 1`，根据当前的 star 值切换其状态。如果 star 当前是 1（星标状态），则改为 0，反之则改为 1。

- 更新数据库中的星标值：
    - 使用 `UPDATE images SET star = ? WHERE id = ?`，将新的 star 值写回到数据库对应的图片记录中。

- 返回JSON响应：

通过 `echo json_encode(['star' => $newStarValue]);` 返回一个JSON格式的响应，包含更新后的 star 状态。这样，前端可以根据新的 star 值更新用户界面。

总结：该代码的功能是根据图片的ID，查询该图片是否已被标记为“星标”（star），并在每次请求时切换其状态（从“标记”到“未标记”或反之），然后将新的状态更新到数据库并返回给前端。


### 3. 环境变量

```php
include '08_db_config.php';
```

注意：只需要引入了包含数据库连接信息的配置文件即可


### 4. 模块调用

通常在 `08_picDisplay_mysql_galleryExistTab.php ` 和 `08_picDisplay_mysql_orderExistTab.php`中调用本模块，在`08_image_leftRight_navigation_voteStar.php`等后续系列脚本中也被调用。调用该模块，实现图片收藏与取消，需要修改和添加以下代码部分。

- 确保数据库查询正确获取 star 值：

```php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1";
```

- CSS修改以美化五角星按钮：

```css
.star-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    transition: color 0.3s ease;
}
```

- 新增JavaScript函数`toggleStar`：

```javascript
// 对应图片收藏或取消操作
function toggleStar(imageId) {
    fetch('08_db_toggle_star.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `imageId=${imageId}`
    })
    .then(response => response.json())
    .then(data => {
        // 更新五角星按钮的颜色
        const starBtn = document.getElementById(`star-${imageId}`);
        starBtn.style.color = data.star == 1 ? 'green' : 'red';
    });
}
```

- 在HTML中新增五角星收藏按钮：

```html
<!-- 五角星收藏按钮，颜色根据数据库中的 star 值动态设置 -->
<button id="star-<?php echo $image['id']; ?>" class="star-btn" 
    onclick="toggleStar(<?php echo $image['id']; ?>)" 
    style="color: <?php echo ($image['star'] == 1) ? 'green' : 'red'; ?>;">
    ★
</button>
```



## 7. `08_image_leftRight_navigation_voteStar.php` 点赞+收藏


### 1. 功能

1. 图片左右切换+点赞/踩+收藏，是 `08_image_leftRight_navigation.php` 升级版本。
2. 相比于 `08_image_leftRight_navigation.php` 代码，新增了点赞/点踩、收藏图标以及相应模块的调用；
3. 针对不同客户端（电脑/手机），新增了图标尺寸的优化。


### 2. 环境变量

相比于 `08_image_leftRight_navigation.php`，多了`08_image_management.php`和`08_db_toggle_star.php`两个模块调用。

```php
$key = 'signin-key-1'; // 应与加密时使用的密钥相同

// 引入数据库配置
include '08_db_config.php';

$domain = "https://19640810.xyz";
$dir5 = str_replace("/home/01_html", "", "/home/01_html/08_x/image/01_imageHost");

// 点赞和点踩功能
fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})

// 收藏和取消收藏功能
fetch('08_db_toggle_star.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}`
})

// 更换脚本名 08_image_leftRight_navigation_voteStar.php
<button class="arrow arrow-left" onclick="window.location.href='08_image_leftRight_navigation_voteStar.php?id=<?php echo $validImages[$prevIndex]['id']; ?>&sort=<?php echo $sortType; ?>'">←</button>
<button class="arrow arrow-right" onclick="window.location.href='08_image_leftRight_navigation_voteStar.php?id=<?php echo $validImages[$nextIndex]['id']; ?>&sort=<?php echo $sortType; ?>'">→</button>
```

### 3. 模块调用

通常在 `08_picDisplay_mysql_galleryExistTab.php ` 、 `08_picDisplay_mysql_orderExistTab.php`等脚本中调用本模块，点击🔁按钮，传递 `id` 和 `sort` 参数给本脚本。调用示例如下所示，注意`sort`为1或者2，代表不同的排序算法。

```html
<button onclick="window.open('08_image_leftRight_navigation_voteStar.php?id=<?php echo $image['id']; ?>&sort=2', '_blank')">🔁</button>
```

注意：该模块 `08_image_leftRight_navigation_voteStar.php` 与 `08_image_leftRight_navigation.php` 模块的调用方式相同。排序1（sort=1）：按照 (likes - dislikes) 的差值进行降序排序。排序2（sort=2）：保持数据库中的默认排序（不做额外排序处理）。



### 4. 衍生脚本

💡 **`08_image_leftRight_navigation_voteStar.php` 系列脚本主要区别**

```php
// 从数据库中获取所有本地存在的图片
// 08_image_leftRight_navigation.php
$query = "SELECT id, image_name, likes, dislikes FROM images WHERE image_exists = 1";

// 从数据库中获取所有本地存在的图片以及star值
// 08_image_leftRight_navigation_voteStar.php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1";

// 从数据库中获取所有本地存在且star为1的图片
// 08_image_leftRight_navigation_starT.php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1";

// 从数据库中获取所有本地存在且star为0的图片
// 08_image_leftRight_navigation_starF.php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 0";
```



## 8. `08_image_web_category.php` 图片分类模块

功能：通过与 MySQL 数据库交互，提供了一组函数和 AJAX 接口，用于管理图片及其分类信息，包括查询图片详情、获取所有分类、查询图片所属分类、获取分类下的图片 ID，以及更新图片的分类关联。

### 1. 创建数据库表格

```mysql
USE image_db;


-- 创建 Categories 表
CREATE TABLE Categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


-- 创建 PicCategories 表，实现 images 和 Categories 的多对多关系，
-- 并在外键约束后加 ON DELETE CASCADE ON UPDATE CASCADE
-- 当父表记录被删除/更新时，子表自动执行相应操作
CREATE TABLE PicCategories (
  image_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (image_id, category_id),
  FOREIGN KEY (image_id) 
    REFERENCES images(id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  FOREIGN KEY (category_id) 
    REFERENCES Categories(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;
```

1. 这段 SQL 代码在名为 `image_db` 的数据库中创建了两个表：`Categories` 和 `PicCategories`，用于管理图片分类。
2. `Categories` 表存储分类信息，包含自增主键 `id` 和分类名称 `category_name`。
3. `PicCategories` 表通过 `image_id` 和 `category_id` 建立图片与分类的多对多关系，并设置外键约束，带有 `ON DELETE CASCADE` 和 `ON UPDATE CASCADE`，确保父表记录删除或更新时子表自动同步。


- 创建后的所有表格

```sql
mysql> describe images;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int          | NO   | PRI | NULL    | auto_increment |
| image_name   | varchar(255) | NO   |     | NULL    |                |
| likes        | int          | YES  |     | 0       |                |
| dislikes     | int          | YES  |     | 0       |                |
| image_exists | tinyint      | YES  |     | 0       |                |
| star         | tinyint(1)   | YES  |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+
6 rows in set (0.04 sec)

mysql> describe Categories;
+---------------+--------------+------+-----+---------+----------------+
| Field         | Type         | Null | Key | Default | Extra          |
+---------------+--------------+------+-----+---------+----------------+
| id            | int          | NO   | PRI | NULL    | auto_increment |
| category_name | varchar(255) | NO   |     | NULL    |                |
+---------------+--------------+------+-----+---------+----------------+
2 rows in set (0.01 sec)

mysql> describe PicCategories;
+-------------+------+------+-----+---------+-------+
| Field       | Type | Null | Key | Default | Extra |
+-------------+------+------+-----+---------+-------+
| image_id    | int  | NO   | PRI | NULL    |       |
| category_id | int  | NO   | PRI | NULL    |       |
+-------------+------+------+-----+---------+-------+
2 rows in set (0.01 sec)
```


### 2. 编程思路

现在我想要编写一个 `08_image_web_category.php` 模块，其中包含多个php函数，以便在其他脚本中调用，需求如下：
1. 能够根据图片id在 `images` 表格中查询该图片的相关信息
2. 能够查询 `Categories` 中的所有分类
3. 能够输入图片id返回 `PicCategories` 中该图片所属的所有分类
4. 能够查询 `PicCategories` 中某一分类下的所有图片id
5. 能够根据输入的图片id和分类名在 `PicCategories` 中更新该图片的所属分类

注意，数据库连接可以通过调用 `08_db_config.php` 模块来实现



### 3. 环境变量

```php
// 引入数据库配置
include '08_db_config.php';
```

注意：数据库 `image_db` 中应包含以下 `images`、`Categories` 和 `PicCategories` 表格



### 4. 模块调用

- 要调用这个脚本：
1. 使用 POST 请求。
2. 提供 `action` 参数（`getCategoriesForImage` 或 `setImageCategories`）。
3. 根据 `action` 提供额外的参数（`imageId` 和/或 `categories`）。
4. 通过前端工具（如 fetch 或 jQuery）发送请求并处理返回的 JSON 数据。

通常在 `08_image_leftRight_navigation_starT.php` 脚本中调用，部分调用示例如下


```php
// 引入分类操作文件，以便使用 getImagesOfCategory()、getCategoriesOfImage() 等
include '08_image_web_category.php';
```

- 功能：获取所有分类以及当前图片所属的分类，并显示分类弹窗。

```js
// 打开分类弹窗：获取所有分类 + 当前图片所属分类
function openCategoryWindow(imageId) {
    fetch('08_image_web_category.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=getCategoriesForImage&imageId=' + imageId
    })
    .then(response => response.json())
    .then(data => {
        // data.allCategories: 所有分类
        // data.imageCategories: 当前图片已关联的分类
        const categoryContainer = document.getElementById('category-list');
        categoryContainer.innerHTML = '';

        // 把当前图片所属的分类ID记录成一个数组, 方便判断是否勾选
        const imageCatIds = data.imageCategories.map(item => item.id);

        data.allCategories.forEach(cat => {
            // 创建 checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = cat.category_name;
            // 如果该分类在 imageCatIds 里则设为已选中
            checkbox.checked = imageCatIds.includes(cat.id);

            const label = document.createElement('label');
            label.style.marginLeft = '5px';
            label.textContent = cat.category_name;

            const divItem = document.createElement('div');
            divItem.appendChild(checkbox);
            divItem.appendChild(label);

            categoryContainer.appendChild(divItem);
        });

        // 记录当前操作的 imageId，后续保存时要用
        document.getElementById('save-category-btn').setAttribute('data-image-id', imageId);

        // 显示弹窗
        document.getElementById('category-popup').style.display = 'block';
    });
}
```

- 流程：
    - 通过 `fetch` 向 `08_image_web_category.php` 发送 POST 请求，`action=getCategoriesForImage` 和 `imageId` 参数。
    - 后端返回：
        - `data.allCategories`：所有分类数据。
        - `data.imageCategories`：当前图片已经关联的分类。
    - 遍历 `allCategories` 并创建对应的复选框 (checkbox)，如果当前图片包含该分类 (imageCatIds 里有该分类 id)，则将该分类默认勾选。
    - 将所有复选框添加到 `#category-list` 容器中。
    - 记录当前操作的 `imageId` 以便后续保存。
    - 显示 `#category-popup` 弹窗。



- 功能：将勾选的分类数据提交到后端并更新。

```js
// 保存当前图片的勾选分类
function saveCategories() {
    const imageId = document.getElementById('save-category-btn').getAttribute('data-image-id');
    // 收集所有勾选的分类名
    const checkboxes = document.querySelectorAll('#category-list input[type="checkbox"]');
    const selected = [];
    checkboxes.forEach(cb => {
        if (cb.checked) {
            selected.push(cb.value);
        }
    });

    // 发送到后端
    fetch('08_image_web_category.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=setImageCategories'
            + '&imageId=' + imageId
            + '&categories=' + encodeURIComponent(JSON.stringify(selected))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('分类更新成功！');
            closeCategoryWindow();
            location.reload(); // 可根据需要刷新页面
        } else {
            alert('分类更新失败: ' + (data.error || '未知错误'));
        }
    });
}
```

- 流程：
    - 读取 `#save-category-btn` 上的 `data-image-id` 获取当前图片的 `imageId`。
    - 遍历 `#category-list` 里所有 checkbox，将已勾选的分类名称存入 selected 数组。
    - 通过 `fetch` 发送 POST 请求至 `08_image_web_category.php`，`action=setImageCategories`，并将 `imageId` 和 `categories` 作为参数。
    - 后端返回 `data.success` 判断是否更新成功：
        - 更新成功：提示`“分类更新成功”`，关闭弹窗，并刷新页面（`location.reload()`）。
        - 更新失败：提示失败信息。




## 9. `08_image_leftRight_navigation_starT.php` 新增分类按钮和分类导航


### 1. 功能

相比于 `08_image_leftRight_navigation_starF.php` 脚本，新增功能如下

1. 分类筛选与导航：支持按分类 (`cat` 参数) 进行图片筛选和切换，仅在指定分类内浏览符合 `image_exists = 1 AND star = 1` 条件的图片。
2. 分类管理功能：新增 分类管理按钮 🎨，可打开弹窗查看和修改图片分类，支持动态更新数据库中的分类信息。
3. 导航增强：左右切换按钮 (`← →`) 现在会携带 `cat` 参数，确保分类内的图片切换，而不是全局图片列表。
4. 分类弹窗交互：新增分类选择弹窗，列出所有分类并标记当前图片所属的分类，用户可勾选或取消后提交更新。
5. 如果 `cat = 0`，则默认在 所有 `image_exists = 1 AND star = 1` 的图片 中进行切换。


### 2. 编程思路

`08_image_leftRight_navigation_starT.php`脚本代码如下，该代码实现了图片展示与左右导航（通过前后图片 ID 跳转）、互动功能（点赞、点踩、收藏），并根据移动端/PC 端自动调整界面样式。该脚本在调用时需要传入图片id和排序类型sort参数。现在我需要新增一些功能，通过调用 `08_image_web_category.php` 模块中的函数来实现，需求如下：

1. 在页面右侧箭头上方合适的高度处添加一个分类图标🎨（注意与点赞、收藏等图标竖直对齐），点击该分类图标时，会查询 Categories 中的所有图片分类名称，并根据当前页面中的图片id查询PicCategories表中该图片所属的所有分类（这些查询调用`08_image_web_category.php` 模块相关函数实现），并弹出一个小窗口。在窗口中展示返回的所有分类名称，以及当前图片所属的分类（窗口中）。

2. 窗口中所有分类名称可以显示为5列，按照Categories中名称id顺序从左到右，从上到下显示为5列，显示的行数根据分类名称的数量来确定，当分类的行数很多，并超过了窗口的高度时，则出现纵向滚动轴。可以将窗口的宽度设置为页面宽度的80%，在页面中水平居中。

3. 如果当前图片在PicCategories中已有大于等于1个分类时，小窗口中应当在相应图片分类名称前的小方框中进行对号勾选标记。用户也可以取消勾选当前分类，或者勾选其他分类，PicCategories表中分类信息在用户点击保存按钮后应当相应更新，且窗口中的勾选符号应同步显示数据库中的该图片分类状态。（更新图片分类可以调用 `08_image_web_category.php` 模块相关函数实现）

4. 小窗口下方显示“保存”和“取消”两个按钮，点击后使得用户的勾选操作在数据库中生效/取消。小窗口右上角还应显示关闭该窗口的叉图标。注意小窗口UI界面美观

`08_image_leftRight_navigation_starT.php` 代码中原有的代码逻辑、样式、功能不要改变，只需要针对上述需求进行修改。输出 `08_image_web_category.php` 代码和修改后的 `08_image_leftRight_navigation_starT.php` 脚本代码


运行上述代码，点击 🎨 图标显示的窗口中，正确显示了 Categories中的所有图片分类名称，但是并没有当前图片所属的分类名称前的方框中勾选对号，以显示当前图片所属的分类。请针对该问题进行修改

上述 `08_image_web_category.php` 代码修改后可以正常工作。我现在有一个新的需求，请继续修改 `08_image_leftRight_navigation_starT.php` 代码，在图片的右上角显示当前图片所属的所有分类名称，字体、字号和颜色分别为
`font-family: Arial, sans-serif;  灰色 (#777)，11px`。针对上述需求进行修改，输出修改后的完整代码，原有的代码逻辑、样式、功能不要改变。


### 3. 环境变量

```php
$key = 'signin-key-1'; // 应与加密时使用的密钥相同

// 引入数据库配置
include '08_db_config.php';

// 引入分类操作文件，以便使用 getImagesOfCategory()、getCategoriesOfImage() 等
include '08_image_web_category.php';

$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1";

// 当前图片信息
$currentImage = $validImages[$currentIndex];
$domain = "https://domain.com";
$dir5 = str_replace("/home/01_html", "", "/home/01_html/08_x/image/01_imageHost");
```

```js
// 点赞和点踩功能
fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})

// 收藏和取消收藏功能
fetch('08_db_toggle_star.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}`
})

// 打开分类弹窗：获取所有分类 + 当前图片所属分类
fetch('08_image_web_category.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=getCategoriesForImage&imageId=' + imageId
})

// 发送到后端
fetch('08_image_web_category.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=setImageCategories'
        + '&imageId=' + imageId
        + '&categories=' + encodeURIComponent(JSON.stringify(selected))
})


<button class="arrow arrow-left"
        onclick="window.location.href='08_image_leftRight_navigation_starT.php?id=<?php echo $validImages[$prevIndex]['id']; ?>&sort=<?php echo $sortType; ?>&cat=<?php echo $catId; ?>'">
    ←
</button>


<button class="arrow arrow-right"
        onclick="window.location.href='08_image_leftRight_navigation_starT.php?id=<?php echo $validImages[$nextIndex]['id']; ?>&sort=<?php echo $sortType; ?>&cat=<?php echo $catId; ?>'">
    →
</button>
```



### 4. 模块调用

该模块通常在 `08_picDisplay_mysql_orderExistTab_starT.php`、`08_picDisplay_mysql_galleryExistTab_starT.php`等web脚本中调用，调用方式接近。


1. 在`08_picDisplay_mysql_orderExistTab_starT.php`中的调用

```js
<button onclick="window.open('08_image_leftRight_navigation_starT.php?id=<?php echo $image['id']; ?>&sort=1&cat=<?php echo $selectedCategory; ?>', '_blank')">
    🔁
</button>
```

点击“🔁”按钮进入 `08_image_leftRight_navigation_starT.php` 时会带上 cat 参数，使左右导航只在该分类下循环。



2. 在`08_picDisplay_mysql_galleryExistTab_starT.php`中的调用，不传入 cat 参数也是可以的。

```js
<button onclick="window.open('08_image_leftRight_navigation_starT.php?id=<?php echo $image['id']; ?>&sort=2', '_blank')">🔁</button>
```

若不传 cat 参数，`08_picDisplay_mysql_galleryExistTab_starT.php` 保持原先逻辑显示所有(满足 `star=1, image_exists=1`)的图片。





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

- 源码：[08_image_dislikes_delete.php](08_image_dislikes_delete.php)

`08_image_dislikes_delete.php` 是 `08_image_likes_manager.php` 升级版本，

1. 新增功能4：统计 dislikes 在 [a, b] 范围内的图片数量，并将云服务器项目文件夹中dislikes 在 [a, b] 范围的这些对应的图片都删除掉，删除前打印出这些文件的名称，提醒用户确认，最后打印删除后的项目文件中图片总数量。

2. 新增功能5：创建的数据库连接对象 `$mysqli` 中有一列是 `image_exists`，`image_exists`列表示数据库中每张图片的`存储状态`，0表示不存在，1表示存在。功能5就是：对于所有 `image_exists`为1的图片，分别查找likes和dislikes在 `[a, b]` 区间内的数量并打印出来。除此之外，还打印出数据库中图片总数，`image_exists`为0和为1的数量。

3. 新功能6：将允许用户选择将特定范围内的 `likes-dislikes` 的图片复制到指定的目录。

4. 新增功能8：统计 likes 在 `[a, b]` 区间内的图片文件，列出文件名并在用户确认后删除这些文件，同时显示删除后的剩余图片数量和与数据库记录的差值。与功能4相对应。

- 该脚本中需要初始化的参数如下所示

```php
include '08_db_config.php';                             // 创建数据库连接对象 $mysqli

include '08_db_sync_images.php';                        // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");    // 调用函数并提供图片存储目录

include '08_db_image_status.php';                       // 判断数据库中所有图片的存在状态

# 功能4
$project_folder = '/home/01_html/08_x/image/01_imageHost/';      // 替换为项目文件夹的路径

# 功能6
$destination_folder = '/home/01_html/08_x/image/06_picVideo/';
$source_file = "/home/01_html/08_x/image/01_imageHost/" . $row['image_name'];

# 功能8
$project_folder = '/home/01_html/08_x/image/01_imageHost/'; // 替换为项目文件夹的路径
```


### 3. `08_image_rclone_replace.php`

💎 **代码功能：**

1. 首先获取 图片数据库中 `likes-dislikes` 大于等于0 的图片名，存到数组A中，从中随机抽取5000张图片名存到数组B中
2. 获取 `/home/01_html/08_x/image/01_imageHost` 目录下的所有png图片名，存到数组C中
3. 数组B和数组C的交集称为数组D
4. 删除掉 `/home/01_html/08_x/image/01_imageHost` 目录下存在于 `C-D` 数组的图片，即删除 D 在 C 中的补集
5. 若数组 D 的长度等于5000，则退出脚本；若数组D的长度小于5000，则利用 `rclone copy` 命令下载 `B-D` 中的图片到  `/home/01_html/08_x/image/01_imageHost` 目录，即下载 D 在 B 中的补集
相关参考命令如下：

```php
$diffBD = array_diff($arrayB, $arrayD);
foreach ($randomDiffBD as $index) {
    $remote_file_path = $remote_dir . '/' . $diffBD[$index];
    $local_file_path = $local_dir;
    $copy_command = "rclone copy '$remote_file_path' '$local_file_path'";
    exec($copy_command, $copy_output, $copy_return_var);
    if ($copy_return_var != 0) {
        echo "Failed to copy " . $diffBD[$index] . "\n";
    } else {
        echo "Copied " . $diffBD[$index] . " successfully\n";
    }
}
```

💎 **环境变量：**

1. 参数初始化

```php
include '08_db_config.php';
include '08_db_sync_images.php';                           // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");       // 调用 08_db_sync_images.php 模块中的 syncImages 函数，该函数需要传递图片存储路径参数

$directory = '/home/01_html/08_x/image/01_imageHost';      // 指定图片所在路径

$remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 请替换为远程目录路径

exec('php /home/01_html/08_db_image_status.php');          // 更新图片的状态
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');      // 重启 08_pic_url_check.js 应用
```

2. 环境配置

需要提前安装 PM2，实现对于 node.js 脚本的重启管理 

```bash
alias pms='pm2 stop /home/01_html/08_x_nodejs/08_pic_url_check.js'
alias pmr='pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js'
alias pmd='pm2 delete /home/01_html/08_x_nodejs/08_pic_url_check.js'
alias pml='pm2 list'
alias pre='nohup php /home/01_html/08_image_rclone_replace.php &'
```


### 4. `08_server_manage_categories.php` 图片分类管理

💡 **1. 初始编程思路**

现在需要编写一个php脚本，在终端运行该脚本时，通过调用 `08_db_config.php` 模块连接到数据库，然后显示如下三个选项，用户通过输入序号进行选择：
1. 在 Categories 表中创建一个新的图片分类，创建时需要检查表中是否已经存在同名的图片分类，如果不存在则创建
2. 修改 Categories 表中的图片分类名，分别提示用户输入待修改的分类名，以及新的分类名，同样需要检测分类名（待修改的分类名，以及新的分类名）是否存在再进行后续操作
3. 删除 Categories 表中的图片分类名，删除前需要确认图片名是否存在
表中上述增删查改最后实施前，还需要提示用户确认，输入y表示确认执行。执行完成后，在页面打印出  Categories 表的内容。



💡 **2. 新增编程思路**

现在我有新的需求，如下：
- 在 Categories 表中新添加一列 kindID 列，即每一个分类名称都有一个 kindID，是一个独特的字符串，默认值可以设置为空，给出相应在Categories表中创建列的mysql语句。

上述创建好 kindID 列后，现在需要帮我修改 08_server_manage_categories.php 脚本。新增如下功能：
1. 新增功能4：给指定已有分类名添加或修改 kindID。提示用户输入分类名（核查该分类名是否存在，如果不存在则给出提示并结束程序），然后再提示用户输入对应 kindID（注意核查输入的 kindID 与其他分类名对应的 kindID 是否重复，如果重复则提示并结束程序）。修改前提示用户输入y进行确认。然后打印 Categories 表中所有的分类名以及对应的 kindID。
2. 新增功能5：添加新的分类名和对应kindID。提示用户输入新分类名（核查该分类名是否存在，如果已存在则给出提示并结束程序），然后再提示用户输入对应 kindID（注意核查输入的 kindID 与其他分类名对应的 kindID 是否重复，如果重复则提示并结束程序）。修改前提示用户输入y进行确认。然后打印 Categories 表中所有的分类名以及对应的 kindID。
3. 新增功能6：打印 Categories 表中所有的分类名以及对应的 kindID。

针对上述需求进行修改，08_server_manage_categories.php 脚本中其余代码部分不要变，输出修改后的完整代码。



💎 **3. 环境变量：**

```php
// 引入数据库配置文件（确保 08_db_config.php 与本脚本在同一目录下）
require_once '08_db_config.php';
```


- 注意：功能 4、5和6 需要提前在 Categories 表中新增 kindID 列，mysql语句如下所示：

```sql
-- 在 Categories 表中新增 kindID 列
ALTER TABLE Categories
ADD COLUMN kindID VARCHAR(255) DEFAULT NULL AFTER category_name,
ADD UNIQUE (kindID);
```




### 5. `08_server_update_unknowImage_picCategories.php` 

功能：更新 `"0.0 未知"` 分类下的图片id

💡 **1. 初始编程思路**

基于上述信息，现在我需要编写一个php脚本，
1. 查询"0.0 未知"分类在Categories表中是否提前创建，没有创建则提示并结束脚本运行
2. 筛选 images 表中所有 image_exists=1并且star=1 的图片id，后续操作对象是基于这一部分筛选出来的图片id
3. 判断上述每一个图片id在 PicCategories 表中是否有关联的图片分类，如果没有关联分类，则在 PicCategories 表中将其关联到分类名称"0.0 未知"下。
4. 如果上述图片id在 PicCategories 表中有且仅有一个关联图片分类，则跳过该图片id操作。
5. 如果上述图片id在 PicCategories 表中有大于等于2个的关联图片分类，并且其中一个图片分类是 "0.0 未知"，则需要删掉PicCategories表中该图片id关联的 "0.0 未知" 图片分类（因为该图片的分类并不是未知），保留其他关联图片分类；如果关联的图片分类均不是"0.0 未知"，则跳过该图片id操作。
6. 打印出PicCategories表中 "0.0 未知" 分类下的图片数量。

请编写脚本实现上述需求（需要调用08_db_config.php创建数据库连接），注意该脚本运行在ubuntu终端


💎 **2. 环境变量：**

```php
require_once '08_db_config.php';  // 引用数据库连接配置

// 1. 查询 "0.0 未知" 分类是否已经存在
$unknownCategoryName = "0.0 未知";
```



### 6. `08_server_image_rclone_likesRange.php` 图片下载

功能：后台下载指定likes值或范围内的图片（根据 image_exists=0来筛选）

💡 **1. 初始编程思路**

请编写一个php脚本实现以下图片下载需求，根据用户输入 likes 数范围筛选并下载相应的图片到指定目录：
1. 如上所示，image_db图片数据库中有一个images表，里面存储了多张图片的元数据，包括每一张图片的id， 图片名，点赞数，点踩数，状态，受否被收藏等信息。每条数据在mysql数据库中占据一行，大概有几万条数据。
2. 首先调用以下模块和函数，同步更新图片数据库中的数据，确保数据库中的数据是最新的。

```php
include '08_db_sync_images.php';                     // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");    // 调用函数并提供图片存储目录
```

3. 提示用户输入 likes 数范围或者具体值。例如：3-5（数字之间使用连字符，核查确保第一个数字小于第二个数字，均为整数），代表likes数从3到5，包含3，4和5；如果输入的是 3 或者 3,5 （只有一个数字代表一个确定的likes；若输入多个数字使用英文逗号分隔，代表多个 likes 值，需确保多个值不同，均为整型）。
4. 初步筛选数据库中满足上述 likes 值的图片 id，再从其中筛选出 `image_exists = 0` 的图片id，确定这些图片id对应的 image_name。打印出符合要求的图片数量。提示用户核查要下载的 likes 值范围以及图片数量，输入 y 确认。
5. 用户确认后，使用rclone从远程路径 `$remote_dir` 下载上述筛选出来的图片到 `$local_dir` 目录下，相关具体路径和下载实现请参考如下代码块：

```php
$remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 请替换为远程目录路径
$local_dir = '/home/01_html/08_x/image/01_imageHost';
foreach ($diffBD as $filename) {
    $remote_file_path = $remote_dir . '/' . $filename;
    $local_file_path = $local_dir;
    $copy_command = "rclone copy '$remote_file_path' '$local_file_path' --transfers=16";
    exec($copy_command, $copy_output, $copy_return_var);
    if ($copy_return_var != 0) {
        echo "Failed to copy " . $filename . "\n";
    } else {
        echo "Copied " . $filename . " successfully\n";
    }
}
```

6. 完成上述下载后，给出提示。然后再运行以下代码块。

```php
exec('php /home/01_html/08_db_image_status.php');
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');
echo "Process completed.\n";
```

请针对上述需求，编写php代码实现。


💎 **2. 环境变量：**

```php
// 1. 引入数据库配置和同步模块
include '08_db_config.php';               // 数据库连接
include '08_db_sync_images.php';          // 用于将新下载的图片名写入数据库

// 2. 同步更新数据库(确保数据库是最新的)
syncImages("/home/01_html/08_x/image/01_imageHost");

// 注意：使用 --files-from 时，rclone 从 $remote_dir 下的这些文件名一并下载到 $local_dir
$remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 根据实际情况修改
$local_dir  = '/home/01_html/08_x/image/01_imageHost';
$copy_command = "rclone copy '$remote_dir' '$local_dir' --files-from '$tmpFile' --transfers=16";

// 6. 完成后执行后续脚本，更新数据库图片状态，重启 Node 服务等
exec('php /home/01_html/08_db_image_status.php');
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');
```


**3. rclone并行批量下载**

```php
$remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 请根据实际情况修改
$local_dir  = '/home/01_html/08_x/image/01_imageHost';

foreach ($diffBD as $filename) {
    // 构造源与目标路径
    $remote_file_path = $remote_dir . '/' . $filename;
    $local_file_path  = $local_dir;

    // 运行 rclone copy 命令
    $copy_command = "rclone copy '$remote_file_path' '$local_file_path' --transfers=16";

    exec($copy_command, $copy_output, $copy_return_var);

    if ($copy_return_var !== 0) {
        echo "Failed to copy {$filename}\n";
    } else {
        echo "Copied {$filename} successfully\n";
    }
}

```

注意：上述代码中，每次只调用一次 rclone 命令，仅针对单个文件，所以并没有达到并行下载多张图片的效果（`--transfers=16`未有效利用）。

```php
// 将需要下载的文件名提取成一个数组(去掉 id，只保留文件名)
$fileList = array_values($diffBD);

// 生成一个临时文件，列出所有要下载的文件名（每行一个）
$tmpFile = '/tmp/files_to_download.txt';
file_put_contents($tmpFile, implode("\n", $fileList));

// 准备 rclone 命令
// 注意：使用 --files-from 时，rclone 从 $remote_dir 下的这些文件名一并下载到 $local_dir
$remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 根据实际情况修改
$local_dir  = '/home/01_html/08_x/image/01_imageHost';

$copy_command = "rclone copy '$remote_dir' '$local_dir' --files-from '$tmpFile' --transfers=16";

// 执行批量下载
exec($copy_command, $copy_output, $copy_return_var);

if ($copy_return_var !== 0) {
    echo "Failed to copy files.\n";
} else {
    echo "Copied all files successfully.\n";
}

// 如果临时文件无需保留，可以在这里删除
unlink($tmpFile);
```

1. 构造文件列表 fileList
   - 由于 $diffBD 里存储了需要下载的所有文件名（以 id 为键），我们使用 array_values($diffBD) 获取到一个纯文件名的索引数组。

2. 写入文件
   - 将所有文件名写入一个临时文件（如 /tmp/files_to_download.txt），每行一个文件名。

3. rclone 命令
   - 使用 `rclone copy <remote> <local> --files-from <file>` 即可让 rclone 根据文件列表一次性下载所有文件。
   - `--transfers=16` 告知 rclone 可以并行下载最多 16 个文件。
   - 这种方式下，rclone 自身会并行处理所有文件（而不是在 PHP 中一个个循环下载），能够更有效地利用带宽和系统资源。

4. 执行并检查结果
   - 用 `exec($copy_command, $copy_output, $copy_return_var);` 执行命令后，通过 `$copy_return_var` 判断成功(0)或失败(非 0)。

5. 清理临时文件
   - 如果无需保留文件列表，可用 `unlink($tmpFile)` 删除临时文件。




### 7. `08_server_filter_delete_images.php` 图片删除

功能：该脚本实现了一个交互式的图片筛选和删除工具，允许用户根据图片的多种条件（如 star、ID 范围、分类、likes、dislikes 等）从数据库中筛选图片，并选择性地删除指定目录下的对应图片文件，同时更新数据库状态。

💡 **1. 初始编程思路**

基于上述数据库信息，现在需要编写一个php脚本，基于数据库多个筛选条件实现对指定目录下细粒度的图片删除管理，具体思路如下：

1. 首先调用以下模块和函数，同步更新图片数据库中的数据，确保数据库中的数据是最新的。

```php
include '08_db_sync_images.php';                     // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");    // 调用函数并提供图片存储目录
```

2. 查询 image_db 图片数据库中的 images 表，筛选出 `image_exists = 1` 的图片id，后续筛选和操作是基于这部分筛选的结果。

3. 询问用户是否需要进一步基于 images 表中的 star 值进行筛选，如果不基于，则输入 n。如果需要考虑，则输入 y，然后进一步提示用户输入 star 的值（注意 star 值只能为0或者1，其他数值是非法值）。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。如果用户输入了y，然后输入了合法的的star值，例如0，则需要基于 star = 0 进一步筛选图片id。

4. 询问用户是否需要进一步选取 images 表中的 id 范围，如果不需要则输入n（即 id 项不作为筛选依据）。如果需要，则输入y，然后提示用户输入 id 范围，支持的输入格式如：31-101，使用连字符代表范围，包含范围边界；如果需要输入多个范围或者确定的 id 值，则使用英文逗号分隔，例如：1-10,12-15,18,20 （注意检查多个范围或者确定值是否有重叠，输入值是否为整数，不支持负数）。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

5. 询问用户是否需要基于 PicCategories 表中图片的分类进行筛选，如果不需要，则输入n。如果需要则输入y，然后提示用户输入 Categories 中的类别，由于类别中可能出现空格，因此用户输入类别时需使用引号""，例如："1.1 林希威"，如果输入多个类别，则需要用英文逗号分隔，如："1.1 林希威","1.1 IES"。注意核查这些类别在 Categories 中是否存在或者输入有重复，如果不存在则给出提示，并提示重新输入。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

6. 询问用户是否需要基于images 表中的 likes 进行筛选，如果不需要，则输入n。如果需要则输入y，然后提示用户输入 likes 的具体值或者范围，例如：1-5,10,20-50,51 （注意检查多个范围或者确定值是否有重叠），多个值和范围之间使用英文逗号分隔。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

7. 询问用户是否需要基于images 表中的 dislikes 进行筛选，如果不需要，则输入n。如果需要则输入y，然后提示用户输入 dislikes 的具体值或者范围，例如：1-5,10,20-50,51 （注意检查多个范围或者确定值是否有重叠），多个值和范围之间使用英文逗号分隔。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

8. 询问用户是否需要基于images 表中的 (likes-dislikes) 的差值进行筛选，如果不需要，则输入n。如果需要则输入y，然后提示用户输入 (likes-dislikes) 的具体值或者范围，例如：1-5,10,20-50,51 （注意检查多个范围或者确定值是否有重叠），多个值和范围之间使用英文逗号分隔。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

9. 询问用户是否需要基于 images 表中的 image_name 进行筛选，如果不需要，则输入n。如果需要则输入y，然后提示用户输入字符串，例如： "vegoro1"，注意用户不需要输入引号，因为字符串不含空格；如果输入多个字符串，则需要用英文逗号分隔，例如："vegoro1,g2w2w4"。进一步筛选 image_name 中包含所有输入字符串的图片id，例如 "20250301-174729-vegoro1-g2w2w4" 这个图片名同时包含上述两个字符串。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

10. 总结并打印出上述所有筛选项用户的选择以及输入的具体值，供用户进行核对。基于上述所有筛选条件，筛选出数据库中符合要求的图片id，并打印出数量。

11. 如果筛选出来的图片数量不为0，则询问用户是否需要删除如下目录中对应上述筛选出来id的图片，如果需要输入y，不需要输入n。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。

```php
$local_dir = '/home/01_html/08_x/image/01_imageHost';
```

12. 核对所选id的图片在上述目录下是否都存在，如果不存在则给出缺少相关图片的提示，并结束程序运行。如果选择了y进行删除，则进行删除操作，并打印删除的图片数量，以及 $local_dir 目录下剩余的文件数量。

13. 完成上述筛选和删除后，给出提示。然后再运行以下代码块。

```php
exec('php /home/01_html/08_db_image_status.php');
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');
echo "Process completed.\n";
```

请编写脚本实现上述需求（需要调用08_db_config.php创建数据库连接）。


💎 **2. 环境变量：**

```php
// 1. 引入数据库配置与同步脚本
include '08_db_config.php';        // 连接数据库
include '08_db_sync_images.php';   // 同步数据库函数

// 同步数据库
syncImages("/home/01_html/08_x/image/01_imageHost");

// 12. 核对对应ID的图片是否都存在
$local_dir = '/home/01_html/08_x/image/01_imageHost';

// 13. 删除操作完成后，执行下面两个命令
exec('php /home/01_html/08_db_image_status.php');
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');
```



### 8. `08_server_batch_categorize_images.php` 图片分类

功能：该脚本根据图片名是否包含分类表中的 kindID，为存在的图片自动建立对应的分类关系并插入数据库。

💡 **1. 初始编程思路**

现在我还需要再编写一个新的php脚本，完成以下需求：
1. 查询 image_db 图片数据库中的 images 表，筛选出 `image_exists = 1` 的图片，后续筛选和操作是基于这部分筛选的结果。
2. 询问用户是否需要进一步基于 images 表中的 star 值进行筛选，如果不基于，则输入 n。如果需要考虑，则输入 y，然后进一步提示用户输入 star 的值（注意 star 值只能为0或者1，其他数值是非法值）。输入q代表结束程序运行，除了n、y和q之外的其他值均为非法值，提示重新输入。如果用户输入了y，然后输入了合法的的star值，例如0，则需要基于 star = 0 进一步筛选图片id。
3. 针对上述筛选后的每一张图片（如果图片数量不为0），查询其 image_name 字符串中是否包含 Categories 表中某一个或者某几个分类名的 kindID 字符串（对比的kindID字符串不能为空），如果包含（即kindID字符串是 image_name 字符串的一部分），则在 PicCategories 表中添加该图片和相应分类之间的对应关系（如果该分类对应关系在PicCategories中已存在，则忽略，以避免覆盖或者重复写入；写入的必须是新的对应关系）。例如：假设分类名"1.0 vegoro"的 kindID 是"vegoro1"，然后 "20250301-174819-vegoro1-ap5lc4.png" 图片名中有该kindID字符串"vegoro1"，则需要将该图片的 image_id 和分类名"1.0 vegoro"对应的 category_id 写入到 PicCategories 表中。
4. 打印出上述符合要求的图片image_name。打印出筛选出来的具有对应分类关系图片总数量（不考虑PicCategories表中是否已经存在），打印出其中对应关系还未写入到PicCategories表中图片数量，打印出符合要求但是PicCategories表中已经存在相应对应关系的图片数量。提示用户输入y确认将新的对应关系写入 PicCategories 表中。

针对上述需求，输出新的php脚本。


💎 **2. 环境变量：**

```php
require_once '08_db_config.php';
```





# 5. web交互脚本

### 1. `08_picDisplay_mysql.php` 随机显示数据库中 n 张图片

1. 用户认证：检查用户是否已经登录，如果未登录则重定向到登录页面。
2. 图片管理：从特定目录获取所有PNG格式的图片，检查这些图片是否已经存入数据库中。如果没有，则将其添加到数据库。
3. 图片展示：从数据库中随机选取指定数量的图片（在此脚本中设置为3张），然后在网页上显示。
4. 互动功能：用户可以点击喜欢或不喜欢的按钮来更新图片的喜欢和不喜欢的数量。
5. 终端识别：能够根据客户端类型（手机/电脑）自适应图片宽度，相关实现可以参考[链接](https://github.com/Yiwei666/03_Python-PHP/blob/main/08_pictureEdit/06_imageHost/README.md#6-08_picdisplayphp-%E9%9A%8F%E6%9C%BA%E6%98%BE%E7%A4%BA%E6%8C%87%E5%AE%9A%E7%9B%AE%E5%BD%95%E4%B8%8B-n-%E5%BC%A0%E5%9B%BE%E7%89%87)

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

注意：从数据库中随机选取图片名称，然后通过构造链接进行访问，~~但是并未考虑到项目文件夹中图片已经删除，但是数据库中仍保留其信息。因此，对于部分已删除图片显示的是空白~~。新代码中通过如下命令，即新增`WHERE image_exists = 1`查询，确保已删除的图片在页面中不会显示空白。

```php
// $result = $mysqli->query("SELECT id, image_name, likes, dislikes FROM images");
$result = $mysqli->query("SELECT id, image_name, likes, dislikes FROM images WHERE image_exists = 1");
```


🟢 note: 下面3个脚本的环境配置都是一样的，参考上述 `08_picDisplay_mysql.php`，区别在于 点赞/踩 图标的样式有一些区别

```
08_picDisplay_mysql.php                    # 点赞图标位于图片外右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRight.php            # 点赞图标位于图片内右侧居中，能够写入图片名到数据库
08_picDisplay_mysql_inRigTra.php           # 点赞图标位于图片内右侧居中，点赞图标所在方框设置为透明，能够写入图片名到数据库
```


### 2. `08_picDisplay_order.php` 按总点赞数递减显示数据库中 n 张图片

1. 用户验证：检查用户是否登录，若未登录，则重定向到登录页面。
2. 登出操作：若用户点击了登出链接，注销用户会话并重定向到登录页面。
3. 数据库连接：通过包含的数据库配置文件建立与数据库的连接。
4. 图片读取：从数据库中读取图片名称，**按照点赞数减去踩数的差值降序排序，并限制显示的图片数量**。页面将只显示数据库中`image_exists为1`且存在于服务器上的图片（可以根据如下SQL命令个性化设置筛选需求）。
5. 图片展示：在网页上展示选定数量的图片，并通过设备类型自动调整图片宽度。
6. 刷新按钮：提供一个按钮，用户点击后刷新页面，以重新显示图片。


- 图片读取SQL命令：根据不同的图片筛选需求，可以使用如下不同的SQL命令，只需要替换掉php脚本中的对应行即可。

```php
//从名为images的表中按(likes - dislikes)的顺序获取图片信息，最多显示 picnumber 张图片，未考虑图片存在状态，可能显示空白
$stmt = $pdo->prepare("SELECT image_name FROM images ORDER BY (likes - dislikes) DESC LIMIT :picnumber");

// 这条SQL命令的作用是从数据库中选择符合条件的图片，并按照一定的排序规则进行排序，最终限制返回的记录数量，考虑图片存在状态
$stmt = $pdo->prepare("SELECT image_name FROM images WHERE image_exists = 1 ORDER BY (likes - dislikes) DESC LIMIT :picnumber");

// 从 (likes - dislikes) 大于 5 的图片中随机选择 picnumber 条记录，考虑图片存在状态
$stmt = $pdo->prepare("SELECT image_name FROM images WHERE image_exists = 1 AND (likes - dislikes) > 5 ORDER BY RAND() LIMIT :picnumber");

```



- 环境变量

```php
include '08_db_config.php';  // 包含数据库连接信息

$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4); // 去除目录前缀
$domain = "https://19640810.xyz"; // 域名网址
$picnumber = 50; // 设置需要显示的图片数量
```




### 3. `08_picDisplay_mysql_orderExist.php`

1. 环境变量

```php
include '08_db_config.php';

// 设置图片所在的文件夹
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

// 设置每页显示的图片数量
$imagesPerPage = 20;

fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})
```

2. 核心特性
   - 按照总点赞数由大到小降序排序
   - 只显示图片目录中实际存在的图片，页面中没有图片空白缺失



## 4. `08_picDisplay_mysql_orderExistTab.php`

### 1. 功能

-  在`08_picDisplay_mysql_orderExist.php`基础上进行改进，保留了原有功能，新增在新标签页打开图片的按钮。

1. 新增特性如下：
   - 同时使用Session和Cookie来验证用户的登录信息
   - 新增图标，点击后在新的标签页打开相应图片
   - 使用数据库中的`image_exists`列来直接过滤和处理存在的图片，而不是在文件系统上检查每张图片的存在性。这将提高性能，特别是当图片数量较多时。
   - 新增图标，点击后在新的标签页打开相应图片，并且显示图片左右切换的箭头，根据sort参数实现不同排序的图片切换，调用 `08_image_leftRight_navigation.php` 脚本
   - 新增收藏或取消图标，调用 `08_db_toggle_star.php` 模块


### 2. 环境变量

```php
$key = 'singin-key-1'; // 应与加密时使用的密钥相同

include '08_db_config.php';
include '08_db_sync_images.php';
syncImages("/home/01_html/08_x/image/01_imageHost"); // 调用函数并提供图片存储目录

// 设置图片所在的文件夹
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

// 设置每页显示的图片数量
$imagesPerPage = 20;

// 调用点赞模块 08_image_management.php
fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})

// 调用收藏模块 08_db_toggle_star.php
fetch('08_db_toggle_star.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}`
})

// 指定跳转脚本08_image_leftRight_navigation.php和排序算法sort=1：动态排序和左右切换功能的图片浏览页面
<button onclick="window.open('08_image_leftRight_navigation.php?id=<?php echo $image['id']; ?>&sort=1', '_blank')">🔁</button>
```

注意：

- 虽然本脚本中调用了`08_db_sync_images.php`模块将新图片信息插入到数据库中，但没有调用`08_db_image_status.php`模块，因此新插入图片的`image_exists`默认值仍然为0，页面上不会显示新插入的图片。
- 需要在后台手动运行 `08_image_dislikes_delete.php` 脚本完成新图片状态写入，该脚本调用了`08_db_image_status.php`模块。
- 没有在web脚本调用`08_db_image_status.php`模块，主要是考虑到尽量减少页面加载时间。理论上来说，`08_db_image_status.php`模块应当仅在后台手动运行的脚本中调用，避免错误上传的图片污染mysql数据库。


### 3. 模块调用方法

1. 新增在新标签页打开图片的代码仅一行

```js
<button onclick="window.open('<?php echo $domain . $dir5 . '/' . htmlspecialchars($image['image_name']); ?>', '_blank')">🔗</button>
```

2. `08_db_toggle_star.php` 模块调用较复杂，参考上面相应小节。

3. `08_image_leftRight_navigation.php` 模块调用：点击🔁按钮，在新标签页打开图片，并实现图片顺序切换。该功能在本脚本的相关代码仅一行。
    - 点击🔁按钮，传递`id和sort`参数给本脚本。调用示例如下所示，注意`sort`为1或者2，代表不同的排序算法。`08_image_leftRight_navigation.php`模块名需要根据实际情况调整。

```html
<button onclick="window.open('08_image_leftRight_navigation.php?id=<?php echo $image['id']; ?>&sort=1', '_blank')">🔁</button>
```





## 5. `08_picDisplay_mysql_galleryExistTab.php`

### 1. 功能特性

-  在`08_picDisplay_mysql_galleryExist.php`基础上进行改进，保留了原有功能，新增在新标签页打开图片的按钮。

1. 新增特性如下：
   - 新增图标，点击后在新的标签页打开相应图片
   - 使用数据库中的`image_exists`列来直接过滤和处理存在的图片，而不是在文件系统上检查每张图片的存在性。这将提高性能，特别是当图片数量较多时。
   - 新增图标，点击后在新的标签页打开相应图片，并且显示图片左右切换的箭头，根据sort参数实现不同排序的图片切换，调用 `08_image_leftRight_navigation.php` 脚本
   - 新增收藏或取消图标，调用 `08_db_toggle_star.php` 模块


### 2. 环境变量

```php
$key = 'signin-key-1'; // 应与加密时使用的密钥相同

include '08_db_config.php';

// 设置图片所在的文件夹
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

// 设置每页显示的图片数量
$imagesPerPage = 20;

// 调用点赞模块 08_image_management.php
fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})

// 调用收藏模块 08_db_toggle_star.php
fetch('08_db_toggle_star.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}`
})

// 指定跳转脚本08_image_leftRight_navigation.php和排序算法sort=2：动态排序和左右切换功能的图片浏览页面
<button onclick="window.open('08_image_leftRight_navigation.php?id=<?php echo $image['id']; ?>&sort=2', '_blank')">🔁</button>
```


### 3. 系列脚本主要区别

1. `获取数据库中标记为存在的所有图片的记录`

```php
//  08_picDisplay_mysql_galleryExistTab.php 添加收藏功能前
$query = "SELECT id, image_name, likes, dislikes FROM images WHERE image_exists = 1";

// 08_picDisplay_mysql_galleryExistTab.php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1";

// 08_picDisplay_mysql_galleryExistTab_starF.php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 0";

// 08_picDisplay_mysql_galleryExistTab_starT.php
$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1";
```



## 6. `08_picDisplay_mysql_orderExistTab_starT.php`

### 1. 功能

1. 在 `08_picDisplay_mysql_orderExistTab_starT.php` 中，你可以点击左上角的“分类”按钮，在弹出层中选择某分类，页面即可只显示该分类下的图片，并保留原有分页、点赞、收藏等功能。
2. 点击“🔁”按钮进入 `08_image_leftRight_navigation_starT.php` 时会带上 `cat` 参数，使左右导航只在该分类下循环。
3. 若不传 cat 参数，`08_image_leftRight_navigation_starT.php` 保持原先逻辑显示所有(满足 `star=1, image_exists=1`)的图片。


### 2. 编程思路


上述修改后的 `08_image_leftRight_navigation_starT.php` 脚本 和 `08_image_web_category.php` 模块都是正常工作的。

`08_picDisplay_mysql_orderExistTab_starT.php` 脚本如下所示，其调用了 `08_image_leftRight_navigation_starT.php` 脚本（调用时将图片id和排序类型sort参数传给 `08_image_leftRight_navigation_starT.php` 脚本），也是正常工作的。

下面的 `08_picDisplay_mysql_orderExistTab_starT.php` 脚本 显示的是满足 `$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1";` 条件的图片，即 `image_exists = 1 AND star = 1` 的图片。被调用的`08_image_leftRight_navigation_starT.php`脚本为了与`08_picDisplay_mysql_orderExistTab_starT.php`保持一致，也是使用了相同的查询条件 `$query = "SELECT id, image_name, likes, dislikes, star FROM images WHERE image_exists = 1 AND star = 1"`。但是我现在有一个新的需求，如下所示:

1. 在 `08_picDisplay_mysql_orderExistTab_starT.php` 页面左上角显示一个小按钮，点击该按钮会显示 Categories 表中的所有分类（可能需要调用 `08_image_web_category.php` 模块中的函数来实现）。

2. 点击其中任意一个分类，页面显示的便是该分类下的所有图片（可能需要在原有的$query查询设置基础上新增对该分类的查询限制）。用户在点击切换不同页码时，对于用户选择的分类筛选需要始终有效。其余功能仍保持不变（点赞、点踩、收藏等）。

3. 与此同时，当用户点击 🔁 按钮时，除了将图片id和排序类型sort参数传给`08_image_leftRight_navigation_starT.php`脚本，也需要将用户选择的分类作为一个参数传递给`08_image_leftRight_navigation_starT.php`脚本，以便用户且换图片时仍是该分类下的图片。因此，`08_image_leftRight_navigation_starT.php`脚本可能需要进行进一步修改，尤其是在$query查询时增加分类限制。其余功能仍保持不变（如点赞、点踩、收藏、左右导航、分类弹窗等）。

请在如下的 `08_picDisplay_mysql_orderExistTab_starT.php`，`08_image_leftRight_navigation_starT.php`代码基础上进行修改，除非必要，`08_image_web_category.php` 代码尽量不要改动。

针对上述需求进行修改，输出修改后的完整代码，原有的代码逻辑、样式、功能不要改变。



### 3. 环境变量

```php
$key = 'signin-key-1'; // 应与加密时使用的密钥相同

include '08_db_config.php';
include '08_db_sync_images.php';
syncImages("/home/01_html/08_x/image/01_imageHost"); // 调用函数并提供图片存储目录

// ★ 新增：引入分类操作文件，以便使用 getAllCategories() / getImagesOfCategory()
include '08_image_web_category.php';

// 设置图片所在的文件夹
$dir4 = "/home/01_html/08_x/image/01_imageHost";
$dir5 = str_replace("/home/01_html", "", $dir4);
$domain = "https://19640810.xyz";

// 设置每页显示的图片数量
$imagesPerPage = 20;


fetch('08_image_management.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}&action=${action}`
})


fetch('08_db_toggle_star.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `imageId=${imageId}`
})


<button onclick="window.open('08_image_leftRight_navigation_starT.php?id=<?php echo $image['id']; ?>&sort=1&cat=<?php echo $selectedCategory; ?>', '_blank')">
    🔁
</button>
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






