# -*- coding: utf-8 -*-
"""
Created on Wed Jul 19 14:15:48 2023

@author: sun78
"""

import os
import http.server
import socketserver
from urllib.parse import quote
import time
import threading

def generate_directory_structure(root_dir):
    # 获取指定目录下的所有文件和文件夹
    entries = os.listdir(root_dir)

    # 生成目录结构
    tree_structure = "<ul>"
    for entry in entries:
        full_path = os.path.join(root_dir, entry)
        if os.path.isdir(full_path):
            tree_structure += f"<li>{entry}/</li>"
            tree_structure += generate_directory_structure(full_path)
        else:
            file_url = f"file:///{quote(full_path)}"
            tree_structure += f"<li><a href='{file_url}' target='_blank' style='text-decoration: none; color: white;'>{entry}</a></li>"
    tree_structure += "</ul>"
    return tree_structure

def update_index_file():
    while True:
        directory_structure = generate_directory_structure(target_directory)
        html_content = f"""
        <!DOCTYPE html>
        <html>
        <head>
            <title>文件目录查看器</title>
            <style>
                /* 页面背景颜色 */
                body {{
                    background-color: #333; /* 深灰色背景 */
                    color: white; /* 白色字体颜色 */
                    margin: 0; /* 移除默认边距 */
                    padding: 0; /* 移除默认内边距 */
                }}
                /* 居中容器，宽度为50% */
                .container {{
                    width: 50%;
                    margin: 0 auto;
                    background-color: #333; /* 深灰色背景 */
                    padding: 20px; /* 添加一些内边距，美观些 */
                }}
                /* 移除链接下划线 */
                a {{
                    text-decoration: none;
                }}
            </style>
        </head>
        <body>
            <div class="container"> <!-- 将内容包裹在容器中 -->
                <h1>文件目录查看器</h1>
                <p>目录：{target_directory}</p>
                {directory_structure}
            </div>
        </body>
        </html>
        """

        with open("index.html", "w", encoding="utf-8") as index_file:
            index_file.write(html_content)

        print("index.html 已更新。")
        time.sleep(180)  # 每3分钟更新一次 (180 秒)

if __name__ == "__main__":
    # 指定要展示的目录路径
    target_directory = r"D:\onedrive\3图书\01_编程书"

    # 生成目录结构
    directory_structure = generate_directory_structure(target_directory)

    # 创建HTML页面内容
    html_content = f"""
    <!DOCTYPE html>
    <html>
    <head>
        <title>文件目录查看器</title>
        <style>
            /* 页面背景颜色 */
            body {{
                background-color: #333; /* 深灰色背景 */
                color: white; /* 白色字体颜色 */
                margin: 0; /* 移除默认边距 */
                padding: 0; /* 移除默认内边距 */
            }}
            /* 居中容器，宽度为50% */
            .container {{
                width: 50%;
                margin: 0 auto;
                background-color: #333; /* 深灰色背景 */
                padding: 20px; /* 添加一些内边距，美观些 */
            }}
            /* 移除链接下划线 */
            a {{
                text-decoration: none;
            }}
        </style>
    </head>
    <body>
        <div class="container"> <!-- 将内容包裹在容器中 -->
            <h1>文件目录查看器</h1>
            <p>目录：{target_directory}</p>
            {directory_structure}
        </div>
    </body>
    </html>
    """

    # 将HTML内容写入index.html文件
    with open("index.html", "w", encoding="utf-8") as index_file:
        index_file.write(html_content)

    # 启动简单的Web服务器
    PORT = 2000

    class UTF8Handler(http.server.SimpleHTTPRequestHandler):
        def __init__(self, *args, **kwargs):
            super().__init__(*args, directory=os.getcwd(), **kwargs)
    
        def do_GET(self):
            self.send_response(200)
            self.send_header("Content-type", "text/html; charset=utf-8")
            self.end_headers()
            f = self.send_head()
            if f:
                try:
                    self.copyfile(f, self.wfile)
                finally:
                    f.close()
    
    Handler = UTF8Handler
    with socketserver.TCPServer(("", PORT), Handler) as httpd:
        print(f"正在监听 http://localhost:{PORT}")
        
        # 启动一个线程来定期更新index.html
        update_thread = threading.Thread(target=update_index_file)
        update_thread.daemon = True
        update_thread.start()

        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            pass


