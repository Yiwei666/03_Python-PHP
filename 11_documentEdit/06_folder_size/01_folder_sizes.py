#!/usr/bin/env python3
"""统计一级子目录及其全部后代文件的逻辑大小（仅使用 Python 标准库）。"""

import argparse
import os
import stat
import sys
import time
from dataclasses import dataclass
from pathlib import Path


KB = 1024
MB = KB ** 2
GB = KB ** 3
TB = KB ** 4


@dataclass
class FolderSize:
    name: str
    size: int = 0
    errors: int = 0
    skipped_links: int = 0


def format_size(size: int) -> str:
    if size < MB:
        return f"{size / KB:,.2f} KB"
    if size < GB:
        return f"{size / MB:,.1f} MB"
    if size < TB:
        return f"{size / GB:,.2f} GB"
    return f"{size / TB:,.2f} TB"


def is_link_or_reparse_point(info: os.stat_result) -> bool:
    # Windows 的目录联接也可能指向扫描范围之外，不能只检查符号链接。
    return stat.S_ISLNK(info.st_mode) or bool(
        getattr(info, "st_file_attributes", 0)
        & getattr(stat, "FILE_ATTRIBUTE_REPARSE_POINT", 0x400)
    )


def scan_folder(path: Path) -> FolderSize:
    result = FolderSize(name=path.name)
    pending = [path]
    # 用显式栈代替递归，避免目录层级过深导致递归溢出。
    while pending:
        current = pending.pop()
        try:
            with os.scandir(current) as entries:
                for entry in entries:
                    try:
                        info = entry.stat(follow_symlinks=False)
                        if is_link_or_reparse_point(info):
                            result.skipped_links += 1
                        elif stat.S_ISDIR(info.st_mode):
                            pending.append(Path(entry.path))
                        elif stat.S_ISREG(info.st_mode):
                            result.size += info.st_size
                    except OSError:
                        # 无权限、文件已消失等情况只影响当前条目。
                        result.errors += 1
        except OSError:
            # 也捕获目录枚举途中出现的错误；保留此前已统计的大小。
            result.errors += 1
    return result


def list_child_folders(root: Path):
    folders = []
    errors = 0
    skipped_links = 0
    with os.scandir(root) as entries:
        for entry in entries:
            try:
                info = entry.stat(follow_symlinks=False)
                if is_link_or_reparse_point(info):
                    skipped_links += 1
                elif stat.S_ISDIR(info.st_mode):
                    folders.append(Path(entry.path))
            except OSError:
                errors += 1
    return folders, errors, skipped_links


def main() -> int:
    parser = argparse.ArgumentParser(
        description="统计指定路径下各一级子目录的大小，按占用从大到小排列。"
    )
    parser.add_argument("path", nargs="?", help="目录路径；省略时交互输入")
    args = parser.parse_args()
    raw_path = args.path if args.path is not None else input("请输入要统计的目录路径：")
    raw_path = raw_path.strip()
    if len(raw_path) >= 2 and raw_path[0] == raw_path[-1] and raw_path[0] in "\"'":
        raw_path = raw_path[1:-1].strip()
    if not raw_path:
        print("错误：目录路径不能为空。", file=sys.stderr)
        return 1

    started = time.monotonic()
    try:
        root = Path(os.path.expandvars(raw_path)).expanduser().absolute()
        if not root.is_dir():
            print(f"错误：路径不存在、无法访问或不是目录：{root}", file=sys.stderr)
            return 1
        folders, root_errors, root_links = list_child_folders(root)
    except (OSError, ValueError, RuntimeError) as exc:
        print(f"错误：无法读取指定目录：{exc}", file=sys.stderr)
        return 1

    print(f"\n统计路径：{root}")
    print("统计每个一级子目录内的全部文件，首次扫描较大目录可能需要一些时间。")
    results = []
    for index, folder in enumerate(folders, start=1):
        print(f"正在扫描 [{index}/{len(folders)}] {folder.name}", file=sys.stderr, flush=True)
        results.append(scan_folder(folder))
    results.sort(key=lambda item: (-item.size, item.name.casefold()))

    print("\n大小（降序）       目录名称 / 状态")
    print("-" * 64)
    for result in results:
        notes = []
        if result.errors:
            notes.append(f"统计不完整：{result.errors} 处读取失败")
        if result.skipped_links:
            notes.append(f"跳过 {result.skipped_links} 个链接/重解析点")
        suffix = f"  [{'; '.join(notes)}]" if notes else ""
        print(f"{format_size(result.size):>16}  {result.name}{suffix}")
    if not results:
        print("未找到可统计的一级子目录。")
    print("-" * 64)
    print(f"已读取的子目录文件合计：{format_size(sum(item.size for item in results))}")
    print(f"一级子目录数：{len(results)}；耗时：{time.monotonic() - started:.1f} 秒")
    if root_errors:
        print(f"注意：一级目录中有 {root_errors} 个条目无法读取，可能遗漏子目录。")
    if root_links:
        print(f"一级目录中已跳过 {root_links} 个链接/重解析点。")
    print("\n说明：1 KB = 1024 字节；父目录直接包含的文件不计入上述合计。")
    print("显示文件逻辑大小，可能与磁盘实际占用不同；硬链接按路径重复计数。")
    print("链接和 Windows 重解析点不跟随；读取失败时，显示的大小可能偏小。")
    print("本脚本只读取和统计文件，不执行清理或删除。")
    return 0


if __name__ == "__main__":
    try:
        sys.exit(main())
    except KeyboardInterrupt:
        print("\n已取消扫描。", file=sys.stderr)
        sys.exit(130)
    except EOFError:
        print("\n未收到路径输入。", file=sys.stderr)
        sys.exit(1)
