import os
import paramiko
import socks
import socket
from paramiko import SSHClient, AutoAddPolicy

def setup_socks5_proxy():
    # 设置 SOCKS5 代理
    socks.set_default_proxy(socks.SOCKS5, "localhost", 1080)
    socket.socket = socks.socksocket

def scp_transfer(local_path, remote_path, remote_host, remote_port, username, password):
    setup_socks5_proxy()

    # 创建SSH客户端
    ssh = SSHClient()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    
    try:
        # 连接远程服务器
        ssh.connect(remote_host, port=remote_port, username=username, password=password)

        # 使用SFTP进行文件传输
        sftp = ssh.open_sftp()

        if os.path.isdir(local_path):
            # 如果是目录，则递归上传
            for root, dirs, files in os.walk(local_path):
                remote_dir = os.path.normpath(os.path.join(remote_path, os.path.relpath(root, local_path))).replace("\\", "/")
                try:
                    sftp.mkdir(remote_dir)
                except OSError:
                    pass  # 目录可能已经存在
                for file in files:
                    local_file = os.path.join(root, file)
                    remote_file = os.path.normpath(os.path.join(remote_dir, file)).replace("\\", "/")
                    sftp.put(local_file, remote_file)
                    # 验证文件传输是否成功
                    if not verify_file_transfer(sftp, remote_file):
                        print(f"File transfer failed for {local_file}")
        else:
            # 如果是文件，直接上传
            remote_file = os.path.normpath(remote_path).replace("\\", "/")
            sftp.put(local_path, remote_file)
            # 验证文件传输是否成功
            if not verify_file_transfer(sftp, remote_file):
                print(f"File transfer failed for {local_path}")

        sftp.close()
        print(f"File(s) transferred successfully from {local_path} to {remote_path}")
    except Exception as e:
        print(f"An error occurred: {e}")
    finally:
        ssh.close()

def verify_file_transfer(sftp, remote_file):
    try:
        sftp.stat(remote_file)
        return True
    except FileNotFoundError:
        return False

if __name__ == "__main__":
    local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
    remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
    remote_host = "74.48.107.63"  # 远程服务器IP
    remote_port = 22  # 远程服务器SSH端口
    username = "root"  # SSH用户名
    password = "your_password"  # SSH密码

    scp_transfer(local_path, remote_path, remote_host, remote_port, username, password)
