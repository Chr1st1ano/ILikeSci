import os
import glob

# Search for any slide_1.png in uploads/
slides = glob.glob('uploads/**/slide_1.png', recursive=True)
print(f"Total slide_1.png files found in uploads/: {len(slides)}")
for s in slides[:15]:
    print(" ", s)
