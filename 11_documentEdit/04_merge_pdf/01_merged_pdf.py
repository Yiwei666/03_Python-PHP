import os
import sys
from PyPDF2 import PdfMerger

def list_pdfs_sorted(directory: str):
    """Return alphabetically sorted list of PDF filenames in directory."""
    pdfs = [f for f in os.listdir(directory) if f.lower().endswith(".pdf")]
    pdfs.sort()
    return pdfs

def print_order(files):
    print("即将合并的文件顺序（按字母序）：")
    for i, name in enumerate(files, 1):
        print(f"{i:>3}. {name}")
    print()

def ask_confirmation() -> str:
    """Ask user to confirm. Returns 'y', 'n', or 'q'."""
    while True:
        ans = input("确认是否按上述顺序合并？(y=采用 / n=不采用 / q=结束): ").strip().lower()
        if ans in {"y", "n", "q"}:
            return ans
        print("非法输入，请仅输入 y / n / q。")

def merge_pdfs_in_directory():
    current_directory = os.getcwd()
    pdf_files = list_pdfs_sorted(current_directory)

    if not pdf_files:
        print("当前目录未找到 PDF 文件。")
        return

    print_order(pdf_files)
    choice = ask_confirmation()

    if choice == "q":
        print("已结束程序运行。")
        sys.exit(0)
    if choice == "n":
        print("未采用该顺序，未进行任何合并操作。")
        return

    # choice == 'y'：执行合并
    merger = PdfMerger()
    try:
        for pdf_file in pdf_files:
            pdf_path = os.path.join(current_directory, pdf_file)
            merger.append(pdf_path)

        output_file = "merged_output.pdf"
        with open(output_file, "wb") as output:
            merger.write(output)
        print(f"已合并 {len(pdf_files)} 个 PDF 文件至 {output_file}。")
    except Exception as e:
        print(f"合并过程中发生错误：{e}")
    finally:
        try:
            merger.close()
        except Exception:
            pass

if __name__ == "__main__":
    merge_pdfs_in_directory()
