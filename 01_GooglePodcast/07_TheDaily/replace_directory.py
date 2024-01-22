import os

# 定义字符串替换的映射关系
replacement_mapping = {
    "45_TodayExplained": "46_AllEarsEnglishPodcast",
    # 在这里添加更多的映射关系
}

# 获取当前脚本所在目录
current_directory = os.path.dirname(os.path.abspath(__file__))

# 定义要处理的脚本文件列表
script_files = [
    "source.sh",
    "source_move_to_target.sh",
    "rclone_limitFileSize.sh",
    "download_mp3.sh",
]

# 遍历每个脚本文件
for script_file in script_files:
    script_path = os.path.join(current_directory, script_file)
    
    # 打印当前脚本文件名
    print(f"处理脚本文件: {script_file}")
    
    # 打开脚本文件，以二进制模式读取并使用utf-8编码
    with open(script_path, 'rb') as file:
        lines = file.readlines()
        
        for index, line in enumerate(lines):
            # 遍历替换映射关系
            for key, value in replacement_mapping.items():
                if key.encode('utf-8') in line:
                    # 替换字符串并打印替换前后的内容
                    new_line = line.replace(key.encode('utf-8'), value.encode('utf-8'))
                    print(f"  行 {index + 1} 替换前：{line.strip().decode('utf-8', 'ignore')}")
                    print(f"     替换后：{new_line.strip().decode('utf-8', 'ignore')}")
                    # 更新替换后的行
                    lines[index] = new_line
                    
    # 将替换后的内容写回文件
    with open(script_path, 'wb') as file:
        file.writelines(lines)

    print("完成替换\n")
