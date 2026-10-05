import os
import sqlite3
import re

con = sqlite3.connect('ilikesci_db.sqlite')
con.row_factory = sqlite3.Row
rows = con.execute('SELECT id, file_type, original_name, filename, slides_dir FROM pptx_uploads').fetchall()

# List local pdf files
local_pdfs = [f for f in os.listdir('uploads/pptx') if f.endswith('.pdf')]
# List local slides dirs with slide_1.png
local_slides = [d for d in os.listdir('uploads/pptx/slides') if os.path.exists(os.path.join('uploads/pptx/slides', d, 'slide_1.png'))]

print(f"Found {len(local_pdfs)} local PDFs and {len(local_slides)} valid slide folders.")

# For each row in pptx_uploads, see if we can find matching local PDF and matching local slide folder
for r in rows:
    orig = r['original_name']
    # Match PDF
    clean_orig = re.sub(r'[^a-zA-Z0-9]', '', orig).lower()
    matching_pdf = None
    for lp in local_pdfs:
        clean_lp = re.sub(r'[^a-zA-Z0-9]', '', lp).lower()
        if clean_orig in clean_lp or clean_lp in clean_orig:
            matching_pdf = lp
            break
    print(f"DB ID {r['id']}: {orig}")
    print(f"   DB filename: {r['filename']} | Local matching PDF: {matching_pdf}")
    print(f"   DB slides_dir: {r['slides_dir']}")
