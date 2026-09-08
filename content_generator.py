import os
import sys
import json
import sqlite3

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SQLITE_DB = os.path.join(BASE_DIR, "ilikesci_db.sqlite")

# Configuration for Central Elementary School Database (MySQL fallback)
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'ilikesci_db'
}

def connect_db():
    try:
        import mysql.connector
        from mysql.connector import Error
        connection = mysql.connector.connect(**DB_CONFIG)
        if connection.is_connected():
            return connection
    except Exception as e:
        return None

def generate_lesson_material(grade, topic):
    """
    This is a template generator. 
    In a real scenario, you could integrate OpenAI/Gemini API here 
    to generate content automatically.
    """
    print(f"\n--- Generating Materials for Grade {grade}: {topic} ---")
    
    # Example template for a lesson
    lesson = {
        "title": topic,
        "grade": grade,
        "slides": [
            {
                "title": f"Introduction to {topic}",
                "content": f"Today we will learn about {topic}. This is a fundamental concept in Science for Grade {grade}.",
                "mediaType": "image",
                "mediaUrl": ""
            },
            {
                "title": "Key Concept",
                "content": "A detailed explanation of the concept goes here. Remember to use visuals!",
                "mediaType": "step",
                "mediaUrl": "Step 1 | Step 2 | Step 3"
            }
        ]
    }
    
    # Example template for questions
    questions = [
        {"grade": grade, "topic": topic, "difficulty": "Easy", "text": f"What is the basic definition of {topic}?"},
        {"grade": grade, "topic": topic, "difficulty": "Medium", "text": f"Explain the process of {topic} in your own words."},
        {"grade": grade, "topic": topic, "difficulty": "Hard", "text": f"How does {topic} affect our daily lives at Central Elementary School?"}
    ]
    
    return lesson, questions

def save_to_json(lesson, questions):
    data = {
        "customLessons": [lesson],
        "questions": questions
    }
    filename = f"material_{lesson['title'].replace(' ', '_')}.json"
    filepath = os.path.join(BASE_DIR, filename)
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4)
    print(f"\n✅ Material saved to {filepath}")
    print("👉 You can now go to ILikeSci > Settings > Import Data and select this file.")

def main():
    print("="*40)
    print(" ILikeSci - Python Content Assistant")
    print("="*40)
    
    grade = input("Enter Grade Level (3-6): ")
    topic = input("Enter Science Topic Name: ")
    
    lesson, questions = generate_lesson_material(grade, topic)
    
    print("\n[1] Save to JSON (For manual import)")
    print("[2] Push to Database (Direct sync to ILikeSci)")
    choice = input("\nChoose an option: ")
    
    if choice == '1':
        save_to_json(lesson, questions)
    elif choice == '2':
        conn = connect_db()
        if conn:
            cursor = conn.cursor()
            # Note: This is a simplified direct injection
            # In a full system, you'd insert into 'curriculum_lessons' and 'curriculum_questions'
            print("Direct database injection requires specific table mapping...")
            # For now, JSON is safer for the teacher
            save_to_json(lesson, questions)
            conn.close()
        else:
            print("❌ Could not connect to MySQL. Is XAMPP running?")
            save_to_json(lesson, questions)

if __name__ == "__main__":
    main()
