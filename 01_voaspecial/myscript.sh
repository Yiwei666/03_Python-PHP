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
