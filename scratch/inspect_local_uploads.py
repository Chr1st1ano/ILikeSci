import os
import re

pdf_files = [f for f in os.listdir('uploads/pptx') if f.endswith('.pdf')]
slides_dirs = [d for d in os.listdir('uploads/pptx/slides') if os.path.isdir(os.path.join('uploads/pptx/slides', d))]

print("Sample PDF files:")
for f in pdf_files[:10]:
    print(" ", f)

print(f"\nTotal slides dirs: {len(slides_dirs)}")
for d in slides_dirs[:10]:
    s1 = os.path.exists(os.path.join('uploads/pptx/slides', d, 'slide_1.png'))
    print(" ", d, "slide_1 exists:", s1)
