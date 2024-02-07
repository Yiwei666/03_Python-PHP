import os
from PyPDF2 import PdfReader, PdfWriter

def list_files():
    files = os.listdir('.')
    print("当前目录下的文件:")
    for file in files:
        print(file)

def decrypt_pdf(input_file_name, password):
    if not os.path.exists(input_file_name):
        print("文件不存在")
        return
    output_file_name = "decrypted_" + input_file_name
    with open(input_file_name, 'rb') as input_file, open(output_file_name, 'wb') as output_file:
        pdf_reader = PdfReader(input_file)
        if pdf_reader.is_encrypted:
            if not pdf_reader.decrypt(password):
                print("密码错误")
                return
        pdf_writer = PdfWriter()
        for page in pdf_reader.pages:
            pdf_writer.add_page(page)
        pdf_writer.write(output_file)
    print("解密完成。解密后的文件名为:", output_file_name)

def main():
    list_files()
    input_file_name = input("请输入待解密文件的名字: ")
    password = input("请输入密码: ")
    decrypt_pdf(input_file_name, password)

if __name__ == "__main__":
    main()
