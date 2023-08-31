#!/bin/bash

# Remove the specified files
rm /home/experiment/01_pastKeke/musicUrl.txt
rm /home/experiment/01_pastKeke/finalmusic.txt

# Set the URL
total_url="http://www.kekenet.com/song/tingge/List_326.shtml"

# Download the webpage
curl -o /home/experiment/01_pastKeke/latest.html $total_url

# Run the first Python script
/home/anaconda/anaconda3_installation/bin/python /home/experiment/01_pastKeke/01keke.py

# Run the second Python script
/home/anaconda/anaconda3_installation/bin/python /home/experiment/01_pastKeke/musicdown.py

# Execute the final command
/usr/bin/bash insert_unique_urls.sh
