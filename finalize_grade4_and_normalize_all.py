import os
import json
import sqlite3
import re
from docx import Document

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SQLITE_DB = os.path.join(BASE_DIR, "ilikesci_db.sqlite")

conn = sqlite3.connect(SQLITE_DB)
cursor = conn.cursor()

print("==================================================")
print("FINALIZE DEPED CURRICULUM AND NORMALIZE ALL GRADES")
print("==================================================")

def find_file(filename):
    for root, dirs, files in os.walk(os.path.join(BASE_DIR, "Curriculums")):
        if filename in files:
            return os.path.join(root, filename)
    return None

def parse_docx_questions(fp, grade, quarter, fallback_topic):
    if not fp or not os.path.exists(fp):
        return []
    doc = Document(fp)
    lines = [p.text.strip() for p in doc.paragraphs if p.text.strip()]
    questions = []
    current_q = None

    answer_keys = {}
    in_key_section = False
    for line in lines:
        if any(h in line.upper() for h in ['ANSWER KEY', 'KEY TO CORRECTION', 'ANSWER KEYS']):
            in_key_section = True
            continue
        if in_key_section:
            m = re.match(r'^(\d+)[\.\)]\s*([A-D])', line, re.IGNORECASE)
            if m:
                answer_keys[int(m.group(1))] = m.group(2).upper()

    choice_pattern = re.compile(r'(?:^|\s+)([A-D])[\.\:\)]\s*(.*?)(?=(?:\s+[A-D][\.\:\)])|$)', re.DOTALL)

    for line in lines:
        if any(h in line.upper() for h in ['ANSWER KEY', 'KEY TO CORRECTION', 'ANSWER KEYS']):
            break

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
            matches = list(choice_pattern.finditer(line))
            if matches:
                for cm in matches:
                    letter = cm.group(1).upper()
                    val = cm.group(2).strip().rstrip('|').strip()
                    if val:
                        current_q['choices'][letter] = val
            elif any(line.startswith(f'{opt}.') or line.startswith(f'{opt})') for opt in ['A','B','C','D']):
                current_q['choices'][line[0].upper()] = line[2:].strip().rstrip('|').strip()
            else:
                if not current_q['choices']:
                    current_q['text'] += ' ' + line

    if current_q and current_q.get('text') and len(current_q.get('text')) > 8:
        questions.append(current_q)

    return questions

# 1. Reset Grade 4 to the 20 official MATATAG lessons
print("\n--- Resetting Grade 4 to 20 Canonical Lessons ---")
cursor.execute("DELETE FROM curriculum_lessons WHERE grade = '4'")
cursor.execute("DELETE FROM lesson_slides WHERE curriculum_lesson_id NOT IN (SELECT id FROM curriculum_lessons)")
cursor.execute("DELETE FROM topics WHERE grade = '4'")
cursor.execute("DELETE FROM questions WHERE grade = '4'")
conn.commit()

G4_UNITS = [
    # Q1
    {"quarter": "1", "lesson_number": "1", "topic": "Properties of Materials: Absorption and Porosity", "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (1, 8), "content": "Porous materials like sponge and cotton absorb liquids due to tiny openings. Non-porous materials like plastic and glass repel liquids."},
    {"quarter": "1", "lesson_number": "2", "topic": "Properties of Materials: Sinking and Floating", "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (9, 16), "content": "Objects float when buoyant force equals their weight or density is lower than water. Heavy metal nails sink, while plastic bottles float."},
    {"quarter": "1", "lesson_number": "3", "topic": "Chemical Properties: Decaying and Non-Decaying Materials", "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (17, 24), "content": "Biodegradable materials rot naturally through microorganisms, enriching soil. Non-decaying plastics remain intact and require recycling."},
    {"quarter": "1", "lesson_number": "4", "topic": "Changes in Materials: Useful and Harmful Effects", "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (25, 32), "content": "Changes in matter can be useful (cooking food) or harmful (rusting iron, burning plastics releasing toxic smoke)."},
    {"quarter": "1", "lesson_number": "5", "topic": "Waste Management and the 5Rs for Environmental Protection", "doc": "PT_SCIENCE 4_Q1.docx", "q_range": (33, 40), "content": "The 5Rs: Reduce, Reuse, Recycle, Recover, and Repair help keep communities clean and protect waterways."},
    # Q2
    {"quarter": "2", "lesson_number": "1", "topic": "Human Body Systems: Muscular and Skeletal Systems", "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (1, 8), "content": "Bones give structure and protect internal organs. Muscles contract and relax in pairs to create movement."},
    {"quarter": "2", "lesson_number": "2", "topic": "Human Body Systems: Digestive System and Healthy Habits", "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (9, 16), "content": "Digestion breaks down food: mouth chews, stomach churns with acid, small intestine absorbs nutrients, large intestine absorbs water."},
    {"quarter": "2", "lesson_number": "3", "topic": "Plant Organs: Root and Shoot Systems and Adaptations", "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (17, 24), "content": "Roots absorb water from soil; stems and leaves transport nutrients and create food through photosynthesis."},
    {"quarter": "2", "lesson_number": "4", "topic": "Animal Habitats and Feeding Groups (Herbivores, Carnivores, Omnivores)", "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (25, 32), "content": "Animals inhabit terrestrial, aquatic, and aerial environments. Based on diet: Herbivores eat plants, Carnivores eat meat, Omnivores eat both."},
    {"quarter": "2", "lesson_number": "5", "topic": "Life Cycles, Metamorphosis, and Food Chains in Ecosystems", "doc": "Q2 PT - SCIENCE 4 MATATAG.docx", "q_range": (33, 40), "content": "Butterflies undergo complete metamorphosis: Egg -> Caterpillar (Larva) -> Pupa (Chrysalis) -> Adult. In food chains, energy flows from producers to consumers."},
    # Q3
    {"quarter": "3", "lesson_number": "1", "topic": "Effects of Force on the Size, Shape, and Movement of Objects", "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (1, 10), "content": "Force is a push or pull that changes the speed, direction, shape, or size of an object."},
    {"quarter": "3", "lesson_number": "2", "topic": "Magnets and Magnetic Force (Attraction and Repulsion)", "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (11, 20), "content": "Magnets attract iron, nickel, and steel. Like poles repel (N-N, S-S); unlike poles attract (N-S)."},
    {"quarter": "3", "lesson_number": "3", "topic": "Heat Energy, Temperature, and Heat Transfer", "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (21, 30), "content": "Heat transfers from hot regions to cold regions through conduction (solids), convection (fluids), and radiation (empty space)."},
    {"quarter": "3", "lesson_number": "4", "topic": "Light Energy, Reflection, and Shadows", "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (31, 40), "content": "Light travels in straight lines. Opaque objects block light and create shadows. Smooth surfaces reflect light evenly."},
    {"quarter": "3", "lesson_number": "5", "topic": "Sound Energy, Vibrations, and Sound Reflection", "doc": "THIRD PERIODICAL TEST IN SCIENCE 4.docx", "q_range": (41, 50), "content": "Sound is produced by vibrations. Sound travels through solids, liquids, and gases. An echo is reflected sound."},
    # Q4
    {"quarter": "4", "lesson_number": "1", "topic": "Soil Types and Plant Growth in the Community", "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (1, 8), "content": "Sandy soil drains fast, clay holds water tightly, and loam is the ideal balanced soil for gardening and crop growth."},
    {"quarter": "4", "lesson_number": "2", "topic": "Water Resources, the Water Cycle, and Safe Water Practices", "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (9, 16), "content": "Water circulates continuously through evaporation, condensation, and precipitation. Safe drinking water is essential for health."},
    {"quarter": "4", "lesson_number": "3", "topic": "Weather Elements, Instruments, and Cloud Types", "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (17, 24), "content": "Weather is measured using thermometer (temperature), rain gauge (rain), anemometer (wind speed), and wind vane (direction)."},
    {"quarter": "4", "lesson_number": "4", "topic": "Typhoons, PAGASA Warning Signals, and Disaster Preparedness", "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (25, 32), "content": "PAGASA issues storm signals 1 to 5 based on wind speed. During typhoons, stay indoors and follow disaster preparedness guidelines."},
    {"quarter": "4", "lesson_number": "5", "topic": "The Sun: Solar Energy, Benefits, and Protection", "doc": "SCIENCE4 MATATAG PT4.docx", "q_range": (33, 40), "content": "The Sun provides light and heat for Earth. Protect yourself from UV radiation using wide-brimmed hats, sunglasses, and umbrellas."}
]

def clean_q(stem, choices_dict, correct="A"):
    s = stem.replace('\ufffd', "'").replace('\t', ' ').strip()
    s = re.sub(r'\s+', ' ', s)

    # Specific overrides
    if "boats float in water" in s.lower():
        s = "Why do boats float in water?"
        choices_dict = {
            "A": "Boats are made of wood and shapes that displace water",
            "B": "Man uses paddle to make the boat float in water",
            "C": "The buoyant force of water supports the boat's shape",
            "D": "The water current pushes the boat upward"
        }
        correct = "A"

    if "protecting oneself from the heat of the sun" in s.lower():
        s = "Which of the following shows proper way of protecting oneself from the heat of the sun?"
        choices_dict = {
            "A": "Wearing protective clothing like long-sleeved clothes when working under the sun",
            "B": "Drinking plenty of water to keep hydrated during summer",
            "C": "Use wide-brimmed hat when working in the fields",
            "D": "All of these options"
        }
        correct = "D"

    if "pot holder" in s.lower():
        s = "Why do we need to use pot holder when handling hot casseroles or any hot cooking wares?"
        choices_dict = {
            "A": "To protect our new cuticles from fading",
            "B": "To maintain the softness of our hands",
            "C": "To protect our hands from getting hurt or burned",
            "D": "None of the above"
        }
        correct = "C"

    if "swim in a hot sunny day" in s.lower():
        s = "What must you do if you want to swim in a hot sunny day?"
        choices_dict = {
            "A": "Wear jacket so that your skin will not get burned",
            "B": "Wear protective footwear like boots and knee-high socks",
            "C": "Apply sunblock lotion to protect your skin from the sun",
            "D": "Use beach umbrella while swimming in the water"
        }
        correct = "C"

    if "shoot system transports water" in s.lower():
        s = "Which part of the shoot system transports water and nutrients from the roots to the leaves?"
        choices_dict = {"A": "Phloem", "B": "Cambium", "C": "Xylem", "D": "Cortex"}
        correct = "C"

    if "bear that eats both berries and fish" in s.lower():
        s = "A bear that eats both berries and fish is classified as what?"
        choices_dict = {"A": "Herbivore", "B": "Carnivore", "C": "Omnivore", "D": "Detritivore"}
        correct = "C"

    if "first stage of the butterfly" in s.lower():
        s = "What is the first stage of the butterfly's life cycle?"
        choices_dict = {"A": "Egg", "B": "Caterpillar (Larva)", "C": "Pupa (Chrysalis)", "D": "Adult Butterfly"}
        correct = "A"

    if "animals is commonly found in seashores" in s.lower():
        s = "Which of the following animals is commonly found along seashores?"
        choices_dict = {"A": "Crabs", "B": "Eagle", "C": "Python", "D": "Monkey"}
        correct = "A"

    if "fauna is primarily responsible for pollination" in s.lower():
        s = "What type of fauna is primarily responsible for pollination in garden ecosystems?"
        choices_dict = {"A": "Bees and butterflies", "B": "Earthworms", "C": "Snails", "D": "Ants"}
        correct = "A"

    if "which of the following is a carnivore" in s.lower():
        s = "Which of the following animals is a carnivore?"
        choices_dict = {"A": "Hawk", "B": "Rabbit", "C": "Cow", "D": "Goat"}
        correct = "A"

    if "reminders to become safe at school" in s.lower():
        s = "Which of the following is an unsafe behavior at school?"
        choices_dict = {"A": "Pushing others while in line", "B": "Walking quietly in hallway", "C": "Following teacher instructions", "D": "Keeping bags under desk"}
        correct = "A"

    if "animals in a forest if the water supply was reduced" in s.lower():
        s = "What would happen to animals in a forest if the natural water supply was severely reduced?"
        choices_dict = {"A": "Animals would migrate or perish from dehydration", "B": "Animals would reproduce faster", "C": "Animals would turn into plants", "D": "Nothing would happen"}
        correct = "A"

    if "which weather instrument is used to measure the temperature" in s.lower():
        s = "Which weather instrument is used to measure the temperature of the air?"
        choices_dict = {"A": "Thermometer", "B": "Barometer", "C": "Anemometer", "D": "Hygrometer"}
        correct = "A"

    if "how can we determine the direction of the wind" in s.lower():
        s = "How can we determine the direction of the wind?"
        choices_dict = {"A": "By using a wind vane", "B": "By using a thermometer", "C": "By using a rain gauge", "D": "By using a scale"}
        correct = "A"

    if "tells us how much moisture is in the air" in s.lower():
        s = "Which weather element tells us how much moisture is in the air?"
        choices_dict = {"A": "Humidity", "B": "Air pressure", "C": "Wind direction", "D": "Cloud cover"}
        correct = "A"

    if "cloud is typically associated with rainy or stormy" in s.lower():
        s = "What type of cloud is typically associated with rainy or stormy weather?"
        choices_dict = {"A": "Cumulonimbus", "B": "Cirrus", "C": "Cumulus", "D": "Altocumulus"}
        correct = "A"

    if "which weather instrument is used to measure the air pressure" in s.lower():
        s = "Which weather instrument is used to measure air pressure?"
        choices_dict = {"A": "Barometer", "B": "Thermometer", "C": "Anemometer", "D": "Rain gauge"}
        correct = "A"

    if "characteristics is measured using a thermometer" in s.lower():
        s = "Which weather characteristic is measured using a thermometer?"
        choices_dict = {"A": "Air temperature", "B": "Wind speed", "C": "Precipitation volume", "D": "Atmospheric pressure"}
        correct = "A"

    if "helps us measure how much rain has fallen" in s.lower():
        s = "Which weather instrument measures how much rain has fallen?"
        choices_dict = {"A": "Rain gauge", "B": "Hygrometer", "C": "Anemometer", "D": "Barometer"}
        correct = "A"

    if "mostly covered with thick clouds and there is no direct sunlight" in s.lower():
        s = "If the sky is covered with thick dark clouds and there is no direct sunlight, what cloud cover is this?"
        choices_dict = {"A": "Overcast", "B": "Clear sky", "C": "Partly cloudy", "D": "Sunny"}
        correct = "A"

    if "instruments helps measure the air pressure" in s.lower():
        s = "Which instrument is specifically used to measure atmospheric air pressure?"
        choices_dict = {"A": "Barometer", "B": "Thermometer", "C": "Rain gauge", "D": "Anemometer"}
        correct = "A"

    if "why do shadows change direction throughout the day" in s.lower():
        s = "Why do shadows change direction and length throughout the day?"
        choices_dict = {"A": "Because Earth's rotation changes the apparent position of the Sun", "B": "Because wind moves the shadows", "C": "Because clouds absorb the shadows", "D": "Because the ground expands"}
        correct = "A"

    if "how does the sun support life on earth" in s.lower():
        s = "How does the Sun support life on Earth?"
        choices_dict = {"A": "By providing heat and light for photosynthesis and warmth", "B": "By cooling the oceans", "C": "By creating lunar eclipses", "D": "By removing oxygen from air"}
        correct = "A"

    out_c = []
    for letter in ['A', 'B', 'C', 'D']:
        if letter in choices_dict:
            out_c.append(f"{letter}: {choices_dict[letter]}")
    full = s + " | " + " | ".join(out_c)
    return full, correct.upper()

for u in G4_UNITS:
    quarter = u["quarter"]
    lesson_no = u["lesson_number"]
    topic = u["topic"]
    doc_file = u["doc"]
    q_start, q_end = u["q_range"]

    fp = find_file(doc_file)
    raw_questions = parse_docx_questions(fp, "4", quarter, topic)
    selected = [q for q in raw_questions if q_start <= q['num'] <= q_end]

    norm_qs = []
    for q in selected:
        diff = "Easy" if (q['num'] % 3 == 1) else "Medium" if (q['num'] % 3 == 2) else "Hard"
        clean_text, clean_corr = clean_q(q['text'], q['choices'], q.get('correct', 'A'))
        norm_qs.append({
            "text": clean_text,
            "correct": clean_corr,
            "difficulty": diff
        })

    cursor.execute("""
        INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, lesson_title, content, objectives, questions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    """, ("4", quarter, lesson_no, topic, topic, u["content"], json.dumps(["Understand " + topic, "Apply concepts in community"]), json.dumps(norm_qs)))
    lesson_id = cursor.lastrowid

    cursor.execute("INSERT INTO topics (grade, topic_name) VALUES ('4', ?)", (topic,))

    slides = [
        (lesson_id, 1, f"Overview: {topic}", f"Grade 4 Quarter {quarter} Lesson {lesson_no}\n\n{u['content']}", "title", "image", ""),
        (lesson_id, 2, "Learning Competencies", f"• Understand core principles of {topic}\n• Relate concepts to daily life\n• Practice scientific inquiry", "content", "step", ""),
        (lesson_id, 3, "Core Discussion", f"Key Scientific Principles:\n{u['content']}", "content", "none", ""),
        (lesson_id, 4, "Philippine Community Application", f"Observing {topic} in homes and schools across the Philippines.", "step", "none", ""),
        (lesson_id, 5, "Recitation Review", f"Review questions for oral recitation and flash quiz on {topic}.", "quiz", "none", "")
    ]
    cursor.executemany("""
        INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, slides)

    for q in norm_qs:
        cursor.execute("""
            INSERT INTO questions (grade, topic, difficulty, question_text)
            VALUES ('4', ?, ?, ?)
        """, (topic, q["difficulty"], q["text"]))

conn.commit()
print("Grade 4 seeded with 20 clean lessons.")

# 2. Normalize and format all choices across ALL grades (3, 4, 5, 6)
print("\n--- Standardizing choices across all grades ---")
choice_pattern = re.compile(r'(?:^|[|\s]+)([A-D])[\:\.\)]\s*(.*?)(?=(?:[|\s]+[A-D][\:\.\)]\s*)|$)', re.DOTALL)

cursor.execute("SELECT id, grade, topic, questions FROM curriculum_lessons")
all_lessons = cursor.fetchall()

for lid, grade, topic, q_json in all_lessons:
    if not q_json:
        continue
    qs = json.loads(q_json)
    cleaned_qs = []
    for q in qs:
        raw = q.get('text', '') if isinstance(q, dict) else str(q)
        corr = q.get('correct', 'A') if isinstance(q, dict) else 'A'
        diff = q.get('difficulty', 'Medium') if isinstance(q, dict) else 'Medium'

        # Separate stem and choices
        parts = raw.split('|')
        stem = parts[0].strip()
        stem = stem.replace('\ufffd', "'").replace('\t', ' ')
        stem = re.sub(r'[\ufffd\?]+C\b', '°C', stem)
        stem = re.sub(r'\s+', ' ', stem).strip()

        choices_str = ' '.join(parts[1:]).strip() if len(parts) > 1 else ''
        if not choices_str:
            m = re.search(r'(?:^|[|\s]+)([A-D])[\:\.\)]\s+', stem)
            if m:
                idx = m.start()
                choices_str = stem[idx:].strip()
                stem = stem[:idx].strip()

        matches = list(choice_pattern.finditer(choices_str))
        c_dict = {}
        for cm in matches:
            l = cm.group(1).upper()
            val = cm.group(2).strip().rstrip('|').strip()
            val = val.replace('\ufffd', "'").replace('\t', ' ')
            val = re.sub(r'\s+', ' ', val).strip()
            if val and l not in c_dict:
                c_dict[l] = val

        out_choices = []
        for l in ['A', 'B', 'C', 'D']:
            if l in c_dict:
                out_choices.append(f"{l}: {c_dict[l]}")

        final_text = stem + " | " + " | ".join(out_choices) if out_choices else raw
        cleaned_qs.append({
            "text": final_text,
            "correct": corr.upper(),
            "difficulty": diff
        })

    cursor.execute("UPDATE curriculum_lessons SET questions = ?, lesson_title = topic WHERE id = ?", (json.dumps(cleaned_qs), lid))

conn.commit()

# Sync to questions table
cursor.execute("DELETE FROM questions")
for lid, grade, topic, q_json in cursor.execute("SELECT id, grade, topic, questions FROM curriculum_lessons").fetchall():
    if not q_json:
        continue
    qs = json.loads(q_json)
    for q in qs:
        cursor.execute("""
            INSERT INTO questions (grade, topic, difficulty, question_text)
            VALUES (?, ?, ?, ?)
        """, (grade, topic, q["difficulty"], q["text"]))

conn.commit()

# 3. Final Re-indexing and Integrity Verification
print("\n==================================================")
print("FINAL RE-INDEXING & INTEGRITY AUDIT")
print("==================================================")
for g in ['3', '4', '5', '6']:
    cursor.execute("SELECT quarter FROM curriculum_lessons WHERE grade = ? GROUP BY quarter ORDER BY CAST(quarter AS INT)", (g,))
    quarters = [r[0] for r in cursor.fetchall()]
    for qtr in quarters:
        cursor.execute("SELECT id FROM curriculum_lessons WHERE grade = ? AND quarter = ? ORDER BY id", (g, qtr))
        q_rows = cursor.fetchall()
        for idx, (lid,) in enumerate(q_rows, 1):
            cursor.execute("UPDATE curriculum_lessons SET lesson_number = ? WHERE id = ?", (str(idx), lid))
conn.commit()

total_checked = 0
failed = 0
for g in ['3', '4', '5', '6']:
    cursor.execute("SELECT count(*) FROM curriculum_lessons WHERE grade = ?", (g,))
    l_cnt = cursor.fetchone()[0]
    cursor.execute("SELECT count(*) FROM topics WHERE grade = ?", (g,))
    t_cnt = cursor.fetchone()[0]
    cursor.execute("SELECT count(*) FROM questions WHERE grade = ?", (g,))
    q_cnt = cursor.fetchone()[0]
    print(f"Grade {g}: {l_cnt} curriculum lessons, {t_cnt} topics, {q_cnt} questions in bank.")

for r in cursor.execute("SELECT id, grade, topic, questions FROM curriculum_lessons").fetchall():
    qs = json.loads(r[3])
    for q in qs:
        total_checked += 1
        txt = q["text"]
        parts = txt.split('|')
        choices_str = ' '.join(parts[1:]).strip()
        matches = list(choice_pattern.finditer(choices_str))
        if len(matches) < 3:
            failed += 1
            print(f"[FAIL] G{r[1]} {r[2]}: {txt}")

print(f"\nAudit complete: {total_checked} total questions verified. Failed: {failed}")
conn.close()
print("==================================================")
