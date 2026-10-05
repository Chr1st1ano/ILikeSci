import os
import glob
import re

local_pdfs = [f for f in os.listdir('uploads/pptx') if f.endswith('.pdf')]
slides_dirs = [d for d in os.listdir('uploads/pptx/slides') if os.path.isdir(os.path.join('uploads/pptx/slides', d))]

# Check if there are slide dirs with timestamp 1789927448... or similar
for lp in local_pdfs[:10]:
    ts_match = re.search(r'pdf_(\d+)_', lp)
    if ts_match:
        ts = ts_match.group(1)
        matching_dirs = [d for d in slides_dirs if ts in d]
        print(f"PDF {lp} (ts {ts}) -> matching slide dirs: {matching_dirs}")
