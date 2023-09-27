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
