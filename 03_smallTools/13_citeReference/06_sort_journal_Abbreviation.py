import os

# 定义文件名
input_file = "06_journal_Abbreviation.txt"
output_file = "sorted_06_journal_Abbreviation.txt"

def read_and_sort_file(input_file, output_file):
    try:
        # 获取脚本所在目录
        script_dir = os.path.dirname(os.path.abspath(__file__))
        input_path = os.path.join(script_dir, input_file)
        output_path = os.path.join(script_dir, output_file)
        
        # 检查输入文件是否存在
        if not os.path.exists(input_path):
            print(f"文件 {input_file} 不存在！请检查文件名和路径。")
            return
        
        # 读取文件内容
        with open(input_path, 'r', encoding='utf-8') as file:
            lines = file.readlines()
        
        # 按照字母顺序排序，忽略大小写
        sorted_lines = sorted(lines, key=lambda line: line.strip().lower())
        
        # 写入排序结果到新的文件
        with open(output_path, 'w', encoding='utf-8') as file:
            file.writelines(sorted_lines)
        
        print(f"文件已成功排序并保存到 {output_file}")
    except Exception as e:
        print(f"发生错误：{e}")

# 调用函数
read_and_sort_file(input_file, output_file)
