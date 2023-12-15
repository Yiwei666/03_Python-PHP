import os
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.application import MIMEApplication

def send_email(sender_email, sender_password, receiver_email, subject, body, attachment_path):
    # 设置邮件服务器和端口
    smtp_server = 'smtp.gmail.com'
    smtp_port = 587

    # 创建SMTP对象
    server = smtplib.SMTP(smtp_server, smtp_port)
    server.starttls()

    try:
        # 登录到邮箱
        server.login(sender_email, sender_password)

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
        print("邮件发送成功")

    except Exception as e:
        print(f"邮件发送失败: {str(e)}")

    finally:
        # 关闭连接
        server.quit()

# 设置发件人、收件人和附件路径
sender_email = 'sender@gmail.com'                          # 发送者邮箱
sender_password = 'your_generated_app_password'            # 使用生成的应用密码
receiver_email = 'reciever@gmail.com'                      # 收件者
subject = 'Test Email with Attachment'
body = 'This is a test email with attachment.'
attachment_path = '/path/to/your/file.txt'  # 附件路径，绝对路径和相对路径皆可

# 发送邮件
send_email(sender_email, sender_password, receiver_email, subject, body, attachment_path)
