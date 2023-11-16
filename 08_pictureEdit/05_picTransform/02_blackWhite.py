from PIL import Image
import os

def convert_to_black_and_white():
    """
    将彩色图片转换为黑白图像并保存。

    Parameters:
    - input_path (str): 输入图片路径。
    - output_path (str): 保存黑白图像的路径。
    """
    input_image_path = input("Enter the input image filename (including extension): ")

    # Extracting the filename and extension
    file_name, file_extension = os.path.splitext(input_image_path)

    # Constructing the output filename with "_black_and_white" added
    output_image_path = f"{file_name}_black_and_white{file_extension}"

    with Image.open(input_image_path) as img:
        black_and_white_img = img.convert("L")  # Convert to grayscale
        black_and_white_img.save(output_image_path, quality=95)

# 示例用法:
convert_to_black_and_white()
