from PIL import Image
import os

def make_transparent(image_path, output_path):
    # 打开图像
    image = Image.open(image_path)

    # 将图像转换为 RGBA 模式
    image = image.convert("RGBA")

    # 获取图像中的每个像素点
    data = image.getdata()

    # 创建一个新的像素列表，将非白色像素的 Alpha 值设置为 0，以实现透明效果
    new_data = []
    for item in data:
        # 判断是否为白色像素
        if item[:3] == (255, 255, 255):
            # 设置白色像素的 Alpha 值为 0
            new_data.append((255, 255, 255, 0))
        else:
            # 非白色像素保持不变
            new_data.append(item)

    # 更新图像数据
    image.putdata(new_data)

    # 保存图像
    image.save(output_path, "PNG")

    print(f"已转换：{output_path}")


# 获取当前文件夹下的所有文件名
files = [f for f in os.listdir('.') if os.path.isfile(f)]

# 打印文件名（不包括子文件夹）
for file in files:
    print(file)

# 获取同级目录下所有PNG图像文件
png_files = [f for f in files if f.lower().endswith('.png')]

# 转换所有PNG图像
for image_name in png_files:
    # 输出图像路径
    output_image = os.path.splitext(image_name)[0] + "_transparent.png"

    # 转换图像为透明
    make_transparent(image_name, output_image)

print("所有PNG图像转换完成！")
