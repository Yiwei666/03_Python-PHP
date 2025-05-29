#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
批量重命名 twitter 目录下各子文件夹中的 JPG 图片
新名称: YYYYMMDD-HHmmss-<子文件夹名>-<6位随机数字/小写字母>.jpg
示例: 20250518-135453-Japantravelco-i908rd.jpg
"""

import os
import random
import re
from datetime import datetime
from pathlib import Path

# === 1. 修改为你的 twitter 根目录 ===
ROOT = Path(r"D:\software\27_nodejs\gallery-dl\gallery-dl\twitter")

# === 2. 随机 6 位数字+小写字母 ===
def rand6() -> str:
    return ''.join(random.choices('0123456789abcdefghijklmnopqrstuvwxyz', k=6))

# === 3. 判断文件是否已重命名过 ===
def already_ok(fname: str, folder: str) -> bool:
    pattern = rf"^\d{{8}}-\d{{6}}-{re.escape(folder)}-[0-9a-z]{{6}}\.jpg$"
    return re.fullmatch(pattern, fname) is not None

# === 4. 主循环 ===
for subdir in filter(Path.is_dir, ROOT.iterdir()):
    folder_name = subdir.name
    for img in subdir.glob("*.jpg"):
        if already_ok(img.name, folder_name):          # 跳过已合规文件
            continue

        # 取文件“创建时间”作时间戳（Windows）。如需改用修改时间把 st_ctime 换成 st_mtime
        timestamp = datetime.fromtimestamp(img.stat().st_ctime).strftime("%Y%m%d-%H%M%S")
        new_name = f"{timestamp}-{folder_name}-{rand6()}.jpg"
        new_path = img.with_name(new_name)

        # 若生成的名字已存在，换随机串直至唯一
        while new_path.exists():
            new_name = f"{timestamp}-{folder_name}-{rand6()}.jpg"
            new_path = img.with_name(new_name)

        img.rename(new_path)
        print(f"[OK] {img.relative_to(ROOT.parent)}  →  {new_name}")
