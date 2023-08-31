#!/bin/bash

# Loop through the desired page range
# for page_number in {409..400}; do
for page_number in {399..380}; do
    # Remove the specified files at the beginning of each iteration
    rm /home/experiment/01_pastKeke/musicUrl.txt
    rm /home/experiment/01_pastKeke/finalmusic.txt

    total_url="http://www.kekenet.com/song/tingge/List_${page_number}.shtml"

    # Download the webpage
    curl -o "/home/experiment/01_pastKeke/latest.html" "$total_url"

    # Run the first Python script
    /home/anaconda/anaconda3_installation/bin/python /home/experiment/01_pastKeke/01keke.py

    # Run the second Python script
    /home/anaconda/anaconda3_installation/bin/python /home/experiment/01_pastKeke/musicdown.py

    # Execute the final command
    /usr/bin/bash insert_unique_urls.sh
done

