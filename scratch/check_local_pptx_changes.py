import os
import re

pptx_files = [f for f in os.listdir('uploads/pptx') if f.endswith('.pptx')]
print("PPTX files in uploads/pptx/:", pptx_files)

# Check slides dirs for pptx
for p in pptx_files:
    clean = re.sub(r'[^a-zA-Z0-9]', '', p).lower()
    for d in os.listdir('uploads/pptx/slides'):
        if 'changes' in d.lower() or 'matter' in d.lower() or '1790' in d or '1789' in d:
            pass
