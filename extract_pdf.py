import os
import sys
import subprocess

def install(package):
    subprocess.check_call([sys.executable, "-m", "pip", "install", package])

try:
    import PyPDF2
except ImportError:
    install("PyPDF2")
    import PyPDF2

pdf_path = sys.argv[1] if len(sys.argv) > 1 else "MATATAG-Science-CG-Grade-4-and-7.pdf"

if not os.path.isabs(pdf_path):
    candidates = [
        os.path.join(os.path.dirname(os.path.abspath(__file__)), pdf_path),
        os.path.join(os.path.dirname(os.path.abspath(__file__)), "pdfs", pdf_path)
    ]
    for c in candidates:
        if os.path.exists(c):
            pdf_path = c
            break

if not os.path.exists(pdf_path):
    print(f"PDF file not found: {pdf_path}")
    print("Usage: python extract_pdf.py [path_to_pdf]")
    sys.exit(0)

try:
    reader = PyPDF2.PdfReader(pdf_path)
    text = ""
    for page in reader.pages:
        extracted = page.extract_text()
        if extracted:
            text += extracted + "\n"
    
    out_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pdf_text.txt")
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(text)
    print(f"Done extracting {len(reader.pages)} pages to {out_path}")
except Exception as e:
    print("Error:", e)
