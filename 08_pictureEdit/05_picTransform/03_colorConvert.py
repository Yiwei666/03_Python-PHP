from PIL import Image
import os

def invert_colors():
    """
    将图像中的颜色取反并保存。

    Parameters:
    - input_path (str): 输入图片路径。
    - output_path (str): 保存颜色反转后图片的路径。
    """
    input_image_path = input("Enter the input image filename (including extension): ")

    # Extracting the filename and extension
    file_name, file_extension = os.path.splitext(input_image_path)

    # Constructing the output filename with "_inverted" added
    output_image_path = f"{file_name}_inverted{file_extension}"

    with Image.open(input_image_path) as img:
        inverted_img = Image.eval(img, lambda x: 255 - x)  # Invert colors
        inverted_img.save(output_image_path, quality=95)

# 示例用法:
invert_colors()
