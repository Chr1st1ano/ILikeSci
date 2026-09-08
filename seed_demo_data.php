<?php
require 'db.php';

header("Content-Type: application/json");

try {
    // 1. Create Student 'abc def ghi' in Grade 7 Section A (1st Quarter score ONLY)
    $studentId = 7001;
    $studentName = "abc def ghi";
    $gradeLevel = "7";
    $section = "A";
    $quarter = 1;

    // Upsert into students table
    if (is_sqlite()) {
        $stmtStudent = $pdo->prepare(
            "INSERT INTO students (id, name, grade, section, recitations, total_score, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON CONFLICT(id) DO UPDATE SET
               name = excluded.name,
               grade = excluded.grade,
               section = excluded.section,
               recitations = excluded.recitations,
               total_score = excluded.total_score"
        );
    } else {
        $stmtStudent = $pdo->prepare(
            "INSERT INTO students (id, name, grade, section, recitations, total_score, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               name = VALUES(name),
               grade = VALUES(grade),
               section = VALUES(section),
               recitations = VALUES(recitations),
               total_score = VALUES(total_score)"
        );
    }
    $stmtStudent->execute([$studentId, $studentName, $gradeLevel, $section, 8, 90, null]);

    // Ensure any existing quarters 2, 3, 4 records for this student are removed if present
    $stmtDelGrades = $pdo->prepare("DELETE FROM student_grades WHERE student_name = ? AND grade_level = ? AND quarter != 1");
    $stmtDelGrades->execute([$studentName, $gradeLevel]);

    // Insert or update Quarter 1 record ONLY in student_grades table
    $wwScores = json_encode([85, 90, 88, 92]);
    $wwHighest = json_encode([100, 100, 100, 100]);
    $wwTotal = 355.00;
    $wwPS = 88.75;
    $wwWS = 35.50; // 40% weight

    $ptScores = json_encode([90, 88, 95]);
    $ptHighest = json_encode([100, 100, 100]);
    $ptTotal = 273.00;
    $ptPS = 91.00;
    $ptWS = 36.40; // 40% weight

    $qaScore = 90.00;
    $qaHighest = 100.00;
    $qaPS = 90.00;
    $qaWS = 18.00; // 20% weight

    $initialGrade = 89.90; // 35.50 + 36.40 + 18.00
    $transmutedGrade = 93;

    if (is_sqlite()) {
        $stmtGrade = $pdo->prepare(
            "INSERT INTO student_grades 
             (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws, pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade, ww_highest, pt_highest, qa_highest)
             VALUES (?, ?, ?, ?, 'M', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON CONFLICT(student_name, grade_level, section, quarter) DO UPDATE SET
               ww_scores = excluded.ww_scores, ww_total = excluded.ww_total, ww_ps = excluded.ww_ps, ww_ws = excluded.ww_ws,
               pt_scores = excluded.pt_scores, pt_total = excluded.pt_total, pt_ps = excluded.pt_ps, pt_ws = excluded.pt_ws,
               qa_score = excluded.qa_score, qa_ps = excluded.qa_ps, qa_ws = excluded.qa_ws,
               initial_grade = excluded.initial_grade, transmuted_grade = excluded.transmuted_grade,
               ww_highest = excluded.ww_highest, pt_highest = excluded.pt_highest, qa_highest = excluded.qa_highest"
        );
    } else {
        $stmtGrade = $pdo->prepare(
            "INSERT INTO student_grades 
             (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws, pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade, ww_highest, pt_highest, qa_highest)
             VALUES (?, ?, ?, ?, 'M', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               ww_scores = VALUES(ww_scores), ww_total = VALUES(ww_total), ww_ps = VALUES(ww_ps), ww_ws = VALUES(ww_ws),
               pt_scores = VALUES(pt_scores), pt_total = VALUES(pt_total), pt_ps = VALUES(pt_ps), pt_ws = VALUES(pt_ws),
               qa_score = VALUES(qa_score), qa_ps = VALUES(qa_ps), qa_ws = VALUES(qa_ws),
               initial_grade = VALUES(initial_grade), transmuted_grade = VALUES(transmuted_grade),
               ww_highest = VALUES(ww_highest), pt_highest = VALUES(pt_highest), qa_highest = VALUES(qa_highest)"
        );
    }
    $stmtGrade->execute([
        $studentName, $gradeLevel, $section, $quarter,
        $wwScores, $wwTotal, $wwPS, $wwWS,
        $ptScores, $ptTotal, $ptPS, $ptWS,
        $qaScore, $qaPS, $qaWS,
        $initialGrade, $transmutedGrade,
        $wwHighest, $ptHighest, $qaHighest
    ]);

    // 2. Auto-generate Question Bank Demonstration Data fitting all system measures
    $demoQuestions = [
        // Grade 3
        ['3', 'Living Things', 'Easy', 'What do living things need to survive? A) Water, food, air B) Soda and candy C) Rocks and soil D) Plastic', 'multiple-choice'],
        ['3', 'Living Things', 'Medium', 'Organism that can make its own food using sunlight is called a ____.', 'identification'],
        ['3', 'Five Senses', 'Hard', 'True or False: Our taste buds can detect textures, while skin detects light.', 'true-false'],
        ['3', 'Matter', 'Easy', 'Which state of matter has a definite shape and volume? A) Solid B) Liquid C) Gas D) Plasma', 'multiple-choice'],

        // Grade 4
        ['4', 'Properties of Materials', 'Easy', 'A material that allows heat and electricity to pass easily is called a ____.', 'identification'],
        ['4', 'Force and Movement', 'Medium', 'What force pulls objects towards the center of the Earth? A) Friction B) Gravity C) Tension D) Magnetism', 'multiple-choice'],
        ['4', 'Plant Systems', 'Hard', 'Which plant tissue transports water from roots to leaves? A) Xylem B) Phloem C) Stomata D) Epidermis', 'multiple-choice'],
        ['4', 'Weather and Sun', 'Medium', 'True or False: Earth rotation on its axis causes day and night.', 'true-false'],

        // Grade 5
        ['5', 'States of Matter', 'Easy', 'Phase change from solid to liquid is called ____.', 'identification'],
        ['5', 'Electricity and Circuits', 'Medium', 'In a series circuit, if one bulb burns out, what happens to the remaining bulbs? A) They stay lit B) They go out C) They get brighter D) They flash', 'multiple-choice'],
        ['5', 'Human Reproduction', 'Hard', 'Which body system produces hormones that regulate growth and development? A) Endocrine B) Digestive C) Skeletal D) Respiratory', 'multiple-choice'],
        ['5', 'Solar System', 'Easy', 'Which planet is closest to the Sun? A) Venus B) Earth C) Mercury D) Mars', 'multiple-choice'],

        // Grade 6
        ['6', 'Mixtures and Solutions', 'Easy', 'A mixture that appears uniform throughout is called a ____ solution.', 'identification'],
        ['6', 'Vertebrates and Invertebrates', 'Medium', 'Which animal group belongs to vertebrates? A) Insects B) Reptiles C) Jellyfish D) Earthworms', 'multiple-choice'],
        ['6', 'Patterns of Motion', 'Hard', 'Calculate the speed of a car that travels 120 kilometers in 2 hours. A) 60 km/h B) 240 km/h C) 50 km/h D) 100 km/h', 'multiple-choice'],
        ['6', 'Volcanoes and Earthquakes', 'Medium', 'True or False: Tectonic plate movements trigger seismic activity and volcanic eruptions.', 'true-false'],

        // Grade 7 (Junior High Science)
        ['7', 'Cell Structure & Function', 'Easy', 'What is the basic structural and functional unit of all living organisms? A) Cell B) Tissue C) Organ D) Molecule', 'multiple-choice'],
        ['7', 'Cell Structure & Function', 'Medium', 'The organelle known as the powerhouse of the cell is the ____.', 'identification'],
        ['7', 'Ecosystems & Biodiversity', 'Hard', 'Explain how energy flows through an ecological pyramid from primary producers to top consumers.', 'multiple-choice'],
        ['7', 'Force & Motion (Physics)', 'Medium', 'State Newton First Law of Motion (Law of Inertia) and give one real-world application.', 'multiple-choice'],
        ['7', 'Earth Systems & Climate', 'Easy', 'True or False: Greenhouse gases trap heat in Earth atmosphere, regulating global temperature.', 'true-false']
    ];

    $stmtQCheck = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE grade = ? AND topic = ? AND question_text = ?");
    $stmtQInsert = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text, type) VALUES (?, ?, ?, ?, ?)");
    $insertedQ = 0;

    foreach ($demoQuestions as $q) {
        $stmtQCheck->execute([$q[0], $q[1], $q[3]]);
        if ($stmtQCheck->fetchColumn() == 0) {
            $stmtQInsert->execute([$q[0], $q[1], $q[2], $q[3], $q[4]]);
            $insertedQ++;
        }
    }

    // Auto-sync topics table for Grade 7 and new topics
    $ignoreKw = is_sqlite() ? "INSERT OR IGNORE INTO" : "INSERT IGNORE INTO";
    $pdo->exec("$ignoreKw topics (grade, topic_name) VALUES 
        ('7', 'Cell Structure & Function'),
        ('7', 'Ecosystems & Biodiversity'),
        ('7', 'Force & Motion (Physics)'),
        ('7', 'Earth Systems & Climate')");

    echo json_encode([
        "status" => "success",
        "message" => "Demo data successfully created!",
        "student" => [
            "name" => $studentName,
            "grade" => $gradeLevel,
            "section" => $section,
            "quarter" => "1st Quarter ONLY",
            "initial_grade" => $initialGrade,
            "transmuted_grade" => $transmutedGrade
        ],
        "questions_inserted" => $insertedQ
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
