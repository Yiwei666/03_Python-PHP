import os

def list_files_in_directory():
    try:
        current_directory = os.getcwd()
        files = [f for f in os.listdir(current_directory) if os.path.isfile(os.path.join(current_directory, f))]
        print("当前目录下的文件:")
        for file in files:
            print(file)
    except Exception as e:
        print(f"发生错误：{e}")

def compare_files(file1_path, file2_path):
    try:
        with open(file1_path, 'r', encoding='utf-8') as file1, open(file2_path, 'r', encoding='utf-8') as file2:
            lines1 = file1.readlines()
            lines2 = file2.readlines()

            # 比较每一行
            for i, (line1, line2) in enumerate(zip(lines1, lines2), start=1):
                if line1 != line2:
                    print(f"第 {i} 行不同:")
                    print(f"文件1: {line1.strip()}")
                    print(f"文件2: {line2.strip()}")
            
            if lines1 == lines2:
                print("两个文件的内容相同")
            else:
                print("两个文件的内容不同")
    except FileNotFoundError:
        print("文件未找到")
    except Exception as e:
        print(f"发生错误：{e}")

# 显示当前目录下的文件
list_files_in_directory()

# 提示用户输入文件路径
file1_name = input("请输入第一个文件的名称：")
file2_name = input("请输入第二个文件的名称：")

# 构建完整的文件路径
file1_path = os.path.join(os.getcwd(), file1_name)
file2_path = os.path.join(os.getcwd(), file2_name)

# 比较文件内容
compare_files(file1_path, file2_path)
