import os
import http.server
import socketserver
from urllib.parse import quote
from datetime import datetime

def format_file_size(file_size_bytes):
    if file_size_bytes >= 1e6:
        file_size_str = f"{file_size_bytes / 1e6:.2f}"
    else:
        file_size_str = f"{file_size_bytes / 1e3:.2f}"
    
    integer_part, decimal_part = file_size_str.split('.')
    integer_part = integer_part.zfill(4)
    decimal_part = decimal_part.ljust(2, '0')
    return f"{integer_part}.{decimal_part}"

def generate_directory_structure(root_dir, depth=0):
    entries = os.listdir(root_dir)

    tree_structure = "<ul>"
    for entry in entries:
        full_path = os.path.join(root_dir, entry)
        if os.path.isdir(full_path):
            # Different symbols for different directory levels
            symbols = ['🗂', '📁', '📂', '📄']
            symbol = symbols[depth % len(symbols)]
            tree_structure += f"<li>{symbol} {entry}/</li>"
            tree_structure += generate_directory_structure(full_path, depth + 1)
        else:
            # 获取文件创建时间并以人类可读的格式显示
            creation_time = datetime.fromtimestamp(os.path.getctime(full_path)).strftime('%Y-%m-%d %H:%M:%S')
            
            # 获取文件大小并以MB或KB为单位显示
            file_size_bytes = os.path.getsize(full_path)
            file_size_str = format_file_size(file_size_bytes)

            if file_size_bytes >= 1e6:
                file_size_str += " MB"
            else:
                file_size_str += " KB"

            file_url = f"file:///{quote(full_path)}"
            # Fix the extra "-" symbol by combining the file name and other information properly
            tree_structure += f"<li><a href='{file_url}' target='_blank' style='text-decoration: none; color: white;'>{entry}</a> - Date: {creation_time} - Size: {file_size_str}</li>"
    tree_structure += "</ul>"
    return tree_structure

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
                margin: 0; /* 去除默认的边距 */
                padding: 0; /* 去除默认的填充 */
            }}
            /* 居中的容器，宽度为70% */
            .container {{
                width: 70%;
                margin: 0 auto;
                background-color: #333; /* 深灰色背景 */
                padding: 20px; /* 添加一些填充以便更好地显示 */
            }}
            /* 去除链接下划线 */
            a {{
                text-decoration: none;
            }}
            /* 右对齐文件创建时间和文件大小 */
            li {{
                display: flex;
                justify-content: space-between;
            }}
            /* 针对Date和Size部分设置flex属性 */
            .date-size {{
                flex: 1;
                text-align: right;
            }}
        </style>
    </head>
    <body>
        <div class="container"> <!-- 将内容包装在容器中 -->
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
        print(f"正在服务于 http://localhost:{PORT}")
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            pass
