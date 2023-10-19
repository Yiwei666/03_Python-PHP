from PyPDF2 import PdfReader, PdfWriter

def split_pdf_to_individual_pages():
    input_pdf = input("请输入PDF文件的绝对路径：")
    pdf = PdfReader(input_pdf)

    for page_num, page in enumerate(pdf.pages):
        pdf_writer = PdfWriter()
        pdf_writer.add_page(page)

        output_pdf = f"page_{page_num + 1}.pdf"  # 使用原文件中的页数来命名文件
        with open(output_pdf, 'wb') as output_file:
            pdf_writer.write(output_file)

if __name__ == "__main__":
    split_pdf_to_individual_pages()
