import os
import paramiko
import socks
import socket
from paramiko import SSHClient, AutoAddPolicy
import concurrent.futures

def setup_socks5_proxy():
    # 设置 SOCKS5 代理
    socks.set_default_proxy(socks.SOCKS5, "localhost", 1080)
    socket.socket = socks.socksocket

def progress_callback(transferred, total):
    percent = 100.0 * transferred / total
    print(f"\rProgress: {percent:.2f}%", end='')

def scp_transfer_file(file_info, remote_path, remote_host, remote_port, username, password):
    setup_socks5_proxy()

    local_file, remote_file = file_info

    # 创建SSH客户端
    ssh = SSHClient()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    
    try:
        # 连接远程服务器
        ssh.connect(remote_host, port=remote_port, username=username, password=password)

        # 使用SFTP进行文件传输
        sftp = ssh.open_sftp()
        print(f"Uploading {local_file} to {remote_file}")
        sftp.put(local_file, remote_file, callback=progress_callback)
        print()  # 换行
        # 验证文件传输是否成功
        if not verify_file_transfer(sftp, remote_file):
            print(f"File transfer failed for {local_file}")
        
        sftp.close()
        print(f"File {local_file} transferred successfully to {remote_file}")
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

def scp_transfer_parallel(local_path, remote_path, remote_host, remote_port, username, password, m=5):
    # 获取所有文件
    all_files = [os.path.join(local_path, file) for file in os.listdir(local_path) if os.path.isfile(os.path.join(local_path, file))]
    n = len(all_files)
    a = n // m
    b = n % m
    
    # 创建文件传输任务
    tasks = []
    for i in range(m):
        start_index = i * a
        end_index = start_index + a
        tasks.extend([(all_files[j], os.path.normpath(os.path.join(remote_path, os.path.basename(all_files[j]))).replace("\\", "/")) for j in range(start_index, end_index)])

    # 添加剩余文件到最后一个任务
    if b > 0:
        tasks.extend([(all_files[j], os.path.normpath(os.path.join(remote_path, os.path.basename(all_files[j]))).replace("\\", "/")) for j in range(m * a, m * a + b)])

    # 并行传输文件
    with concurrent.futures.ThreadPoolExecutor(max_workers=m+1) as executor:
        futures = [executor.submit(scp_transfer_file, task, remote_path, remote_host, remote_port, username, password) for task in tasks]
        concurrent.futures.wait(futures)

if __name__ == "__main__":
    local_path = r"D:\software\27_nodejs\海外风景"  # 本地文件或目录路径
    remote_path = "/home/01_html/08_x/image/03_picTemp/海外风景"  # 远程服务器上的目标路径
    remote_host = "74.48.107.63"  # 远程服务器IP
    remote_port = 22  # 远程服务器SSH端口
    username = "root"  # SSH用户名
    password = "your_password"  # SSH密码

    scp_transfer_parallel(local_path, remote_path, remote_host, remote_port, username, password, m=5)