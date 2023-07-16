from PIL import Image
import os

def convert_png_to_jpg(png_file):
    try:
        image = Image.open(png_file)
        jpg_file = os.path.splitext(png_file)[0] + ".jpg"
        image.convert("RGB").save(jpg_file, "JPEG")
        print(f"转换成功：{png_file} -> {jpg_file}")
    except Exception as e:
        print(f"转换失败：{e}")

# 获取当前目录
current_dir = os.getcwd()

# 获取当前目录下所有的 PNG 文件
png_files = [file for file in os.listdir(current_dir) if file.endswith(".png")]

if len(png_files) == 0:
    print("当前目录下没有 PNG 文件。")
else:
    print("当前目录下的 PNG 文件列表:")
    for png_file in png_files:
        print(png_file)

    # 提示用户输入 PNG 文件名
    png_filename = input("请输入要转换的 PNG 文件名（包括文件扩展名）：")

    # 检查用户输入的文件名是否存在
    if png_filename in png_files:
        png_path = os.path.join(current_dir, png_filename)
        convert_png_to_jpg(png_path)
    else:
        print("输入的文件名无效或文件不存在。")
