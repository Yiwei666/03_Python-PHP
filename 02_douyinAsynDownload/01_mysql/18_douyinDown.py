import pymysql
import requests
from bs4 import BeautifulSoup
import os
import random
from datetime import datetime

# 数据库配置
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '123456',
    'database': 'douyin_db'
}

# 定义下载目录
download_dir = "/home/01_html/02_douyVideo/"
if not os.path.exists(download_dir):
    os.makedirs(download_dir)

# 连接到数据库
connection = pymysql.connect(**db_config)

try:
    with connection.cursor() as cursor:
        # 查询所有 download_status 为 0 的 video_url
        sql = "SELECT id, video_url FROM douyin_videos WHERE download_status = 0"
        cursor.execute(sql)
        result = cursor.fetchall()

        # 随机选择一个链接进行下载
        if result:
            video_id, encoded_url = random.choice(result)
            print(f"Selected URL for download: {encoded_url}")

            # 构建请求 URL
            url1 = "https://dlpanda.com/zh-CN/?url="
            url = url1 + requests.utils.quote(encoded_url) + "&token=G7eRpMaa"
            headers = {
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
                "Referer": "https://dlpanda.com/",
            }

            # 发送请求
            response = requests.get(url, headers=headers)
            soup = BeautifulSoup(response.content, 'html.parser')
            source_tag = soup.find("source")

            if source_tag and 'src' in source_tag.attrs:
                video_url = "https:" + source_tag['src'].replace("amp;", "")
                print(f"Download URL found: {video_url}")

                # 下载视频内容
                video_response = requests.get(video_url)
                if video_response.status_code == 200:
                    # 根据当前时间生成文件名
                    file_name = datetime.now().strftime("%Y%m%d-%H%M%S") + ".mp4"
                    file_path = os.path.join(download_dir, file_name)

                    # 保存视频文件
                    with open(file_path, 'wb') as file:
                        file.write(video_response.content)
                    print(f"Downloaded and saved as: {file_name}")

                    # 更新数据库记录
                    update_sql = """
                    UPDATE douyin_videos
                    SET download_status = 1, downloaded_video_name = %s, video_download_time = %s
                    WHERE id = %s
                    """
                    cursor.execute(update_sql, (file_name, datetime.now(), video_id))
                    connection.commit()
                    print("Database updated successfully.")
                else:
                    print("Failed to download video.")
            else:
                print("No video source found in the response.")
        else:
            print("No undownloaded videos found in the database.")

finally:
    connection.close()
