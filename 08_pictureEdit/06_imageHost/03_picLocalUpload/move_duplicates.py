import os
import hashlib
from shutil import move
from PIL import Image

def get_image_hash(image_path):
    with Image.open(image_path) as img:
        return hashlib.md5(img.tobytes()).hexdigest()

def find_and_move_duplicates(src_folder, dest_folder):
    if not os.path.exists(dest_folder):
        os.makedirs(dest_folder)

    hash_map = {}
    duplicates = []
    total_files = 0

    for filename in os.listdir(src_folder):
        if filename.lower().endswith('.png'):
            total_files += 1
            file_path = os.path.join(src_folder, filename)
            if not os.path.exists(file_path):
                continue
            img_hash = get_image_hash(file_path)

            if img_hash in hash_map:
                duplicates.append(file_path)
                if len(hash_map[img_hash]) == 1:
                    duplicates.append(hash_map[img_hash][0])
            else:
                hash_map[img_hash] = [file_path]

    for dup_path in duplicates:
        if os.path.exists(dup_path):
            basename = os.path.basename(dup_path)
            new_path = os.path.join(dest_folder, basename)
            try:
                move(dup_path, new_path)
                print(f"Moved: {dup_path} -> {new_path}")
            except Exception as e:
                print(f"Error moving {dup_path} to {new_path}: {e}")

    # 统计信息
    unique_files_count = len(hash_map)
    duplicate_files_count = len(duplicates)
    non_duplicate_files_count = total_files - duplicate_files_count

    print(f"Total files: {total_files}")
    print(f"Files without duplicates: {non_duplicate_files_count}")
    print(f"Duplicate files moved: {duplicate_files_count}")
    print(f"Unique files (by hash): {unique_files_count}")

source_folder = r"D:\onedrive\图片\01_家乡风景\海外风景"
destination_folder = r"D:\onedrive\图片\01_家乡风景\海外风景\01_repeat"
find_and_move_duplicates(source_folder, destination_folder)
