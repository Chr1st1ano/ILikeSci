import sys
import subprocess
import os
import glob
import re

def install(package):
    subprocess.check_call([sys.executable, "-m", "pip", "install", package])

try:
    import pymysql
except ImportError:
    install("pymysql")
    import pymysql

# Database connection
db = pymysql.connect(
    host="localhost",
    user="root",
    password="",
    database="ilikesci_db"
)
cursor = db.cursor()

txt_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "extracted_texts_q1_science")
txt_files = glob.glob(os.path.join(txt_dir, "*.txt"))

for filepath in txt_files:
    filename = os.path.basename(filepath)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 1. Extract Topic
    # We look for "Lesson Title/ Topic:" and grab the text until the next typical field like "Name:" or "I. Activity"
    topic_match = re.search(r"Lesson Title/\s*Topic:\s*(.*?)(?:\nName:|\nI\. Activity|\n\n)", content, re.DOTALL | re.IGNORECASE)
    
    topic_name = "Unknown Topic"
    if topic_match:
        topic_name = topic_match.group(1).replace('\n', ' ').strip()
        # Clean up excessive spaces
        topic_name = re.sub(r'\s+', ' ', topic_name)
    else:
        # Fallback to filename if not found
        topic_name = "Science 4: " + filename.replace('.txt', '')
        
    print(f"File: {filename} -> Topic: {topic_name}")
    
    # Insert topic into database if not exists
    cursor.execute("SELECT id FROM topics WHERE topic_name = %s", (topic_name,))
    topic_row = cursor.fetchone()
    if not topic_row:
        cursor.execute("INSERT INTO topics (grade, topic_name) VALUES (%s, %s)", ("Grade 4", topic_name))
        db.commit()
    
    # 2. Extract Questions
    question_matches = re.findall(r"(?:^|\n)\s*(\d+\.)\s+(.*?\?)", content)
    
    lesson_questions = []
    import json
    for match in question_matches:
        q_text = match[1].replace('\n', ' ').strip()
        q_text = re.sub(r'\s+', ' ', q_text)
        
        lesson_questions.append({
            "question": q_text,
            "options": {"a": "", "b": "", "c": "", "d": ""},
            "type": "open_ended"
        })
        
        # Insert question into database
        cursor.execute("SELECT id FROM questions WHERE question_text = %s", (q_text,))
        if not cursor.fetchone():
            cursor.execute("INSERT INTO questions (grade, topic, difficulty, question_text) VALUES (%s, %s, %s, %s)",
                           ("Grade 4", topic_name, "Medium", q_text))
            db.commit()
            print(f"  -> Inserted Q: {q_text}")

    # 3. Insert Lesson
    cursor.execute("SELECT id FROM curriculum_lessons WHERE topic = %s", (topic_name,))
    lesson_row = cursor.fetchone()
    if not lesson_row:
        query = "INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (%s, %s, %s, %s, %s, %s, %s)"
        values = ("4", "1", "0", topic_name, content[:5000], json.dumps(["Extracted"]), json.dumps(lesson_questions))
        cursor.execute(query, values)
        db.commit()
        print(f"  -> Inserted Lesson: {topic_name}")

db.close()
print("Import to database completed!")
