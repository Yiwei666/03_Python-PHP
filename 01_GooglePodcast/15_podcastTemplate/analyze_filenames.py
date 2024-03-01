# 打开文件并读取内容
with open('nameURL.txt', 'r') as file:
    lines = file.readlines()

# 初始化文件名列表和字典
file_names = []
file_name_count = {}

# 遍历每一行，提取文件名并进行处理
for line in lines:
    parts = line.strip().split(',')
    if len(parts) == 2:
        file_name = parts[0].strip()
        # 打印文件名
        # print(f'文件名: {file_name}')
        
        # 统计文件名总数
        file_names.append(file_name)
        
        # 统计文件名出现次数
        if file_name in file_name_count:
            file_name_count[file_name] += 1
        else:
            file_name_count[file_name] = 1

# 打印文件名总数
print(f'\n文件名总数: {len(file_names)}')

# 打印不重复的文件名数量
unique_file_names = set(file_names)
print(f'不重复的文件名数量: {len(unique_file_names)}')

# 打印重复的文件名和出现次数
print('重复的文件名和出现次数:')
for file_name, count in file_name_count.items():
    if count > 1:
        print(f'{file_name}: {count} 次')
