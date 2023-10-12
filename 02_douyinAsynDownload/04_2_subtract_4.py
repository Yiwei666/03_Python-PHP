# 文件名变量
file_2 = '/home/01_html/05_douyinAsynDload/2.txt'
file_4_success = '/home/01_html/05_douyinAsynDload/4_success.txt'

# 读取4_success.txt中的链接
with open(file_4_success, 'r') as success_file:
    success_links = set(line.strip() for line in success_file)

# 读取2.txt文件中的链接，并保留不在success_links中的链接
with open(file_2, 'r') as input_file:
    remaining_links = [line.strip() for line in input_file if line.strip() not in success_links]

# 将剩余的链接写回2.txt文件中
with open(file_2, 'w') as output_file:
    output_file.write('\n'.join(remaining_links))

print("链接已成功处理！")
