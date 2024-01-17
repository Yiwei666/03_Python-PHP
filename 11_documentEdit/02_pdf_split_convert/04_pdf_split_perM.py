from PyPDF2 import PdfReader, PdfWriter

def split_pdf_to_pages():
    input_pdf = input("请输入PDF文件的绝对路径：")
    m = int(input("请输入每组包含的页数（m应大于等于1）："))
    
    if m < 1:
        print("错误：每组包含的页数必须大于等于1。")
        return

    pdf = PdfReader(input_pdf)
    total_pages = len(pdf.pages)

    for start_page in range(0, total_pages, m):
        pdf_writer = PdfWriter()

        # Add pages to the current chunk
        for page_num in range(start_page, min(start_page + m, total_pages)):
            pdf_writer.add_page(pdf.pages[page_num])

        output_pdf = f"glencoe-Pages_{start_page + 1}_{min(start_page + m, total_pages)}.pdf"

        with open(output_pdf, 'wb') as output_file:
            pdf_writer.write(output_file)

if __name__ == "__main__":
    split_pdf_to_pages()
