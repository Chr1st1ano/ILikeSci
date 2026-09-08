import os
import re
import json
import mysql.connector
from docx import Document
from mysql.connector import Error

# Configuration for Central Elementary School Database
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'ilikesci_db'
}

CURRICULUM_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "Curriculums")

def connect_db():
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except Error as e:
        print(f"Database Error: {e}")
        return None

def extract_text_from_docx(path):
    try:
        doc = Document(path)
        text = [para.text for para in doc.paragraphs]
        # Extract text from tables as well
        for table in doc.tables:
            for row in table.rows:
                for cell in row.cells:
                    text.append(cell.text)
        return "\n".join(text)
    except Exception as e:
        print(f"Error reading {path}: {e}")
        return ""

def parse_questions(text):
    questions = []
    lines = text.split('\n')
    current_q = None
    
    for line in lines:
        line = line.strip()
        if not line: continue
        
        # Detect Question (Number followed by text, e.g., "1. What is...")
        q_match = re.match(r'^(\d+)\.\s+(.+)', line)
        if q_match:
            if current_q: 
                questions.append(current_q)
            current_q = {'question': q_match.group(2), 'options': {'a': '', 'b': '', 'c': '', 'd': ''}, 'type': 'multiple_choice'}
            continue
            
        # Detect Options (a. option text)
        if current_q:
            opt_match = re.match(r'^([a-dA-D])\.\s*(.+)', line)
            if opt_match:
                opt_letter = opt_match.group(1).lower()
                current_q['options'][opt_letter] = opt_match.group(2)
            else:
                # Append extra text to the question itself if it's not an option
                if len(line) > 5 and not current_q['options']['a']:
                    current_q['question'] += " " + line
    
    if current_q: 
        questions.append(current_q)
        
    # Process and classify valid questions
    valid_questions = []
    for q in questions:
        has_options = any(q['options'].values())
        q_text_lower = q['question'].lower()
        
        if has_options:
            q['type'] = 'multiple_choice'
            valid_questions.append(q)
        else:
            # No options found. Let's classify based on keywords or patterns
            if "____" in q['question'] or "identify" in q_text_lower or "what is" in q_text_lower:
                q['type'] = 'identification'
            elif "enumerate" in q_text_lower or "list" in q_text_lower or "give" in q_text_lower:
                q['type'] = 'enumeration'
            else:
                q['type'] = 'open_ended'
                
            # Only append if it looks like a real question (at least 15 chars)
            if len(q['question']) > 15:
                valid_questions.append(q)
            
    return valid_questions

def import_curriculum():
    conn = connect_db()
    if not conn: return
    cursor = conn.cursor()

    print("--- ILikeSci Curriculum Importer ---")
    print(f"Scanning: {CURRICULUM_PATH}")

    files_processed = 0
    questions_found = 0

    for root, dirs, files in os.walk(CURRICULUM_PATH):
        for file in files:
            if "science" in file.lower() and file.lower().endswith(".docx"):
                full_path = os.path.join(root, file)
                print(f"Processing: {file}...")
                
                text = extract_text_from_docx(full_path)
                if not text: continue
                
                questions = parse_questions(text)
                files_processed += 1
                
                # Extract Grade and Quarter
                grade = "4"
                if "Grade 5" in root.upper() or "GRADE 5" in file.upper(): grade = "5"
                elif "Grade 6" in root.upper() or "GRADE 6" in file.upper(): grade = "6"
                
                quarter = "1"
                if "QUARTER 2" in root.upper() or "Q2" in file.upper(): quarter = "2"
                elif "QUARTER 3" in root.upper() or "Q3" in file.upper(): quarter = "3"
                elif "QUARTER 4" in root.upper() or "Q4" in file.upper(): quarter = "4"
                
                topic = file.replace(".docx", "").replace("DLL_", "").replace("PT_", "")
                
                try:
                    # Insert Lesson
                    query = "INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (%s, %s, %s, %s, %s, %s, %s)"
                    values = (grade, quarter, "0", topic, text[:5000], json.dumps(["Extracted"]), json.dumps(questions))
                    cursor.execute(query, values)
                    
                    # Insert Questions into the general pool
                    for q in questions:
                        q_query = "INSERT INTO questions (grade, topic, difficulty, question_text) VALUES (%s, %s, %s, %s)"
                        q_text = q['question']
                        if q['type'] == 'multiple_choice':
                            q_text += f" | A: {q['options']['a']} B: {q['options']['b']} C: {q['options']['c']} D: {q['options']['d']}"
                        
                        cursor.execute(q_query, (grade, topic, "Medium", q_text))
                    
                    questions_found += len(questions)
                except Error as e:
                    print(f"DB Insert Error for {file}: {e}")

    conn.commit()
    cursor.close()
    conn.close()
    
    print("\n" + "="*40)
    print(f"IMPORT COMPLETE!")
    print(f"Files Processed: {files_processed}")
    print(f"Total Questions Imported: {questions_found}")
    print("="*40)
    print("You can now see these in ILikeSci > Assessment > Select Topic")

if __name__ == "__main__":
    import_curriculum()
