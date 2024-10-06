import os
import paramiko
from paramiko import SSHClient, AutoAddPolicy
import socks
import socket
import stat  # 新增stat模块用于判断文件类型

def setup_socks5_proxy():
    # 设置 SOCKS5 代理
    socks.set_default_proxy(socks.SOCKS5, "localhost", 1080)
    socket.socket = socks.socksocket

def list_local_files(local_path):
    """列出本地目录下所有文件及其大小"""
    local_files = {}
    for root, dirs, files in os.walk(local_path):
        for file in files:
            full_path = os.path.join(root, file)
            relative_path = os.path.relpath(full_path, local_path).replace("\\", "/")
            local_files[relative_path] = os.path.getsize(full_path)
    return local_files

def list_remote_files(ssh, remote_path):
    """列出远程目录下所有文件及其大小"""
    sftp = ssh.open_sftp()
    remote_files = {}

    def scan_dir(path):
        for item in sftp.listdir_attr(path):
            remote_file_path = f"{path}/{item.filename}".replace("//", "/")
            # 使用stat.S_ISDIR判断是否为目录
            if stat.S_ISDIR(item.st_mode):
                scan_dir(remote_file_path)  # 递归扫描子目录
            else:
                remote_files[remote_file_path.replace(remote_path + '/', '')] = item.st_size

    scan_dir(remote_path)
    sftp.close()
    return remote_files

def compare_files(local_files, remote_files):
    missing_files = []
    size_mismatch_files = []

    for file, size in local_files.items():
        if file not in remote_files:
            missing_files.append(file)
        elif size != remote_files[file]:
            size_mismatch_files.append((file, size, remote_files[file]))

    return missing_files, size_mismatch_files

def compare_local_and_remote(local_path, remote_path, remote_host, remote_port, username, password):
    setup_socks5_proxy()

    # 创建SSH客户端
    ssh = SSHClient()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    
    try:
        # 连接远程服务器
        ssh.connect(remote_host, port=remote_port, username=username, password=password)

        # 获取本地和远程文件信息
        local_files = list_local_files(local_path)
        remote_files = list_remote_files(ssh, remote_path)

        # 比较本地和远程文件
        missing_files, size_mismatch_files = compare_files(local_files, remote_files)

        # 打印比较结果
        if missing_files:
            print("以下文件在远程服务器中缺失：")
            for file in missing_files:
                print(f"- {file}")
        else:
            print("远程服务器没有缺失文件。")

        if size_mismatch_files:
            print("\n以下文件在本地和远程的大小不一致：")
            for file, local_size, remote_size in size_mismatch_files:
                print(f"- {file}: 本地大小 {local_size} bytes, 远程大小 {remote_size} bytes")
        else:
            print("\n本地和远程的文件大小都一致。")
        
    except Exception as e:
        print(f"An error occurred: {e}")
    finally:
        ssh.close()

if __name__ == "__main__":
    local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
    remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
    remote_host = "74.48.107.63"  # 远程服务器IP
    remote_port = 22  # 远程服务器SSH端口
    username = "root"  # SSH用户名
    password = "your_password"  # SSH密码

    compare_local_and_remote(local_path, remote_path, remote_host, remote_port, username, password)
