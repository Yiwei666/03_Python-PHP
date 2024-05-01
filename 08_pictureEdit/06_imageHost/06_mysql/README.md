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
```

# 3. 环境配置






# 4. to do list

1. rclone onedrive 图片备份
2. mysql 数据库备份











