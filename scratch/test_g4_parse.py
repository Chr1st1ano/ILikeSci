import openpyxl
import re
import sys
import unicodedata

sys.stdout.reconfigure(encoding='utf-8')

def clean_name(raw_name):
    if raw_name is None:
        return ""
    # Normalize unicode (combining diacritics etc.)
    name = unicodedata.normalize('NFC', str(raw_name))
    name = name.strip()
    # Remove leading/trailing tabs, spaces, quotes
    name = re.sub(r'[\t\r\n]+', ' ', name)
    name = re.sub(r'\s+', ' ', name)
    # Remove trailing hyphen like ", -"
    name = re.sub(r',\s*-\s*$', '', name)
    name = name.strip()
    return name

def is_header_or_footer(text):
    t = text.upper()
    keywords = [
        'GRADE 4', 'GRADE 6', 'G4', 'G6', 'PREPARED BY', 'TEACHER', 'ADVISER',
        'SY 202', 'NAME OF LEARNER', 'MALE', 'FEMALE', 'LEARNERS', 'SF1', 'BCES'
    ]
    return any(k in t for k in keywords)

def parse_grade4():
    wb = openpyxl.load_workbook('masterlists/Grade-4-Masterlist-BCES-2026-2027.xlsx', data_only=True)
    all_students = []
    
    for sheetname in wb.sheetnames:
        ws = wb[sheetname]
        # Section name from sheetname: e.g. "G4 Maagap" -> "Maagap" (or keep full sheet name?)
        # Let's see what section name is best: e.g. "Maagap" or "G4 Maagap"
        section = sheetname.replace('G4', '').strip()
        
        current_gender = None
        sheet_students = []
        
        for r in range(1, ws.max_row + 1):
            row_vals = [ws.cell(row=r, column=c).value for c in range(1, ws.max_column + 1)]
            # filter non-empty
            non_empty = [(idx+1, c) for idx, c in enumerate(row_vals) if c is not None and str(c).strip() != '']
            if not non_empty:
                continue
            
            row_str = " ".join(str(c[1]).strip() for c in non_empty).upper()
            
            # Check footer
            if any(k in row_str for k in ['PREPARED BY', 'TEACHER', 'ADVISER']):
                break
                
            # Check explicit gender in row
            if 'MALE' in row_str or 'BOY' in row_str:
                current_gender = 'M'
                continue
            if 'FEMALE' in row_str or 'GIRL' in row_str:
                current_gender = 'F'
                continue
                
            if is_header_or_footer(row_str) and not any(char.isdigit() for char in str(non_empty[0][1])):
                continue
                
            # Check columns for student data
            # Typically col 1 is number, col 2 is Name, col 3 is Gender (M/F) or None
            # Let's inspect
            val1 = str(row_vals[0]).strip() if row_vals[0] is not None else ""
            val2 = str(row_vals[1]).strip() if len(row_vals) > 1 and row_vals[1] is not None else ""
            val3 = str(row_vals[2]).strip() if len(row_vals) > 2 and row_vals[2] is not None else ""
            
            num = None
            name = None
            gender = current_gender
            
            if val1.isdigit() and val2 and not is_header_or_footer(val2):
                num = int(val1)
                name = clean_name(val2)
                if val3 in ['M', 'F']:
                    gender = val3
            elif val2 and not val1 and not is_header_or_footer(val2):
                # Maybe row has no number in col 1 or col 2 is header
                continue
            elif val1 and not val1.isdigit() and not is_header_or_footer(val1):
                # In some sheets, maybe col 1 is name?
                # Let's check
                pass
            
            if name:
                # If gender is not specified by column, check if number restarted at 1
                if val3 not in ['M', 'F'] and current_gender is None:
                    # check default
                    pass
                sheet_students.append({
                    'num': num,
                    'name': name,
                    'gender': gender or 'M',
                    'section': section,
                    'sheet': sheetname,
                    'row': r,
                    'raw': row_vals[:4]
                })
        
        all_students.append((sheetname, section, sheet_students))
    return all_students

g4 = parse_grade4()
for sname, sec, students in g4:
    print(f"Section {sec} ({sname}): {len(students)} students")
    for s in students[:3]:
        print(f"  {s['num']}: {s['name']} ({s['gender']})")
    print(f"  ...")
    for s in students[-2:]:
        print(f"  {s['num']}: {s['name']} ({s['gender']})")
