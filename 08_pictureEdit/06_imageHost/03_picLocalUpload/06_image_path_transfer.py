from pathlib import Path
import shutil
import sys


# ============================================================
# 配置
# ============================================================

ROOT_DIR = Path(r"D:\software\27_nodejs\海外风景")


def main():
    # --------------------------------------------------------
    # 1. 检查目标目录
    # --------------------------------------------------------
    if not ROOT_DIR.exists():
        print(f"错误：目录不存在：{ROOT_DIR}")
        sys.exit(1)

    if not ROOT_DIR.is_dir():
        print(f"错误：目标路径不是文件夹：{ROOT_DIR}")
        sys.exit(1)

    # 获取根目录下所有一级子文件夹
    subfolders = sorted(
        [p for p in ROOT_DIR.iterdir() if p.is_dir()],
        key=lambda x: x.name.lower()
    )

    if not subfolders:
        print("未发现任何子文件夹，无需处理。")
        return

    # --------------------------------------------------------
    # 2. 检查子文件夹内容，并统计 JPG
    # --------------------------------------------------------
    folder_info = []
    all_jpg_files = []
    invalid_items = []

    for folder in subfolders:
        jpg_files = []

        for item in folder.iterdir():

            # 理论上不应再有子文件夹
            if item.is_dir():
                invalid_items.append(
                    (folder.name, item.name, "子文件夹")
                )
                continue

            if item.is_file():
                if item.suffix.lower() == ".jpg":
                    jpg_files.append(item)
                else:
                    invalid_items.append(
                        (
                            folder.name,
                            item.name,
                            item.suffix.lower() or "无扩展名"
                        )
                    )

        jpg_files.sort(key=lambda x: x.name.lower())

        folder_info.append(
            (folder, jpg_files)
        )

        all_jpg_files.extend(jpg_files)

    # --------------------------------------------------------
    # 3. 打印统计信息
    # --------------------------------------------------------
    print("=" * 70)
    print("子文件夹 JPG 文件统计")
    print("=" * 70)

    total_count = 0

    for index, (folder, jpg_files) in enumerate(folder_info, start=1):
        count = len(jpg_files)
        total_count += count

        print(f"{index:>3}. {folder.name}")
        print(f"     JPG 数量：{count}")

    print("-" * 70)
    print(f"子文件夹数量：{len(subfolders)}")
    print(f"JPG 照片总数：{total_count}")
    print("=" * 70)

    # --------------------------------------------------------
    # 4. 如果存在非 JPG 文件或其他子文件夹，立即退出
    # --------------------------------------------------------
    if invalid_items:
        print()
        print("发现非 JPG 文件或额外的子文件夹！")
        print("为避免误操作，程序不会移动任何文件。")
        print()

        for folder_name, item_name, item_type in invalid_items:
            print(
                f"  [{folder_name}] "
                f"{item_name}    类型：{item_type}"
            )

        print()
        print("请处理以上文件后重新运行程序。")
        sys.exit(1)

    # --------------------------------------------------------
    # 5. 检查照片文件名冲突
    # --------------------------------------------------------
    filename_map = {}
    conflicts = []

    # 检查不同子文件夹之间是否存在同名照片
    for jpg_file in all_jpg_files:
        filename_key = jpg_file.name.lower()

        if filename_key in filename_map:
            conflicts.append(
                (
                    jpg_file.name,
                    filename_map[filename_key],
                    jpg_file
                )
            )
        else:
            filename_map[filename_key] = jpg_file

    # 检查根目录是否已经存在同名文件
    root_files = {
        p.name.lower(): p
        for p in ROOT_DIR.iterdir()
        if p.is_file()
    }

    root_conflicts = []

    for jpg_file in all_jpg_files:
        if jpg_file.name.lower() in root_files:
            root_conflicts.append(
                (
                    jpg_file,
                    root_files[jpg_file.name.lower()]
                )
            )

    if conflicts or root_conflicts:
        print()
        print("=" * 70)
        print("发现文件名冲突！")
        print("为避免覆盖照片，程序不会进行任何移动操作。")
        print("=" * 70)

        if conflicts:
            print()
            print("不同子文件夹中存在同名照片：")

            for filename, file1, file2 in conflicts:
                print()
                print(f"  文件名：{filename}")
                print(f"    {file1}")
                print(f"    {file2}")

        if root_conflicts:
            print()
            print("子文件夹中的照片与根目录文件重名：")

            for source, target in root_conflicts:
                print()
                print(f"  源文件：{source}")
                print(f"  已存在：{target}")

        print()
        print("请先处理这些重名文件，然后重新运行程序。")
        sys.exit(1)

    # --------------------------------------------------------
    # 6. 用户最终确认
    # --------------------------------------------------------
    print()
    print("即将执行以下操作：")
    print(f"  1. 将 {total_count} 张 JPG 照片移动到：")
    print(f"     {ROOT_DIR}")
    print(f"  2. 删除 {len(subfolders)} 个已经清空的子文件夹")
    print()
    print("该操作会改变现有文件目录结构。")
    print()

    confirm = input(
        '确认执行请输入 "YES"，其他任意输入取消：'
    ).strip()

    if confirm != "YES":
        print()
        print("操作已取消，没有移动或删除任何文件。")
        return

    # --------------------------------------------------------
    # 7. 移动 JPG 文件
    # --------------------------------------------------------
    print()
    print("=" * 70)
    print("开始移动照片...")
    print("=" * 70)

    moved_count = 0

    try:
        for folder, jpg_files in folder_info:

            for jpg_file in jpg_files:
                destination = ROOT_DIR / jpg_file.name

                print(
                    f"[移动] {folder.name}\\{jpg_file.name}"
                    f"  ->  {jpg_file.name}"
                )

                shutil.move(str(jpg_file), str(destination))
                moved_count += 1

        # ----------------------------------------------------
        # 8. 删除已经清空的子文件夹
        # ----------------------------------------------------
        print()
        print("开始删除空子文件夹...")

        for folder, _ in folder_info:
            folder.rmdir()
            print(f"[删除文件夹] {folder.name}")

    except Exception as e:
        print()
        print("=" * 70)
        print("处理过程中发生错误！")
        print("=" * 70)
        print(f"错误信息：{e}")
        print(f"已经成功移动：{moved_count} 张照片")
        print()
        print("请检查目录状态后再决定是否重新运行程序。")
        sys.exit(1)

    # --------------------------------------------------------
    # 9. 完成
    # --------------------------------------------------------
    print()
    print("=" * 70)
    print("处理完成！")
    print("=" * 70)
    print(f"成功移动照片：{moved_count} 张")
    print(f"删除子文件夹：{len(subfolders)} 个")
    print(f"照片当前位置：{ROOT_DIR}")


if __name__ == "__main__":
    main()
