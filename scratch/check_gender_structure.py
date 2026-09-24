import openpyxl
import sys

sys.stdout.reconfigure(encoding='utf-8')

def inspect_sheet_structure(filename):
    wb = openpyxl.load_workbook(filename, data_only=True)
    print("=" * 60)
    print("FILE:", filename)
    print("=" * 60)
    for sheetname in wb.sheetnames:
        ws = wb[sheetname]
        print(f"\n--- Sheet: '{sheetname}' ---")
        rows = list(ws.iter_rows(values_only=True))
        for idx, row in enumerate(rows, 1):
            non_empty = [c for c in row if c is not None and str(c).strip() != '']
            if not non_empty:
                continue
            first_col = str(row[0]).strip() if row[0] is not None else ""
            second_col = str(row[1]).strip() if len(row) > 1 and row[1] is not None else ""
            third_col = str(row[2]).strip() if len(row) > 2 and row[2] is not None else ""
            
            # Print rows that have headers, text in col 1 without number, or col 1 == '1'
            if any(k in " ".join(map(str, non_empty)).upper() for k in ['MALE', 'FEMALE', 'GRADE', 'PREPARED', 'TEACHER', 'NAME']):
                print(f"  Row {idx:2d} [SPECIAL]: {non_empty[:4]}")
            elif first_col in ['1', 1]:
                print(f"  Row {idx:2d} [NUM 1]: {non_empty[:4]}")

inspect_sheet_structure('masterlists/Grade-4-Masterlist-BCES-2026-2027.xlsx')
inspect_sheet_structure('masterlists/GRADE-6-MASTERLIST.xlsx')
