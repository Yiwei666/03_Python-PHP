# 项目结构

```

├── latest.html            # 临时文件
├── 01keke.py              # 解析 mainpage 的python脚本，链接写入到homePageUrl.txt文件中
├── homePageUrl.txt        # 储存mainpage中的所有url
├── musicdown.py           # 提取music.html中的url，储存到audioUrl.txt文件中
├── music.html             # 临时文件，含有音频链接
├── audioUrl.txt           # 储存音频链接的文件
├── insert_unique_urls.sh  # 将audioUrl.txt中的链接写入到mysql数据库中的脚本  
├── myscript.sh
└── myscript_loop.sh

voaspecialSQL.php          # 基于mysql音频链接播放随机播放15个音频的php脚本

```


# 文件说明

- **insert_unique_urls.sh**

将audioUrl.txt中的音频链接写入到mysql数据库中，写入前检查txt中的链接与数据库中的链接是否有重复，重复则跳过。注意`done < /home/01_html/30_VOAspecial/audioUrl.txt`路径的修改。

```bash
#!/bin/bash

# Database connection details
DB_HOST="localhost"
DB_NAME="kkmusicdb"
DB_USER="root"
DB_PASS="password123"             # 注意修改密码
TABLE_NAME="voaspecialTABLE"

# Read and process the text file
while IFS=',' read -r datetime url; do
  # Check if the URL already exists in the table
  existing_url=$(mysql -N -s -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT url FROM $TABLE_NAME WHERE url='$url'")

  # If the URL doesn't exist, insert the data into the table
  if [ -z "$existing_url" ]; then
    query="INSERT INTO $TABLE_NAME (datetime, url) VALUES ('$datetime', '$url');"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$query"
    echo "Inserted: $datetime, $url"
  else
    echo "Skipped (URL already exists): $datetime, $url"
  fi
done < /home/01_html/30_VOAspecial/audioUrl.txt
```

- **myscript.sh**

爬取指定页码的音频链接audioUrl.txt，并写入到mysql数据库中，

```bash
#!/bin/bash

# Remove the specified files
rm /home/01_html/30_VOAspecial/homePageUrl.txt
rm /home/01_html/30_VOAspecial/audioUrl.txt

# Set the URL
total_url="http://www.kekenet.com/broadcast/voaspecial/List_307.shtml"

# Download the webpage
curl -o /home/01_html/30_VOAspecial/latest.html $total_url

# Run the first Python script
/home/anaconda/anaconda3_installation/bin/python /home/01_html/30_VOAspecial/01keke.py

# Run the second Python script
/home/anaconda/anaconda3_installation/bin/python /home/01_html/30_VOAspecial/musicdown.py

# Execute the final command
/usr/bin/bash /home/01_html/30_VOAspecial/insert_unique_urls.sh
```

- **myscript_loop.sh**

将指定页码范围内的音频链接写入到mysql数据库中

```bash
#!/bin/bash

# Loop through the desired page range
# for page_number in {409..400}; do
for page_number in {249..200}; do
    # Remove the specified files at the beginning of each iteration
    rm /home/01_html/30_VOAspecial/homePageUrl.txt
    rm /home/01_html/30_VOAspecial/audioUrl.txt
    # http://www.kekenet.com/broadcast/voaspecial/List_${page_number}.shtml

    total_url="http://www.kekenet.com/broadcast/voaspecial/List_${page_number}.shtml"

    echo "Current total_url: $total_url"  # Print the total_url

    # Download the webpage
    curl -o "/home/01_html/30_VOAspecial/latest.html" "$total_url"

    # Run the first Python script
    /home/anaconda/anaconda3_installation/bin/python /home/01_html/30_VOAspecial/01keke.py

    # Run the second Python script
    /home/anaconda/anaconda3_installation/bin/python /home/01_html/30_VOAspecial/musicdown.py

    # Execute the final command
    /usr/bin/bash insert_unique_urls.sh
done
```



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
