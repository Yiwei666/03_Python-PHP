





# mysql数据库操作

- 创建表格 voaspecialTABLE

```mysql
CREATE TABLE voaspecialTABLE (
    datetime DATETIME,
    url VARCHAR(255)
);
```



- 数据库访问

```mysql
SHOW DATABASES;
USE kkmusicdb;
SHOW TABLES;
DESCRIBE voaspecialTABLE;
SELECT datetime,url FROM voaspecialTABLE;
```
