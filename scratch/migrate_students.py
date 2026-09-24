import sqlite3
import json
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

DB_PATH = 'ilikesci_db.sqlite'
JSON_PATH = 'scratch/extracted_students.json'

if not os.path.exists(JSON_PATH):
    print(f"Error: {JSON_PATH} does not exist!")
    sys.exit(1)

with open(JSON_PATH, 'r', encoding='utf-8') as f:
    master_data = json.load(f)

g4_students = master_data['grade4']
g6_students = master_data['grade6']

print(f"Loaded {len(g4_students)} Grade 4 students and {len(g6_students)} Grade 6 students from JSON.")

conn = sqlite3.connect(DB_PATH)
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

# 1. Inspect before state
print("\n--- BEFORE DELETION ---")
for r in cursor.execute("SELECT grade, COUNT(*) FROM students GROUP BY grade").fetchall():
    print(f"Grade {r[0]}: {r[1]} students")

# 2. Get IDs of students in Grade 4 and Grade 6 currently
cursor.execute("SELECT id FROM students WHERE grade IN ('4', '6')")
old_student_ids = [row['id'] for row in cursor.fetchall()]
print(f"\nFound {len(old_student_ids)} old student records in Grade 4 and Grade 6 to remove: {old_student_ids}")

# 3. Remove associated recitation_records for these students
if old_student_ids:
    placeholders = ','.join('?' for _ in old_student_ids)
    cursor.execute(f"DELETE FROM recitation_records WHERE student_id IN ({placeholders})", old_student_ids)
    print(f"Removed recitation records for old Grade 4 and 6 students (rows affected: {cursor.rowcount})")

# 4. Remove old student_grades for Grade 4 and Grade 6
cursor.execute("DELETE FROM student_grades WHERE grade_level IN ('4', '6')")
print(f"Removed student_grades for old Grade 4 and 6 (rows affected: {cursor.rowcount})")

# 5. Remove students in Grade 4 and Grade 6 from students table
cursor.execute("DELETE FROM students WHERE grade IN ('4', '6')")
print(f"Removed old Grade 4 and 6 students from students table (rows affected: {cursor.rowcount})")

# 6. Insert Official Students from Bay Central Elementary School
# Assign IDs:
# Grade 4: 4001..4233
# Grade 6: 6001..6245

default_ww_highest = json.dumps([10, 10, 15, 10, 10, 20, 10, 15, 10, 20])
default_pt_highest = json.dumps([15, 15, 20, 15, 15, 30, 15, 20, 15, 30])
default_qa_highest = 50.0

g4_id_start = 4001
for idx, s in enumerate(g4_students):
    sid = g4_id_start + idx
    s['db_id'] = sid
    cursor.execute(
        "INSERT INTO students (id, name, grade, section, recitations, total_score, photo) VALUES (?, ?, ?, ?, 0, 0, NULL)",
        (sid, s['name'], '4', s['section'])
    )
    # Insert initial entry into student_grades for Q1
    cursor.execute(
        """INSERT INTO student_grades 
           (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws,
            pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade,
            ww_highest, pt_highest, qa_highest)
           VALUES (?, '4', ?, 1, ?, '[]', 0, 0, 0, '[]', 0, 0, 0, 0, 0, 0, 0, 60, ?, ?, ?)""",
        (s['name'], s['section'], s['gender'], default_ww_highest, default_pt_highest, default_qa_highest)
    )

print(f"Inserted {len(g4_students)} official Grade 4 students into students and student_grades.")

g6_id_start = 6001
for idx, s in enumerate(g6_students):
    sid = g6_id_start + idx
    s['db_id'] = sid
    cursor.execute(
        "INSERT INTO students (id, name, grade, section, recitations, total_score, photo) VALUES (?, ?, ?, ?, 0, 0, NULL)",
        (sid, s['name'], '6', s['section'])
    )
    # Insert initial entry into student_grades for Q1
    cursor.execute(
        """INSERT INTO student_grades 
           (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws,
            pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade,
            ww_highest, pt_highest, qa_highest)
           VALUES (?, '6', ?, 1, ?, '[]', 0, 0, 0, '[]', 0, 0, 0, 0, 0, 0, 0, 60, ?, ?, ?)""",
        (s['name'], s['section'], s['gender'], default_ww_highest, default_pt_highest, default_qa_highest)
    )

print(f"Inserted {len(g6_students)} official Grade 6 students into students and student_grades.")

conn.commit()

# 7. Verify final counts
print("\n--- AFTER INSERTION (FINAL DATABASE STATE) ---")
for r in cursor.execute("SELECT grade, COUNT(*), MIN(id), MAX(id) FROM students GROUP BY grade").fetchall():
    print(f"Grade {r[0]}: {r[1]} students (IDs {r[2]} to {r[3]})")

print("\n--- STUDENT_GRADES BREAKDOWN ---")
for r in cursor.execute("SELECT grade_level, section, gender, COUNT(*) FROM student_grades GROUP BY grade_level, section, gender ORDER BY grade_level, section, gender").fetchall():
    print(f"Grade {r[0]} Section {r[1]} [{r[2]}]: {r[3]} students")

conn.close()
print("\nMigration completed successfully!")
