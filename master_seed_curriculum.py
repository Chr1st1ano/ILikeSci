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
print("ILIKESCI MASTER CURRICULUM RESEED & STANDARDIZER")
print("==================================================")

# Step 1: Wipe existing curriculum, slides, topics, and questions
cursor.execute("DELETE FROM curriculum_lessons")
cursor.execute("DELETE FROM lesson_slides")
cursor.execute("DELETE FROM topics")
cursor.execute("DELETE FROM questions")
conn.commit()
print("Cleared old curriculum tables.")

def normalize_choices(raw_text, default_correct="A"):
    # Clean up non-ascii or replacement characters
    t = raw_text
    t = re.sub(r'[\ufffd\?]+C\b', '°C', t)
    t = re.sub(r'water[\ufffd\?]+s\b', "water's", t)
    t = re.sub(r'Earth[\ufffd\?]+s\b', "Earth's", t)
    t = re.sub(r'Sun[\ufffd\?]+s\b', "Sun's", t)
    t = re.sub(r'butterfly[\ufffd\?]+s\b', "butterfly's", t)
    t = t.replace('\ufffd', "'").replace('\t', ' ')
    t = re.sub(r'\s+', ' ', t).strip()

    # Manual specific overrides
    if "boats float in water" in t.lower():
        stem = "Why do boats float in water?"
        c_dict = {
            "A": "Boats are made of wood and shapes that displace water",
            "B": "Man uses paddle to make the boat float in water",
            "C": "The buoyant force of water supports the boat's shape",
            "D": "The water current pushes the boat upward"
        }
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "A"

    if "protecting oneself from the heat of the sun" in t.lower():
        stem = "Which of the following shows proper way of protecting oneself from the heat of the sun?"
        c_dict = {
            "A": "Wearing protective clothing like long-sleeved clothes when working under the sun",
            "B": "Drinking plenty of water to keep hydrated during summer",
            "C": "Use wide-brimmed hat when working in the fields",
            "D": "All of these options"
        }
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "D"

    if "pot holder" in t.lower():
        stem = "Why do we need to use pot holder when handling hot casseroles or any hot cooking wares?"
        c_dict = {
            "A": "To protect our new cuticles from fading",
            "B": "To maintain the softness of our hands",
            "C": "To protect our hands from getting hurt or burned",
            "D": "None of the above"
        }
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "C"

    if "swim in a hot sunny day" in t.lower():
        stem = "What must you do if you want to swim in a hot sunny day?"
        c_dict = {
            "A": "Wear jacket so that your skin will not get burned",
            "B": "Wear protective footwear like boots and knee-high socks",
            "C": "Apply sunblock lotion to protect your skin from the sun",
            "D": "Use beach umbrella while swimming in the water"
        }
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "C"

    if "shoot system transports water" in t.lower():
        stem = "Which part of the shoot system transports water and nutrients from the roots to the leaves?"
        c_dict = {"A": "Phloem", "B": "Cambium", "C": "Xylem", "D": "Cortex"}
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "C"

    if "bear that eats both berries and fish" in t.lower():
        stem = "A bear that eats both berries and fish is classified as what?"
        c_dict = {"A": "Herbivore", "B": "Carnivore", "C": "Omnivore", "D": "Detritivore"}
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "C"

    if "first stage of the butterfly" in t.lower():
        stem = "What is the first stage of the butterfly's life cycle?"
        c_dict = {"A": "Egg", "B": "Caterpillar (Larva)", "C": "Pupa (Chrysalis)", "D": "Adult Butterfly"}
        return stem + " | A: " + c_dict["A"] + " | B: " + c_dict["B"] + " | C: " + c_dict["C"] + " | D: " + c_dict["D"], "A"

    # Separate stem from choices
    parts = t.split('|')
    stem = parts[0].strip()
    # Strip any dangling choice label at end of stem
    stem = re.sub(r'\s+[A-D][:\.\)]\s*$', '', stem).strip()

    choices_str = ' '.join(parts[1:]).strip() if len(parts) > 1 else ''
    if not choices_str:
        m = re.search(r'(?:^|[|\s]+)([A-D])[:.)\-]\s+', stem)
        if m:
            idx = m.start()
            choices_str = stem[idx:].strip()
            stem = stem[:idx].strip()

    pattern = re.compile(r'(?:^|[|\s]+)([A-D])[:.)\-]?\s*(.*?)(?=(?:[|\s]+[A-D][:.)\-]?\s*)|$)', re.DOTALL)
    matches = pattern.findall(choices_str)

    c_dict = {}
    for letter, ctext in matches:
        clean_val = ctext.strip().rstrip('|').strip()
        if clean_val and letter not in c_dict:
            c_dict[letter] = clean_val

    if len(c_dict) >= 2:
        out_choices = []
        for l in ['A', 'B', 'C', 'D']:
            if l in c_dict:
                out_choices.append(f"{l}: {c_dict[l]}")
        formatted = stem + " | " + " | ".join(out_choices)
        return formatted, (default_correct or 'A').upper()
    else:
        return t, (default_correct or 'A').upper()

# Step 2: Seed Grades 3, 5, 6 from JSON files in exports/
print("\n--- Seeding Grades 3, 5, and 6 from exports/ ---")
EXPORT_FILES = [
    os.path.join(BASE_DIR, "exports", "material_g3_matter.json"),
    os.path.join(BASE_DIR, "exports", "material_g3_life_science.json"),
    os.path.join(BASE_DIR, "exports", "material_g5_matter.json"),
    os.path.join(BASE_DIR, "exports", "material_g5_life_science.json"),
    os.path.join(BASE_DIR, "exports", "material_g6_matter.json"),
    os.path.join(BASE_DIR, "exports", "material_g6_life_science.json"),
]

seen_lessons = set()

for ef in EXPORT_FILES:
    if not os.path.exists(ef):
        print(f"Warning: {ef} not found")
        continue
    with open(ef, 'r', encoding='utf-8') as f:
        data = json.load(f)

    units = data.get("units", [])
    for u in units:
        grade = str(u.get("grade", "3"))
        quarter = str(u.get("quarter", "1"))
        lesson_num = str(u.get("lesson_number", "1"))
        topic = u.get("topic", "").strip()

        # Deduplicate
        key = f"{grade}-{topic.lower()}"
        if key in seen_lessons or not topic:
            continue
        seen_lessons.add(key)

        content = u.get("content", "")
        objectives = u.get("objectives", [])
        raw_qs = u.get("questions", [])

        norm_qs = []
        for q in raw_qs:
            q_txt = q.get("text", "") if isinstance(q, dict) else str(q)
            q_corr = q.get("correct", "A") if isinstance(q, dict) else "A"
            q_diff = q.get("difficulty", "Medium") if isinstance(q, dict) else "Medium"
            clean_txt, clean_corr = normalize_choices(q_txt, q_corr)
            norm_qs.append({
                "text": clean_txt,
                "correct": clean_corr,
                "difficulty": q_diff
            })

        cursor.execute("""
            INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, lesson_title, content, objectives, questions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        """, (grade, quarter, lesson_num, topic, topic, content, json.dumps(objectives), json.dumps(norm_qs)))
        lesson_id = cursor.lastrowid

        # Register topic
        cursor.execute("SELECT count(*) FROM topics WHERE grade = ? AND lower(topic_name) = lower(?)", (grade, topic))
        if cursor.fetchone()[0] == 0:
            cursor.execute("INSERT INTO topics (grade, topic_name) VALUES (?, ?)", (grade, topic))

        # Register slides
        slides = u.get("slides", [])
        for idx, s in enumerate(slides, 1):
            cursor.execute("""
                INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            """, (lesson_id, idx, s.get("title", f"Slide {idx}"), s.get("content", ""), s.get("slide_type", "content"), s.get("media_type", "none"), s.get("media_url", "")))

        # Register questions in question bank
        for q in norm_qs:
            cursor.execute("""
                INSERT INTO questions (grade, topic, difficulty, question_text)
                VALUES (?, ?, ?, ?)
            """, (grade, topic, q["difficulty"], q["text"]))

conn.commit()
print("Grades 3, 5, 6 base seeded.")

# Step 3: Add the 6 specialized units with full questions and slides
EXTRA_UNITS = [
    {
        "grade": "3", "quarter": "1", "lesson_number": "5",
        "topic": "Five Senses",
        "content": "The human body relies on five primary sensory organs to interact with the surroundings: Eyes for sight, Ears for hearing, Nose for smell, Tongue for taste, and Skin for touch. Taking care of these sensory organs through proper hygiene and safety prevents illness and injury.",
        "objectives": ["Identify the five sense organs and describe their functions.", "Practice health habits to protect the sense organs."],
        "slides": [
            ("The Five Senses", "Grade 3 Science\nExplore the five senses: Seeing, Hearing, Smelling, Tasting, and Touching!", "title", "image", ""),
            ("Eyes and Ears", "• Eyes: Detect light, colors, and motion.\n• Ears: Detect sound vibrations and help maintain balance.", "content", "none", ""),
            ("Nose and Tongue", "• Nose: Detects odors and scent particles.\n• Tongue: Houses taste buds for sweet, salty, sour, and bitter.", "content", "none", ""),
            ("The Skin", "• Skin is the largest organ of the human body, providing touch, pressure, and temperature sensation.", "content", "none", ""),
            ("Sense Organ Care", "• Avoid loud sounds\n• Wash hands and face daily\n• Do not insert sharp objects into ears or nose.", "step", "none", "")
        ],
        "questions": [
            {"text": "Which sense organ allows humans to see colors, shapes, and movement? | A: Eyes | B: Ears | C: Nose | D: Skin", "correct": "A", "difficulty": "Easy"},
            {"text": "Which sense organ detects sound vibrations in the environment? | A: Tongue | B: Ears | C: Eyes | D: Nose", "correct": "B", "difficulty": "Easy"},
            {"text": "What part of the body contains taste buds to identify sweet, sour, salty, and bitter flavors? | A: Nose | B: Tongue | C: Skin | D: Teeth", "correct": "B", "difficulty": "Medium"},
            {"text": "Which is the largest organ of the human body that provides the sense of touch and temperature? | A: Skin | B: Brain | C: Stomach | D: Liver", "correct": "A", "difficulty": "Medium"},
            {"text": "Why is it dangerous to clean inside the ear canal with sharp or hard objects? | A: It can damage the delicate eardrum and cause hearing loss | B: It makes the ear grow larger | C: It changes the color of the ear | D: It cools the brain too quickly", "correct": "A", "difficulty": "Hard"}
        ]
    },
    {
        "grade": "5", "quarter": "1", "lesson_number": "5",
        "topic": "Human Reproduction",
        "content": "The human reproductive system is essential for the continuation of human life. The female system includes ovaries, fallopian tubes, and the uterus. The male system includes testes and the sperm duct. During puberty, hormones trigger physical maturation.",
        "objectives": ["Describe the parts of the male and female reproductive systems.", "Explain the menstrual cycle and physical changes during puberty."],
        "slides": [
            ("Human Reproductive System", "Grade 5 Science: Structure and functions of human reproductive organs and healthy adolescent development.", "title", "image", ""),
            ("Female Reproductive Organs", "• Ovaries produce eggs and estrogen.\n• Fallopian tubes are where fertilization occurs.\n• Uterus holds and nourishes the developing baby.", "content", "none", ""),
            ("Male Reproductive Organs", "• Testes produce sperm and testosterone.\n• Scrotum maintains optimal temperature for sperm.", "content", "none", ""),
            ("Puberty & Hygiene", "• Physical changes occur during puberty due to hormones.\n• Regular bathing and menstrual hygiene are vital.", "step", "none", ""),
            ("Review & Recitation", "Reviewing reproductive biology concepts for oral recitation.", "quiz", "none", "")
        ],
        "questions": [
            {"text": "Which female reproductive organ produces egg cells (ova) and hormones like estrogen? | A: Ovaries | B: Uterus | C: Fallopian tubes | D: Cervix", "correct": "A", "difficulty": "Easy"},
            {"text": "Which male reproductive organ produces sperm cells and testosterone? | A: Testes | B: Prostate | C: Bladder | D: Urethra", "correct": "A", "difficulty": "Easy"},
            {"text": "Where does fertilization normally take place when a sperm unites with an egg cell? | A: Fallopian tube (Oviduct) | B: Uterus | C: Ovary | D: Vagina", "correct": "A", "difficulty": "Medium"},
            {"text": "What is the monthly shedding of the uterine lining in females called? | A: Menstruation | B: Ovulation | C: Gestation | D: Puberty", "correct": "A", "difficulty": "Medium"},
            {"text": "Which secondary sex characteristic typically develops in boys during puberty due to testosterone? | A: Deepening of the voice and growth of facial hair | B: Widening of the hips | C: Production of breast milk | D: Loss of skeletal muscle", "correct": "A", "difficulty": "Hard"}
        ]
    },
    {
        "grade": "5", "quarter": "2", "lesson_number": "4",
        "topic": "Electricity and Circuits",
        "content": "Electricity powers everyday appliances through the movement of electric charges. A circuit is a closed path through which electric current flows. Conductors permit charge flow while insulators resist it.",
        "objectives": ["Distinguish conductors from insulators.", "Construct and diagram simple series and parallel circuits."],
        "slides": [
            ("Electricity and Circuits", "Grade 5 Physical Science: Electrical energy, conductors, insulators, and circuit safety.", "title", "image", ""),
            ("Conductors vs Insulators", "• Conductors: Metals (copper, iron, aluminum) allow current flow.\n• Insulators: Rubber, plastic, dry wood block current.", "content", "none", ""),
            ("Simple Circuits", "A complete circuit requires: Power Source (battery), Load (bulb), and Conductive Path (wire) with a Switch.", "content", "none", ""),
            ("Circuit Safety", "• Never touch switches with wet hands\n• Use fuses and circuit breakers to prevent fires.", "step", "none", ""),
            ("Assessment Check", "Test your knowledge on circuits and electrical conductors.", "quiz", "none", "")
        ],
        "questions": [
            {"text": "Which material allows electric current to flow through it easily? | A: Copper wire | B: Rubber band | C: Dry wood | D: Plastic ruler", "correct": "A", "difficulty": "Easy"},
            {"text": "What type of circuit provides a continuous, unbroken path for electricity to flow? | A: Closed circuit | B: Open circuit | C: Short circuit | D: Broken circuit", "correct": "A", "difficulty": "Easy"},
            {"text": "Why are electrical wires commonly coated with plastic or rubber? | A: Plastic is an insulator that prevents accidental electric shocks | B: Plastic makes wires heavier | C: Plastic speeds up electrical current | D: Plastic generates more voltage", "correct": "A", "difficulty": "Medium"},
            {"text": "In a series circuit with three bulbs, what happens if one bulb is unscrewed or burns out? | A: All remaining bulbs immediately turn off | B: The remaining bulbs become brighter | C: The circuit continues working normally | D: The battery explodes", "correct": "A", "difficulty": "Medium"},
            {"text": "Which safety device automatically breaks the circuit when excessive current flows to prevent fire? | A: Circuit breaker or fuse | B: Voltmeter | C: Light switch | D: Battery terminal", "correct": "A", "difficulty": "Hard"}
        ]
    },
    {
        "grade": "5", "quarter": "2", "lesson_number": "5",
        "topic": "Solar System",
        "content": "Our solar system consists of the Sun at its center, orbited by eight planets, dwarf planets, moons, asteroids, and comets. Earth's rotation causes day and night, while its revolution around the Sun creates the year.",
        "objectives": ["Identify the planets of the solar system in order.", "Explain Earth's rotation and revolution."],
        "slides": [
            ("The Solar System", "Grade 5 Earth & Space: Journey through the solar system, planets, and celestial motions.", "title", "image", ""),
            ("The Sun and Inner Planets", "• The Sun is a yellow dwarf star powering life.\n• Inner rocky planets: Mercury, Venus, Earth, Mars.", "content", "none", ""),
            ("Outer Gas Giants", "• Jupiter: Largest planet with Great Red Spot.\n• Saturn (rings), Uranus, and Neptune (ice giants).", "content", "none", ""),
            ("Motions of the Earth", "• Rotation (24 hours) -> Day and Night\n• Revolution (365.25 days) -> 1 Year cycle.", "step", "none", ""),
            ("Space Quiz", "Oral recitation questions on the solar system and planetary motion.", "quiz", "none", "")
        ],
        "questions": [
            {"text": "What is the center of our solar system that provides light and heat to all planets? | A: The Sun | B: The Moon | C: Jupiter | D: Polaris", "correct": "A", "difficulty": "Easy"},
            {"text": "Which is the largest planet in our solar system? | A: Jupiter | B: Saturn | C: Earth | D: Mars", "correct": "A", "difficulty": "Easy"},
            {"text": "What causes the cycle of day and night on Earth? | A: Earth's rotation on its axis once every 24 hours | B: Earth's revolution around the Sun | C: The movement of the Moon around Earth | D: Solar flares from the Sun", "correct": "A", "difficulty": "Medium"},
            {"text": "Which planet is known as the 'Red Planet' due to iron oxide on its surface? | A: Mars | B: Venus | C: Mercury | D: Neptune", "correct": "A", "difficulty": "Medium"},
            {"text": "Why does a year on Earth last approximately 365.25 days? | A: That is the time it takes Earth to complete one full revolution around the Sun | B: That is how long the Moon takes to orbit Earth | C: That is the time the Sun takes to rotate once | D: That is how long tides take to cycle", "correct": "A", "difficulty": "Hard"}
        ]
    },
    {
        "grade": "6", "quarter": "2", "lesson_number": "4",
        "topic": "Patterns of Motion",
        "content": "Forces govern how objects move in our physical universe. Gravity pulls objects toward Earth, friction resists sliding motion, and simple machines (levers, pulleys, inclined planes) multiply force or change direction to make work easier.",
        "objectives": ["Describe effects of gravity and friction on movement.", "Identify simple machines and calculate speed."],
        "slides": [
            ("Patterns of Motion", "Grade 6 Physics: Gravity, friction, speed, and simple machines in daily life.", "title", "image", ""),
            ("Gravity and Friction", "• Gravity: Downward attraction toward Earth's core.\n• Friction: Contact force opposing motion.", "content", "none", ""),
            ("Simple Machines", "• Levers, Pulleys, Inclined Planes, Screws, Wedges, Wheel and Axle.", "content", "none", ""),
            ("Calculating Speed", "• Speed = Distance ÷ Time (e.g. 100 meters in 10 seconds = 10 m/s).", "step", "none", ""),
            ("Motion Challenge", "Recitation review on force, acceleration, and machines.", "quiz", "none", "")
        ],
        "questions": [
            {"text": "What invisible force pulls all physical objects downward toward the center of the Earth? | A: Gravity | B: Friction | C: Magnetism | D: Tension", "correct": "A", "difficulty": "Easy"},
            {"text": "Which force opposes the motion between two surfaces sliding against each other? | A: Friction | B: Gravity | C: Inertia | D: Buoyancy", "correct": "A", "difficulty": "Easy"},
            {"text": "How can you reduce friction between moving mechanical parts in a bicycle chain? | A: Apply lubricating oil or grease | B: Pour sand into the chain | C: Wet the chain with saltwater | D: Scratch the metal surfaces", "correct": "A", "difficulty": "Medium"},
            {"text": "Which simple machine consists of a rigid bar that pivots around a fixed point called a fulcrum? | A: Lever | B: Pulley | C: Inclined plane | D: Screw", "correct": "A", "difficulty": "Medium"},
            {"text": "If a car travels a distance of 120 kilometers in 2 hours, what is its average speed? | A: 60 km/h | B: 240 km/h | C: 30 km/h | D: 100 km/h", "correct": "A", "difficulty": "Hard"}
        ]
    },
    {
        "grade": "6", "quarter": "2", "lesson_number": "5",
        "topic": "Volcanoes and Seasons",
        "content": "Located along the Pacific Ring of Fire, the Philippines features magnificent active and dormant volcanoes like Mayon, Pinatubo, and Taal. Understanding volcanic processes and tropical seasonal weather patterns saves lives.",
        "objectives": ["Describe volcanic structures and hazards.", "Explain wet and dry seasons in the Philippines."],
        "slides": [
            ("Volcanoes and Seasons", "Grade 6 Earth Science: Philippine volcanism, disaster preparedness, and tropical climate.", "title", "image", ""),
            ("Philippine Volcanoes", "• Mayon (perfect cone in Albay), Taal (lake caldera in Batangas), Pinatubo.\n• Magma below surface; Lava once erupted.", "content", "none", ""),
            ("Volcanic Hazards", "• Lahar: Rain-triggered volcanic mudflow\n• Pyroclastic flows and toxic ashfall.", "step", "none", ""),
            ("Philippine Climate Seasons", "• Tropical climate: Dry season (Amihan) and Wet season (Habagat).", "content", "none", ""),
            ("Earth & Climate Quiz", "Evaluation on Philippine volcanology and meteorology.", "quiz", "none", "")
        ],
        "questions": [
            {"text": "Which famous Philippine volcano in Albay is renowned worldwide for its near-perfect cone shape? | A: Mayon Volcano | B: Taal Volcano | C: Mount Pinatubo | D: Mount Apo", "correct": "A", "difficulty": "Easy"},
            {"text": "What molten rock material is called when it is still beneath Earth's surface? | A: Magma | B: Lava | C: Ash | D: Basalt", "correct": "A", "difficulty": "Easy"},
            {"text": "What are the two predominant seasons experienced in the tropical climate of the Philippines? | A: Wet season and Dry season | B: Summer and Winter | C: Spring and Autumn | D: Monsoon and Blizzard", "correct": "A", "difficulty": "Medium"},
            {"text": "What volcanic hazard consists of fast-moving mudflows of volcanic ash and water during heavy rains? | A: Lahar | B: Lava fountain | C: Tephra fall | D: Pyroclastic cloud", "correct": "A", "difficulty": "Medium"},
            {"text": "Why is the Philippines prone to frequent earthquakes and active volcanic eruptions? | A: It is situated along the Pacific Ring of Fire | B: It is surrounded by deep ocean water | C: It is near the equator | D: It has many tropical rainforests", "correct": "A", "difficulty": "Hard"}
        ]
    }
]

for eu in EXTRA_UNITS:
    cursor.execute("""
        INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, lesson_title, content, objectives, questions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    """, (eu["grade"], eu["quarter"], eu["lesson_number"], eu["topic"], eu["topic"], eu["content"], json.dumps(eu["objectives"]), json.dumps(eu["questions"])))
    lid = cursor.lastrowid

    cursor.execute("SELECT count(*) FROM topics WHERE grade = ? AND lower(topic_name) = lower(?)", (eu["grade"], eu["topic"]))
    if cursor.fetchone()[0] == 0:
        cursor.execute("INSERT INTO topics (grade, topic_name) VALUES (?, ?)", (eu["grade"], eu["topic"]))

    for idx, s in enumerate(eu["slides"], 1):
        cursor.execute("""
            INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (lid, idx, s[0], s[1], s[2], s[3], s[4]))

    for q in eu["questions"]:
        cursor.execute("""
            INSERT INTO questions (grade, topic, difficulty, question_text)
            VALUES (?, ?, ?, ?)
        """, (eu["grade"], eu["topic"], q["difficulty"], q["text"]))

conn.commit()
print("Extra specialized units seeded.")

# Step 4: Seed Grade 4 with the 20 official DepEd MATATAG lessons
print("\n--- Seeding Grade 4 from official DepEd documents ---")

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
            # Match choices formatted like A. text, B. text
            c_matches = list(re.finditer(r'([A-D])[\.\)]\s*([^A-D\.\)]+)', line))
            if c_matches:
                for cm in c_matches:
                    current_q['choices'][cm.group(1).upper()] = cm.group(2).strip().rstrip('|').strip()
            elif any(line.startswith(f'{opt}.') or line.startswith(f'{opt})') for opt in ['A','B','C','D']):
                current_q['choices'][line[0].upper()] = line[2:].strip().rstrip('|').strip()
            else:
                if not current_q['choices']:
                    current_q['text'] += ' ' + line

    if current_q and current_q.get('text') and len(current_q.get('text')) > 8:
        questions.append(current_q)

    return questions

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

for u in G4_UNITS:
    quarter = u["quarter"]
    lesson_no = u["lesson_number"]
    topic = u["topic"]
    doc_file = u["doc"]
    q_start, q_end = u["q_range"]

    fp = find_file(doc_file)
    raw_questions = parse_docx_questions_with_keys(fp, "4", quarter, topic)
    selected = [q for q in raw_questions if q_start <= q['num'] <= q_end]

    norm_qs = []
    for q in selected:
        choice_str = ""
        for letter in ['A', 'B', 'C', 'D']:
            if letter in q['choices']:
                choice_str += f" | {letter}: {q['choices'][letter]}"
        full_text = q['text'] + choice_str
        diff = "Easy" if (q['num'] % 3 == 1) else "Medium" if (q['num'] % 3 == 2) else "Hard"
        clean_txt, clean_corr = normalize_choices(full_text, q.get('correct', 'A'))
        norm_qs.append({
            "text": clean_txt,
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
print("Grade 4 seeded with 20 canonical lessons.")

# Step 5: Final sequential re-indexing per quarter for all grades
print("\n--- Sequential lesson re-indexing ---")
for g in ['3', '4', '5', '6']:
    cursor.execute("SELECT quarter FROM curriculum_lessons WHERE grade = ? GROUP BY quarter ORDER BY CAST(quarter AS INT)", (g,))
    quarters = [r[0] for r in cursor.fetchall()]
    for qtr in quarters:
        cursor.execute("SELECT id FROM curriculum_lessons WHERE grade = ? AND quarter = ? ORDER BY id", (g, qtr))
        q_rows = cursor.fetchall()
        for idx, (lid,) in enumerate(q_rows, 1):
            cursor.execute("UPDATE curriculum_lessons SET lesson_number = ? WHERE id = ?", (str(idx), lid))
conn.commit()

# Step 6: Full validation of all choices
print("\n==================================================")
print("COMPREHENSIVE INTEGRITY AUDIT")
print("==================================================")
choice_pattern = re.compile(r'(?:^|[|\s]+)([A-D])[:.)\-]\s*(.*?)(?=(?:[|\s]+[A-D][:.)\-]\s*)|$)', re.DOTALL)

for g in ['3', '4', '5', '6']:
    cursor.execute("SELECT count(*) FROM curriculum_lessons WHERE grade = ?", (g,))
    l_cnt = cursor.fetchone()[0]
    cursor.execute("SELECT count(*) FROM topics WHERE grade = ?", (g,))
    t_cnt = cursor.fetchone()[0]
    cursor.execute("SELECT count(*) FROM questions WHERE grade = ?", (g,))
    q_cnt = cursor.fetchone()[0]
    print(f"Grade {g}: {l_cnt} lessons, {t_cnt} topics, {q_cnt} questions in bank.")

failed = 0
total_checked = 0
for r in cursor.execute("SELECT id, grade, topic, questions FROM curriculum_lessons").fetchall():
    qs = json.loads(r[3])
    for q in qs:
        total_checked += 1
        txt = q["text"]
        parts = txt.split('|')
        choices_str = ' '.join(parts[1:]).strip()
        matches = choice_pattern.findall(choices_str)
        if len(matches) < 3:
            failed += 1
            print(f"[FAIL] G{r[1]} {r[2]} -> Found {len(matches)} choices: {txt}")

print(f"\nAudit complete: {total_checked} total questions verified. Failed: {failed}")

conn.close()
print("==================================================")
