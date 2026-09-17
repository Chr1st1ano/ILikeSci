<?php
/**
 * ILikeSci — Official Curriculum Importer for:
 * 1. "life matter.txt" (Scientific Inquiry in Life Science)
 * 2. "matter and materials.txt" (Properties and Uses of Materials)
 * 
 * Synchronizes database curriculum_lessons, lesson_slides, questions, and topics.
 * Idempotent, robust, engine-agnostic (MySQL and SQLite).
 */

require_once __DIR__ . '/db.php';

$isCli = (php_sapi_name() === 'cli');
if (!$isCli && !headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

function out($msg, $isCli) {
    if ($isCli) {
        echo $msg . "\n";
    } else {
        echo "<p>" . htmlspecialchars($msg) . "</p>\n";
    }
}

out("=== ILikeSci Curriculum Importer: Life & Matter ===", $isCli);

// -------------------------------------------------------------
// 1. Parse 'life matter.txt'
// -------------------------------------------------------------
$lifeFile = __DIR__ . '/life matter.txt';
if (!file_exists($lifeFile)) {
    out("ERROR: 'life matter.txt' not found at " . $lifeFile, $isCli);
    exit(1);
}
$lifeRaw = file_get_contents($lifeFile);

$lifeLesson = [
    'topic' => 'Scientific Inquiry in Life Science',
    'competencies' => [
        'Perform simple guided investigations using the skills of observing, predicting, and measuring to gather data and answer questions about the natural world.'
    ],
    'content' => "LESSON 1: Scientific Inquiry in Life Science\n\n" .
        "Learning Competency:\n" .
        "Perform simple guided investigations using the skills of observing, predicting, and measuring to gather data and answer questions about the natural world.\n\n" .
        "LET'S EXPLORE\n" .
        "Have you ever wondered why a plant grows toward sunlight? Or why some animals behave differently when they are in different places?\n" .
        "Scientists ask questions about things they observe. They use simple investigations to find answers.\n\n" .
        "WHAT IS SCIENTIFIC INQUIRY?\n" .
        "Scientific inquiry is a way of learning about the natural world by asking questions, observing, making predictions, gathering information, and using evidence.\n\n" .
        "For young scientists, three important skills are:\n" .
        "• Observing: Using our senses or tools to gather information (e.g., Looking at the leaves of a plant)\n" .
        "• Predicting: Making a reasonable guess about what may happen (e.g., Predicting that a plant near sunlight will grow well)\n" .
        "• Measuring: Using tools or standard units to determine something (e.g., Measuring the height of a plant)\n\n" .
        "SIMPLE INVESTIGATION\n" .
        "A simple investigation follows these steps:\n" .
        "Ask → Predict → Observe → Measure → Record → Explain\n\n" .
        "Example:\n" .
        "• Question: Does a plant grow taller over time?\n" .
        "• Prediction: The plant will become taller after several days.\n" .
        "• Observation: The plant is measured each day.\n" .
        "• Measurement: Its height is recorded in centimeters.\n" .
        "• Conclusion: The observations and measurements are used to explain what happened.\n\n" .
        "MULTIMEDIA SUPPORT:\n" .
        "• A short video of a plant growing\n" .
        "• Pictures of scientists observing living things\n" .
        "• A simple time-lapse of plant growth\n\n" .
        "INTERACTIVE ACTIVITY: Young Scientist!\n" .
        "1. Ana looks closely at a flower. -> Observing\n" .
        "2. Ben says the seed will grow into a plant. -> Predicting\n" .
        "3. Carlo uses a ruler to measure a leaf. -> Measuring\n" .
        "4. Dana records the number of leaves on a plant. -> Observing/Recording\n" .
        "5. Ella guesses that the plant will grow taller next week. -> Predicting\n\n" .
        "KEY TAKEAWAYS:\n" .
        "• Scientists ask questions about the natural world.\n" .
        "• Observing means gathering information using senses.\n" .
        "• Predicting means making a reasonable guess about what may happen.\n" .
        "• Measuring uses tools or standard units to determine quantities.\n" .
        "• Scientists use evidence from observations and measurements to answer questions.",
    'slides' => [
        [
            'title' => 'Scientific Inquiry in Life Science',
            'content' => "Term 1 — Lesson 1: Scientific Inquiry in Life Science\n\n" .
                "• Have you ever wondered why a plant grows toward sunlight?\n" .
                "• Why do animals behave differently in different habitats?\n\n" .
                "Scientists ask questions about the natural world and conduct simple investigations to discover answers!",
            'slide_type' => 'title',
            'media_type' => 'image',
            'media_url' => 'https://images.unsplash.com/photo-1530587191325-3db32d826c18?w=800'
        ],
        [
            'title' => 'Three Core Inquiry Skills',
            'content' => "What is Scientific Inquiry?\n" .
                "A systematic way of learning about nature by asking questions and using evidence.\n\n" .
                "1. Observing:\n" .
                "   Using senses or tools to gather information (e.g. Looking closely at leaves)\n" .
                "2. Predicting:\n" .
                "   Making a reasonable guess about what will happen (e.g. Plant will grow toward light)\n" .
                "3. Measuring:\n" .
                "   Using tools or units to determine quantity (e.g. Measuring plant height with a ruler)",
            'slide_type' => 'content',
            'media_type' => 'step',
            'media_url' => "Skill 1: Observing (Senses & Magnifier)\nSkill 2: Predicting (Reasonable Guess)\nSkill 3: Measuring (Standard Units & Tools)"
        ],
        [
            'title' => 'Steps of a Simple Investigation',
            'content' => "Follow the Scientific Investigation Cycle:\n" .
                "Ask → Predict → Observe → Measure → Record → Explain\n\n" .
                "Plant Growth Experiment Example:\n" .
                "• Ask: Does a bean plant grow taller over 7 days?\n" .
                "• Predict: Yes, the seedling will become taller.\n" .
                "• Observe: Check stem and leaves daily.\n" .
                "• Measure: Use a metric ruler to log height in centimeters.\n" .
                "• Explain: Sunlight and water fueled cell elongation!",
            'slide_type' => 'step',
            'media_type' => 'step',
            'media_url' => "Step 1: Ask a question\nStep 2: Predict outcome\nStep 3: Observe changes\nStep 4: Measure with ruler\nStep 5: Record data\nStep 6: Explain evidence"
        ],
        [
            'title' => 'Interactive Activity: Young Scientist!',
            'content' => "Identify whether each situation is Observing, Predicting, or Measuring:\n\n" .
                "1. Ana looks closely at a flower. -> [Observing]\n" .
                "2. Ben says the seed will grow into a plant. -> [Predicting]\n" .
                "3. Carlo uses a ruler to measure a leaf. -> [Measuring]\n" .
                "4. Dana records the number of leaves on a plant. -> [Observing/Recording]\n" .
                "5. Ella guesses the plant will grow taller next week. -> [Predicting]",
            'slide_type' => 'interactive',
            'media_type' => 'none',
            'media_url' => ''
        ],
        [
            'title' => 'Assessment & Key Takeaways',
            'content' => "Quick Review:\n" .
                "• Observing = gathering information with our senses\n" .
                "• Predicting = making reasonable guesses about the future\n" .
                "• Measuring = determining quantity with standard units/tools\n" .
                "• Evidence is recorded to prove conclusions scientifically!\n\n" .
                "Ready for the oral recitation quiz? Turn on your student device!",
            'slide_type' => 'quiz',
            'media_type' => 'none',
            'media_url' => ''
        ]
    ],
    'questions' => [
        [
            'text' => "What skill involves gathering information using our senses? | A: Predicting B: Observing C: Guessing D: Drawing",
            'correct' => 'B',
            'difficulty' => 'Easy'
        ],
        [
            'text' => "What do we do when we say what we think will happen? | A: Measuring B: Predicting C: Recording D: Comparing",
            'correct' => 'B',
            'difficulty' => 'Easy'
        ],
        [
            'text' => "Which tool can be used to measure the height of a plant? | A: Ruler B: Spoon C: Plate D: Pencil case",
            'correct' => 'A',
            'difficulty' => 'Easy'
        ],
        [
            'text' => "Why do scientists record their observations and measurements? | A: To decorate their notebook B: To keep evidence of what they found C: To make the investigation longer D: To avoid asking questions",
            'correct' => 'B',
            'difficulty' => 'Medium'
        ],
        [
            'text' => "Which sequence shows a simple investigation? | A: Ask → Predict → Observe → Measure B: Measure → Sleep → Predict → Ask C: Draw → Play → Ask → Sleep D: Guess → Stop → Measure → Forget",
            'correct' => 'A',
            'difficulty' => 'Medium'
        ]
    ]
];

// -------------------------------------------------------------
// 2. Parse 'matter and materials.txt'
// -------------------------------------------------------------
$matterFile = __DIR__ . '/matter and materials.txt';
if (!file_exists($matterFile)) {
    out("ERROR: 'matter and materials.txt' not found at " . $matterFile, $isCli);
    exit(1);
}
$matterRaw = file_get_contents($matterFile);

$matterLesson = [
    'topic' => 'Properties and Uses of Materials',
    'competencies' => [
        'Describe and compare the observable physical properties of solid materials, such as hardness, shininess, roughness, smoothness, flexibility, and stretchability.',
        'Explain how changing the shape or form of solid materials, such as shaping, pressing, hammering, joining, or cutting, can make them useful for different purposes.',
        'Describe the observable properties and everyday uses of common metal materials in Philippine homes, schools, and communities, such as iron or steel, galvanized iron sheets or yero, aluminum, copper, and stainless steel.'
    ],
    'content' => "TERM 1 LESSON 1: Properties and Uses of Materials\n\n" .
        "Learning Competencies:\n" .
        "1. Describe and compare observable physical properties of solid materials: hardness, shininess, roughness, smoothness, flexibility, and stretchability.\n" .
        "2. Explain how changing shape/form (shaping, pressing, hammering, joining, cutting) makes them useful.\n" .
        "3. Describe properties and uses of metals in Philippine homes, schools, and communities (iron/steel, yero, aluminum, copper, stainless steel).\n\n" .
        "LET'S EXPLORE\n" .
        "Look around the classroom. What objects can you see? What are they made of?\n" .
        "Materials have different properties that we can observe and describe.\n\n" .
        "DISCUSSION: Observable Properties of Materials\n" .
        "• Hardness — how hard a material is (e.g., Rock -> hard)\n" .
        "• Shininess — how much light a material reflects (e.g., Metal spoon -> shiny)\n" .
        "• Roughness — having an uneven surface (e.g., Sandpaper -> rough)\n" .
        "• Smoothness — having an even surface (e.g., Glass -> smooth)\n" .
        "• Flexibility — ability to bend without breaking (e.g., Rubber -> flexible)\n" .
        "• Stretchability — ability to stretch when pulled (e.g., Rubber band -> stretchable)\n\n" .
        "CHANGING MATERIALS\n" .
        "The shape or form of solid materials can be changed by:\n" .
        "shaping • pressing • hammering • joining • cutting\n" .
        "These changes make materials useful for different purposes.\n\n" .
        "METALS AROUND US (Philippine Context)\n" .
        "• Iron or steel — chairs, school gates, construction tools\n" .
        "• Galvanized iron sheets (yero) — typhoon-resistant roofing\n" .
        "• Aluminum — cooking containers, lunchboxes, window frames\n" .
        "• Copper — electrical wiring and conductors\n" .
        "• Stainless steel — spoons, forks, surgical instruments, utensils\n\n" .
        "INTERACTIVE ACTIVITY: What Property Is It?\n" .
        "A rubber band becomes longer when pulled. What property does it show?\n" .
        "Answer: C — Stretchability\n\n" .
        "KEY TAKEAWAYS:\n" .
        "• Materials have different observable physical properties.\n" .
        "• Materials can be changed in shape or form to make them useful.\n" .
        "• Metals have diverse properties and essential everyday applications.",
    'slides' => [
        [
            'title' => 'Properties and Uses of Materials',
            'content' => "Term 1 — Lesson 1: Properties and Uses of Materials\n\n" .
                "Look around your classroom:\n" .
                "• Desks, windows, water bottles, notebooks...\n" .
                "Every object is made of specific materials chosen for their unique properties!",
            'slide_type' => 'title',
            'media_type' => 'image',
            'media_url' => 'https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?w=800'
        ],
        [
            'title' => '6 Observable Properties of Materials',
            'content' => "Learn how to describe and compare solid materials:\n\n" .
                "1. Hardness: Resists scratching and deformation (Rock, iron)\n" .
                "2. Shininess: Reflects light with high luster (Polished metal spoon)\n" .
                "3. Roughness: Uneven, coarse surface texture (Sandpaper, tree bark)\n" .
                "4. Smoothness: Even, level surface texture (Window glass, marble)\n" .
                "5. Flexibility: Ability to bend easily without breaking (Rubber strip)\n" .
                "6. Stretchability: Ability to elongate when pulled (Rubber band)",
            'slide_type' => 'content',
            'media_type' => 'step',
            'media_url' => "Property 1: Hardness (Rock)\nProperty 2: Shininess (Metal Spoon)\nProperty 3: Roughness (Sandpaper)\nProperty 4: Smoothness (Glass)\nProperty 5: Flexibility (Rubber)\nProperty 6: Stretchability (Rubber band)"
        ],
        [
            'title' => 'Changing Solid Materials for Use',
            'content' => "We can alter materials through 5 physical processes:\n" .
                "• Shaping: Molding clay into bowls\n" .
                "• Pressing: Flattening dough or recycled paper\n" .
                "• Hammering: Beating hot iron into scythes and bolo blades\n" .
                "• Joining: Welding steel rebar or gluing wood joints\n" .
                "• Cutting: Trimming cloth into school uniforms",
            'slide_type' => 'step',
            'media_type' => 'step',
            'media_url' => "Action 1: Shaping\nAction 2: Pressing\nAction 3: Hammering\nAction 4: Joining\nAction 5: Cutting"
        ],
        [
            'title' => 'Common Metals in Philippine Communities',
            'content' => "Key metals used in homes, schools, and barangays:\n\n" .
                "• Iron or Steel: Heavy gates, school armchairs, construction nails\n" .
                "• Galvanized Iron (Yero): Lightweight, weather-resistant roofing\n" .
                "• Aluminum: Non-rusting pots, casserole dishes, window frames\n" .
                "• Copper: Highly conductive electrical wiring for school lights\n" .
                "• Stainless Steel: Rust-free dining spoons, forks, and surgical tools",
            'slide_type' => 'content',
            'media_type' => 'none',
            'media_url' => ''
        ],
        [
            'title' => 'Interactive Check & Quick Assessment',
            'content' => "What Property Is It?\n" .
                "• A rubber band becomes longer when pulled: Stretchability!\n" .
                "• A copper wire can bend around corners into electrical conduits: Flexibility!\n" .
                "• Yero protects our classrooms from monsoon downpours: Water resistance & Durability!\n\n" .
                "Remember:\n" .
                "Materials are deliberately engineered to match their practical purpose.",
            'slide_type' => 'quiz',
            'media_type' => 'none',
            'media_url' => ''
        ]
    ],
    'questions' => [
        [
            'text' => "Which property means a material can bend? | A: Hardness B: Flexibility C: Shininess D: Transparency",
            'correct' => 'B',
            'difficulty' => 'Easy'
        ],
        [
            'text' => "Which material is stretchable? | A: Rubber band B: Glass C: Rock D: Wood",
            'correct' => 'A',
            'difficulty' => 'Easy'
        ],
        [
            'text' => "Which metal is commonly used for electrical wires? | A: Copper B: Yero C: Stainless steel D: Lead",
            'correct' => 'A',
            'difficulty' => 'Medium'
        ],
        [
            'text' => "Which material is commonly used for roofing in Philippine homes? | A: Rubber B: Yero (Galvanized iron) C: Paper D: Cardboard",
            'correct' => 'B',
            'difficulty' => 'Medium'
        ],
        [
            'text' => "Why do people change the shape of solid materials? | A: To make them useful for different purposes B: To make them disappear C: To make them useless D: To break them permanently",
            'correct' => 'A',
            'difficulty' => 'Hard'
        ]
    ]
];

// -------------------------------------------------------------
// 3. Database Ingestion Helper Function
// -------------------------------------------------------------
function ingestUnit($pdo, $unitData, $targetGrades, $quarter = '1', $lessonNumber = '1') {
    $insertedLessons = 0;
    $insertedSlides = 0;
    $insertedQuestions = 0;

    foreach ($targetGrades as $grade) {
        // A. Register Topic
        $topic = $unitData['topic'];
        $stmtTCheck = $pdo->prepare("SELECT id FROM topics WHERE grade = ? AND topic_name = ?");
        $stmtTCheck->execute([$grade, $topic]);
        $existingTopicId = $stmtTCheck->fetchColumn();
        if (!$existingTopicId) {
            $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
            $stmtTIns->execute([$grade, $topic]);
        }

        // B. Upsert Curriculum Lesson
        $stmtLCheck = $pdo->prepare("SELECT id FROM curriculum_lessons WHERE grade = ? AND quarter = ? AND lesson_number = ?");
        $stmtLCheck->execute([$grade, $quarter, $lessonNumber]);
        $existingLessonId = $stmtLCheck->fetchColumn();

        if ($existingLessonId) {
            $stmtUpdate = $pdo->prepare("UPDATE curriculum_lessons SET topic = ?, content = ?, objectives = ?, questions = ? WHERE id = ?");
            $stmtUpdate->execute([
                $topic,
                $unitData['content'],
                json_encode($unitData['competencies'], JSON_UNESCAPED_UNICODE),
                json_encode($unitData['questions'], JSON_UNESCAPED_UNICODE),
                $existingLessonId
            ]);
            $lessonId = $existingLessonId;
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $grade,
                $quarter,
                $lessonNumber,
                $topic,
                $unitData['content'],
                json_encode($unitData['competencies'], JSON_UNESCAPED_UNICODE),
                json_encode($unitData['questions'], JSON_UNESCAPED_UNICODE)
            ]);
            $lessonId = $pdo->lastInsertId();
        }
        $insertedLessons++;

        // C. Refresh Lesson Slides
        $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$lessonId]);
        $stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($unitData['slides'] as $sIdx => $s) {
            $stmtSlide->execute([
                $lessonId,
                $sIdx + 1,
                $s['title'],
                $s['content'],
                $s['slide_type'],
                $s['media_type'] ?? 'none',
                $s['media_url'] ?? ''
            ]);
            $insertedSlides++;
        }

        // D. Insert Questions into questions bank
        foreach ($unitData['questions'] as $q) {
            // Check if question text already exists for this grade
            $stmtQCheck = $pdo->prepare("SELECT id FROM questions WHERE grade = ? AND question_text = ?");
            $stmtQCheck->execute([$grade, $q['text']]);
            if (!$stmtQCheck->fetchColumn()) {
                $stmtQIns = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text, type) VALUES (?, ?, ?, ?, 'multiple-choice')");
                $stmtQIns->execute([
                    $grade,
                    $topic,
                    $q['difficulty'],
                    $q['text']
                ]);
                $insertedQuestions++;
            }
        }
    }

    return [
        'lessons' => $insertedLessons,
        'slides' => $insertedSlides,
        'questions' => $insertedQuestions
    ];
}

// Ingest Scientific Inquiry in Life Science for Grade 3 (Q1 L1) and Grade 4 (Q2 L1)
out("Ingesting 'Scientific Inquiry in Life Science'...", $isCli);
$resLife = ingestUnit($pdo, $lifeLesson, ['3', '4'], '1', '1');
out("  -> Lessons processed: {$resLife['lessons']}, Slides created: {$resLife['slides']}, Questions added: {$resLife['questions']}", $isCli);

// Ingest Properties and Uses of Materials for Grade 3 (Q1 L2) and Grade 4 (Q1 L1)
out("Ingesting 'Properties and Uses of Materials'...", $isCli);
$resMatter = ingestUnit($pdo, $matterLesson, ['3', '4'], '1', '2');
out("  -> Lessons processed: {$resMatter['lessons']}, Slides created: {$resMatter['slides']}, Questions added: {$resMatter['questions']}", $isCli);

// Re-verify counts in database
$lessonCount = $pdo->query("SELECT COUNT(*) FROM curriculum_lessons")->fetchColumn();
$slideCount = $pdo->query("SELECT COUNT(*) FROM lesson_slides")->fetchColumn();
$questionCount = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$topicCount = $pdo->query("SELECT COUNT(*) FROM topics")->fetchColumn();

out("\n=== Ingestion Complete! ===", $isCli);
out("Total Curriculum Lessons in DB: $lessonCount", $isCli);
out("Total Lesson Slides in DB:      $slideCount", $isCli);
out("Total Questions in DB:          $questionCount", $isCli);
out("Total Topics in DB:             $topicCount", $isCli);
?>
