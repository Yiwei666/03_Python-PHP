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
