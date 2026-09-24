import glob
import re

for f in sorted(glob.glob('*.html') + glob.glob('*.js') + glob.glob('*.php')):
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        lines = fp.readlines()
        for idx, line in enumerate(lines, 1):
            if 'section' in line.lower() and ('select' in line.lower() or 'filter' in line.lower() or 'dropdown' in line.lower() or 'option' in line.lower()):
                print(f"{f}:{idx}: {line.strip()[:100]}")
