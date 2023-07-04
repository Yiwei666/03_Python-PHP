#!/bin/bash

for file in *.flv; do
    if [ -f "$file" ]; then
        filename="${file%.*}"
        output="${filename}.mp4"
        ffmpeg -i "$file" -c:v libx264 -c:a aac "$output"
    fi
done
