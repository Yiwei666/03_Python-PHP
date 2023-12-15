import os
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.application import MIMEApplication

def send_email(sender_email, sender_password, receiver_email, subject, body, attachment_path):
    smtp_server = 'smtp.gmail.com'
    smtp_port = 587

    server = smtplib.SMTP(smtp_server, smtp_port)
    server.starttls()

    try:
        server.login(sender_email, sender_password)

        message = MIMEMultipart()
        message['From'] = sender_email
        message['To'] = receiver_email
        message['Subject'] = subject
        message.attach(MIMEText(body, 'plain'))

        if attachment_path:
            with open(attachment_path, 'rb') as file:
                attachment = MIMEApplication(file.read(), Name=os.path.basename(attachment_path))
                attachment['Content-Disposition'] = f'attachment; filename="{os.path.basename(attachment_path)}"'
                message.attach(attachment)

        server.sendmail(sender_email, receiver_email, message.as_string())
        print("邮件发送成功")

    except Exception as e:
        print(f"邮件发送失败: {str(e)}")

    finally:
        server.quit()

def get_attachment_path():
    attachment_input = input("是否添加附件？输入附件的绝对路径或输入 'n' 或 'N' 跳过: ")
    if attachment_input.lower() in ('n', 'no'):
        return None
    elif os.path.exists(attachment_input):
        return attachment_input
    else:
        print("文件路径不存在，请重新输入。")
        return get_attachment_path()

def get_receiver_email():
    receiver_email = input("请输入收件人的邮件地址: ")
    return receiver_email

def get_email_subject():
    return input("请输入邮件主题: ")

def get_email_body():
    return input("请输入邮件正文: ")

if __name__ == "__main__":
    # 设置发件人等信息
    sender_email = 'sender@gmail.com'
    sender_password = 'your_generated_app_password'

    # 获取收件人、主题、正文和附件路径
    receiver_email = get_receiver_email()
    subject = get_email_subject()
    body = get_email_body()
    attachment_path = get_attachment_path()

    # 发送邮件
    send_email(sender_email, sender_password, receiver_email, subject, body, attachment_path)

