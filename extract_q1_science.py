import sys
import subprocess
import os
import glob

def install(package):
    subprocess.check_call([sys.executable, "-m", "pip", "install", package])

try:
    import PyPDF2
except ImportError:
    install("PyPDF2")
    import PyPDF2

pdf_dir = r"c:\Games\xampp\htdocs\ILikeSci\Curriculums\GRADE 4- QUARTER 1\Quarter 1\Science"
output_dir = r"c:\Games\xampp\htdocs\ILikeSci\extracted_texts_q1_science"

if not os.path.exists(output_dir):
    os.makedirs(output_dir)

pdf_files = glob.glob(os.path.join(pdf_dir, "*.pdf"))

for pdf_path in pdf_files:
    filename = os.path.basename(pdf_path)
    txt_filename = filename.replace(".pdf", ".txt")
    output_path = os.path.join(output_dir, txt_filename)
    
    print(f"Extracting {filename}...")
    try:
        reader = PyPDF2.PdfReader(pdf_path)
        text = ""
        for page in reader.pages:
            extracted = page.extract_text()
            if extracted:
                text += extracted + "\n"
        
        with open(output_path, "w", encoding="utf-8") as f:
            f.write(text)
        print(f"  -> Saved to {txt_filename}")
    except Exception as e:
        print(f"  -> Error extracting {filename}: {e}")

print("\nAll extractions completed!")
