import sys
import subprocess

def install(package):
    subprocess.check_call([sys.executable, "-m", "pip", "install", package])

try:
    import PyPDF2
except ImportError:
    install("PyPDF2")
    import PyPDF2

try:
    reader = PyPDF2.PdfReader("MATATAG-Science-CG-Grade-4-and-7.pdf")
    text = ""
    for page in reader.pages:
        if page.extract_text():
            text += page.extract_text() + "\n"
    
    with open("pdf_text.txt", "w", encoding="utf-8") as f:
        f.write(text)
    print("Done extracting to pdf_text.txt")
except Exception as e:
    print("Error:", e)
