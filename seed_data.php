<?php
/**
 * Seed the curriculum_lessons, questions, students, and recitation_records tables.
 * Run once after init_db.php to populate the database with rich analysis data.
 */

require 'db.php';

$lessonsData = [
    ['3', 1, 'Living Things', "Living things need food, water, and air to survive. They can grow, move, and reproduce.\n\nPlants make their own food using sunlight through photosynthesis.\n\nAnimals eat food to get energy. They move from place to place to find food and shelter.\n\nNon-living things do not need food, water, or air. They cannot grow or reproduce on their own.", '["Identify living and non-living things","Describe basic needs of living organisms","Differentiate between plants and animals"]'],
    ['3', 1, 'Matter', "Matter is anything that has mass and takes up space.\n\nSolids have a definite shape and size.\nLiquids take the shape of their container.\nGases spread out to fill all the space available.", '["Define matter","Identify the three states of matter","Describe properties of solids, liquids, and gases"]'],
    ['3', 1, 'Five Senses', "We have five senses: sight, hearing, smell, taste, and touch.\n\nOur eyes help us see colors, shapes, and movement.\nOur ears help us hear sounds.\nOur nose smells odors, and our tongue tastes flavors.\nOur skin helps us feel textures, temperature, and pain.", '["Name the five senses","Match each sense to its organ","Explain how senses help us understand work"]'],

    ['4', 1, 'Properties of Materials', "Different materials have different properties like hardness, flexibility, and ability to conduct heat.\n\nHard materials like stone and metal are difficult to scratch. Soft materials like cloth and rubber can be easily bent.\nFlexible materials can bend without breaking. Rigid materials keep their shape.\nSome materials like metals conduct heat and electricity well. Others like wood and plastic are insulators.", '["Classify materials by their properties","Compare hard and soft materials","Distinguish conductors from insulators"]'],
    ['4', 1, 'Plant and Animal Systems', "Plants have root systems to absorb water and nutrients, and shoot systems to capture sunlight and reproduce.\n\nRoots anchor the plant in soil and absorb water and minerals.\nStems transport water and nutrients. Leaves capture sunlight for photosynthesis.\nAnimals have systems like digestive, respiratory, and circulatory systems.", '["Identify parts of a plant","Describe the function of roots, stems, and leaves","Name major animal body systems"]'],
    ['4', 2, 'Force and Movement', "Force is a push or pull that can change how an object moves or stays still.\n\nGravity pulls objects down. Friction slows moving objects. Magnetism attracts or repels magnetic materials.\nObjects can move in straight lines, curves, or circles.\nSimple machines like levers, pulleys, and inclined planes help us do work with less force.", '["Define force","Identify types of forces","Explain how simple machines help us"]'],
    ['4', 2, 'Weather and Sun', "The Sun is a star that gives light and heat to Earth.\n\nEarth rotates on its axis once every 24 hours, causing day and night.\nWeather is the condition of the air outside — temperature, wind, rain, and clouds.\nEarth has four seasons because it tilts on its axis as it orbits the Sun.", '["Explain day and night","Describe basic weather elements","Relate Earth tilt to seasons"]'],

    ['5', 1, 'States of Matter', "Matter exists in three main states: solid, liquid, and gas.\n\nWhen heated, solids can melt into liquids, and liquids can evaporate into gases. Cooling reverses these changes.\nWater evaporates from oceans, forms clouds, falls as rain or snow, and flows back to oceans in a continuous cycle.\nAll matter is made of tiny particles. In solids, particles vibrate in place. In liquids, they slide past each other. In gases, they move freely.", '["Explain changes of state","Describe the water cycle","Apply particle theory to states of matter"]'],
    ['5', 1, 'Human Reproduction', "The human body has systems that work together: respiratory, circulatory, digestive, nervous, and reproductive systems.\n\nThe respiratory system brings oxygen into the body and removes carbon dioxide.\nThe heart pumps blood through blood vessels to deliver oxygen and nutrients to all body cells.\nHumans grow from babies to adults through childhood and adolescence.", '["Identify major body systems","Describe the respiratory system","Explain how the circulatory system works"]'],
    ['5', 2, 'Electricity and Circuits', "Electricity is a form of energy caused by moving electrons.\n\nA circuit is a path that electricity flows through — power source, wires, and a device.\nIn series circuits, components are connected one after another. In parallel circuits, components are on separate paths.\nConductors like copper allow electricity to flow easily. Insulators like rubber block it.", '["Define electricity","Differentiate series and parallel circuits","Identify conductors and insulators"]'],
    ['5', 2, 'Solar System', "The Solar System consists of the Sun and all objects that orbit it.\n\nThe Sun is a star at the center of our Solar System.\nThere are eight planets: Mercury, Venus, Earth, Mars, Jupiter, Saturn, Uranus, and Neptune.\nEarth is the third planet from the Sun — the only planet known to support life.", '["Name the planets in order","Describe the role of the Sun","Explain Earth position in the Solar System"]'],

    ['6', 1, 'Mixtures and Solutions', "A mixture is made when two or more substances are combined but not chemically bonded.\n\nHeterogeneous mixtures have visible different parts. Homogeneous mixtures look uniform throughout.\nA solution is a homogeneous mixture where one substance dissolves in another, like salt in water.\nWe can separate mixtures using filtration, distillation, and evaporation.", '["Define mixture and solution","Classify mixtures as homogeneous or heterogeneous","Describe methods of separating mixtures"]'],
    ['6', 1, 'Vertebrates and Invertebrates', "Animals are classified into two main groups: vertebrates (with backbones) and invertebrates (without backbones).\n\nVertebrates include fish, amphibians, reptiles, birds, and mammals.\nInvertebrates make up 95% of animal species — insects, worms, jellyfish, and many others.\nAnimals have adaptations like camouflage, sharp teeth, or protective shells.", '["Classify animals as vertebrates orদর্শী or invertebrates","Name the five vertebrate groups","Describe animal adaptations"]'],
    ['6', 2, 'Patterns of Motion', "Objects move in predictable patterns.\n\nSpeed is how fast something moves. Velocity is speed in a specific direction.\nAcceleration is when speed or direction changes. Objects accelerate when force is applied.\nNewton laws describe how forces affect motion. Objects at rest stay at rest unless acted on by a force.", '["Calculate speed","Differentiate speed and velocity","Apply Newton first law of motion"]'],
    ['6', 2, 'Volcanoes and Seasons', "Volcanoes are openings in Earth crust where magma, gas, and ash can erupt.\n\nVolcanoes form at plate boundaries where tectonic plates move apart or collide.\nEruptions can be explosive or gentle and can affect climate.\nSeasons are caused by Earth tilt as it orbits the Sun.", '["Explain how volcanoes form","Describe types of eruptions","Relate Earth tilt to seasonal changes"]']
];

$questionsData = [
    ['3', 'Living Things', 'Easy', 'What do living things need to survive?'],
    ['3', 'Living Things', 'Medium', 'How are plants different from animals in getting food?'],
    ['3', 'Matter', 'Easy', 'What is the basic need of a plant to grow?'],
    ['3', 'Matter', 'Medium', 'Name the three states of matter.'],
    ['3', 'Five Senses', 'Easy', 'What are our five senses?'],
    ['4', 'Properties of Materials', 'Easy', 'What is a conductor?'],
    ['4', 'Properties of Materials', 'Medium', 'Give examples of hard and soft materials.'],
    ['4', 'Plant and Animal Systems', 'Easy', 'What do roots do for a plant?'],
    ['4', 'Force and Movement', 'Medium', 'What is the difference between a push and a pull?'],
    ['4', 'Weather and Sun', 'Easy', 'What causes day and night?'],
    ['5', 'States of Matter', 'Medium', 'What happens to matter when it is heated?'],
    ['5', 'Human Reproduction', 'Easy', 'What does the respiratory system do?'],
    ['5', 'Electricity and Circuits', 'Medium', 'What is the difference between series and parallel circuits?'],
    ['5', 'Solar System', 'Easy', 'How many planets are in the Solar System?'],
    ['6', 'Mixtures and Solutions', 'Medium', 'What is a solution?'],
    ['6', 'Vertebrates and Invertebrates', 'Easy', 'What is a vertebrate?'],
    ['6', 'Patterns of Motion', 'Hard', 'How do you calculate speed from a distance-time graph?'],
    ['6', 'Volcanoes and Seasons', 'Medium', 'What causes volcanoes to erupt?']
];

$studentsBase = [
    // Grade 3
    [101, 'Alex Brown', '3', 'A'],
    [102, 'Sophia Martinez', '3', 'A'],
    [103, 'Ethan Williams', '3', 'A'],
    [104, 'Olivia Taylor', '3', 'B'],
    [105, 'Liam Johnson', '3', 'B'],
    [106, 'Mia Anderson', '3', 'B'],
    // Grade 4
    [107, 'Maria Garcia', '4', 'A'],
    [108, 'Noah Thomas', '4', 'A'],
    [109, 'Emma Jackson', '4', 'A'],
    [110, 'Lucas White', '4', 'B'],
    [111, 'Ava Harris', '4', 'B'],
    [112, 'Elijah Martin', '4', 'B'],
    // Grade 5
    [113, 'James Smith', '5', 'A'],
    [114, 'Isabella Clark', '5', 'A'],
    [115, 'Benjamin Lewis', '5', 'A'],
    [116, 'Charlotte Robinson', '5', 'B'],
    [117, 'William Walker', '5', 'B'],
    [118, 'Amelia Young', '5', 'B'],
    // Grade 6
    [119, 'Linda Johnson', '6', 'A'],
    [120, 'Oliver King', '6', 'A'],
    [121, 'Evelyn Wright', '6', 'A'],
    [122, 'Henry Scott', '6', 'B'],
    [123, 'Harper Green', '6', 'B'],
    [124, 'Alexander Baker', '6', 'B']
];

$topicsByGrade = [
    '3' => [
        'Scientific Inquiry in Life Science', 'Characteristics and Life Processes of Living Things', 'Basic Needs of Living Things', 'Structure and Function of Organisms', 'Interactions Among Living Things and Their Environment', 'Environmental Stewardship and Conservation',
        'Properties and Uses of Materials', 'Changes in Materials and Environmental Responsibility', 'Earth Materials and Their Uses'
    ],
    '4' => [
        'Systems in Animals and Plants', 'Plant and Animal Habitats', 'Life Cycles of Plants and Animals', 'Animals and the Food They Eat', 'Food Chains', 'Water and Living Things', 'Soil and Plant Growth',
        'Physical Properties of Materials', 'Chemical Properties of Materials', 'Effect of Temperature on Materials', 'Physical and Chemical Changes', 'Responsible Use and Management of Materials'
    ],
    '5' => [
        'Human Body Systems (Digestive, Respiratory, Reproductive System)', 'Classification and Reproduction of Living Things', 'Life Cycles of Living Things', 'Plant and Animal Adaptations',
        'Properties of Matter', 'States of Matter', 'Changes in Matter', 'Scientific Investigation of Matter'
    ],
    '6' => [
        'Human Body Systems (Circulatory and Nervous Systems)', 'Reproduction in Plants', 'Vertebrates and Invertebrates', 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)',
        'Changes in Matter', 'Physical and Chemical Changes', 'Mixtures and Solutions', 'Separation of Mixtures'
    ]
];

$diffMap = [
    'Easy' => 1,
    'Medium' => 3,
    'Hard' => 5
];

function clearTable($pdo, $table) {
    if (is_sqlite()) {
        $pdo->exec("DELETE FROM $table");
        @$pdo->exec("DELETE FROM sqlite_sequence WHERE name = '$table'");
    } else {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $pdo->exec("TRUNCATE TABLE $table");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }
}

try {
    echo "<h2>🌱 Seeding ILikeSci Database...</h2>";

    // 1. Seed curriculum_lessons
    clearTable($pdo, 'curriculum_lessons');

    $stmtLesson = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives) VALUES (?, ?, ?, ?, ?, ?)");
    $lessonCounter = [];
    foreach ($lessonsData as $l) {
        $grade = $l[0];
        $quarter = $l[1];
        $topic = $l[2];
        $content = $l[3];
        $objectives = $l[4];
        $key = "$grade-$quarter";
        if (!isset($lessonCounter[$key])) $lessonCounter[$key] = 1;
        $stmtLesson->execute([$grade, $quarter, $lessonCounter[$key]++, $topic, $content, $objectives]);
    }
    echo "<p>✅ Seeded " . count($lessonsData) . " curriculum lessons (Grades 3-6)</p>";

    // 2. Seed questions (without duplicating)
    $stmtQCheck = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE grade = ? AND topic = ? AND question_text = ?");
    $stmtQInsert = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text) VALUES (?, ?, ?, ?)");
    $qCount = 0;
    foreach ($questionsData as $q) {
        $stmtQCheck->execute([$q[0], $q[1], $q[3]]);
        if ($stmtQCheck->fetchColumn() == 0) {
            $stmtQInsert->execute([$q[0], $q[1], $q[2], $q[3]]);
            $qCount++;
        }
    }
    echo "<p>✅ Seeded $qCount questions (Grades 3-6)</p>";

    // 3. Seed students & recitation records
    clearTable($pdo, 'recitation_records');
    clearTable($pdo, 'students');

    $stmtStudent = $pdo->prepare("INSERT INTO students (id, name, grade, section, recitations, total_score) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtRecitation = $pdo->prepare("INSERT INTO recitation_records (student_id, topic, difficulty, points, is_correct, created_at) VALUES (?, ?, ?, ?, ?, ?)");

    $totalRecitationsCount = 0;

    foreach ($studentsBase as $index => $s) {
        $studentId = $s[0];
        $name = $s[1];
        $grade = $s[2];
        $section = $s[3];

        $gradeTopics = $topicsByGrade[$grade];

        // Deterministically calculate recitations count and total score first
        $recCount = 4 + ($index % 5); 
        $studentRecitations = 0;
        $studentTotalScore = 0;

        $recitationsToInsert = [];

        for ($i = 0; $i < $recCount; $i++) {
            $topic = $gradeTopics[($index + $i) % count($gradeTopics)];
            
            // Determine difficulty
            $diffs = ['Easy', 'Medium', 'Hard'];
            $difficulty = $diffs[($index + $i) % 3];
            $possiblePoints = $diffMap[$difficulty];

            // 85% chance of being correct
            $isCorrect = (($index + $i) % 7 !== 0) ? 1 : 0;
            $points = $isCorrect ? $possiblePoints : 0;

            // Generate timestamp within last 30 days
            $daysAgo = (($index * 3 + $i * 5) % 28) + 1;
            $hoursAgo = (($index * 7 + $i * 11) % 23) + 1;
            $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days -$hoursAgo hours"));

            $recitationsToInsert[] = [$studentId, $topic, $difficulty, $points, $isCorrect, $createdAt];

            $studentRecitations++;
            $studentTotalScore += $points;
            $totalRecitationsCount++;
        }

        // Insert student first (parent table)
        $stmtStudent->execute([$studentId, $name, $grade, $section, $studentRecitations, $studentTotalScore]);

        // Insert recitation records second (child table)
        foreach ($recitationsToInsert as $rec) {
            $stmtRecitation->execute($rec);
        }
    }

    echo "<p>✅ Seeded " . count($studentsBase) . " sample students across Grades 3-6 (Sections A & B)</p>";
    echo "<p>✅ Seeded $totalRecitationsCount realistic recitation records for analysis & E-Class grading</p>";

    // 4. Seed student_grades
    clearTable($pdo, 'student_grades');

    $stmtGrade = $pdo->prepare("INSERT INTO student_grades (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws, pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade, ww_highest, pt_highest, qa_highest) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $femaleNames = ['Sophia', 'Olivia', 'Mia', 'Maria', 'Emma', 'Ava', 'Isabella', 'Charlotte', 'Amelia', 'Evelyn', 'Harper', 'Linda'];
    
    $ww_highest = [10, 10, 15, 10, 10, 20, 10, 15, 10, 20];
    $pt_highest = [15, 15, 20, 15, 15, 30, 15, 20, 15, 30];
    $qa_highest = 50;

    $seededGradesCount = 0;

    foreach ($studentsBase as $index => $s) {
        $name = $s[1];
        $grade = $s[2];
        $section = $s[3];

        // Determine gender
        $firstName = explode(' ', $name)[0];
        $gender = in_array($firstName, $femaleNames) ? 'F' : 'M';

        // We will seed Quarter 1 for all students
        $quarters = [1];
        foreach ($quarters as $q) {
            $wwFactor = 0.70 + (($index + $q) % 6) * 0.05; // 0.70 to 0.95
            $ptFactor = 0.75 + (($index + $q) % 5) * 0.05; // 0.75 to 0.95
            $qaFactor = 0.65 + (($index + $q) % 7) * 0.05; // 0.65 to 0.95

            $ww_scores = [];
            foreach ($ww_highest as $hps) {
                $ww_scores[] = round($hps * $wwFactor);
            }

            $pt_scores = [];
            foreach ($pt_highest as $hps) {
                $pt_scores[] = round($hps * $ptFactor);
            }

            $qa_score = round($qa_highest * $qaFactor);

            $record = [
                'ww_scores' => json_encode($ww_scores),
                'pt_scores' => json_encode($pt_scores),
                'ww_highest' => json_encode($ww_highest),
                'pt_highest' => json_encode($pt_highest),
                'qa_highest' => $qa_highest,
                'qa_score' => $qa_score
            ];

            computeGrades($record);

            $stmtGrade->execute([
                $name, $grade, $section, $q, $gender,
                $record['ww_scores'], $record['ww_total'], $record['ww_ps'], $record['ww_ws'],
                $record['pt_scores'], $record['pt_total'], $record['pt_ps'], $record['pt_ws'],
                $record['qa_score'], $record['qa_ps'], $record['qa_ws'],
                $record['initial_grade'], $record['transmuted_grade'],
                $record['ww_highest'], $record['pt_highest'], $record['qa_highest']
            ]);
            $seededGradesCount++;
        }
    }
    // 5. Seed Official MATATAG Matter and Materials Curriculum & Presentation Slides
    require_once __DIR__ . '/seed_matter_materials.php';

    // 6. Seed Official MATATAG Life Science Curriculum & Presentation Slides
    require_once __DIR__ . '/seed_life_science.php';

    echo "<h3>🎉 Database seeded successfully!</h3>";
    echo "<p><a href='index.html'>Go to Dashboard →</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

// Helpers for grading math
function getTransmutationTable() {
    return [
        [0, 3.99, 60], [4, 7.99, 61], [8, 11.99, 62], [12, 15.99, 63],
        [16, 19.99, 64], [20, 23.99, 65], [24, 27.99, 66], [28, 31.99, 67],
        [32, 35.99, 68], [36, 39.99, 69], [40, 43.99, 70], [44, 47.99, 71],
        [48, 51.99, 72], [52, 55.99, 73], [56, 59.99, 74], [60, 63.99, 75],
        [64, 67.99, 76], [68, 71.99, 77], [72, 75.99, 78], [76, 79.99, 79],
        [80, 83.99, 80], [84, 87.99, 81], [88, 91.99, 82], [92, 95.99, 83],
        [96, 99.99, 84], [100, 100, 100]
    ];
}

function transmute($initialGrade) {
    if ($initialGrade >= 100) return 100;
    $table = getTransmutationTable();
    foreach ($table as $row) {
        if ($initialGrade >= $row[0] && $initialGrade <= $row[1]) {
            return $row[2];
        }
    }
    return 60;
}

function computeGrades(&$record) {
    $wwScores = json_decode($record['ww_scores'] ?? '[]', true) ?: [];
    $ptScores = json_decode($record['pt_scores'] ?? '[]', true) ?: [];
    $wwHighest = json_decode($record['ww_highest'] ?? '[]', true) ?: [];
    $ptHighest = json_decode($record['pt_highest'] ?? '[]', true) ?: [];
    $qaHighest = floatval($record['qa_highest'] ?? 0);

    $wwTotal = array_sum($wwScores);
    $ptTotal = array_sum($ptScores);
    $qaScore = floatval($record['qa_score'] ?? 0);

    $wwHPS = array_sum($wwHighest) ?: 1;
    $ptHPS = array_sum($ptHighest) ?: 1;
    $qaHPS = $qaHighest ?: 1;

    $wwPS = ($wwTotal / $wwHPS) * 100;
    $ptPS = ($ptTotal / $ptHPS) * 100;
    $qaPS = ($qaScore / $qaHPS) * 100;

    $wwWS = $wwPS * 0.40;
    $ptWS = $ptPS * 0.40;
    $qaWS = $qaPS * 0.20;

    $initialGrade = $wwWS + $ptWS + $qaWS;
    $transmuted = transmute($initialGrade);

    $record['ww_total'] = round($wwTotal, 2);
    $record['ww_ps'] = round($wwPS, 2);
    $record['ww_ws'] = round($wwWS, 2);
    $record['pt_total'] = round($ptTotal, 2);
    $record['pt_ps'] = round($ptPS, 2);
    $record['pt_ws'] = round($ptWS, 2);
    $record['qa_ps'] = round($qaPS, 2);
    $record['qa_ws'] = round($qaWS, 2);
    $record['initial_grade'] = round($initialGrade, 2);
    $record['transmuted_grade'] = $transmuted;
}
?>
