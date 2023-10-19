from pdf2image import convert_from_path
import os

# 获取当前目录下的所有PDF文件
pdf_files = [file for file in os.listdir() if file.endswith(".pdf")]

for pdf_file in pdf_files:
    # 获取PDF文件名（去掉扩展名）
    pdf_file_name = os.path.splitext(pdf_file)[0]

    # 将PDF文件转换为PNG格式
    images = convert_from_path(pdf_file)

    # 保存PNG文件，使用原文件名
    for i, image in enumerate(images):
        png_file_name = f"{pdf_file_name}.png"
        image.save(png_file_name, 'PNG')

    # 删除原始PDF文件
    os.remove(pdf_file)

print("转换并删除完成。")
