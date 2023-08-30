#!/bin/bash

# Database connection details
DB_HOST="localhost"
DB_NAME="<database_name>"
DB_USER="<username>"
DB_PASS="<password>"
TABLE_NAME="<table_name>"

# Read and process the text file
while IFS=',' read -r datetime url; do
  query="INSERT INTO $TABLE_NAME (datetime, url) VALUES ('$datetime', '$url');"

  # Execute the SQL query
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$query"
done < input.txt
