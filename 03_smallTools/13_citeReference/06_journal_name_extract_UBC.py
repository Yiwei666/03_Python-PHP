from bs4 import BeautifulSoup

def extract_and_save_journal_data(input_file, output_file):
    """
    Extracts specific data from the journal.html file and writes it to journal.txt.
    
    :param input_file: Path to the input HTML file.
    :param output_file: Path to the output text file.
    """
    try:
        with open(input_file, "r", encoding="utf-8") as file:
            soup = BeautifulSoup(file, "html.parser")
        
        # Locate the div with id="jaresults"
        div = soup.find("div", id="jaresults")
        if not div:
            print("No div with id='jaresults' found.")
            return
        
        # Locate the table within the div
        table = div.find("table")
        if not table:
            print("No table found inside the div.")
            return
        
        # Locate tbody within the table
        tbody = table.find("tbody")
        if not tbody:
            print("No tbody found inside the table.")
            return
        
        # List to store the extracted results
        results = []
        
        # Iterate over tr elements within tbody
        rows = tbody.find_all("tr")
        for tr in rows:
            # Find all td elements within the tr
            tds = tr.find_all("td")
            # Check if there are exactly two non-empty td elements
            if len(tds) == 2 and tds[0].get_text(strip=True) and tds[1].get_text(strip=True):
                # Combine second td and first td with "/"
                combined = f"{tds[1].get_text(strip=True)}/{tds[0].get_text(strip=True)}"
                results.append(combined)
        
        # Write the results to the output file
        with open(output_file, "w", encoding="utf-8") as output:
            output.write("\n".join(results))
        
        print(f"Extracted data has been saved to {output_file}.")
    
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    input_file = "journal.html"  # Adjust the file path if necessary
    output_file = "journal.txt"
    extract_and_save_journal_data(input_file, output_file)
