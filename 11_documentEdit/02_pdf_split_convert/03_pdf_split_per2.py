from PyPDF2 import PdfReader, PdfWriter

def split_pdf_to_two_pages():
    input_pdf = input("请输入PDF文件的绝对路径：")
    pdf = PdfReader(input_pdf)

    total_pages = len(pdf.pages)

    for page_num in range(0, total_pages, 2):
        pdf_writer = PdfWriter()

        # Add the current page
        pdf_writer.add_page(pdf.pages[page_num])

        # Check if there is another page
        if page_num + 1 < total_pages:
            pdf_writer.add_page(pdf.pages[page_num + 1])

        output_pdf = f"SAT-down-Pages_{page_num + 1}_{min(page_num + 2, total_pages)}.pdf"
        
        with open(output_pdf, 'wb') as output_file:
            pdf_writer.write(output_file)

if __name__ == "__main__":
    split_pdf_to_two_pages()
