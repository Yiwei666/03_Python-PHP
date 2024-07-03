import os
import PyPDF2

def list_pdf_files():
    """列出当前目录下的所有PDF文件"""
    print("Available PDF files:")
    pdf_files = [f for f in os.listdir() if f.endswith('.pdf')]
    for file in pdf_files:
        print(file)
    return pdf_files

def parse_pages(page_ranges, num_pages):
    """解析和验证页码范围，返回有效的页码列表"""
    pages_to_extract = set()
    ranges = page_ranges.split(',')
    for r in ranges:
        if '-' in r:
            start, end = map(int, r.split('-'))
            if start <= end and 1 <= start <= num_pages and 1 <= end <= num_pages:
                pages_to_extract.update(range(start-1, end))
        else:
            page = int(r)
            if 1 <= page <= num_pages:
                pages_to_extract.add(page-1)
    return sorted(pages_to_extract)

def extract_and_merge_pdf(input_file, page_ranges):
    """提取指定页码并合并成新的PDF文件"""
    pdf_reader = PyPDF2.PdfReader(input_file)
    pdf_writer = PyPDF2.PdfWriter()

    num_pages = len(pdf_reader.pages)
    pages_to_extract = parse_pages(page_ranges, num_pages)

    for page_num in pages_to_extract:
        page = pdf_reader.pages[page_num]
        pdf_writer.add_page(page)

    # 使用输入的页码范围字符串作为文件名的一部分
    output_filename = f'extracted_pages_{page_ranges}.pdf'
    
    with open(output_filename, 'wb') as output_pdf:
        pdf_writer.write(output_pdf)
    
    print(f"New PDF file '{output_filename}' has been created with pages: {page_ranges}")

def main():
    pdf_files = list_pdf_files()
    if not pdf_files:
        print("No PDF files found in the current directory.")
        return

    input_file = input("Enter the name of the PDF file to operate on: ")
    if input_file not in pdf_files:
        print("File not found. Please run the program again and enter a valid file name.")
        return

    page_ranges = input("Enter the page numbers to extract (e.g., '1,5,7-10,21-24'): ")
    extract_and_merge_pdf(input_file, page_ranges)

if __name__ == "__main__":
    main()
