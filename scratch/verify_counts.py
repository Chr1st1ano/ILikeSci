import openpyxl
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('scratch/extracted_students.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

print("Grade 4 sections:")
for sec in ['Maagap', 'Magalang', 'Masigasig', 'Masikap', 'Matatag', 'Matiyaga']:
    stus = [s for s in data['grade4'] if s['section'] == sec]
    males = [s for s in stus if s['gender'] == 'M']
    females = [s for s in stus if s['gender'] == 'F']
    print(f"  {sec:10s}: Total={len(stus):2d} (M={len(males):2d}, F={len(females):2d})")

print("\nGrade 6 sections:")
for sec in ['Pasteur', 'Tesla', 'Einstein', 'Newton', 'Aristotle', 'Faraday', 'Galilei']:
    stus = [s for s in data['grade6'] if s['section'] == sec]
    males = [s for s in stus if s['gender'] == 'M']
    females = [s for s in stus if s['gender'] == 'F']
    print(f"  {sec:10s}: Total={len(stus):2d} (M={len(males):2d}, F={len(females):2d})")
