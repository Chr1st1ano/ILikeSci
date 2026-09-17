"""
ILikeSci — Comprehensive DepEd MATATAG Curriculum Organizer & Seeder
Cleans out duplicate lesson numbers, junk test artifacts, and re-indexes all lessons
across Grades 3 to 6 with sequential lesson numbers and authentic DepEd questions.
"""

import os
import re
import json
import sqlite3
from docx import Document

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SQLITE_DB = os.path.join(BASE_DIR, "ilikesci_db.sqlite")

conn = sqlite3.connect(SQLITE_DB)
cursor = conn.cursor()

print("--- Step 1: Purging all obsolete test artifacts and clearing old curriculum tables ---")
cursor.execute("DELETE FROM curriculum_lessons WHERE topic LIKE '%Automated Test%' OR topic LIKE '%Production Verification%' OR topic LIKE '%AP4%' OR topic LIKE '%Test%'")
cursor.execute("DELETE FROM lesson_slides WHERE curriculum_lesson_id NOT IN (SELECT id FROM curriculum_lessons)")
cursor.execute("DELETE FROM topics WHERE topic_name LIKE '%Automated Test%' OR topic_name LIKE '%Production Verification%' OR topic_name LIKE '%AP4%' OR topic_name LIKE '%Test%'")
cursor.execute("DELETE FROM questions WHERE topic LIKE '%Automated Test%' OR topic LIKE '%Production Verification%' OR topic LIKE '%AP4%' OR topic LIKE '%Test%'")
conn.commit()

def find_file(filename):
    for root, dirs, files in os.walk(os.path.join(BASE_DIR, "Curriculums")):
        if filename in files:
            return os.path.join(root, filename)
    return None

def parse_docx_questions_with_keys(fp, grade, quarter, fallback_topic):
    if not fp or not os.path.exists(fp):
        return []
    doc = Document(fp)
    lines = [p.text.strip() for p in doc.paragraphs if p.text.strip()]
    questions = []
    current_q = None

    # Check for answer key in doc
    answer_keys = {}
    in_key_section = False
    for line in lines:
        if any(h in line.upper() for h in ['ANSWER KEY', 'KEY TO CORRECTION', 'ANSWER KEYS']):
            in_key_section = True
            continue
        if in_key_section:
            # Matches '1. A' or '1) A' or '1.   B' or just letters on separate lines
            m = re.match(r'^(\d+)[\.\)]\s*([A-D])', line, re.IGNORECASE)
            if m:
                answer_keys[int(m.group(1))] = m.group(2).upper()

    for line in lines:
        if any(h in line.upper() for h in ['ANSWER KEY', 'KEY TO CORRECTION', 'ANSWER KEYS']):
            break  # stop before answer key section

        m = re.match(r'^(\d+)[\.\)]\s*(.+)', line)
        if m:
            if current_q and current_q.get('text') and len(current_q.get('text')) > 8:
                questions.append(current_q)
            q_num = int(m.group(1))
            current_q = {
                'num': q_num,
                'text': m.group(2).strip(),
                'choices': {},
                'topic': fallback_topic,
                'grade': grade,
                'quarter': quarter,
                'correct': answer_keys.get(q_num, 'A')
            }
            continue

        if current_q:
            choice_matches = list(re.finditer(r'([A-D])[\.\)]\s*([^\sA-D\.\)][^A-D\.\)]*)', line))
            if choice_matches:
                for cm in choice_matches:
                    current_q['choices'][cm.group(1).upper()] = cm.group(2).strip().rstrip('|').strip()
            elif any(line.startswith(f'{opt}.') or line.startswith(f'{opt})') for opt in ['A','B','C','D','a','b','c','d']):
                current_q['choices'][line[0].upper()] = line[2:].strip().rstrip('|').strip()
            else:
                if not current_q['choices']:
                    current_q['text'] += ' ' + line

    if current_q and current_q.get('text') and len(current_q.get('text')) > 8:
        questions.append(current_q)

    return questions

# Clean up Grade 4 to have exactly 20 cleanly indexed lessons
cursor.execute("DELETE FROM curriculum_lessons WHERE grade = '4'")
cursor.execute("DELETE FROM topics WHERE grade = '4'")
cursor.execute("DELETE FROM questions WHERE grade = '4'")
conn.commit()

# Define canonical Grade 4 units with precise question slicing
G4_UNITS = [
    # --- QUARTER 1 (MATTER & MATERIALS) ---
    {
        "quarter": "1", "lesson_number": "1",
        "topic": "Properties of Materials: Absorption and Porosity",
        "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (1, 8),
        "content": "Lesson 1: Properties of Materials — Absorption and Porosity\nMaterials have observable physical properties including porosity, density, and absorptive capacity. Porous materials contain microscopic air spaces that allow liquids to be absorbed (cotton, sponges, towels). Non-porous materials repel water and do not absorb liquids (plastics, glass, metals)."
    },
    {
        "quarter": "1", "lesson_number": "2",
        "topic": "Properties of Materials: Sinking and Floating",
        "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (9, 16),
        "content": "Lesson 2: Sinking and Floating Properties\nObjects float when their buoyant force equals their weight or when their overall density is less than water. Dense metal bolts sink in water, while plastic bottles and dry wooden blocks float on the surface."
    },
    {
        "quarter": "1", "lesson_number": "3",
        "topic": "Chemical Properties: Decaying and Non-Decaying Materials",
        "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (17, 24),
        "content": "Lesson 3: Decaying and Non-Decaying Materials\nBiodegradable materials decompose naturally through the action of microorganisms (bacteria and fungi), returning organic matter to the soil. Non-decaying plastics remain intact for decades and require responsible management."
    },
    {
        "quarter": "1", "lesson_number": "4",
        "topic": "Changes in Materials: Useful and Harmful Effects",
        "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (25, 32),
        "content": "Lesson 4: Changes in Materials — Useful and Harmful Effects\nMaterials change when exposed to temperature variations, moisture, and chemical reactions. Useful changes improve human life (cooking, shaping glass). Harmful changes like metal rusting, toxic smoke from burning plastics, and rotting garbage pollute the environment."
    },
    {
        "quarter": "1", "lesson_number": "5",
        "topic": "Waste Management and the 5Rs for Environmental Protection",
        "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (33, 40),
        "content": "Lesson 5: Waste Management and the 5Rs\nEffective ecological waste management protects Philippine waterways and communities. By practicing the 5Rs (Reduce, Reuse, Recycle, Recover, and Repair), learners minimize landfill usage and conserve natural resources."
    },

    # --- QUARTER 2 (LIVING THINGS & HABITATS) ---
    {
        "quarter": "2", "lesson_number": "1",
        "topic": "Human Body Systems: Muscular and Skeletal Systems",
        "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (1, 8),
        "content": "Lesson 1: Muscular and Skeletal Systems\nThe skeletal system provides structural framework, protects vital internal organs (skull protects brain, ribcage protects heart and lungs), and produces blood cells. Muscles contract and relax in pairs attached to bones by tendons to enable locomotion."
    },
    {
        "quarter": "2", "lesson_number": "2",
        "topic": "Human Body Systems: Digestive System and Healthy Habits",
        "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (9, 16),
        "content": "Lesson 2: The Digestive System\nDigestion begins in the mouth where teeth chew food and salivary amylase breaks down starch. In the stomach, gastric juices churn food into chyme. The small intestine absorbs glucose and amino acids through villi, while the large intestine absorbs water and expels solid waste."
    },
    {
        "quarter": "2", "lesson_number": "3",
        "topic": "Plant Organs: Root and Shoot Systems and Adaptations",
        "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (17, 24),
        "content": "Lesson 3: Root and Shoot Systems in Plants\nRoots anchor plants and absorb water and minerals from the soil. The shoot system includes stems that transport nutrients, leaves that produce glucose through photosynthesis, and flowers for reproduction."
    },
    {
        "quarter": "2", "lesson_number": "4",
        "topic": "Animal Habitats and Feeding Groups (Herbivores, Carnivores, Omnivores)",
        "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (25, 32),
        "content": "Lesson 4: Animal Habitats and Feeding Habits\nAnimals live in terrestrial, aquatic, or aerial habitats. Based on diet, animals are classified into plant-eaters (herbivores), meat-eaters (carnivores), and plant-and-meat eaters (omnivores)."
    },
    {
        "quarter": "2", "lesson_number": "5",
        "topic": "Life Cycles, Metamorphosis, and Food Chains in Ecosystems",
        "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (33, 40),
        "content": "Lesson 5: Life Cycles and Food Chains\nButterflies and frogs undergo complete metamorphosis with distinct developmental stages. In food chains, green plants act as producers, passing energy to herbivores, carnivores, and decomposers."
    },

    # --- QUARTER 3 (FORCE, MOTION, AND ENERGY) ---
    {
        "quarter": "3", "lesson_number": "1",
        "topic": "Effects of Force on the Size, Shape, and Movement of Objects",
        "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (1, 10),
        "content": "Lesson 1: Effects of Force on Objects\nForce is a push or pull that changes the speed, direction, shape, or size of an object. Applying force can bend, compress, stretch, or accelerate materials."
    },
    {
        "quarter": "3", "lesson_number": "2",
        "topic": "Magnets and Magnetic Force (Attraction and Repulsion)",
        "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (11, 20),
        "content": "Lesson 2: Magnets and Magnetic Force\nMagnets attract ferromagnetic materials like iron, nickel, and steel. Every magnet has two poles: North and South. Opposite poles attract; identical poles repel."
    },
    {
        "quarter": "3", "lesson_number": "3",
        "topic": "Heat Energy, Temperature, and Heat Transfer",
        "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (21, 30),
        "content": "Lesson 3: Heat Transfer and Temperature\nHeat flows from hotter regions to cooler regions through conduction (solids), convection (fluids), and radiation (electromagnetic waves across empty space)."
    },
    {
        "quarter": "3", "lesson_number": "4",
        "topic": "Light Energy, Reflection, and Shadows",
        "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (31, 40),
        "content": "Lesson 4: Light and Shadows\nLight travels in straight lines. When light strikes an opaque object, it cannot pass through, casting a dark shadow behind the object. Smooth shiny surfaces reflect light predictably."
    },
    {
        "quarter": "3", "lesson_number": "5",
        "topic": "Sound Energy, Vibrations, and Sound Reflection",
        "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (41, 50),
        "content": "Lesson 5: Sound Energy and Vibrations\nAll sound originates from physical vibrations. Sound travels through solids, liquids, and gases but cannot travel in a vacuum. Reflected sound is known as an echo."
    },

    # --- QUARTER 4 (EARTH AND SPACE) ---
    {
        "quarter": "4", "lesson_number": "1",
        "topic": "Soil Types and Plant Growth in the Community",
        "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (1, 8),
        "content": "Lesson 1: Soil Types and Plant Growth\nSoil is classified into sandy soil (large particles, quick drainage), clay soil (tiny particles, high water retention), and loam (balanced, rich in humus), which is the ideal soil for growing crops."
    },
    {
        "quarter": "4", "lesson_number": "2",
        "topic": "Water Resources, the Water Cycle, and Safe Water Practices",
        "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (9, 16),
        "content": "Lesson 2: Water Resources and the Water Cycle\nFreshwater comes from springs, rivers, lakes, and underground wells. Water continuously circulates through evaporation, condensation, precipitation, and collection."
    },
    {
        "quarter": "4", "lesson_number": "3",
        "topic": "Weather Elements, Instruments, and Cloud Types",
        "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (17, 24),
        "content": "Lesson 3: Weather Elements and Instruments\nWeather conditions are monitored using instruments: thermometers measure temperature, wind vanes indicate direction, anemometers track speed, and rain gauges measure precipitation."
    },
    {
        "quarter": "4", "lesson_number": "4",
        "topic": "Typhoons, PAGASA Warning Signals, and Disaster Preparedness",
        "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (25, 32),
        "content": "Lesson 4: Typhoons and Disaster Preparedness\nThe Philippines experiences tropical cyclones from the Pacific Ocean. PAGASA issues Public Storm Warning Signals (Signal 1 to 5) to alert communities to take precautionary safety measures."
    },
    {
        "quarter": "4", "lesson_number": "5",
        "topic": "The Sun: Solar Energy, Benefits, and Protection",
        "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (33, 40),
        "content": "Lesson 5: The Sun and Solar Protection\nThe Sun is Earth's main source of heat and light. It powers photosynthesis and weather. People protect themselves from excessive ultraviolet radiation by wearing hats, sunglasses, and umbrellas."
    }
]

print("--- Step 2: Seeding 20 canonical Grade 4 lessons with authentic DepEd questions ---")

for unit in G4_UNITS:
    quarter = unit["quarter"]
    lesson_no = unit["lesson_number"]
    topic = unit["topic"]
    doc_file = unit["doc"]
    q_start, q_end = unit["q_range"]

    fp = find_file(doc_file)
    raw_questions = parse_docx_questions_with_keys(fp, "4", quarter, topic)

    selected_questions = []
    for q in raw_questions:
        num = q['num']
        if q_start <= num <= q_end:
            selected_questions.append(q)

    # Format questions
    formatted_questions = []
    for q in selected_questions:
        q_text = q['text']
        choices = q['choices']
        choice_str = ""
        for letter in ['A', 'B', 'C', 'D']:
            if letter in choices:
                choice_str += f" | {letter}: {choices[letter]}"

        full_q_text = q_text + choice_str
        correct_ans = q.get('correct', 'A')

        formatted_questions.append({
            "text": full_q_text,
            "correct": correct_ans,
            "difficulty": "Easy" if (q['num'] % 3 == 1) else "Medium" if (q['num'] % 3 == 2) else "Hard"
        })

    # Insert curriculum lesson
    cursor.execute("""
        INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, ("4", quarter, lesson_no, topic, unit["content"], json.dumps(["Understand " + topic, "Apply concepts to Philippine setting"]), json.dumps(formatted_questions)))
    lesson_id = cursor.lastrowid

    # Insert 5 lesson slides
    cursor.execute("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?", (lesson_id,))
    slides = [
        (lesson_id, 1, f"Overview: {topic}", f"Grade 4 Quarter {quarter} Lesson {lesson_no}\n\n{unit['content'][:200]}...", "title", "image", ""),
        (lesson_id, 2, "Learning Competencies", f"• Describe and understand the core principles of {topic}\n• Relate concepts to everyday classroom and community experiences\n• Practice safety and scientific inquiry", "content", "step", ""),
        (lesson_id, 3, "Core Discussion", f"Key Scientific Principles:\n{unit['content']}", "content", "none", ""),
        (lesson_id, 4, "Philippine Community Application", f"Observing {topic} in homes, schools, and communities across the Philippines.", "step", "none", ""),
        (lesson_id, 5, "Recitation Review", f"Review question on {topic} for oral recitation and flash quiz evaluation.", "quiz", "none", "")
    ]
    cursor.executemany("""
        INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, slides)

    # Register in topics table
    cursor.execute("INSERT INTO topics (grade, topic_name) VALUES ('4', ?)", (topic,))

    # Register questions in question pool
    for q_data in formatted_questions:
        cursor.execute("""
            INSERT INTO questions (grade, topic, difficulty, question_text)
            VALUES ('4', ?, ?, ?)
        """, (topic, q_data["difficulty"], q_data["text"]))

conn.commit()

# Clean up Grade 3, 5, 6 duplicate lesson numbers and duplicate names
print("--- Step 3: Deduplicating Grade 3, 5, and 6 lesson entries ---")
for g in ['3', '5', '6']:
    cursor.execute("SELECT id, quarter, lesson_number, topic FROM curriculum_lessons WHERE grade = ? ORDER BY CAST(quarter AS INT), CAST(lesson_number AS INT)", (g,))
    rows = cursor.fetchall()
    seen = set()
    for r in rows:
        lid, qtr, lnum, top = r
        key = top.strip().lower()
        if key in seen:
            cursor.execute("DELETE FROM curriculum_lessons WHERE id = ?", (lid,))
            cursor.execute("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?", (lid,))
        else:
            seen.add(key)
    conn.commit()

    # Re-index lesson numbers sequentially per quarter
    cursor.execute("SELECT quarter FROM curriculum_lessons WHERE grade = ? GROUP BY quarter", (g,))
    quarters = [r[0] for r in cursor.fetchall()]
    for qtr in quarters:
        cursor.execute("SELECT id FROM curriculum_lessons WHERE grade = ? AND quarter = ? ORDER BY id", (g, qtr))
        q_rows = cursor.fetchall()
        for idx, (lid,) in enumerate(q_rows, 1):
            cursor.execute("UPDATE curriculum_lessons SET lesson_number = ? WHERE id = ?", (str(idx), lid))
    conn.commit()

# Final count verification
cursor.execute("SELECT count(*) FROM curriculum_lessons")
c_lessons = cursor.fetchone()[0]
cursor.execute("SELECT count(*) FROM topics")
c_topics = cursor.fetchone()[0]
cursor.execute("SELECT count(*) FROM questions")
c_questions = cursor.fetchone()[0]
cursor.execute("SELECT count(*) FROM lesson_slides")
c_slides = cursor.fetchone()[0]

conn.close()

print("\n" + "="*50)
print("DEPED CURRICULUM RE-INDEXING & CLEANUP COMPLETE!")
print(f"Total Curriculum Lessons: {c_lessons}")
print(f"Total Topics: {c_topics}")
print(f"Total Questions: {c_questions}")
print(f"Total Slides: {c_slides}")
print("="*50)
