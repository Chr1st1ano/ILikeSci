import sqlite3
import os

con = sqlite3.connect('ilikesci_db.sqlite')
con.row_factory = sqlite3.Row
rows = con.execute('SELECT id, file_type, original_name, filename, slides_dir FROM pptx_uploads').fetchall()
print(f"Total rows in DB: {len(rows)}")

pdf_files = [f for f in os.listdir('uploads/pptx') if f.endswith('.pdf')]
print(f"Total PDF files in uploads/pptx/: {len(pdf_files)}")

for r in rows[:10]:
    pdf_path = os.path.join('uploads/pptx', r['filename'])
    pdf_exists = os.path.exists(pdf_path)
    sdir = r['slides_dir']
    slide1 = os.path.join(sdir, 'slide_1.png')
    slide1_exists = os.path.exists(slide1)
    print(f"ID {r['id']} ({r['file_type']}): {r['original_name']}")
    print(f"   filename: {r['filename']} -> exists: {pdf_exists}")
    print(f"   slides_dir: {sdir} -> slide_1 exists: {slide1_exists}")
