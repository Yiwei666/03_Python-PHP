import re
from datetime import datetime

# Path of the HTML file to be parsed
html_file_path = '/home/01_html/08_x/image/05_webPicDownload/08_web_url.html'

# Paths of the output files
total_url_path = '01_total_url.txt'
temp_url_path = '02_temp_url.txt'

# Function to write image filenames to files with specified requirements
def write_image_names(html_content, total_file, temp_file):
    # Extract image filenames using regex
    image_names = re.findall(r'/file/([a-zA-Z0-9]+\.jpg)', html_content)

    if not image_names:
        print("No image names found.")
        return

    # Prefix for each image URL
    prefix = 'https://telegra.ph/file/'

    # Write the current date and time to the total file, then append image names with the prefix
    with open(total_file, 'a') as total_file:
        current_time = datetime.now().strftime('%Y-%m-%d-%H%M%S')
        total_file.write(f'=== {current_time} ===\n')
        for name in image_names:
            total_file.write(f'{prefix}{name}\n')

    # Overwrite the temp file with the new image names and prefix
    with open(temp_file, 'w') as temp_file:
        for name in image_names:
            temp_file.write(f'{prefix}{name}\n')

    # Print the total number of extracted image names
    print(f'Total number of image names extracted: {len(image_names)}')

# Read the content of the HTML file
try:
    with open(html_file_path, 'r', encoding='utf-8') as html_file:
        html_content = html_file.read()
except FileNotFoundError:
    print(f'Error: HTML file not found at path: {html_file_path}')
    html_content = ''

# If we read the HTML content successfully, proceed with writing the image names
if html_content:
    write_image_names(html_content, total_url_path, temp_url_path)
