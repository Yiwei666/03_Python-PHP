from PIL import Image
import os

def mirror_image():
    """
    对图片进行左右对称变换并保存。

    Parameters:
    - input_path (str): 输入图片路径。
    - output_path (str): 保存左右对称变换后图片的路径。
    """
    input_image_path = input("Enter the input image filename (including extension): ")

    # Extracting the filename and extension
    file_name, file_extension = os.path.splitext(input_image_path)

    # Constructing the output filename with "mirrored" added
    output_image_path = f"{file_name}-mirrored{file_extension}"

    with Image.open(input_image_path) as img:
        mirrored_img = img.transpose(Image.FLIP_LEFT_RIGHT)
        mirrored_img.save(output_image_path, quality=95)

# 示例用法:
mirror_image()
