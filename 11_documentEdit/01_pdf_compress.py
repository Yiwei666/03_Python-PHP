#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
图片型PDF压缩：整页渲染为位图后以JPEG重新打包（含稳健兜底）
依赖：
  pip install pymupdf pillow
"""

import os
import sys
import math
from typing import List

import fitz  # PyMuPDF
from PIL import Image, ImageFile
import io

ImageFile.LOAD_TRUNCATED_IMAGES = True

# ========================= 基础交互 =========================

def list_pdfs() -> List[str]:
    pdfs = [f for f in os.listdir(".") if f.lower().endswith(".pdf")]
    pdfs.sort()
    return pdfs

def human_mb(nbytes: int) -> float:
    return nbytes / (1024 * 1024)

def suggest_quality(current_mb: float, target_mb: float) -> int:
    if current_mb <= 0: return 75
    ratio = max(0.03, min(target_mb / current_mb, 1.2))
    q = int(10 + 80 * ratio)
    return max(10, min(95, q))

def ask_input(prompt: str) -> str:
    try:
        return input(prompt).strip()
    except EOFError:
        print("\n输入中断。"); sys.exit(1)

def pick_pdf_file(pdf_list: List[str]) -> str:
    print("当前目录下检测到的 PDF 文件：")
    for i, name in enumerate(pdf_list, 1):
        print(f"  [#{i}] {name}")
    while True:
        s = ask_input("请输入要压缩的文件名或序号：")
        if s.isdigit():
            idx = int(s)
            if 1 <= idx <= len(pdf_list):
                return pdf_list[idx - 1]
        if s in pdf_list:
            return s
        print("未匹配到文件，请重新输入。")

def ask_target_size_mb() -> float:
    while True:
        s = ask_input("请输入目标压缩大小（单位MB，例如 50）：")
        try:
            v = float(s)
            if v > 0:
                return v
        except ValueError:
            pass
        print("输入无效，请输入大于0的数字。")

def ask_use_quality(q_suggest: int) -> int:
    print(f"建议 JPEG 压缩质量为：{q_suggest}（范围1-95）")
    while True:
        yn = ask_input("是否采用该质量？是(y) / 否(n)：").lower()
        if yn in ("y", "yes", ""):
            return q_suggest
        if yn in ("n", "no"):
            while True:
                s = ask_input("请输入想要采用的质量数值（1-95）：")
                if s.isdigit():
                    q = int(s)
                    if 1 <= q <= 95:
                        return q
                print("质量数值不合法，请输入 1-95 的整数。")
        print("请输入 y 或 n。")

# ========================= 工具函数 =========================

def pixmap_to_jpeg_bytes(pix: fitz.Pixmap, quality: int) -> bytes:
    """
    将 Pixmap 转为 JPEG 字节串。
    先尝试 MuPDF 直接导出（更快），失败则用 Pillow 兜底。
    """
    # 先确保没有 alpha（JPEG 不支持）
    if pix.alpha:
        pix = fitz.Pixmap(pix, 0)  # 去掉 alpha 通道

    # 尝试 MuPDF 自带编码（有些版本只认 "jpg"）
    for fmt in ("jpg", "jpeg"):
        try:
            return pix.tobytes(fmt, jpg_quality=int(quality))
        except Exception:
            pass  # 继续尝试

    # 兜底：转 Pillow 再编码
    # 颜色通道：pix.n=1(灰度), 3(RGB)
    mode = "L" if pix.n == 1 else "RGB"
    img = Image.frombytes(mode, (pix.width, pix.height), pix.samples)
    if mode != "RGB":
        img = img.convert("RGB")
    buf = io.BytesIO()
    img.save(buf, format="JPEG", quality=int(quality), optimize=True)
    return buf.getvalue()

# ========================= 压缩实现（PyMuPDF 栅格化） =========================

def compress_pdf_raster(input_file: str, output_file: str, quality: int, dpi: int = 150) -> dict:
    """
    将每一页渲染为位图，再以 JPEG 写回新PDF。
      - 对扫描版 / 图片型 PDF 有效
      - quality: 1-95
      - dpi: 100~200 常用，越低体积越小
    """
    src = fitz.open(input_file)
    out = fitz.open()
    zoom = dpi / 72.0
    matrix = fitz.Matrix(zoom, zoom)

    pages = len(src)
    rendered_pages = 0

    for page in src:
        pix = page.get_pixmap(matrix=matrix, alpha=False)
        jpg_bytes = pixmap_to_jpeg_bytes(pix, quality=quality)
        rect = page.rect  # 页面尺寸（pt）

        newpage = out.new_page(width=rect.width, height=rect.height)
        newpage.insert_image(newpage.rect, stream=jpg_bytes)
        rendered_pages += 1

    out.save(output_file, garbage=4, deflate=True)
    out.close()
    src.close()

    return {"pages": pages, "rendered_pages": rendered_pages, "dpi": dpi, "quality": quality}

# ========================= 主流程 =========================

def main():
    pdfs = list_pdfs()
    if not pdfs:
        print("当前目录未找到任何 PDF 文件。"); return

    in_name = pick_pdf_file(pdfs)
    in_size = os.path.getsize(in_name)
    in_mb = human_mb(in_size)
    print(f"待压缩文件：{in_name}")
    print(f"原始大小：{in_mb:.2f} MB")

    target_mb = ask_target_size_mb()
    q_suggest = suggest_quality(in_mb, target_mb)
    q_final = ask_use_quality(q_suggest)

    default_dpi = 150
    print(f"\n将使用 DPI={default_dpi}、JPEG质量={q_final} 进行压缩。")
    out_name = f"compressed_{in_name}"

    stats = compress_pdf_raster(in_name, out_name, quality=q_final, dpi=default_dpi)

    if not os.path.exists(out_name):
        print("压缩失败：未生成输出文件。"); return

    out_size = os.path.getsize(out_name)
    out_mb = human_mb(out_size)
    ratio = (out_mb / in_mb) if in_mb > 0 else 0.0

    print("\n压缩完成。")
    print(f"页数：{stats['pages']}  |  已渲染：{stats['rendered_pages']}  |  DPI：{stats['dpi']}  |  质量：{stats['quality']}")
    print(f"输出文件：{out_name}")
    print(f"输出大小：{out_mb:.2f} MB  (约为原来的 {ratio*100:.1f}%)")

    if out_mb > target_mb:
        next_q = max(10, q_final - 10)
        # 简单估算一个更低 DPI 建议
        next_dpi = max(90, int(default_dpi * math.sqrt(target_mb / max(out_mb, 1e-6))))
        print(f"提示：当前仍高于目标 {target_mb:.2f} MB。可尝试：质量≈{next_q} 或 将DPI降至≈{next_dpi} 后重试。")

if __name__ == "__main__":
    main()
