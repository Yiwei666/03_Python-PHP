import os
import random
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.application import MIMEApplication

def get_unsent_pdf_filenames(pdf_folder, sent_pdf_list_path):
    # 从文件夹中获取所有pdf文件名列表1
    all_pdf_files = [file for file in os.listdir(pdf_folder) if file.endswith('.pdf')]

    # 从已发送列表文件中读取已发送的pdf文件名列表2
    try:
        with open(sent_pdf_list_path, 'r') as file:
            sent_pdf_list = file.read().splitlines()
    except FileNotFoundError:
        sent_pdf_list = []

    # 获取还未发送的pdf文件名列表3（列表1减去列表2）
    unsent_pdf_list = list(set(all_pdf_files) - set(sent_pdf_list))

    return unsent_pdf_list

def send_email(sender_email, sender_password, receiver_emails, subject, body, pdf_folder, sent_pdf_list_path, base_url):
    # 设置邮件服务器和端口
    smtp_server = 'smtp.mail.yahoo.com'
    smtp_port = 587

    # 创建SMTP对象
    server = smtplib.SMTP(smtp_server, smtp_port)
    server.starttls()

    try:
        # 登录到邮箱
        server.login(sender_email, sender_password)

        # 获取未发送的pdf文件名列表
        unsent_pdf_list = get_unsent_pdf_filenames(pdf_folder, sent_pdf_list_path)

        if not unsent_pdf_list:
            print("没有未发送的PDF文件")
            return

        # 从未发送的pdf文件中随机选择一个
        selected_pdf = random.choice(unsent_pdf_list)
        attachment_path = os.path.join(pdf_folder, selected_pdf)

        #构造正文中pdf附件链接，需要位于循环外，否则报错
        body = body + "   附件链接：" + base_url + selected_pdf

        for receiver_email in receiver_emails:
            # 构建邮件
            message = MIMEMultipart()
            message['From'] = sender_email
            message['To'] = receiver_email
            message['Subject'] = subject

            # 添加正文
            message.attach(MIMEText(body, 'plain'))

            # 添加附件
            with open(attachment_path, 'rb') as file:
                attachment = MIMEApplication(file.read(), Name=os.path.basename(attachment_path))
                attachment['Content-Disposition'] = f'attachment; filename="{os.path.basename(attachment_path)}"'
                message.attach(attachment)

            # 发送邮件
            server.sendmail(sender_email, receiver_email, message.as_string())
            print(f"邮件发送成功，附件: {selected_pdf}")

        # 记录已发送的pdf文件名
        with open(sent_pdf_list_path, 'a') as file:
            file.write(selected_pdf + '\n')

    except Exception as e:
        print(f"邮件发送失败: {str(e)}")

    finally:
        # 关闭连接
        server.quit()

if __name__ == "__main__":
    # 设置发件人、收件人和附件路径
    sender_email = 'sender@yahoo.com'
    sender_password = 'aaaabbbbccccdddd'             # 16位应用程序专用密码
    receiver_emails = ['receiver1@gmail.com', 'receiver2@gmail.com']  # 收件者列表
    subject = 'American History The Modern Era Since 1865'
    body = "American History The Modern Era Since 1865, Student Edition (McGraw-Hill)"
    
    # 设置文件夹和已发送列表文件的路径变量
    pdf_folder = '/home/01_html/04_glencoe'
    sent_pdf_list_path = '/home/01_html/04_emailPDF.txt'

    # base_url为 https://domain.com/04_glencoe/glencoe-Pages_991_993.pdf 文件名前面部分，构造附件访问链接，位于正文最后部分
    base_url = "https://domain.com/04_glencoe/"

    # 发送邮件
    send_email(sender_email, sender_password, receiver_emails, subject, body, pdf_folder, sent_pdf_list_path, base_url)
