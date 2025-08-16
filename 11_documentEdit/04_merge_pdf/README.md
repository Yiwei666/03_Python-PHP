# 1. 项目功能

按照多种排序方式合并 pdf 文件

# 2. 文件结构



# 3. 环境配置

## 1. `01_merged_pdf.py`



## 2. `02_merge_PDF_Pro.py`

### 1. 编程思路

```py
import os
from PyPDF2 import PdfMerger

def merge_pdfs_in_directory():
    # Get the current working directory
    current_directory = os.getcwd()
    
    # Create a PdfMerger object to hold the merged PDF
    merger = PdfMerger()
    
    # Get a list of all PDF files in the current directory
    pdf_files = [file for file in os.listdir(current_directory) if file.lower().endswith('.pdf')]
    
    if not pdf_files:
        print("No PDF files found in the current directory.")
        return
    
    # Sort the PDF files to merge them in alphabetical order
    pdf_files.sort()
    
    # Merge all the PDFs into the merger object
    for pdf_file in pdf_files:
        pdf_path = os.path.join(current_directory, pdf_file)
        merger.append(pdf_path)
    
    # Output the merged PDF to a new file
    output_file = "merged_output.pdf"
    with open(output_file, "wb") as output:
        merger.write(output)
    
    print(f"Merged {len(pdf_files)} PDF files into {output_file}.")

if __name__ == "__main__":
    merge_pdfs_in_directory()
```

请修改上述代码，实现以下需求：

1. 获取当前目录下所有文件名后缀为 `.pdf` 的文件名以及相应的修改日期
2. 对上述文件名列表支持 4 种排序方式，分别是按照名称递增、按照名称递减、按照修改日期递增、按照修改日期递减
3. 打印出上述 4 种排序方式及对应序号，提示用户输入序号选择按照哪种排序方式对列表中的 pdf 文件进行合并
4. 合并前打印出将使用的文件名顺序，以便用户进行检查和确认，使用 `y，n，q` 分别代表采用，不采用和结束程序运行，其余 符号均为非法输入。

输出满足上述需求的完整python代码。




