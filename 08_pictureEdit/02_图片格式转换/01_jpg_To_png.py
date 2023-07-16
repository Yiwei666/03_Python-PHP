from PIL import Image
import os

def convert_jpg_to_png():
    current_dir = os.getcwd()
    image_files = [file for file in os.listdir(current_dir) if file.endswith('.jpg')]

    if not image_files:
        print('没有找到任何jpg文件。')
        return

    print('可用的JPG文件:')
    for i, file in enumerate(image_files, start=1):
        print(f'{i}. {file}')

    file_number = input('请选择要转换的JPG文件的编号: ')

    try:
        file_number = int(file_number)
        if file_number < 1 or file_number > len(image_files):
            raise ValueError
    except ValueError:
        print('选择的文件编号无效。')
        return

    selected_file = image_files[file_number - 1]
    file_name, ext = os.path.splitext(selected_file)
    jpg_file = os.path.join(current_dir, selected_file)
    png_file = os.path.join(current_dir, file_name + '.png')

    image = Image.open(jpg_file)

    jpg_size = os.path.getsize(jpg_file) / 1024  # JPG file size in KB

    image.save(png_file, 'PNG')

    png_size = os.path.getsize(png_file) / 1024  # PNG file size in KB

    print(f'转换成功: {selected_file}')
    print(f'转换前文件大小: {jpg_size:.2f} KB')
    print(f'转换后文件大小: {png_size:.2f} KB')

    print('所有jpg图片已转换为png图片。')

convert_jpg_to_png()
