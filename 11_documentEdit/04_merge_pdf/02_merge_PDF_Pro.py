import os
from datetime import datetime
from PyPDF2 import PdfMerger

SORT_OPTIONS = {
    1: "按名称递增 (A→Z)",
    2: "按名称递减 (Z→A)",
    3: "按修改日期递增 (旧→新)",
    4: "按修改日期递减 (新→旧)",
}

def list_pdfs_with_mtime(directory: str):
    items = []
    for fname in os.listdir(directory):
        if fname.lower().endswith(".pdf"):
            path = os.path.join(directory, fname)
            if os.path.isfile(path):
                mtime = os.path.getmtime(path)
                items.append({
                    "name": fname,
                    "path": path,
                    "mtime": mtime,
                    "mtime_str": datetime.fromtimestamp(mtime).strftime("%Y-%m-%d %H:%M:%S"),
                })
    return items

def print_sort_menu():
    print("\n可选排序方式：")
    for k in sorted(SORT_OPTIONS):
        print(f"{k}. {SORT_OPTIONS[k]}")
    while True:
        choice = input("请输入排序方式序号 (1-4): ").strip()
        if choice.isdigit() and int(choice) in SORT_OPTIONS:
            return int(choice)
        print("非法输入：请输入 1/2/3/4。")

def sort_items(items, choice: int):
    if choice == 1:
        return sorted(items, key=lambda x: x["name"].casefold())
    elif choice == 2:
        return sorted(items, key=lambda x: x["name"].casefold(), reverse=True)
    elif choice == 3:
        return sorted(items, key=lambda x: x["mtime"])
    elif choice == 4:
        return sorted(items, key=lambda x: x["mtime"], reverse=True)
    return items

def print_order_preview(items):
    print("\n即将按以下顺序合并（索引. 文件名 [修改时间]）：")
    for i, it in enumerate(items, 1):
        print(f"{i:>3}. {it['name']}  [{it['mtime_str']}]")

def confirm_merge():
    while True:
        ans = input("确认合并？(y=采用 / n=不采用并重新选择 / q=结束程序): ").strip().lower()
        if ans in {"y", "n", "q"}:
            return ans
        print("非法输入：仅允许 y / n / q。")

def merge_pdfs_in_order(items, output_file="merged_output.pdf"):
    merger = PdfMerger()
    for it in items:
        merger.append(it["path"])
    with open(output_file, "wb") as f:
        merger.write(f)
    merger.close()
    print(f"\n已合并 {len(items)} 个PDF文件为：{output_file}")

def main():
    current_dir = os.getcwd()
    pdfs = list_pdfs_with_mtime(current_dir)
    if not pdfs:
        print("当前目录未找到任何 .pdf 文件。")
        return

    while True:
        choice = print_sort_menu()
        sorted_list = sort_items(pdfs, choice)
        print_order_preview(sorted_list)
        ans = confirm_merge()
        if ans == "y":
            merge_pdfs_in_order(sorted_list)
            break
        elif ans == "n":
            print("\n已取消本次排序，返回重新选择排序方式。")
            continue
        elif ans == "q":
            print("程序结束。")
            break

if __name__ == "__main__":
    main()
