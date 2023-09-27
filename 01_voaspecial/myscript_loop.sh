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

