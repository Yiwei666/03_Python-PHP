# -*- coding: utf-8 -*-

import math
import os

# 与 PHP 代码相同的 Base32 字母表
ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'

def base32_encode(input_str):
    """
    按照题目提供的 PHP 逻辑对字符串进行 Base32 编码
    """
    if not input_str:
        return ""

    # 将输入字符串的每个字符转换为 8 位二进制形式
    binary_str = ""
    for char in input_str:
        ascii_val = ord(char)
        binary_str += format(ascii_val, '08b')  # 转为 8 位二进制

    # 将二进制字符串补齐到 5 位的倍数
    if len(binary_str) % 5 != 0:
        binary_str = binary_str.ljust((len(binary_str) // 5 + 1) * 5, '0')

    # 每 5 位二进制转一个 Base32 字符
    base32_result = ""
    for i in range(0, len(binary_str), 5):
        chunk = binary_str[i:i+5]
        index = int(chunk, 2)
        base32_result += ALPHABET[index]

    # 按照题目逻辑，Base32 的输出长度如果不是 8 的倍数，需要使用 '=' 填充
    if len(base32_result) % 8 != 0:
        base32_result += "=" * (8 - (len(base32_result) % 8))

    return base32_result

def main():
    # 提示用户输入
    doi = input("请输入要下载的论文 DOI 号，例如 '10.1063/1.446740': ").strip()
    if not doi:
        print("DOI 不能为空！")
        return

    # Base32 编码
    encoded_doi = base32_encode(doi)
    print(f"对 DOI 进行 Base32 编码后得到：{encoded_doi}")

    # 拼接出最终的保存路径（Windows 环境下注意转义反斜杠）
    # save_path = f"C:\\Users\\sun78\\下载_chrome\\{encoded_doi}.pdf"
    save_path = f"C:\\Users\\sun78\\Desktop\\Al_rdf\\{encoded_doi}.pdf"
    

    # 通过系统调用 scidownl 命令下载
    # 若环境中没有 scidownl，需先安装： pip install scidownl
    cmd = f'scidownl download --doi {doi} --out "{save_path}"'
    print(f"正在执行下载命令：{cmd}")
    os.system(cmd)
    print("下载完成，请检查输出路径。")

if __name__ == "__main__":
    main()
