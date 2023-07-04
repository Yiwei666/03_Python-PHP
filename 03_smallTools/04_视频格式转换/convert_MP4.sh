#!/bin/bash

for file in *.mp4; do
    if [ -f "$file" ]; then
        filename="${file%.*}"
        output="${filename}_standard.mp4"
        ffmpeg -i "$file" -c:v copy -c:a copy "$output"
    fi
done
