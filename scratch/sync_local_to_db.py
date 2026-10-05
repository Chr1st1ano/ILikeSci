import os
import shutil
import sqlite3
import re

con = sqlite3.connect('ilikesci_db.sqlite')
con.row_factory = sqlite3.Row
rows = con.execute('SELECT id, file_type, original_name, filename, slides_dir FROM pptx_uploads').fetchall()

local_pdfs = [f for f in os.listdir('uploads/pptx') if f.endswith('.pdf')]
slides_dirs = [d for d in os.listdir('uploads/pptx/slides') if os.path.isdir(os.path.join('uploads/pptx/slides', d))]

print(f"Syncing local files to match DB ({len(rows)} presentations)...")

for r in rows:
    db_fn = r['filename']
    db_sdir = r['slides_dir'].strip('/\\')  # e.g. uploads/pptx/slides/slides_1790258255_922
    db_sdir_name = os.path.basename(db_sdir)
    
    # 1. Check if DB PDF filename exists
    target_pdf_path = os.path.join('uploads/pptx', db_fn)
    if not os.path.exists(target_pdf_path):
        # Find matching local PDF
        orig = r['original_name']
        clean_orig = re.sub(r'[^a-zA-Z0-9]', '', orig).lower()
        match_pdf = None
        for lp in local_pdfs:
            clean_lp = re.sub(r'[^a-zA-Z0-9]', '', lp).lower()
            if clean_orig in clean_lp or clean_lp in clean_orig:
                match_pdf = lp
                break
        if match_pdf:
            src_pdf = os.path.join('uploads/pptx', match_pdf)
            shutil.copy2(src_pdf, target_pdf_path)
            print(f"Copied PDF: {match_pdf} -> {db_fn}")
        else:
            print(f"WARN: No matching local PDF for {orig}")

    # 2. Check if DB slides directory exists and has slide_1.png
    target_sdir = os.path.join(db_sdir)
    target_s1 = os.path.join(target_sdir, 'slide_1.png')
    if not os.path.exists(target_s1):
        # Try to find corresponding local slides dir
        # Let's find source slide directory by matching timestamp from matching PDF
        match_sdir = None
        if 'match_pdf' in locals() and match_pdf:
            m_ts = re.search(r'pdf_(\d+)_', match_pdf)
            if m_ts:
                ts = m_ts.group(1)
                for sd in slides_dirs:
                    if ts in sd and os.path.exists(os.path.join('uploads/pptx/slides', sd, 'slide_1.png')):
                        match_sdir = sd
                        break
        
        # If not found by ts, search for Changes in Matter PPTX slides
        if not match_sdir and 'Changes in Matter' in r['original_name']:
            for sd in slides_dirs:
                if os.path.exists(os.path.join('uploads/pptx/slides', sd, 'slide_1.png')):
                    # Check text or slide count
                    match_sdir = sd

        if match_sdir:
            src_sdir = os.path.join('uploads/pptx/slides', match_sdir)
            if not os.path.exists(target_sdir):
                shutil.copytree(src_sdir, target_sdir)
                print(f"Copied slide dir: {match_sdir} -> {db_sdir_name}")
            else:
                for item in os.listdir(src_sdir):
                    s = os.path.join(src_sdir, item)
                    d = os.path.join(target_sdir, item)
                    if not os.path.exists(d):
                        shutil.copy2(s, d)
                print(f"Populated slides into: {db_sdir_name}")
        else:
            print(f"WARN: No matching slide dir found for {r['original_name']}")

print("Local sync complete.")
