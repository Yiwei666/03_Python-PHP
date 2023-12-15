# 项目功能

在linux系统中使用python脚本发送e-mail

# 文件结构

🟢 **01_emailSend.py**

- 上述脚本在使用时需要在终端中按照提示输入：
  -  收件人邮箱地址
  -  主题和正文
  -  是否添加附件以及附件的绝对地址

```py
# 获取收件人、主题、正文和附件路径
receiver_email = get_receiver_email()
subject = get_email_subject()
body = get_email_body()
attachment_path = get_attachment_path()
```


### 1. 选择发送邮件的SMTP服务器

- 需要在 `send_email()`函数中指定`smtp_server`和`smtp_port`，默认发送邮件采用Gmail的SMTP服务器，相应设置如下

```py
def send_email(sender_email, sender_password, receiver_email, subject, body, attachment_path):
    smtp_server = 'smtp.gmail.com'
    smtp_port = 587
```

在这段代码中，smtp_server 和 smtp_port 是用于配置邮件发送的SMTP服务器和端口的参数。

`smtp_server`: 这是用于发送邮件的SMTP服务器的地址。在这里，smtp.gmail.com 是谷歌提供的SMTP服务器地址，用于发送Gmail邮件。SMTP服务器负责接收你的邮件，并将其传递到接收方的电子邮件服务器。

`smtp_port`: 这是SMTP服务器的端口号。在这里，587 是TLS加密的SMTP端口。TLS（Transport Layer Security）是一种安全传输层协议，用于在客户端和服务器之间加密通信。这通常是为了确保邮件传输的安全性。

下面是一些常见邮件提供商的 `smtp_server`和`smtp_port`

```
    Hotmail/Outlook:
        SMTP服务器: smtp.live.com
        SMTP端口: 587 (使用STARTTLS加密)

    ProtonMail:
        ProtonMail使用自己的加密协议，而不是传统的SMTP。因此，通常不需要手动配置SMTP服务器和端口。ProtonMail提供了加密的端到端电子邮件服务。

    Yahoo:
        SMTP服务器: smtp.mail.yahoo.com
        SMTP端口: 587 (使用STARTTLS加密)
```



### 2. 生成应用程序密码

应用程序专用密码（App Password）是一种由一些在线服务提供商提供的安全功能，用于允许应用程序或脚本代表用户进行身份验证，而无需使用用户的主要账户密码。

这种类型的密码是为了提高安全性，特别是在需要通过应用程序或脚本进行身份验证的情况下。

- 注意：**设置发件人信息以及设置邮箱密码时，需要采用应用程序专用密码，且发件邮箱需要与上述SMTP服务器信息一致**

```py
# 设置发件人等信息
sender_email = 'sender@gmail.com'
sender_password = 'your_generated_app_password'
```

在提供的代码中，`sender_password` 变量用于存储为您的 Gmail 账户生成的应用程序专用密码。

如果您在 Gmail 账户上启用了双因素身份验证（2FA），您需要生成一个“应用程序密码”以在您的脚本中使用。

以下是生成应用程序密码的步骤：

- 转到您的 Google 帐户设置：https://myaccount.google.com/
- 在左侧导航面板中，单击`安全性`。
- 在“登录到 Google”部分下，找到`应用密码`选项。
- 如果提示，登录到您的 Google 帐户。
- 在“应用密码”页面上，选择要为其生成密码的应用程序和设备。
- 单击“生成”。
- 复制生成的应用程序密码，并用它替换 `sender_password` 变量中的 `your_generated_app_password`。
- 请注意，这个应用程序密码是一个16位的长、随机生成的密码，专门用于此应用程序，并将用于身份验证，代替您正常的 Google 帐户密码。
- 如果您怀疑密码已泄漏，您随时可以撤销应用程序密码并生成一个新的。








