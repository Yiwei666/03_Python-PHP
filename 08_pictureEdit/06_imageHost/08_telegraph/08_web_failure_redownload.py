import os
import time
import requests
from datetime import datetime

# Paths to the files and directories involved
temp_url_path = '/home/01_html/08_x/image/05_webPicDownload/03_failure_downURL.txt'
failure_url_path = '/home/01_html/08_x/image/05_webPicDownload/04_reDownload_failURL.txt'
pic_temp_dir = '/home/01_html/08_x/image/03_picTemp/海外风景'

# Function to ensure the directory exists
def ensure_directory_exists(directory):
    if not os.path.exists(directory):
        os.makedirs(directory)

# Function to download images from the list in temp_url_path and save them in PNG format
def download_images():
    ensure_directory_exists(pic_temp_dir)

    # Initialize counters and a set to track processed URLs
    success_count = 0
    failure_count = 0
    processed_urls = set()

    # Read all URLs from the temporary URL file
    try:
        with open(temp_url_path, 'r') as temp_url_file:
            image_urls = temp_url_file.read().splitlines()
    except FileNotFoundError:
        print(f'Error: Temporary URL file not found at path: {temp_url_path}')
        return

    total_count = len(image_urls)

    # Create or append to the failure log file
    with open(failure_url_path, 'a') as failure_url_file:
        for url in image_urls:
            # Skip URLs that have already been processed
            if url in processed_urls:
                continue

            processed_urls.add(url)

            # Generate a timestamp-based filename
            timestamp = datetime.now().strftime('%Y%m%d-%H%M%S')
            png_path = os.path.join(pic_temp_dir, f'{timestamp}.png')

            try:
                # Download the image data
                response = requests.get(url)
                response.raise_for_status()

                # Write the downloaded data as a PNG file
                with open(png_path, 'wb') as png_file:
                    png_file.write(response.content)

                success_count += 1

            except requests.RequestException:
                # If a download fails, log the URL to the failure file
                failure_url_file.write(f'{url}\n')
                failure_count += 1

            # Wait for 2 seconds before downloading the next image
            time.sleep(2)

    # Print the summary of the download process
    print(f'Total image URLs: {total_count}')
    print(f'Successful downloads: {success_count}')
    print(f'Failed downloads: {failure_count}')

# Run the download_images function
download_images()
