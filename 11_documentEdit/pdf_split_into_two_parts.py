from PyPDF2 import PdfReader, PdfWriter

def split_pdf_into_two_parts():
    input_pdf = input("请输入PDF文件的绝对路径：")
    pdf = PdfReader(input_pdf)

    total_pages = len(pdf.pages)
    
    if total_pages < 2:
        print("PDF文件的总页数必须大于1才能拆分成两部分。")
        return

    while True:
        page_range = input("请输入第一部分的页码范围（格式示例：1 或 1-10）: ")
        
        if "-" in page_range:
            start, end = map(int, page_range.split('-'))
            if start < 1 or end > total_pages or start > end:
                print("输入错误，请重新输入有效的页码范围。")
            else:
                break
        else:
            page_num = int(page_range)
            if 1 <= page_num <= total_pages:
                start, end = page_num, page_num
                break
            else:
                print("输入错误，请重新输入有效的页码范围。")

    pdf_writer_part1 = PdfWriter()
    pdf_writer_part2 = PdfWriter()

    for page_num, page in enumerate(pdf.pages):
        if start <= page_num + 1 <= end:
            pdf_writer_part1.add_page(page)
        else:
            pdf_writer_part2.add_page(page)

    output_pdf_part1 = f"part1_{start}-{end}.pdf"
    output_pdf_part2 = f"part2_{total_pages - (end - start) + 1}-{total_pages}.pdf"

    with open(output_pdf_part1, 'wb') as output_file_part1:
        pdf_writer_part1.write(output_file_part1)

    with open(output_pdf_part2, 'wb') as output_file_part2:
        pdf_writer_part2.write(output_file_part2)

if __name__ == "__main__":
    split_pdf_into_two_parts()
