from PIL import Image
import os

def convert_jpg_to_png():
    current_dir = os.getcwd()
    image_files = [file for file in os.listdir(current_dir) if file.endswith('.jpg')]

    for file in image_files:
        # 获取文件名和扩展名
        file_name, ext = os.path.splitext(file)

        # 打开jpg图片
        image = Image.open(file)

        # 将jpg图片转换为png图片
        new_file = file_name + '.png'
        image.save(new_file, 'PNG')

        print(f'转换成功: {new_file}')

    print('所有jpg图片已转换为png图片。')

convert_jpg_to_png()
