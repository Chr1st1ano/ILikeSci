<?php
/**
 * ILikeSci — Official MATATAG / DepEd "Life Science" Curriculum Seeder
 * Populates all 21 curriculum units across Grades 3 to 6:
 * - Grade 3 (Term 1 & Term 2): 6 Units
 * - Grade 4 (Term 1, Term 2 & Term 3): 7 Units
 * - Grade 5 (Term 1 & Term 2): 4 Units
 * - Grade 6 (Term 1 & Term 2): 4 Units
 * Total: 21 Curriculum Lessons, 105 Detailed Presentation Slides, 105+ Tiered Questions.
 * Compatible with both XAMPP MySQL and SQLite fallback.
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
}

require_once __DIR__ . '/db.php';

$lifeScienceUnits = [
    // ==========================================
    // GRADE 3 — LIFE SCIENCE
    // ==========================================
    [
        'grade' => '3',
        'quarter' => '1',
        'lesson_number' => '1',
        'topic' => 'Scientific Inquiry in Life Science',
        'objectives' => [
            'Perform simple guided investigations using the skills of observing, predicting, and measuring to gather data and answer questions about the natural world.'
        ],
        'content' => "LESSON 1: Scientific Inquiry in Life Science\n\nLearning Competency:\nPerform simple guided investigations using the skills of observing, predicting, and measuring to gather data and answer questions about the natural world.\n\nLET'S EXPLORE\nHave you ever wondered why a plant grows toward sunlight? Or why some animals behave differently when they are in different places?\nScientists ask questions about things they observe. They use simple investigations to find answers.\n\nWHAT IS SCIENTIFIC INQUIRY?\nScientific inquiry is a way of learning about the natural world by asking questions, observing, making predictions, gathering information, and using evidence.\n\nFor young scientists, three important skills are:\n• Observing: Using our senses or tools to gather information (e.g. Looking at the leaves of a plant)\n• Predicting: Making a reasonable guess about what may happen (e.g. Predicting that a plant near sunlight will grow well)\n• Measuring: Using tools or standard units to determine something (e.g. Measuring the height of a plant)\n\nSIMPLE INVESTIGATION\nA simple investigation may follow these steps:\nAsk → Predict → Observe → Measure → Record → Explain\n\nExample:\n• Question: Does a plant grow taller over time?\n• Prediction: The plant will become taller after several days.\n• Observation: The plant is measured each day.\n• Measurement: Its height is recorded in centimeters.\n• Conclusion: The observations and measurements are used to explain what happened.\n\nMULTIMEDIA SUPPORT:\n• A short video of a plant growing\n• Pictures of scientists observing living things\n• A simple time-lapse of plant growth\n\nINTERACTIVE ACTIVITY: Young Scientist!\n1. Ana looks closely at a flower. -> Observing\n2. Ben says the seed will grow into a plant. -> Predicting\n3. Carlo uses a ruler to measure a leaf. -> Measuring\n4. Dana records the number of leaves on a plant. -> Observing/Recording\n5. Ella guesses that the plant will grow taller next week. -> Predicting\n\nKEY TAKEAWAYS:\n• Scientists ask questions about the natural world.\n• Observing means gathering information.\n• Predicting means making a reasonable guess about what may happen.\n• Measuring uses tools or standard units to determine something.\n• Scientists use evidence from observations and measurements to answer questions.",
        'slides' => [
            [
                'title' => 'Scientific Inquiry in Life Science',
                'content' => "Grade 3 — Term 1 Life Science\n\nBecome a young biologist: Discover how scientists observe, question, and investigate the living world.",
                'slide_type' => 'title',
                'media_type' => 'image',
                'media_url' => 'https://images.unsplash.com/photo-1530587191325-3db32d826c18?w=800'
            ],
            [
                'title' => 'Three Core Inquiry Skills',
                'content' => "For young scientists, three important skills are:\n\n• Observing: Using our senses or tools to gather information\n  - Example: Looking at the leaves of a plant\n• Predicting: Making a reasonable guess about what may happen\n  - Example: Predicting that a plant near sunlight will grow well\n• Measuring: Using tools or standard units to determine something\n  - Example: Measuring the height of a plant with a ruler",
                'slide_type' => 'content',
                'media_type' => 'step',
                'media_url' => "Skill 1: Observing\nSkill 2: Predicting\nSkill 3: Measuring"
            ],
            [
                'title' => 'Steps of a Simple Investigation',
                'content' => "A simple investigation follows these steps:\nAsk → Predict → Observe → Measure → Record → Explain\n\nExample Investigation:\n• Question: Does a plant grow taller over time?\n• Prediction: The plant will become taller after several days.\n• Observation: The plant is measured each day.\n• Measurement: Its height is recorded in centimeters.\n• Conclusion: Observations and measurements explain what happened!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Ask\nStep 2: Predict\nStep 3: Observe\nStep 4: Measure\nStep 5: Record\nStep 6: Explain"
            ],
            [
                'title' => 'Interactive Activity: Young Scientist!',
                'content' => "Identify whether the scientist is Observing, Predicting, or Measuring:\n\n1. Ana looks closely at a flower. (Observing)\n2. Ben says the seed will grow into a plant. (Predicting)\n3. Carlo uses a ruler to measure a leaf. (Measuring)\n4. Dana records the number of leaves on a plant. (Observing/Recording)\n5. Ella guesses that the plant will grow taller next week. (Predicting)",
                'slide_type' => 'interactive',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment & Key Takeaways',
                'content' => "Key Takeaways:\n• Scientists ask questions about the natural world.\n• Observing means gathering information using senses.\n• Predicting means making a reasonable guess about what may happen.\n• Measuring uses tools or standard units to determine quantities.\n• Scientists use evidence from observations and measurements to answer questions.",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Scientific Inquiry in Life Science', 'difficulty' => 'Easy', 'text' => 'What skill involves gathering information using our senses? | A: Predicting B: Observing C: Guessing D: Drawing', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Scientific Inquiry in Life Science', 'difficulty' => 'Easy', 'text' => 'What do we do when we say what we think will happen? | A: Measuring B: Predicting C: Recording D: Comparing', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Scientific Inquiry in Life Science', 'difficulty' => 'Easy', 'text' => 'Which tool can be used to measure the height of a plant? | A: Ruler B: Spoon C: Plate D: Pencil case', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Scientific Inquiry in Life Science', 'difficulty' => 'Medium', 'text' => 'Why do scientists record their observations and measurements? | A: To decorate their notebook B: To keep evidence of what they found C: To make the investigation longer D: To avoid asking questions', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Scientific Inquiry in Life Science', 'difficulty' => 'Medium', 'text' => 'Which sequence shows a simple investigation? | A: Ask → Predict → Observe → Measure B: Measure → Sleep → Predict → Ask C: Draw → Play → Ask → Sleep D: Guess → Stop → Measure → Forget', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '1',
        'lesson_number' => '2',
        'topic' => 'Characteristics and Life Processes of Living Things',
        'objectives' => [
            'Identify the fundamental characteristics of living things (growth, movement, nutrition, respiration, excretion, response to stimuli, reproduction).',
            'Compare life processes in plants and animals.',
            'Explain how living organisms respond to changes in their immediate environment.'
        ],
        'content' => "All living things share fundamental characteristics that set them apart from non-living matter: they grow, move, consume nutrients, breathe, excrete waste, respond to stimuli, and reproduce.\n\nPlants make their own food through sunlight and show tropisms (growing toward light or gravity). Animals actively move from place to place using legs, wings, or fins to find food and shelter.\n\nLiving things respond to external stimuli: a mimosa plant (Makahiya) folds its leaves when touched; animals seek shade when it gets too hot; earthworms burrow into moist soil when exposed to bright sunlight.",
        'slides' => [
            [
                'title' => 'Characteristics of Living Things',
                'content' => "Grade 3 — Term 1 Life Science\n\nLearn the 7 life processes that define living organisms on planet Earth.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The 7 Signs of Life',
                'content' => "Every living organism performs these processes:\n1. Movement: Active locomotion or slow plant bending\n2. Respiration: Exchanging gases to release energy\n3. Sensitivity: Responding to environmental stimuli\n4. Growth: Increasing in size and developing complexity\n5. Reproduction: Producing offspring to continue the species\n6. Excretion: Eliminating cellular waste products\n7. Nutrition: Absorbing or making food nutrients",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Plants vs Animals in Action',
                'content' => "Comparing Life Processes:\n• Feeding: Plants synthesize food via photosynthesis; animals ingest food\n• Movement: Animals walk, fly, or swim; plants turn leaves toward the Sun (phototropism)\n• Breathing: Animals inhale oxygen through lungs/gills; plants exchange gases via leaf stomata",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Place a potted bean plant near a window\nStep 2: Observe stem bending toward sunlight\nStep 3: Touch a Makahiya leaf and watch it fold\nStep 4: Conclude: Plants actively respond to stimuli!"
            ],
            [
                'title' => 'Sensitivity: Responding to Stimuli',
                'content' => "How living things react to keep safe:\n• Makahiya plant folds its leaves when touched to deter herbivores\n• Dogs pant and drink water when overheated\n• Earthworms burrow deeper into soil when sunlight hits their skin",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Living vs Non-Living Quiz',
                'content' => "A toy robot can walk, speak words, and uses battery power. Why is it still considered non-living?\nA) It is too small\nB) It cannot grow, reproduce, or possess living cells\nC) It has bright plastic colors\nD) It moves on wheels\n\nCorrect Answer: B (Non-living objects do not grow, heal, or reproduce!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Characteristics and Life Processes of Living Things', 'difficulty' => 'Easy', 'text' => 'What happens to the leaves of the Makahiya (touch-me-not) plant when gently touched? | A: They fold closed rapidly B: They fall off immediately C: They turn into flowers D: They catch fire', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Characteristics and Life Processes of Living Things', 'difficulty' => 'Easy', 'text' => 'Which of the following life processes ensures that a species of animal does not go extinct? | A: Reproduction B: Sleeping C: Walking D: Running', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Characteristics and Life Processes of Living Things', 'difficulty' => 'Medium', 'text' => 'Why do potted green plants placed near a classroom window slowly bend toward the glass? | A: Stems grow toward sunlight to maximize photosynthesis (phototropism) B: Stems try to break the glass C: Wind pushes them inside D: Roots dislike darkness', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Characteristics and Life Processes of Living Things', 'difficulty' => 'Medium', 'text' => 'How does an animal\'s movement differ from a plant\'s movement? | A: Most animals can travel from place to place, while plants move by slow growth and bending B: Plants run fast C: Animals never move D: Both move identically', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Characteristics and Life Processes of Living Things', 'difficulty' => 'Hard', 'text' => 'A car consumes gasoline, releases exhaust gas, and moves fast. Explain why a car is classified as non-living. | A: It cannot grow, cannot reproduce, lacks biological cells, and requires human construction B: It is made of metal C: It is painted red D: It has rubber tires', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '2',
        'lesson_number' => '1',
        'topic' => 'Basic Needs of Living Things',
        'objectives' => [
            'Identify the basic survival needs of plants and animals: water, food/nutrients, air, sunlight, and shelter/space.',
            'Describe what happens when living organisms are deprived of one or more basic needs.',
            'Relate basic needs to the welfare and care of household pets, farm animals, and crops.'
        ],
        'content' => "All living organisms require essential resources from their environment to survive and thrive.\n\nPlants require sunlight, water, carbon dioxide from the air, soil nutrients (nitrogen, phosphorus, potassium), and space to spread their roots and leaves.\n\nAnimals require clean water, nutritious food, oxygen to breathe, and safe shelter to protect themselves from predators and harsh weather.\n\nDepriving an organism of its basic needs leads to stunted growth, wilting, disease, or death. Caring for pets, farm animals, and trees requires providing these necessities consistently.",
        'slides' => [
            [
                'title' => 'Basic Needs of Living Things',
                'content' => "Grade 3 — Term 2 Life Science\n\nExplore the essential resources that keep all plants, animals, and humans alive.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'What Green Plants Need',
                'content' => "Five essentials for plant life:\n1. Sunlight: Energy source for food production\n2. Water: Carries minerals and maintains turgor pressure\n3. Carbon Dioxide: Carbon building block for glucose\n4. Soil Nutrients: Essential minerals (N-P-K) for cell growth\n5. Space: Adequate room for roots and spreading branches",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'What Animals Need to Survive',
                'content' => "Four essentials for animal life:\n1. Nutritious Food: Energy for muscles, growth, and body warmth\n2. Clean Water: Solvent for blood, cooling, and digestion\n3. Oxygen Gas: Required for cellular respiration\n4. Safe Shelter & Space: Protection from heavy rains, heat, and predators",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Check your pet's water bowl daily\nStep 2: Provide nutritious age-appropriate food\nStep 3: Ensure dry, ventilated sleeping shelter\nStep 4: Allow exercise space for healthy development"
            ],
            [
                'title' => 'Deprivation: What Happens When Needs Are Missing?',
                'content' => "Effects of lacking basic needs:\n• No water: Plant leaves wilt, lose cell pressure, and dry out\n• No sunlight: Plant leaves turn pale yellow and cannot produce food\n• No food: Animals lose weight, become weak, and succumb to illness",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Basic Needs Check Quiz',
                'content' => "What vital gas in the air do dogs, birds, fish, and humans breathe in to stay alive?\nA) Carbon dioxide\nB) Oxygen\nC) Helium\nD) Nitrogen only\n\nCorrect Answer: B (Oxygen gas is required by animals for respiration!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Basic Needs of Living Things', 'difficulty' => 'Easy', 'text' => 'What invisible gas in ambient air do animals and humans need to breathe in? | A: Oxygen B: Carbon dioxide C: Smoke D: Steam', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Basic Needs of Living Things', 'difficulty' => 'Easy', 'text' => 'Which primary energy source do green plants need to manufacture their food? | A: Sunlight B: Moonlight C: Wind D: Sound', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Basic Needs of Living Things', 'difficulty' => 'Medium', 'text' => 'What happens to a healthy potted plant kept in a dark cupboard without water for two weeks? | A: It will turn yellow, wilt, and die from lack of light and moisture B: It will produce flowers C: It will turn into plastic D: It grows faster', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Basic Needs of Living Things', 'difficulty' => 'Medium', 'text' => 'Why must fish tanks and aquariums have an electric aerator pump? | A: The pump dissolves essential oxygen gas into the water for fish gills to breathe B: To heat water to boiling C: To turn water into soda D: To feed the fish automatically', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Basic Needs of Living Things', 'difficulty' => 'Hard', 'text' => 'Why does crowding 50 tomato seedlings into one small flowerpot cause weak, spindly growth even with daily watering? | A: The seedlings compete intensely for limited root space, soil minerals, and sunlight B: Tomato seeds dislike water C: Soil turns to rock D: Roots generate too much heat', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '2',
        'lesson_number' => '2',
        'topic' => 'Structure and Function of Organisms',
        'objectives' => [
            'Identify the external body parts of common animals and explain their functions.',
            'Identify the major vegetative and reproductive organs of flowering plants and their functions.',
            'Relate specialized anatomical structures to specific survival tasks.'
        ],
        'content' => "Living things have specialized body parts called structures, and each structure performs a specific job called a function.\n\nIn plants: Roots anchor the plant and absorb water from soil; stems support branches and transport sap; leaves capture sunlight for photosynthesis; flowers enable reproduction; fruits protect seeds.\n\nIn animals: Eyes, ears, and noses detect predators and food; legs, wings, and fins enable locomotion; claws, beaks, and teeth gather and process food; fur and scales provide protection.\n\nSpecialized structures match survival needs: ducks have webbed feet for paddling; eagles have hooked talons for catching fish; cacti have needle-like spines to reduce water loss.",
        'slides' => [
            [
                'title' => 'Structure and Function of Organisms',
                'content' => "Grade 3 — Term 2 Life Science\n\nDiscover how specialized body parts help plants and animals survive and succeed.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Plant Structures & Their Functions',
                'content' => "The 5 Major Organs of Plants:\n• Roots: Anchor plant into soil; absorb water and dissolved minerals\n• Stem: Supports aerial branches and transports fluids between roots and leaves\n• Leaves: The solar food factories performing photosynthesis\n• Flowers: Colorful structures attracting pollinators for reproduction\n• Fruits & Seeds: Protect developing seeds and aid dispersal",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Animal Structures: Movement & Feeding',
                'content' => "Matching form to function:\n• Fins & Streamlined Body (Fish): Slicing through water with minimal drag\n• Webbed Feet (Ducks): Paddle-like surface for swimming\n• Sharp Curved Talons (Eagles): Grasping and holding slippery fish\n• Grinding Molars (Carabao): Crushing tough grass fibers",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Look at the feet of a bird\nStep 2: Are they webbed, clawed, or grasping?\nStep 3: Webbed = Swimming; Talons = Hunting; Claws = Perching\nStep 4: Structure always reveals function!"
            ],
            [
                'title' => 'Protective Structures',
                'content' => "Defensive body armor in nature:\n• Turtles: Hard bony carapace shields soft internal organs\n• Cacti: Needle-sharp spines deter thirsty desert herbivores\n• Porcupines: Modified sharp quills detach to injure attacking predators",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Organ Function Quiz',
                'content' => "Which plant structure is primarily responsible for absorbing water and dissolved minerals from the soil?\nA) Flowers\nB) Leaves\nC) Roots\nD) Bark\n\nCorrect Answer: C (Roots anchor and absorb water/nutrients!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Structure and Function of Organisms', 'difficulty' => 'Easy', 'text' => 'Which plant structure absorbs water and minerals from the ground? | A: Roots B: Leaves C: Flowers D: Fruits', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Structure and Function of Organisms', 'difficulty' => 'Easy', 'text' => 'What specialized body structure enables fish to extract oxygen from water? | A: Gills B: Lungs C: Skin only D: Fins', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Structure and Function of Organisms', 'difficulty' => 'Medium', 'text' => 'Why do ducks have webbed feet while chickens have slender clawed toes? | A: Webbed feet act like paddles for swimming, while chicken claws scratch soil for seeds B: Ducks cannot walk C: Chickens swim faster D: Chickens have no bones', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Structure and Function of Organisms', 'difficulty' => 'Medium', 'text' => 'What is the primary function of the colorful petals on gumamela and mango flowers? | A: Attract pollinating insects like bees and butterflies B: Absorb soil water C: Make seeds directly D: Protect stems from wind', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Structure and Function of Organisms', 'difficulty' => 'Hard', 'text' => 'Contrast the needle-like spines of a desert cactus with the broad flat leaves of a tropical banana plant. | A: Cactus spines minimize surface area to prevent moisture loss in dry heat; banana leaves maximize sunlight capture in moist rainforests B: Cactus spines make fruit C: Banana leaves are made of stone D: Both are identical', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '2',
        'lesson_number' => '3',
        'topic' => 'Interactions Among Living Things and Their Environment',
        'objectives' => [
            'Identify cooperative relationships where living things help one another (mutualism).',
            'Explain the oxygen and carbon dioxide gas exchange between plants and animals.',
            'Describe how living things interact with non-living environmental elements.'
        ],
        'content' => "No organism lives in isolation. Living things constantly interact with other living things (biotic factors) and non-living surroundings (abiotic factors).\n\nPlants and animals depend on each other: plants release oxygen that animals breathe, while animals exhale carbon dioxide that plants use to make food.\n\nPollination is a key interaction: bees, butterflies, and sunbirds drink flower nectar and simultaneously carry pollen between blossoms, allowing plants to form fruits and seeds.\n\nAnimals use non-living materials for habitat: birds build nests with twigs on tree branches; earthworms aerate soil; fish swim in river water.",
        'slides' => [
            [
                'title' => 'Interactions in the Environment',
                'content' => "Grade 3 — Term 2 Life Science\n\nDiscover the web of life: How plants, animals, and non-living elements depend on each other.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Oxygen-Carbon Dioxide Partnership',
                'content' => "Nature's Perfect Balance:\n• Plants take in Carbon Dioxide (CO2) exhaled by animals and release fresh Oxygen (O2)\n• Animals inhale Oxygen (O2) and exhale Carbon Dioxide (CO2)\n• Together, plants and animals maintain clean, breathable air on Earth!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Pollination: A Win-Win Friendship',
                'content' => "Mutual cooperation in nature:\n• Flowers provide sweet nectar as food for bees, butterflies, and sunbirds\n• Pollinators dust pollen grains across their bodies and carry it to neighboring flowers\n• Result: The plant successfully produces delicious fruits and fertile seeds!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Bee visits flower for nectar\nStep 2: Sticky pollen coats bee's legs\nStep 3: Bee flies to next flower\nStep 4: Pollination enables seed and fruit development"
            ],
            [
                'title' => 'Living Things Using Non-Living Resources',
                'content' => "Biotic organisms utilizing abiotic nature:\n• Birds collect mud, grass, and twigs to construct sturdy nests\n• Earthworms burrow through soil, creating aeration tunnels that help roots breathe\n• Mudfish burrow into moist lakebed mud to survive summer dry spells",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Ecosystem Interaction Quiz',
                'content' => "What gas do plants release during the day that humans and animals need to inhale?\nA) Smoke\nB) Carbon dioxide\nC) Oxygen\nD) Helium\n\nCorrect Answer: C (Plants produce life-giving oxygen during photosynthesis!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Interactions Among Living Things and Their Environment', 'difficulty' => 'Easy', 'text' => 'What essential gas do plants release into the air that animals breathe? | A: Oxygen B: Carbon dioxide C: Helium D: Hydrogen', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Interactions Among Living Things and Their Environment', 'difficulty' => 'Easy', 'text' => 'How do butterflies and honeybees help flowering fruit trees? | A: By carrying pollen between flowers so fruits can grow B: By eating all the leaves C: By making the tree colder D: By blocking sunlight', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Interactions Among Living Things and Their Environment', 'difficulty' => 'Medium', 'text' => 'How do burrowing earthworms improve soil for garden vegetables? | A: Their tunneling aerates soil and allows water to reach roots easily B: They eat the plant roots C: They turn soil into stone D: They dry out the garden', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Interactions Among Living Things and Their Environment', 'difficulty' => 'Medium', 'text' => 'What would happen to animals in a community if all green plants were removed? | A: Herbivores would starve, oxygen levels would drop, and animal life would collapse B: Animals would thrive better C: Rocks would produce food D: It would have no effect', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Interactions Among Living Things and Their Environment', 'difficulty' => 'Hard', 'text' => 'Explain the relationship between clownfish and sea anemones on coral reefs. | A: Clownfish gets protection among stinging tentacles (immune to sting), while it cleans the anemone and lures food (Mutualism) B: Clownfish eats the anemone C: Anemone kills clownfish D: Parasitism', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '2',
        'lesson_number' => '4',
        'topic' => 'Environmental Stewardship and Conservation',
        'objectives' => [
            'Explain the importance of protecting Philippine plant and animal habitats (forests, rivers, mangroves).',
            'Identify harmful human activities that endanger local ecosystems.',
            'Practice concrete conservation actions: waste segregation, tree planting, and wildlife protection.'
        ],
        'content' => "Environmental stewardship means taking responsibility to care for, protect, and preserve the natural world for present and future generations.\n\nPhilippine ecosystems (coral reefs, tropical rainforests, mangrove swamps) are home to unique biodiversity like the Philippine Eagle, Tarsier, and Tamaraw.\n\nDestructive human actions—cutting down forests (deforestation), dumping trash in rivers, and blasting reefs—destroy homes of living things and lead to species endangerment.\n\nEvery student can be an environmental steward: planting native trees, conserving water, avoiding plastic waste, participating in river cleanups, and respecting wild animals.",
        'slides' => [
            [
                'title' => 'Environmental Stewardship & Conservation',
                'content' => "Grade 3 — Term 2 Life Science\n\nProtecting Philippine biodiversity: Caring for our forests, rivers, mangroves, and wildlife.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Philippine Biodiversity Treasures',
                'content' => "Unique organisms found only in the Philippines (Endemic):\n• Philippine Eagle: Magnificent apex predator of Sierra Madre rainforests\n• Philippine Tarsier: Tiny nocturnal primate of Bohol\n• Tamaraw: Rare dwarf wild buffalo of Mindoro\n• Dugong (Sea Cow): Gentle marine herbivore of Palawan seagrass beds",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Threats to Wildlife Habitats',
                'content' => "Human impacts threatening nature:\n• Deforestation: Cutting trees leaves birds and primates homeless\n• River & Ocean Plastic Pollution: Sea turtles ingest plastic bags mistaking them for jellyfish\n• Siltation: Soil runoff smothers living coral reefs",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Sort waste into 4 bins at school\nStep 2: Plant native tree seedlings in the garden\nStep 3: Turn off taps to save freshwater\nStep 4: Spread the word to family and neighbors!"
            ],
            [
                'title' => 'Why Mangrove Forests Are Superheroes',
                'content' => "Coastal guardians of our islands:\n• Roots act as safe nursery schools for baby fish, crabs, and shrimp\n• Dense thickets break typhoon waves and protect coastal communities from storm surges\n• Traps sediment to prevent ocean muddying",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Stewardship Challenge Quiz',
                'content' => "Which action represents responsible environmental stewardship?\nA) Burning plastic garbage in the backyard\nB) Planting native mangrove and tree seedlings along riverbanks\nC) Keeping wild endangered tarsiers as indoor pets\nD) Dumping detergent soap directly into rivers\n\nCorrect Answer: B (Tree planting restores habitats and stabilizes soil!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Environmental Stewardship and Conservation', 'difficulty' => 'Easy', 'text' => 'What is the national bird of the Philippines that lives in dense mountain rainforests? | A: Philippine Eagle B: Maya C: Pigeon D: Ostrich', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Environmental Stewardship and Conservation', 'difficulty' => 'Easy', 'text' => 'What do we call the act of planting new trees to replace destroyed forests? | A: Reforestation B: Mining C: Deforestation D: Harvesting', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Environmental Stewardship and Conservation', 'difficulty' => 'Medium', 'text' => 'Why are mangrove forests along coastlines vital for protecting Philippine villages? | A: Their tangled roots break storm waves and prevent shoreline erosion during typhoons B: They make beaches sandy C: They stop rain from falling D: They produce electricity', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Environmental Stewardship and Conservation', 'difficulty' => 'Medium', 'text' => 'How does throwing plastic bags into a river miles inland endanger sea turtles in the ocean? | A: Plastic floats out to sea where sea turtles mistake it for jellyfish and choke B: Plastic dissolves into salt C: Plastic makes water cold D: Turtles eat plastic intentionally', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Environmental Stewardship and Conservation', 'difficulty' => 'Hard', 'text' => 'Design an action project for Grade 3 pupils to restore a degraded creek bank beside their school. | A: Clean up plastic wrappers, plant bamboo and native shrubs to anchor soil, and post educational no-littering signs B: Dump concrete waste into creek C: Pour bleach into water D: Cut remaining bushes', 'correct' => 'A']
        ]
    ],

    // ==========================================
    // GRADE 4 — LIFE SCIENCE (VALIDATED DEPED MATATAG CURRICULUM)
    // Validated by Ma. Concepcion Nathalie A. Quintos (Teacher III) & Dr. Maricar A. Afuang (Principal III)
    // Bay Central Elementary School — Overall Rating: 9.5/10 (Classroom Ready)
    // ==========================================
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '1',
        'topic' => 'Systems in Animals and Plants',
        'objectives' => [
            'Identify and describe the functions of major body systems in animals: skeletal, muscular, digestive, respiratory, and circulatory systems.',
            'Describe the two major plant systems: root system (anchoring, absorption) and shoot system (stem, leaves, flowers, fruits for photosynthesis, reproduction, and support).',
            'Perform a hands-on diagram labeling and explanation performance task illustrating how plant and animal systems maintain life.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 1: Systems in Animals and Plants\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nDescribe the functions of major organs and organ systems in animals and plants.\n\nORGAN SYSTEMS IN ANIMALS:\n• Skeletal System: Internal framework of bones that supports and protects vital organs (e.g., skull protects brain, ribs protect heart and lungs).\n• Muscular System: Muscle tissues that work together with bones to create body movement (running, jumping, posture).\n• Digestive System: Breaks down and processes food so the body can absorb essential nutrients and energy.\n• Respiratory System: Takes in life-sustaining oxygen gas from the air and removes carbon dioxide waste from the body.\n• Circulatory System: Heart and blood vessels that pump and transport oxygen, nutrients, and fluids throughout the body.\n\nMAJOR SYSTEMS IN PLANTS:\nPlants have two interconnected organ systems:\n1. Root System: Underground roots that anchor the plant securely in the soil and absorb water and dissolved mineral nutrients.\n2. Shoot System: Aerial parts including the stem, leaves, flowers, and fruits.\n   - Leaves: Carry out photosynthesis to manufacture food.\n   - Stem: Provides physical support and transports water and sap.\n   - Flowers & Fruits: Carry out reproduction and seed development.\n\nINTERACTIVE ACTIVITY: Which System?\n• Situation 1: A boy runs across the school playground → Skeletal and Muscular Systems collaborate.\n• Situation 2: A girl inhales fresh morning air → Respiratory System.\n• Situation 3: A plant absorbs rainwater after a thunderstorm → Root System.\n• Situation 4: An athlete digests an energy snack → Digestive and Circulatory Systems.\n\nPERFORMANCE TASK: Diagram Investigation\nLearners observe an illustrated diagram of a flowering plant and human torso, label the primary root/shoot and organ systems, and write a 2-sentence explanation of each system's essential function.\n\nKEY TAKEAWAYS:\n• Animal systems (skeletal, muscular, digestive, respiratory, circulatory) coordinate to maintain life.\n• Plants rely on two systems: Root System (underground anchor/intake) and Shoot System (aerial food production and reproduction).\n• Organs work together in teams called organ systems.",
        'slides' => [
            [
                'title' => 'Systems in Animals and Plants',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nExplore the cooperative body systems that power animal movement and plant life.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Five Animal Body Systems',
                'content' => "Coordinated Teamwork in the Animal Body:\n• Skeletal System: Framework of bones protecting organs and giving shape\n• Muscular System: Muscles pulling on bones to create movement\n• Digestive System: Processing food into absorbable glucose nutrients\n• Respiratory System: Exchanging oxygen and carbon dioxide\n• Circulatory System: Pumping oxygenated blood to all living cells",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Plant Systems: Root vs Shoot',
                'content' => "The Two Plant Super-Systems:\n• Root System (Underground): Taproots and fibrous roots anchoring the plant and taking in soil moisture and minerals\n• Shoot System (Aerial): Stems, green leaves, flowers, and fruits\n  - Leaves perform photosynthesis\n  - Stems support foliage and transport sap\n  - Flowers produce seeds for reproduction!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Roots absorb water and minerals from soil\nStep 2: Stem xylem channels water upward to leaves\nStep 3: Leaves absorb sunlight for photosynthesis\nStep 4: Flowers and fruits ensure species reproduction"
            ],
            [
                'title' => 'Interactive Activity: Which System?',
                'content' => "Identify the active system:\n• Running involves both Skeletal and Muscular systems\n• Breathing involves the Respiratory system\n• Absorbing water from soil involves the Root system\n• Transporting glucose to muscles involves the Circulatory system",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Organ Systems',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-A, 3-C, 4-B, 5-B):\n1. Skeletal System supports and protects organs (B)\n2. Muscular System works with bones for movement (A)\n3. Digestive System processes food and absorbs nutrients (C)\n4. Root System anchors plant and absorbs water (B)\n5. Shoot System carries out photosynthesis and reproduction (B)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Systems in Animals and Plants', 'difficulty' => 'Easy', 'text' => 'Which body system provides an internal framework to support and protect vital organs? | A: Respiratory System B: Skeletal System C: Digestive System D: Circulatory System', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Systems in Animals and Plants', 'difficulty' => 'Easy', 'text' => 'Which body system works together with bones to enable walking, running, and jumping? | A: Muscular System B: Circulatory System C: Root System D: Shoot System', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Systems in Animals and Plants', 'difficulty' => 'Medium', 'text' => 'Which organ system processes the food we eat and helps the body absorb vital nutrients? | A: Respiratory System B: Skeletal System C: Digestive System D: Circulatory System', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Systems in Animals and Plants', 'difficulty' => 'Medium', 'text' => 'Which plant system anchors the plant securely in the soil and absorbs water and dissolved minerals? | A: Shoot System B: Root System C: Skeletal System D: Muscular System', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Systems in Animals and Plants', 'difficulty' => 'Hard', 'text' => 'Which plant system includes the stem, leaves, flowers, and fruits responsible for photosynthesis and reproduction? | A: Root System B: Shoot System C: Digestive System D: Circulatory System', 'correct' => 'B']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '2',
        'topic' => 'Plant and Animal Habitats',
        'objectives' => [
            'Define a habitat as the natural place where an organism lives and gets food, water, shelter, and space.',
            'Differentiate terrestrial and aquatic habitats and describe aerial environments used for feeding and flight.',
            'Explain how organisms depend on healthy habitats and the impacts of habitat loss on survival.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 2: Plant and Animal Habitats\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nIdentify and describe plant and animal habitats and explain how organisms depend on their habitat for survival.\n\nWHAT IS A HABITAT?\nA habitat is the natural place where an organism lives, grows, and gets what it needs to survive.\n\nFOUR BASIC NEEDS PROVIDED BY HABITATS:\n1. Food: Nutrients needed for energy and growth.\n2. Water: Essential for hydration and cellular function.\n3. Shelter: Protection from predators, harsh heat, and bad weather.\n4. Space: Adequate area to move, hunt, grow roots, and raise offspring.\n\nMAJOR HABITAT TYPES:\n• Terrestrial Habitats (Land): Tropical rainforests, open grasslands, farms, and mountains. (Examples: Carabao, narra tree, Philippine deer).\n• Aquatic Habitats (Water): Freshwater (rivers, lakes, ponds like Laguna de Bay) and Marine (coral reefs, open ocean). (Examples: Tilapia, bangus, water lilies, sea turtles).\n• Scientific Note on 'Aerial Habitats': Some animals spend much of their time moving or feeding in the air (eagles, fruit bats, dragonflies, butterflies). However, they rest, nest, and reproduce on land, trees, cliffs, or water. Therefore, 'aerial' is accurately described as an environment used primarily for movement and feeding rather than a permanent home.\n\nINTERACTIVE ACTIVITY: Match the Organism to its Habitat!\n• Bangus (Milkfish) → Aquatic freshwater/marine habitat\n• Carabao → Terrestrial agricultural grassland\n• Philippine Eagle → High canopy trees and forest airspace\n• Water Lily → Aquatic pond surface\n\nKEY TAKEAWAYS:\n• Habitats provide food, water, shelter, and space.\n• Land habitats are terrestrial; water habitats are aquatic.\n• Aerial environments are used for movement and foraging, with organisms resting on land/trees.\n• Protecting natural habitats is crucial for species preservation.",
        'slides' => [
            [
                'title' => 'Plant and Animal Habitats',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nDiscover where living organisms live and find food, water, shelter, and space.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Four Essential Habitat Needs',
                'content' => "Every living organism requires four necessities:\n1. Food: Fuel for body metabolism and growth\n2. Water: Vital fluid for all living cells\n3. Shelter: Safe haven from storms and predators\n4. Space: Territory to forage and rear young without overcrowding",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Terrestrial, Aquatic, & Aerial',
                'content' => "Understanding different habitats:\n• Terrestrial: Forests, plains, backyard gardens (Carabao, trees)\n• Aquatic: Ponds, rivers, coral reefs (Bangus, tilapia, corals)\n• Aerial: Airspace used for feeding and flight, while birds and bats rest and nest in terrestrial trees and cliffs!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Check where organism breathes and feeds\nStep 2: Check where it finds shelter and rests\nStep 3: Terrestrial = on land; Aquatic = in water\nStep 4: Aerial = airspace for flying & hunting"
            ],
            [
                'title' => 'Habitats Under Threat',
                'content' => "Human impacts on wildlife homes:\n• Deforestation deprives forest birds and mammals of canopy shelter\n• Water pollution suffocates aquatic fish and river plants\n• Conserving Philippine habitats protects our rich biodiversity!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Habitats',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-C, 3-A, 4-B, 5-A):\n1. A habitat is the natural place where organisms get their needs (B)\n2. Laguna de Bay is an example of an aquatic habitat (C)\n3. The 4 basic needs are food, water, shelter, and space (A)\n4. Aerial animals must land on trees or cliffs to rest and nest (B)\n5. Habitat destruction threatens wildlife survival (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Plant and Animal Habitats', 'difficulty' => 'Easy', 'text' => 'What is the natural place where an organism lives and obtains food, water, shelter, and space? | A: Ecosystem web B: Habitat C: Weather D: Metamorphosis', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Plant and Animal Habitats', 'difficulty' => 'Easy', 'text' => 'Which of the following is an example of an aquatic habitat in the Philippines? | A: Sierra Madre forest B: Baguio pine ridge C: Laguna de Bay freshwater lake D: Rice paddy dike', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Plant and Animal Habitats', 'difficulty' => 'Medium', 'text' => 'What four basic survival needs does a natural habitat provide to plants and animals? | A: Food, water, shelter, and space B: Sunlight, gold, wind, and iron C: Rocks, noise, clothes, and tools D: Electricity, glass, salt, and roads', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Plant and Animal Habitats', 'difficulty' => 'Medium', 'text' => 'Why is the air described as an environment used mainly for movement and feeding rather than a permanent aerial habitat? | A: Flying animals cannot breathe air B: Flying animals like eagles and bats must land on trees or cliffs to rest, nest, and reproduce C: Birds never touch the ground D: Insects live permanently in clouds', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Plant and Animal Habitats', 'difficulty' => 'Hard', 'text' => 'What happens to forest wildlife when trees are cut down and their habitat is destroyed? | A: They lose their food, shelter, and space, threatening their survival B: They instantly double in population C: They turn into aquatic animals D: They grow bigger wings', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '3',
        'topic' => 'Life Cycles of Plants and Animals',
        'objectives' => [
            'Define a life cycle and describe the developmental stages of flowering plants and common animals.',
            'Sequence the life cycle stages of flowering plants (Seed → Germination → Seedling → Mature Plant → Flower/Fruit → Seeds).',
            'Compare developmental stages in butterflies (Egg → Caterpillar → Pupa → Adult) and chickens (Egg → Chick → Adult).'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 3: Life Cycles of Plants and Animals\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nDescribe and compare the stages in the life cycle of plants and animals.\n\nWHAT IS A LIFE CYCLE?\nA life cycle refers to the series of developmental stages an organism goes through as it grows, develops, and reproduces, ensuring the continuation of its species.\n\nFLOWERING PLANT LIFE CYCLE:\nMany flowering plants begin their life cycle from a seed. (Scientific note: Clarified that this applies specifically to flowering plants, as other plants reproduce via spores or vegetative parts).\n• Stage 1: Seed (Dormant seed containing embryo and stored food)\n• Stage 2: Germination (Sprouting root and shoot emerge with water and warmth)\n• Stage 3: Seedling (Young delicate plant with first green leaves)\n• Stage 4: Mature Plant (Full-sized plant with sturdy stem and foliage)\n• Stage 5: Flower/Fruit (Flowers bloom, pollination occurs, and fruit forms)\n• Stage 6: New Seeds (Dispersed to begin the next generation)\nFlowchart: Seed → Germination → Seedling → Mature Plant → Flower/Fruit → Seeds\n\nANIMAL LIFE CYCLES:\n• Butterfly (Complete Metamorphosis):\n  Egg → Larva (Caterpillar) → Pupa (Chrysalis) → Adult Butterfly\n• Chicken:\n  Egg → Chick → Young Chicken → Adult Chicken\n\nINTERACTIVE ACTIVITY: Whose Life Cycle Is It?\n1. Egg → Larva → Pupa → Adult → Butterfly\n2. Seed → Germination → Seedling → Mature Plant → Flower/Fruit → Flowering Plant (e.g. Tomato / Mongo)\n3. Egg → Chick → Adult Chicken → Chicken\n\nKEY TAKEAWAYS:\n• A life cycle shows the continuous stages of growth and reproduction.\n• Flowering plant cycle: Seed → Germination → Seedling → Mature Plant → Flower/Fruit → Seeds.\n• Butterfly transforms through complete metamorphosis (egg, larva, pupa, adult).\n• Life cycles ensure living things produce new generations.",
        'slides' => [
            [
                'title' => 'Life Cycles of Plants and Animals',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nTrace the wonderful stages of growth: From seeds to flowers and eggs to adults.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Flowering Plant Developmental Cycle',
                'content' => "Standard Sequence for Flowering Plants:\n• Seed: Dormant seed planted in soil\n• Germination: Root sprouts downward, shoot reaches upward\n• Seedling: Young plant unfolds first green leaves\n• Mature Plant: Develops strong stems and branches\n• Flower/Fruit → New Seeds: Blooms, produces fruit, and disperses seeds!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Butterfly Metamorphosis',
                'content' => "Complete Transformation (4 Stages):\n• Egg: Tiny egg laid under host plant leaves\n• Larva (Caterpillar): Voracious leaf-eater growing quickly\n• Pupa (Chrysalis): Resting stage of profound internal transformation\n• Adult Butterfly: Flying adult pollinating flowers and laying eggs",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Stage 1: Egg on green leaf\nStage 2: Leaf-chewing Caterpillar (Larva)\nStage 3: Protective Chrysalis (Pupa)\nStage 4: Adult Butterfly unfolds colorful wings!"
            ],
            [
                'title' => 'The Chicken Life Cycle',
                'content' => "Simple Avian Development:\n• Egg: Hen lays fertilised shelled egg and incubates it with body warmth\n• Chick: Hatches after 21 days, covered in fluffy down feathers\n• Young Chicken: Grows adult feathers and comb\n• Adult Chicken: Fully grown rooster or egg-laying hen",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Life Cycles',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-A, 3-B, 4-B, 5-A):\n1. Life cycle is the stages an organism goes through as it grows (B)\n2. Flowering plants begin their life cycle from a seed (A)\n3. The resting, transforming butterfly stage is the Pupa (B)\n4. Chicken sequence: Egg → Chick → Adult Chicken (B)\n5. Flowcharts show the order of stages clearly (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Life Cycles of Plants and Animals', 'difficulty' => 'Easy', 'text' => 'What is a life cycle in living organisms? | A: A bicycle for animals B: The stages an organism goes through as it grows and develops C: The food an animal eats D: How fast an organism runs', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Life Cycles of Plants and Animals', 'difficulty' => 'Easy', 'text' => 'For many flowering plants, from what structure does their life cycle begin? | A: Seed B: Fruit C: Leaf D: Flower petal', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Life Cycles of Plants and Animals', 'difficulty' => 'Medium', 'text' => 'What is the resting, transforming stage in a butterfly\'s life cycle before it emerges as an adult? | A: Caterpillar B: Pupa (Chrysalis) C: Chick D: Sprout', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Life Cycles of Plants and Animals', 'difficulty' => 'Medium', 'text' => 'Which sequence correctly represents the developmental stages of a chicken? | A: Chick → Egg → Adult Chicken B: Egg → Chick → Adult Chicken C: Adult Chicken → Chick → Egg D: Egg → Pupa → Chick', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Life Cycles of Plants and Animals', 'difficulty' => 'Hard', 'text' => 'Why do scientists use flowcharts or circular diagrams when illustrating life cycles? | A: To show the order of stages clearly and demonstrate how life continues across generations B: Because circles are easy to draw C: To prove animals do not grow D: To hide the developmental stages', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '4',
        'topic' => 'Animals and the Food They Eat',
        'objectives' => [
            'Classify animals into herbivores, carnivores, and omnivores according to the food they eat.',
            'Describe how herbivores mainly eat plants, carnivores mainly eat other animals, and omnivores eat both plants and animals.',
            'Connect teeth structure and feeding adaptations with dietary classifications.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 4: Animals and the Food They Eat\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nClassify animals according to what they eat (herbivores, carnivores, and omnivores).\n\nTHREE DIETARY CLASSIFICATIONS:\n• Herbivores — Animals that mainly eat plants (grass, leaves, fruits, stems, seeds).\n  - Philippine Examples: Carabao, goat, cow, deer, rabbit, caterpillar.\n  - Feeding Adaptations: Broad, flat molars to grind tough plant fibers.\n• Carnivores — Animals that mainly eat other animals (meat, fish, insects).\n  - Philippine Examples: Philippine Eagle, cobra/snake, crocodile, shark, lion, tiger.\n  - Feeding Adaptations: Sharp pointed canine teeth, hooked beaks, and sharp talons to catch and tear flesh.\n• Omnivores — Animals that eat both plants and animals.\n  - Philippine Examples: Chicken (eats grains and insects), pig (eats vegetable slops and small fish), duck, bear, and humans.\n  - Feeding Adaptations: Versatile combination of sharp front incisors/canines and flat grinding molars.\n\nSCIENTIFIC MEMORY AID:\n• Herbivores → Mainly eat plants\n• Carnivores → Mainly eat other animals\n• Omnivores → Eat both plants and animals\n\nINTERACTIVE ACTIVITY: Sort by Diet!\n• Cow → Grass → Herbivore\n• Goat → Leaves and shrubs → Herbivore\n• Philippine Eagle → Monkeys, snakes, fish → Carnivore\n• Snake → Frogs and mice → Carnivore\n• Chicken → Rice grains and worms → Omnivore\n• Pig → Roots, vegetables, and leftover meat → Omnivore\n\nKEY TAKEAWAYS:\n• Animals are grouped by their diet: herbivores (plant eaters), carnivores (meat eaters), and omnivores (both).\n• A chicken is an omnivore because it pecks at grains and catches small insects.\n• Teeth structure matches an animal's diet.",
        'slides' => [
            [
                'title' => 'Animals & the Food They Eat',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nExplore dietary diversity: Herbivores, Carnivores, and Omnivores in Philippine habitats.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Herbivores: Plant Eaters',
                'content' => "Mainly eat plant material:\n• Food: Grass, leaves, green shoots, fruits, and seeds\n• Philippine Examples: Carabao grazing in fields, goats on hillsides, rabbits, caterpillars\n• Anatomy: Wide flat molars for grinding fibrous cellulose",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Carnivores: Animal Eaters',
                'content' => "Mainly eat other animals:\n• Food: Meat, fish, lizards, insects, and frogs\n• Philippine Examples: Philippine Eagle, cobras, monitor lizards, sharks\n• Anatomy: Sharp curved beaks, talons, and sharp canine teeth designed for hunting",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Diet: Meat & prey\nBeak: Hooked and powerful\nClaws: Sharp curved talons\nClassification: Apex Carnivore (Philippine Eagle)"
            ],
            [
                'title' => 'Omnivores: Both Plants and Animals',
                'content' => "Versatile eaters enjoying both:\n• Food: Seeds, fruit, roots, insects, worms, and small animals\n• Common Examples: Chickens pecking corn and earthworms, pigs, ducks, and humans!\n• Anatomy: Mixed teeth suited for cutting, tearing, and chewing",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Animal Feeding Groups',
                'content' => "Check Your Understanding (Validated Answer Key: 1-A, 2-C, 3-A, 4-B, 5-C):\n1. Herbivores like carabaos mainly eat plants (A)\n2. The Philippine Eagle is a carnivore (C)\n3. A chicken is an omnivore because it eats grains and insects (A)\n4. Flat molars adapted for grinding grass indicate an Herbivore (B)\n5. Omnivores obtain energy from both plants and animals (C)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Animals and the Food They Eat', 'difficulty' => 'Easy', 'text' => 'What do herbivorous animals like carabaos, cows, and goats mainly eat? | A: Plants such as grass, leaves, and fruits B: Meat and fish only C: Insects and spiders D: Bones and shells', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Animals and the Food They Eat', 'difficulty' => 'Easy', 'text' => 'Which of the following Philippine animals is a carnivore that feeds on other animals? | A: Carabao B: Goat C: Philippine Eagle D: Grasshopper', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Animals and the Food They Eat', 'difficulty' => 'Medium', 'text' => 'Why is a chicken classified as an omnivore? | A: It eats both plant seeds/grains and small insects/worms B: It eats only tall trees C: It hunts ocean sharks D: It drinks only nectar', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Animals and the Food They Eat', 'difficulty' => 'Medium', 'text' => 'An animal with broad, flat molars adapted for grinding tough fibrous grass belongs to which group? | A: Carnivore B: Herbivore C: Scavenger D: Parasite', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Animals and the Food They Eat', 'difficulty' => 'Hard', 'text' => 'How do omnivorous animals survive in diverse environments? | A: They do not need any food B: They eat only rocks C: They can obtain energy from both plant materials and animal foods D: They only eat at night', 'correct' => 'C']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '5',
        'topic' => 'Food Chains',
        'objectives' => [
            'Construct and interpret simple food chains in Philippine ecosystems.',
            'Identify producers, primary consumers, secondary consumers, and tertiary consumers.',
            'Explain that the arrow in a food chain points from the organism being eaten to the organism that eats it.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 5: Food Chains\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nConstruct simple food chains in Philippine habitats and explain energy flow between organisms.\n\nWHAT IS A FOOD CHAIN?\nA food chain shows the linear sequence of who eats whom in an ecosystem, showing how food energy passes from one living thing to another.\n\nROLES IN A FOOD CHAIN:\n• Producer: Green plants that capture sunlight to manufacture their own food through photosynthesis (e.g., grass, rice plant, algae).\n• Primary Consumer: Herbivores that feed directly on producers (e.g., grasshopper, caterpillar).\n• Secondary Consumer: Carnivores or omnivores that feed on primary consumers (e.g., frog, chicken).\n• Tertiary Consumer / Other Consumers: Predators that feed on secondary consumers (e.g., snake, hawk).\n• Decomposers: Fungi and soil bacteria that recycle nutrients back into soil.\n\nCRUCIAL RULE ON FOOD CHAIN ARROWS:\nThe arrow points in the direction of energy flow: from the organism being eaten TO the organism that eats it!\nExample: Grass → Grasshopper → Frog → Snake\n• Grass is eaten by Grasshopper (arrow points to Grasshopper)\n• Grasshopper is eaten by Frog (arrow points to Frog)\n• Frog is eaten by Snake (arrow points to Snake)\n\nPHILIPPINE RICE FIELD FOOD CHAIN:\nRice Plant (Producer) → Grasshopper (Primary Consumer) → Field Frog (Secondary Consumer) → Snake (Tertiary Consumer)\n\nINTERACTIVE ACTIVITY: Build the Food Chain!\nArrange in the correct energy sequence:\n• Organisms: Frog, Grass, Snake, Grasshopper\n• Correct Order: Grass → Grasshopper → Frog → Snake\n\nKEY TAKEAWAYS:\n• All food chains begin with a producer (green plant) fueled by sunlight.\n• Arrows always point to the consumer that eats the organism.\n• A disruption in one link (e.g. pesticide wiping out frogs) causes grasshopper populations to surge.",
        'slides' => [
            [
                'title' => 'Food Chains & Energy Flow',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nTrace energy pathways in Philippine ecosystems: From producers to apex predators.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Producers, Consumers, & Decomposers',
                'content' => "Roles in the Feeding Order:\n• Producers: Green plants making food from sunlight (Rice, grass)\n• Primary Consumers: Herbivores eating plants (Grasshopper)\n• Secondary Consumers: Carnivores eating herbivores (Frog)\n• Tertiary Consumers: Animals eating secondary consumers (Snake)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Direction of the Arrow',
                'content' => "Always remember this scientific rule:\n• The arrow points toward the eater!\n• It shows where food energy is traveling!\n$$\\text{Grass} \\longrightarrow \\text{Grasshopper} \\longrightarrow \\text{Frog} \\longrightarrow \\text{Snake}$$\n• Energy originates from the Sun!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Start with green plant Producer\nStep 2: Draw arrow pointing to Herbivore\nStep 3: Draw arrow pointing to Carnivore\nStep 4: Arrow points to the organism that eats it"
            ],
            [
                'title' => 'Philippine Habitat Food Chains',
                'content' => "Garden Pond Ecosystem:\n• Water Lily (Producer) → Snail (Primary Consumer) → Tilapia (Secondary Consumer) → Kingfisher Bird (Tertiary Consumer)\n• Balance in the chain keeps populations healthy!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Food Chains',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-C, 3-D, 4-B, 5-A):\n1. A food chain represents feeding relationships and energy transfer (B)\n2. Grass is the producer in the chain (C)\n3. Cobra acts as the tertiary consumer (D)\n4. Arrow indicates energy flowing to the organism eating it (B)\n5. Disappearance of frogs causes grasshoppers to increase (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Food Chains', 'difficulty' => 'Easy', 'text' => 'What does a simple food chain represent in an ecosystem? | A: A list of all animals in a zoo B: The feeding relationships and pathway of food energy from one organism to another C: How plants absorb water from soil D: The physical size of different animals', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Food Chains', 'difficulty' => 'Easy', 'text' => 'In the food chain: Grass → Grasshopper → Frog → Snake, which organism is the producer? | A: Snake B: Frog C: Grass D: Grasshopper', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Food Chains', 'difficulty' => 'Medium', 'text' => 'In the food chain: Rice Plant → Locust → Field Frog → Cobra, which organism acts as a tertiary consumer? | A: Rice Plant B: Locust C: Field Frog D: Cobra', 'correct' => 'D'],
            ['grade' => '4', 'topic' => 'Food Chains', 'difficulty' => 'Medium', 'text' => 'What is the meaning of an arrow pointing from grass to grasshopper in a food chain diagram? | A: The grass eats the grasshopper B: Energy flows from the grass to the grasshopper that eats it C: The grasshopper protects the grass D: Grasshoppers turn into grass', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Food Chains', 'difficulty' => 'Hard', 'text' => 'If all frogs in a garden pond ecosystem disappear, what is the most likely immediate effect on the food chain? | A: The grasshopper population will increase because frogs are no longer eating them B: Snakes will multiply rapidly C: Grass will disappear instantly D: Water will stop flowing', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '6',
        'topic' => 'Water and Living Things',
        'objectives' => [
            'Explain the vital importance of water to plants and animals and identify freshwater sources.',
            'Describe how plants use water as one of the essential materials in photosynthesis and how roots absorb water.',
            'Identify how animals obtain and use water to transport nutrients and maintain health.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 6: Water and Living Things\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nExplain the importance of water to plants and animals and identify freshwater sources.\n\nFRESHWATER SOURCES:\nLiving things depend on freshwater from:\n• Rainwater (precipitation replenishment)\n• Rivers, streams, and creeks\n• Freshwater lakes (e.g., Laguna de Bay, Lake Taal)\n• Groundwater (springs, deep wells, and aquifers)\n\nIMPORTANCE OF WATER TO PLANTS:\n• Essential Material for Photosynthesis: Green plants use water as one of the essential raw materials (combined with sunlight and carbon dioxide) to manufacture food (glucose).\n• Absorption by Roots: Roots are the primary organs that absorb moisture and dissolved soil minerals, transporting them upward through the stem.\n• Structural Support: Water fills plant cells, creating turgor pressure that keeps stems and leaves upright and crisp. Without water, plants wilt.\n\nIMPORTANCE OF WATER TO ANIMALS AND HUMANS:\n• Animals drink water directly or obtain moisture indirectly through their food and moist environments (e.g., frogs absorb moisture through their skin and surroundings).\n• Water helps transport nutrients and oxygen in blood plasma.\n• Water regulates body temperature and helps flush out metabolic body wastes.\n\nINTERACTIVE ACTIVITY: Water in Nature!\n1. Why do wilted leaves stand tall after watering? → Water enters roots and restores internal cell firmness.\n2. How does a dog cool down on a hot summer afternoon? → Panting releases moisture, cooling the body.\n3. What freshwater source supplies water to community irrigation? → Rivers, dams, and lakes.\n\nKEY TAKEAWAYS:\n• Rain, rivers, lakes, and groundwater supply freshwater to living organisms.\n• Plants absorb water through roots as a necessary raw material for photosynthesis.\n• Animals need water to digest food, circulate nutrients, and stay cool.",
        'slides' => [
            [
                'title' => 'Water and Living Things',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nDiscover the vital importance of freshwater for plant growth and animal survival.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Freshwater Sources in the Philippines',
                'content' => "Where our life-giving water comes from:\n• Rain: Replenishes watersheds and reservoirs\n• Rivers & Streams: Moving freshwater nourishing valleys\n• Lakes: Natural inland water bodies (Laguna de Bay)\n• Groundwater: Pure water filtered deep underground in aquifers",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'How Plants Use Water',
                'content' => "Water Powers the Botanical Factory:\n• Roots absorb water and dissolved soil minerals\n• Photosynthesis: Plants use water as a key raw material with light and CO2 to produce food\n• Water pressure keeps non-woody stems erect so leaves capture sunshine!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Rain moistens soil\nStep 2: Root hairs absorb moisture\nStep 3: Xylem tubes channel water to leaves\nStep 4: Leaves use water in photosynthesis"
            ],
            [
                'title' => 'How Animals Obtain Water',
                'content' => "Hydration Mechanisms in Animals:\n• Drinking: Drinking directly from streams or puddles\n• Food Intake: Consuming juicy fruits, leaves, and prey\n• Moist Habitats: Frogs absorb moisture directly through their permeable skin",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Water & Life',
                'content' => "Check Your Understanding (Validated Answer Key: 1-A, 2-B, 3-C, 4-A, 5-C):\n1. Roots are responsible for absorbing water from soil (A)\n2. Rivers, lakes, and groundwater are vital freshwater sources (B)\n3. Plants use water as a raw material in photosynthesis (C)\n4. Deep roots help trees survive dry weather by reaching groundwater (A)\n5. Animals need water to transport nutrients and regulate temperature (C)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Water and Living Things', 'difficulty' => 'Easy', 'text' => 'What main plant part is responsible for absorbing water and dissolved minerals from the soil? | A: Roots B: Petals C: Bark D: Seeds', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Water and Living Things', 'difficulty' => 'Easy', 'text' => 'Which of the following is a vital freshwater source that provides drinking water for humans, animals, and agricultural crops? | A: Ocean saltwater B: Rivers, lakes, and groundwater C: Bleach D: Muddy puddle', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Water and Living Things', 'difficulty' => 'Medium', 'text' => 'Why is water essential for green plants during photosynthesis? | A: Water cools the ground B: Water turns leaves brown C: Plants use water as one of the raw materials combined with light and carbon dioxide to produce food D: Water replaces sunlight', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Water and Living Things', 'difficulty' => 'Medium', 'text' => 'How does a tree survive during sunny days when water in the soil is scarce? | A: Deep root systems absorb groundwater and leaves regulate water loss B: It walks to a river C: It stops needing sunlight D: It turns into stone', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Water and Living Things', 'difficulty' => 'Hard', 'text' => 'Why do living animals need to drink water or obtain moisture from their food? | A: To stay asleep B: Water is only used for swimming C: To transport nutrients in the body, regulate temperature, and maintain healthy cell function D: To prevent breathing', 'correct' => 'C']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '2',
        'lesson_number' => '7',
        'topic' => 'Soil and Plant Growth',
        'objectives' => [
            'Identify components of healthy soil: rock fragments, organic matter, air, water, and living organisms.',
            'Compare the characteristics and particle sizes of Sandy, Clay, Silt, and Loam soils.',
            'Conduct a simple guided investigation to determine how different soil types affect drainage and plant growth.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 7: Soil and Plant Growth\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nInvestigate the characteristics of different types of soil and how they affect plant growth.\n\nCOMPONENTS OF SOIL:\nSoil is made up of:\n• Rock fragments: Tiny particles broken down from large rocks over time.\n• Organic matter: Decayed fallen leaves, dead plants, and decomposing animal matter (humus).\n• Air: Pockets of oxygen needed by root cells to respire.\n• Water: Dissolves soil nutrients for plant absorption.\n• Living organisms: Earthworms, beneficial bacteria, and fungi that aerate soil.\n\nFOUR MAIN SOIL TYPES & PARTICLE SIZES:\n1. Sandy Soil: Large, coarse particles. Rough texture, drains water very quickly, low water-holding capacity.\n2. Clay Soil: Very fine, microscopic particles. Smooth and sticky when wet, hard when dry. Slow drainage, high water-holding capacity.\n3. Silt Soil: Fine particles that are smaller than sand but larger than clay. Smooth, flour-like texture, moderate water retention.\n4. Loam Soil: Balanced mixture of sand, silt, clay, and rich organic matter. It holds enough moisture while allowing proper drainage and root aeration, making it the most suitable soil for growing garden plants!\n\nHOW SOIL SUPPORTS PLANT GROWTH:\nSoil anchors roots firmly, provides access to dissolved mineral nutrients (Nitrogen, Phosphorus, Potassium), and supplies essential root air pockets.\n\nGUIDED INVESTIGATION: Soil Drainage & Water Holding\n• Materials: 3 identical clear plastic cups with small drainage holes, labeled: Sand, Clay, Loam. Measuring cup, 100 mL water per cup, stopwatch.\n• Procedure:\n  1. Fill each cup halfway with its designated soil type.\n  2. Pour 100 mL of water simultaneously into each cup.\n  3. Measure the volume of water drained after 3 minutes.\n• Observations:\n  - Sand cup: Drains water rapidly; low water retained.\n  - Clay cup: Water ponds on surface; very slow trickle.\n  - Loam cup: Drains steady excess water while soil stays moist and dark.\n• Conclusion: Different soil types possess different water-holding and drainage properties. Loam provides the ideal balance for root growth.\n\nKEY TAKEAWAYS:\n• Fertile soil contains minerals, organic matter, air, water, and organisms.\n• Silt particles are smaller than sand but larger than clay.\n• Loam is a balanced mixture ideal for crops and vegetables.\n• Proper soil allows both root breathing and adequate moisture retention.",
        'slides' => [
            [
                'title' => 'Soil and Plant Growth',
                'content' => "Grade 4 — Quarter 2: Life Science\n(Validated Curriculum — Bay Central Elementary School)\n\nDiscover the ground beneath our feet: Soil components, textures, and hands-on investigation.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Five Pillars of Healthy Soil',
                'content' => "What makes up fertile topsoil?\n• Rock fragments: Sand, silt, and clay particles\n• Organic matter: Decayed leaves and humus providing nutrients\n• Air pockets: Oxygen that root cells need to breathe\n• Soil water: Dissolves minerals for root uptake\n• Living organisms: Earthworms tunneling and aerating the earth",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Comparing Soil Types & Particle Sizes',
                'content' => "Understanding the 4 Textures:\n• Sand: Large coarse particles, rough feel, fast drainage\n• Silt: Smaller than sand but larger than clay; smooth silky feel\n• Clay: Microscopic particles, sticky when wet, slow drainage\n• Loam: Balanced mix of sand, silt, clay, and rich humus — the champion for plants!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Particle Size Comparison:\nSand (Largest & Coarsest)\n   ↓\nSilt (Medium & Smooth)\n   ↓\nClay (Smallest & Sticky)"
            ],
            [
                'title' => 'Hands-on Soil Drainage Investigation',
                'content' => "Scientific Investigation Steps:\n1. Prepare 3 cups: Sand, Clay, Loam with drainage holes\n2. Pour equal 100 mL water into each cup\n3. Record drained water and drainage speed\n4. Conclusion: Loam retains optimal moisture while allowing excess water to drain safely!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Soil & Plant Growth',
                'content' => "Check Your Understanding (Validated Answer Key: 1-D, 2-B, 3-A, 4-C, 5-A):\n1. Healthy soil contains rocks, organic matter, air, water, and organisms (D)\n2. Sandy soil has large particles and drains water quickly (B)\n3. Silt particles are smaller than sand but larger than clay (A)\n4. Loam soil is ideal because of balanced particles and humus (C)\n5. Simple investigation proves soils have different drainage capacities (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Soil and Plant Growth', 'difficulty' => 'Easy', 'text' => 'Which of the following is an essential component of healthy, fertile soil? | A: Pure plastic B: Glass fragments C: Aluminum foil D: Rock fragments, organic matter, air, water, and living organisms', 'correct' => 'D'],
            ['grade' => '4', 'topic' => 'Soil and Plant Growth', 'difficulty' => 'Easy', 'text' => 'Which soil type has large particles, feels rough to the touch, and drains water very quickly? | A: Clay soil B: Sandy soil C: Loam soil D: Mud', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Soil and Plant Growth', 'difficulty' => 'Medium', 'text' => 'How does the particle size of silt compare with sand and clay? | A: Silt particles are smaller than sand but larger than clay B: Silt particles are larger than pebbles C: Silt particles are the biggest in nature D: Silt has no particles', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Soil and Plant Growth', 'difficulty' => 'Medium', 'text' => 'Why is loam soil considered the most suitable soil type for growing most garden vegetables and crops? | A: It contains only stones B: It repels all moisture C: It has a balanced mixture of sand, silt, and clay with rich organic matter that retains moisture while allowing drainage D: It is made of pure chalk', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Soil and Plant Growth', 'difficulty' => 'Hard', 'text' => 'In a simple soil investigation comparing sand, clay, and loam, what can pupils conclude after pouring equal amounts of water through each soil cup? | A: Different soil types have different water-holding and drainage capacities affecting plant growth B: All soils drain water at the exact same second C: Water disappears completely inside sand D: Plants do not need soil', 'correct' => 'A']
        ]
    ],

    // ==========================================
    // GRADE 5 — LIFE SCIENCE
    // ==========================================
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '1',
        'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)',
        'objectives' => [
            'Describe the organs and physiological processes of the Digestive System (mouth, esophagus, stomach, small and large intestines).',
            'Describe the structures and gas exchange mechanisms of the Respiratory System (nose, trachea, lungs, alveoli, diaphragm).',
            'Explain the structures and functions of the male and female Reproductive Systems in human life continuity.'
        ],
        'content' => "The human body operates through interconnected organ systems that maintain internal homeostasis:\n- Digestive System: Mechanically breaks down food with teeth and stomach churning, then chemically breaks polymers into glucose, amino acids, and fatty acids using digestive enzymes and bile. The small intestine absorbs nutrients into the bloodstream, while the large intestine reabsorbs water and forms solid waste.\n- Respiratory System: Transports oxygen from ambient air into blood and removes carbon dioxide waste. The diaphragm contracts downward during inhalation, drawing air into lungs where alveoli exchange gases with capillaries.\n- Reproductive System: Ensures species propagation. The male system produces sperm cells and testosterone (testes, vas deferens, penis); the female system produces egg cells, supports fetal development, and produces estrogen/progesterone (ovaries, fallopian tubes, uterus, vagina).",
        'slides' => [
            [
                'title' => 'Human Body Systems',
                'content' => "Grade 5 — Term 1 Life Science\n\nExplore internal anatomy: The Digestive, Respiratory, and Reproductive Systems.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Digestive System: Food to Fuel',
                'content' => "The Gastrointestinal Pathway:\n• Mouth: Teeth chew food; salivary amylase begins carbohydrate breakdown\n• Esophagus: Peristalsis muscle contractions push bolus to stomach\n• Stomach: Hydrochloric acid and pepsin churn and digest proteins\n• Small Intestine: Pancreatic enzymes and liver bile complete digestion; villi absorb nutrients into capillaries\n• Large Intestine: Absorbs water and minerals; compacts feces",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Respiratory System: Gas Exchange',
                'content' => "How we breathe:\n• Inhalation: Diaphragm contracts downward, ribs lift, air rushes into lungs\n• Alveoli: 300 million microscopic air sacs enveloped in capillaries\n• Diffusion: Oxygen moves into blood; Carbon dioxide diffuses into alveoli to be exhaled",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Inhale: Diaphragm contracts -> Air fills alveoli\nGas Exchange: O2 enters capillaries, CO2 leaves\nExhale: Diaphragm relaxes -> Air pushed out\nEvery cell receives oxygen for energy!"
            ],
            [
                'title' => 'The Reproductive System: Continuity of Life',
                'content' => "Ensuring species continuation:\n• Male: Testes produce sperm cells and testosterone\n• Female: Ovaries release mature egg cells; Fallopian tubes are site of fertilization; Uterus (womb) protects developing fetus",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Body Systems Check Quiz',
                'content' => "In which organ of the digestive system are most digested nutrients absorbed into the bloodstream?\nA) Esophagus\nB) Stomach\nC) Small Intestine\nD) Large Intestine\n\nCorrect Answer: C (Small intestine villi absorb amino acids and glucose!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)', 'difficulty' => 'Easy', 'text' => 'In which organ of the digestive tract are most digested nutrients absorbed into blood? | A: Small intestine B: Mouth C: Stomach D: Large intestine', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)', 'difficulty' => 'Easy', 'text' => 'What muscular sheet contracts and moves downward to pull air into the lungs during inhalation? | A: Diaphragm B: Trachea C: Bronchus D: Esophagus', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)', 'difficulty' => 'Medium', 'text' => 'What are the microscopic air sacs in the lungs where oxygen passes into blood capillaries called? | A: Alveoli B: Villi C: Nephrons D: Bronchioles', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)', 'difficulty' => 'Medium', 'text' => 'In the female reproductive system, in which organ does fertilization of the egg typically occur? | A: Fallopian tube (oviduct) B: Uterus C: Ovary D: Cervix', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)', 'difficulty' => 'Hard', 'text' => 'How do the digestive, respiratory, and circulatory systems cooperate to generate cellular energy in muscle cells? | A: Digestive absorbs glucose -> Respiratory provides oxygen -> Circulatory delivers both to muscle mitochondria for ATP respiration B: Digestive pumps air C: Respiratory digests food D: Circulatory breathes', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '2',
        'topic' => 'Classification and Reproduction of Living Things',
        'objectives' => [
            'Compare sexual reproduction (gamete fusion) with asexual reproduction (budding, vegetative propagation, binary fission).',
            'Classify organisms into major taxonomic kingdoms based on cellular structure and reproductive mode.',
            'Describe how vegetative propagation is used in Philippine agriculture.'
        ],
        'content' => "Reproduction is the biological mechanism by which new individual organisms are produced:\n- Sexual Reproduction: Involves two parents providing gametes (sperm and egg) that fuse during fertilization to create genetically unique offspring with blended traits. Common in flowering plants, mammals, birds, and fish.\n- Asexual Reproduction: Involves a single parent producing offspring that are genetically identical clones. Examples include binary fission in bacteria, budding in yeast and hydra, and vegetative propagation in plants (potatoes from tubers, katakataka plantlets from leaves, onion bulbs).\n\nBiological classification groups organisms into Domains and Kingdoms based on cellular complexity and nutrition.",
        'slides' => [
            [
                'title' => 'Classification & Reproduction',
                'content' => "Grade 5 — Term 1 Life Science\n\nCompare sexual and asexual reproduction and explore biological classification.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Sexual vs Asexual Reproduction',
                'content' => "Two Routes to New Life:\n• Sexual Reproduction:\n  - Two parents provide sperm and egg gametes\n  - Fertilization creates genetically unique offspring\n  - High genetic variation helps species adapt to disease\n• Asexual Reproduction:\n  - Single parent produces identical offspring (clones)\n  - Rapid reproduction without needing a mate\n  - Examples: Binary fission (bacteria), Budding (yeast/hydra)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Vegetative Propagation in Crops',
                'content' => "Asexual reproduction in Philippine farming:\n• Stem Cuttings: Sugarcane and cassava grown from stalk nodes\n• Tubers & Bulbs: Sweet potatoes (camote) from tubers; Onions from bulbs\n• Runners / Stolons: Strawberry and grass creeping stems\n• Leaf Plantlets: Katakataka (miracle leaf) budding along leaf edges",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Cut healthy sugarcane stem with nodes\nStep 2: Plant cuttings diagonally in moist furrow\nStep 3: New roots sprout from nodes within days\nStep 4: Genetically identical crop grows reliably"
            ],
            [
                'title' => 'Taxonomic Kingdoms of Life',
                'content' => "Classifying the Living World:\n• Kingdom Animalia: Multicellular heterotrophs (movement, nervous tissue)\n• Kingdom Plantae: Multicellular autotrophs (cellulose cell walls, photosynthesis)\n• Kingdom Fungi: Heterotrophs absorbing nutrients (mushrooms, molds, yeast)\n• Kingdom Protista: Single-celled eukaryotes (amoeba, paramecium, algae)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Reproduction Check Quiz',
                'content' => "What is the primary evolutionary advantage of sexual reproduction over asexual cloning?\nA) It is much faster\nB) It requires no energy\nC) It creates genetic diversity that helps populations survive diseases and climate shifts\nD) It produces identical clones\n\nCorrect Answer: C (Genetic variation drives survival resilience!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Classification and Reproduction of Living Things', 'difficulty' => 'Easy', 'text' => 'What mode of reproduction requires only one parent and produces genetically identical clones? | A: Asexual reproduction B: Sexual reproduction C: Cross-fertilization D: Pollination', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Classification and Reproduction of Living Things', 'difficulty' => 'Easy', 'text' => 'Which agricultural crop in the Philippines is commonly grown asexually using stem cuttings? | A: Sugarcane B: Corn C: Coconut D: Mango', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Classification and Reproduction of Living Things', 'difficulty' => 'Medium', 'text' => 'What is the main biological advantage of sexual reproduction compared to asexual cloning? | A: It creates genetic variation, increasing species survival if diseases strike B: It is faster C: It requires zero food D: It produces fewer offspring', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Classification and Reproduction of Living Things', 'difficulty' => 'Medium', 'text' => 'How does the Katakataka (miracle leaf) plant reproduce asexually? | A: Tiny plantlets sprout along the serrated notches of its leaves and root in soil B: Through underground seeds C: With bright red flowers only D: By spores', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Classification and Reproduction of Living Things', 'difficulty' => 'Hard', 'text' => 'Contrast internal fertilization in mammals with external fertilization in fish. | A: Internal fertilization occurs inside female body protecting few embryos; external releases hundreds of eggs into water with higher predation risk B: Both are identical C: Fish have live birth D: Mammals lay eggs in water', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '3',
        'topic' => 'Life Cycles of Living Things',
        'objectives' => [
            'Analyze diverse life cycles across amphibians, reptiles, birds, mammals, and spore-bearing plants.',
            'Contrast internal embryonic gestation in placental mammals with external egg development in birds and reptiles.',
            'Diagram the alternation of generations in non-flowering ferns and mosses.'
        ],
        'content' => "Organisms have diverse reproductive strategies and life cycles tailored to their environmental niches:\n- Oviparous animals lay eggs outside the body (birds, most reptiles, insects, amphibians). Eggs contain nourishing yolk.\n- Viviparous animals give live birth after nourishing the embryo internally through a placenta (placental mammals including humans, dogs, whales).\n- Ovoviviparous animals produce eggs that hatch inside the female's body before live birth (some sharks, sea snakes).\n\nPlants exhibit life cycles from seeds or spores: Ferns and mosses do not produce flowers; they reproduce using microscopic spores that grow into gametophytes in moist environments.",
        'slides' => [
            [
                'title' => 'Life Cycles of Living Things',
                'content' => "Grade 5 — Term 1 Life Science\n\nFrom oviparous egg-layers to viviparous mammals and ancient spore-bearing ferns.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Three Reproductive Strategies in Animals',
                'content' => "How animal embryos develop:\n• Oviparous: Eggs laid outside mother's body (Birds, turtles, crocodiles)\n  - Nourished by rich yolk inside egg\n• Viviparous: Live birth (Placental mammals, humans, carabao, dolphins)\n  - Placenta provides continuous oxygen and maternal nutrients\n• Ovoviviparous: Eggs hatch INSIDE mother before birth (Some sharks, vipers)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Amphibian Metamorphosis: Dual Life',
                'content' => "Frog transformation:\n1. Jelly-coated eggs in fresh pond water\n2. Aquatic tadpole with gills, lateral line, and swimming tail\n3. Tadpole grows hind legs and develops internal lungs\n4. Adult frog with powerful leaping legs and cutaneous skin respiration",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Aquatic: Egg laid in pond\nLarva: Gills & swimming tail\nTransition: Lungs & legs emerge\nTerrestrial: Adult carnivorous frog"
            ],
            [
                'title' => 'Spore Plants: Ancient Life Cycles',
                'content' => "Reproduction without seeds:\n• Ferns and mosses predate flowering plants by millions of years\n• Fronds produce sori clusters containing millions of microscopic spores\n• Spores germinate into heart-shaped prothallus gametophytes that fertilize in moisture",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Life Cycle Comparison Quiz',
                'content' => "What term describes animals like carabao, dogs, and whales that give birth to live offspring developed inside a womb?\nA) Oviparous\nB) Viviparous\nC) Metamorphic\nD) Spore-bearing\n\nCorrect Answer: B (Viviparous mammals nourish embryos internally!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Life Cycles of Living Things', 'difficulty' => 'Easy', 'text' => 'Which term describes animals like birds and sea turtles that lay eggs outside the body? | A: Oviparous B: Viviparous C: Asexual D: Herbivore', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Life Cycles of Living Things', 'difficulty' => 'Easy', 'text' => 'How do non-flowering plants like ferns reproduce without flowers or seeds? | A: Spores produced in sori clusters on fronds B: Bulbs C: Cones D: Fruit', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Life Cycles of Living Things', 'difficulty' => 'Medium', 'text' => 'Why can bird eggs develop on dry land while frog eggs must be laid in fresh water? | A: Bird eggs have hard waterproof shells and amniotic fluid; frog eggs lack shells and dry out B: Birds fly C: Frogs have fur D: Frog eggs have stones', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Life Cycles of Living Things', 'difficulty' => 'Medium', 'text' => 'What organ connects a mammalian mother to her developing fetus to supply nutrients and oxygen? | A: Placenta B: Gills C: Yolk sac D: Lung', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Life Cycles of Living Things', 'difficulty' => 'Hard', 'text' => 'Contrast the survival chances of a viviparous mammalian newborn with an oviparous sea turtle hatchling. | A: Mammal fetus is protected internally and nursed with milk (high survival rate); sea turtle hatchlings face heavy beach and ocean predation (low survival rate) B: Turtles have higher survival C: Mammals lay 1,000 eggs D: Turtles nurse milk', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '5',
        'quarter' => '2',
        'lesson_number' => '1',
        'topic' => 'Plant and Animal Adaptations',
        'objectives' => [
            'Differentiate structural (physical), behavioral, and physiological adaptations in organisms.',
            'Explain survival mechanisms: camouflage, mimicry, dormancy, and specialized appendages.',
            'Analyze adaptations of Philippine wildlife to tropical rainforest and monsoon conditions.'
        ],
        'content' => "An adaptation is any inherited characteristic that enhances an organism's ability to survive and reproduce in its specific habitat:\n- Structural Adaptations: Physical body features (e.g., thick blubber in whales, hollow bones in birds, webbed feet in ducks).\n- Behavioral Adaptations: Actions organisms take (e.g., bird migration, hibernation during winter, nocturnal hunting by bats).\n- Physiological Adaptations: Internal chemical or metabolic processes (e.g., snake venom production, desert cacti storing water in mucilage, human sweating).\n\nCamouflage allows organisms to blend into surroundings (stick insects, walking leaf insects), while mimicry fools predators by resembling toxic species.",
        'slides' => [
            [
                'title' => 'Plant & Animal Adaptations',
                'content' => "Grade 5 — Term 2 Life Science\n\nEvolutionary genius: Structural, behavioral, and physiological adaptations for survival.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Three Categories of Adaptations',
                'content' => "How organisms adapt to their environment:\n• Structural (Physical Form): Hollow bones in birds for flight; wide pads on camel feet\n• Behavioral (Actions): Bats hunting at night (nocturnal); monkeys grooming in social troops\n• Physiological (Internal Chemistry): Snake venom; desert cactus mucilage storing water",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Masters of Camouflage & Mimicry',
                'content' => "Deception in the Philippine Rainforest:\n• Camouflage (Cryptic coloration): Stick insects looking identical to brown twigs\n• Mimicry (Batesian): Harmless butterflies evolving the wing patterns of toxic poisonous species to scare off birds",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Stick insect freezes on branch -> Blends into twig\nPredator passes without noticing -> Prey survives\nSurviving insect reproduces -> Passes camouflage genes"
            ],
            [
                'title' => 'Extreme Flora: Mangroves & Pitcher Plants',
                'content' => "Thriving in harsh conditions:\n• Mangrove Trees: Excrete salt crystals through specialized leaf glands; breathe air using aerial pneumatophores\n• Pitcher Plants (Nepenthes): Evolved slippery fluid-filled pitfall traps to digest insects in nutrient-poor mountain soil",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Adaptation Mystery Quiz',
                'content' => "Which of the following is a behavioral adaptation rather than a structural adaptation?\nA) Hollow bones in eagles\nB) Bats hunting for insects exclusively at night to avoid daytime heat and hawks\nC) Sharp talons on owls\nD) Green scales on tree snakes\n\nCorrect Answer: B (Nocturnal hunting is an action/behavior!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Plant and Animal Adaptations', 'difficulty' => 'Easy', 'text' => 'What do we call an adaptation where an animal physically blends into its surroundings? | A: Camouflage B: Hibernation C: Migration D: Germination', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Plant and Animal Adaptations', 'difficulty' => 'Easy', 'text' => 'Is a migratory bird flying south to avoid cold winter temperatures a structural or behavioral adaptation? | A: Behavioral adaptation B: Structural adaptation C: Fossil adaptation D: Chemical change', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Plant and Animal Adaptations', 'difficulty' => 'Medium', 'text' => 'How do aerial breathing roots (pneumatophores) help mangrove trees survive in tidal swamp mud? | A: They grow upward above water to absorb oxygen gas from the air because waterlogged mud lacks oxygen B: They catch flying insects C: They store honey D: They make wood lighter', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Plant and Animal Adaptations', 'difficulty' => 'Medium', 'text' => 'What type of adaptation is snake venom production or cactus mucilage water retention? | A: Physiological adaptation B: Structural adaptation C: Mechanical adaptation D: Behavioral adaptation', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Plant and Animal Adaptations', 'difficulty' => 'Hard', 'text' => 'Evaluate why stick insects exhibit both structural camouflage (body shape of a twig) and behavioral camouflage (swaying in the wind). | A: Together they maximize concealment from sharp-eyed bird predators B: It helps them run faster C: It attracts pollinators D: It helps them lay eggs in soil', 'correct' => 'A']
        ]
    ],
    // ==========================================
    // GRADE 6 — LIFE SCIENCE (ENHANCED DEPED CURRICULUM)
    // Enhanced with Evaluator Guidance: Observation, Explanation, Application,
    // Asexual Propagation (Cutting, Budding, Layering, Grafting), Animal Classification Performance Tasks,
    // and Real-World Practical Ecosystem Scenarios.
    // ==========================================
    [
        'grade' => '6',
        'quarter' => '2',
        'lesson_number' => '1',
        'topic' => 'Human Body Systems (Circulatory and Nervous Systems)',
        'objectives' => [
            'Analyze the anatomy and physiology of the Circulatory System (four-chambered heart, vascular circuits, blood components) and Nervous System (CNS, PNS, reflex arc).',
            'Conduct a pulse rate observation investigation comparing resting heart rate with post-exercise cardiovascular response.',
            'Apply higher-order thinking to explain how the autonomic nervous system coordinates cardiovascular and respiratory adaptations during physical stress.'
        ],
        'content' => "GRADE 6 SCIENCE — LESSON 1: Human Body Systems (Circulatory and Nervous Systems)\n(Enhanced DepEd Curriculum — Revised Framework)\n\nLEARNING COMPETENCY:\nExplain how the organs of each system work together to coordinate bodily functions and respond to internal and external stimuli.\n\nI. THE CIRCULATORY SYSTEM: THE BODY'S TRANSPORT HIGHWAY\n• Four-Chambered Cardiovascular Pump:\n  - Right Atrium & Ventricle: Collects deoxygenated blood from body tissues and pumps it to the lungs via the pulmonary artery.\n  - Left Atrium & Ventricle: Muscular power chamber that receives freshly oxygenated blood from lungs and pumps it into the aorta to supply the entire body.\n• Specialized Blood Vessels:\n  - Arteries: Thick, elastic muscular walls carrying high-pressure blood away from the heart.\n  - Veins: Thinner walls equipped with one-way pocket valves that prevent backflow of blood returning to the heart.\n  - Capillaries: Microscopic single-cell-thick vessels where gas exchange (O2 / CO2) and nutrient delivery occur.\n• Components of Blood: Red Blood Cells (hemoglobin carries oxygen), White Blood Cells (immune defense), Platelets (fibrin clot formation), and Plasma (92% water solvent transporting nutrients and hormones).\n\nII. THE NERVOUS SYSTEM: MASTER COMMUNICATION GRID\n• Central Nervous System (CNS): Brain (Cerebrum: thought/memory, Cerebellum: posture/coordination, Brainstem: breathing/heartbeat) and the Spinal Cord.\n• Peripheral Nervous System (PNS): Sensory neurons (receptors to CNS) and Motor neurons (CNS to effector muscles).\n• The Spinal Reflex Arc: An involuntary, lightning-fast protective circuit (e.g., pulling your hand away from a boiling kettle). Stimulus → Sensory Receptor → Sensory Neuron → Spinal Interneuron → Motor Neuron → Muscle Contraction. This survival arc bypasses the brain, acting before pain is consciously registered!\n\nHANDS-ON OBSERVATION & INQUIRY TASK: The Cardiovascular Response to Exercise\n1. Observation: Locate your radial pulse on the wrist. Count your beats for 60 seconds at rest. (Normal resting: 70–85 bpm).\n2. Physical Stress Test: Perform 2 minutes of vigorous jumping jacks or running in place.\n3. Immediate Measurement: Count your pulse rate immediately. Notice: Increased pulse (130–160 bpm), deeper breathing rate, flushed skin.\n4. Explanation & HOTS Application:\n   - Why did your pulse surge? Working leg muscles burned glucose and required massive oxygen delivery!\n   - How did the body coordinate this? Sensory chemoreceptors detected rising CO2 in the blood; the brainstem activated the sympathetic autonomic nervous system, signaling the heart pacemaker (SA node) to pump faster and arteries to dilate!\n\nKEY TAKEAWAYS:\n• The Circulatory System pumps blood in a double loop to deliver oxygen and nutrients.\n• The Nervous System coordinates responses through voluntary brain pathways and rapid spinal reflex arcs.\n• Observation and scientific measurement prove that organ systems dynamically adapt to support bodily exertion.",
        'slides' => [
            [
                'title' => 'Circulatory & Nervous Systems',
                'content' => "Grade 6 — Term 2: Life Science\n(Enhanced DepEd Framework)\n\nInvestigate how your cardiovascular pump and neural command grid team up to power human life.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Double Cardiovascular Circulation Loop',
                'content' => "The Four-Chambered Marvel:\n• Pulmonary Circuit: Right heart sends deoxygenated blood to lung alveoli for fresh O2\n• Systemic Circuit: Left heart pumps high-pressure oxygen-rich blood through the aorta to all organs\n• Vessels: High-pressure Arteries, Valved Veins, and Microscopic Capillaries",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Neural Architecture: CNS & Reflex Arc',
                'content' => "Central Control and Emergency Reactions:\n• Brain: Cerebrum (cognition), Cerebellum (balance/muscle coordination), Brainstem (involuntary survival)\n• Spinal Reflex Arc: Sensory receptor -> Spinal cord interneuron -> Motor neuron -> Immediate muscle jerk!\n• Emergency arcs protect your body before your brain registers pain!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Finger touches hot surface\nStep 2: Sensory nerve fires action potential\nStep 3: Spinal cord interneuron activates motor nerve\nStep 4: Biceps contract; hand snaps back in 0.05 seconds!"
            ],
            [
                'title' => 'Hands-on Investigation: Pulse & Exercise',
                'content' => "Observing Body System Coordination:\n• Measure resting radial pulse for 60 seconds (70-85 bpm)\n• Exercise vigorously for 2 minutes (jumping jacks)\n• Measure post-exercise pulse (130-160 bpm)\n• Explanation: Brainstem detects elevated CO2 and commands heart and lungs to accelerate nutrient and gas delivery!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Body Systems & HOTS',
                'content' => "Check Your Understanding (Answer Key: 1-A, 2-A, 3-A, 4-A, 5-A):\n1. Arteries carry oxygenated blood away from the heart (A)\n2. Cerebellum coordinates balance, posture, and motor precision (A)\n3. Spinal reflex arc bypasses conscious cerebral processing for speed (A)\n4. Pulse rate increases during exercise to supply oxygen to contracting muscles (A)\n5. Double circulation route: Right heart -> Lungs -> Left heart -> Body (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Human Body Systems (Circulatory and Nervous Systems)', 'difficulty' => 'Easy', 'text' => 'Which blood vessels carry oxygenated blood away from the heart to body organs? | A: Arteries B: Veins C: Lymph vessels D: Trachea', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Human Body Systems (Circulatory and Nervous Systems)', 'difficulty' => 'Easy', 'text' => 'What part of the brain is responsible for balance, posture, and smooth muscle coordination? | A: Cerebellum B: Cerebrum C: Medulla D: Spinal cord', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Human Body Systems (Circulatory and Nervous Systems)', 'difficulty' => 'Medium', 'text' => 'Why does touching a hot surface trigger an involuntary hand withdrawal before you consciously feel pain? | A: An emergency reflex arc completes a circuit through the spinal cord without waiting for brain processing B: The heart pumps faster C: The skin is detached D: Nerves freeze', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Human Body Systems (Circulatory and Nervous Systems)', 'difficulty' => 'Medium', 'text' => 'Why does an athlete\'s pulse rate accelerate from 72 bpm to 150 bpm during a 100-meter sprint? | A: Leg muscles consume oxygen rapidly, prompting the brainstem to increase cardiac output and blood delivery B: Blood turns to water C: The heart stops beating D: Lungs shrink', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Human Body Systems (Circulatory and Nervous Systems)', 'difficulty' => 'Hard', 'text' => 'Trace the complete pathway of a red blood cell from the right atrium to the lungs and out to the left leg. | A: Right atrium -> Right ventricle -> Pulmonary artery -> Lung capillaries (oxygenated) -> Pulmonary veins -> Left atrium -> Left ventricle -> Aorta -> Femoral artery to leg B: Directly from right atrium to aorta C: Through stomach D: Through kidneys first', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '6',
        'quarter' => '2',
        'lesson_number' => '2',
        'topic' => 'Reproduction in Plants',
        'objectives' => [
            'Differentiate sexual reproduction (pollination, fertilization, seed formation) from asexual reproduction in plants.',
            'Explain and sequence artificial asexual propagation methods: cutting, budding, layering (marcotting), and grafting.',
            'Analyze natural vegetative structures (runners, rhizomes, tubers, leaf plantlets) and evaluate agricultural advantages of clonal propagation.'
        ],
        'content' => "GRADE 6 SCIENCE — LESSON 2: Reproduction in Plants\n(Enhanced DepEd Curriculum — Sexual and Asexual Plant Propagation Suite)\n\nLEARNING COMPETENCY:\nCompare different modes of reproduction in plants (sexual vs. asexual) and explain how vegetative propagation techniques work.\n\nI. SEXUAL REPRODUCTION IN FLOWERING PLANTS\n• Floral Organs: Stamen (male anther produces pollen grains; filament) and Pistil/Carpel (female sticky stigma, style, ovary with ovules).\n• Pollination & Fertilization: Pollen transfers to stigma via wind or pollinators (bees, butterflies). A pollen tube grows down the style; male gametes fuse with the egg cell inside the ovule. The ovule develops into a SEED containing an embryo, and the ovary wall expands into a fleshy FRUIT.\n• Benefit: Generates genetic variation, enabling seedlings to adapt to changing environmental conditions.\n\nII. ASEXUAL REPRODUCTION: EXACT GENETIC CLONES\nIn asexual reproduction, a single parent produces offspring that are genetically identical to itself without seeds or fertilization.\n\nFOUR MAJOR ARTIFICIAL PROPAGATION METHODS:\n1. Stem Cutting:\n   - Method: A healthy branch or shoot with vegetative nodes is cut and planted in moist soil or water.\n   - Actual Plants: Sanseviera (Snake plant), Rose, Malunggay, Hibiscus (Gumamela), Sugarcane.\n   - Mechanism: Undifferentiated meristematic cells at the stem nodes develop into adventitious roots.\n2. Budding (T-Budding):\n   - Method: A single vegetative bud (scion) is sliced from a desirable plant and inserted into a T-shaped slit in the bark of a hardy rootstock, then bound with grafting tape.\n   - Actual Plants: Citrus (Calamansi, Orange), Roses, Rubber trees.\n3. Layering / Marcotting (Air Layering):\n   - Method: A 1-inch ring of bark is stripped from a mature branch (removing the phloem but leaving the xylem intact so water still reaches the tip). Moist sphagnum moss or coconut coir is wrapped around the wound and sealed in plastic wrap.\n   - Actual Plants: Mango, Guava, Lanzones, Chico, Lemon.\n   - Mechanism: Sugars traveling down from leaves accumulate at the cut bark, triggering rapid root sprouting. Once roots fill the wrap, the branch is sawed off and potted!\n4. Grafting (Cleft/Whip Grafting):\n   - Method: A scion twig with dormant buds from a high-quality mother tree is joined into a notched rootstock seedling. Crucial requirement: The green vascular cambium layers of both scion and rootstock must touch!\n   - Actual Plants: Carabao Mango, Apple, Avocado, Coffee.\n\nNATURAL VEGETATIVE PROPAGATION:\n• Katakataka (Bryophyllum): Tiny complete plantlets sprout along the leaf margins and drop into soil.\n• Tubers: Potato \"eyes\" sprout into new potato vines.\n• Rhizomes: Horizontal underground stems in Ginger and Luya.\n• Runners/Stolons: Creeping above-ground stems in Strawberry and Grass.\n\nHOTS INQUIRY CHALLENGE: Why do Filipino Fruit Farmers Prefer Marcotting and Grafting over Planting Seeds?\n1. Speed to Fruit: A grafted Carabao mango tree produces sweet fruit in 2 to 3 years, whereas a seed-grown tree takes 8 to 12 years!\n2. True-to-Type Clones: Seeds result from cross-pollination and may produce sour, fibrous fruit. Grafting guarantees 100% exact genetic clones of sweet, prized fruit varieties!",
        'slides' => [
            [
                'title' => 'Reproduction in Plants',
                'content' => "Grade 6 — Term 2: Life Science\n(Enhanced DepEd Curriculum)\n\nExplore plant reproduction: From floral pollination to cutting, budding, marcotting, and grafting.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Sexual Reproduction: Flower to Fruit',
                'content' => "The Pollination & Fertilization Journey:\n• Male Stamen: Anther releases microscopic golden pollen grains\n• Female Pistil: Stigma catches pollen -> Pollen tube grows down style\n• Double Fertilization: Ovule becomes a fertile SEED; ovary ripens into a fleshy FRUIT!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Asexual Methods: Cutting & Budding',
                'content' => "Cloning Without Seeds:\n• Stem Cutting: Nodal stem cuttings placed in moist soil generate adventitious roots (Sanseviera, Malunggay, Gumamela)\n• Budding (T-Budding): Inserting a single bud from a superior scion into rootstock bark (Calamansi, Citrus, Roses)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Sequencing: Marcotting & Grafting',
                'content' => "Mastering Philippine Agricultural Techniques:\n1. Marcotting (Air Layering): Strip 1-inch bark ring -> Wrap in moist coconut coir -> Wait for roots -> Cut and pot!\n2. Grafting: Join scion twig into rootstock notch -> Align vascular cambium layers -> Bind tightly with tape -> Cells fuse into one tree!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Select healthy mother tree branch\nStep 2: Strip bark ring (expose cambium)\nStep 3: Pack with moist sphagnum moss\nStep 4: Seal with plastic until roots sprout (Marcotting!)"
            ],
            [
                'title' => 'Assessment: Plant Reproduction',
                'content' => "Check Your Understanding (Answer Key: 1-A, 2-A, 3-A, 4-A, 5-A):\n1. Grafting joins a scion branch to a rootstock aligning cambium layers (A)\n2. Marcotting (air layering) strips a ring of bark to sprout roots on an attached branch (A)\n3. Katakataka produces plantlets along its leaf margins (A)\n4. Farmers prefer grafting because it guarantees exact fruit clones in 2-3 years (A)\n5. The flower's ovary ripens into the fruit after fertilization (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Reproduction in Plants', 'difficulty' => 'Easy', 'text' => 'Which asexual plant propagation method involves joining a scion branch to a rooted rootstock? | A: Grafting B: Pollination C: Seed germination D: Double fertilization', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Reproduction in Plants', 'difficulty' => 'Easy', 'text' => 'In marcotting (air layering), what is wrapped around the debarked tree branch to stimulate root growth? | A: Moist coconut coir or sphagnum moss enclosed in plastic B: Dry sand C: Hot candle wax D: Metal foil only', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Reproduction in Plants', 'difficulty' => 'Medium', 'text' => 'Which Philippine plant naturally reproduces asexually through tiny plantlets growing along its leaf margins? | A: Katakataka (Bryophyllum) B: Coconut tree C: Corn plant D: Narra tree', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Reproduction in Plants', 'difficulty' => 'Medium', 'text' => 'Why must a farmer carefully align the vascular cambium layers when grafting a sweet mango scion onto a rootstock? | A: The cambium contains dividing cells that fuse xylem and phloem vessels together for sap transport B: To make the stem straight C: To change flower color D: To prevent sunlight damage', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Reproduction in Plants', 'difficulty' => 'Hard', 'text' => 'Why do commercial fruit orchard growers prefer asexual grafting over planting seeds from delicious fruits? | A: Grafted trees produce identical true-to-type fruit and bear harvest in 2-3 years, while seed-grown trees take 7-10 years with unpredictable quality B: Seeds never grow in soil C: Grafting creates new seeds D: Seed trees are always poisonous', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '6',
        'quarter' => '2',
        'lesson_number' => '3',
        'topic' => 'Vertebrates and Invertebrates',
        'objectives' => [
            'Classify animals into Vertebrates (with backbones) and Invertebrates (without backbones) and identify their distinguishing anatomical features.',
            'Differentiate the 5 vertebrate classes (Fish, Amphibians, Reptiles, Birds, Mammals) and 5 major invertebrate phyla (Arthropods, Mollusks, Annelids, Cnidarians, Echinoderms).',
            'Perform a hands-on classification activity observing Philippine animal specimen cards and justifying classification with anatomical evidence.'
        ],
        'content' => "GRADE 6 SCIENCE — LESSON 3: Vertebrates and Invertebrates\n(Enhanced DepEd Curriculum — Hands-on Classification & Performance Suite)\n\nLEARNING COMPETENCY:\nClassify animals into vertebrates and invertebrates based on anatomical characteristics, and explain the basis for their classification.\n\nI. VERTEBRATES (CHORDATES): POSSESS AN INTERNAL BACKBONE\nAll vertebrates possess an internal endoskeleton with a spinal column supporting the body:\n1. Fish: Aquatic, ectothermic, breathe with gills, covered in slimy scales, swim with fins (e.g., Bangus / Milkfish, Tilapia).\n2. Amphibians: Ectothermic, dual life cycle (aquatic larvae breathe with gills; adults develop lungs and moist skin for gas exchange), lay jelly-like eggs in water (e.g., Philippine Bullfrog, Cane Toad).\n3. Reptiles: Ectothermic, breathe exclusively with lungs, covered in dry keratinized scales, lay leathery-shelled eggs on land (e.g., Pawikan / Sea Turtle, Monitor Lizard / Bayawak, Cobra).\n4. Birds (Aves): Endothermic (warm-blooded), feathers, lightweight hollow bones, toothless beaks, lay hard-shelled calcium eggs (e.g., Philippine Eagle, Maya Bird).\n5. Mammals: Endothermic, have hair or fur, breathe with lungs, have mammary glands that produce milk to nurse young, give live birth (e.g., Philippine Tarsier, Carabao, Whale, Bat, Humans).\n\nII. INVERTEBRATES: ANIMALS WITHOUT A BACKBONE (>95% OF ALL SPECIES)\n• Arthropods: Largest phylum! Segmented bodies, jointed appendages, tough chitinous exoskeleton (e.g., Rhinoceros Beetle / Uwáng, Crabs, Spiders, Mosquitoes).\n• Mollusks: Soft unsegmented bodies, muscular foot, visceral mass, often enclosed in hard calcium carbonate shells (e.g., Giant Clam / Taklobo, Snails, Squids).\n• Annelids: Segmented cylindrical worm bodies with hydrostatic skeletons (e.g., Earthworms / Bulate, Leeches).\n• Cnidarians: Radial symmetry, gelatinous body, tentacles armed with stinging cnidocyte capsules (e.g., Box Jellyfish, Sea Anemones, Coral polyps).\n• Echinoderms: Spiny skin, five-part radial symmetry, marine water vascular system with tube feet (e.g., Starfish, Sea Urchins, Sea Cucumbers / Balatan).\n\nHANDS-ON PERFORMANCE TASK: Philippine Specimen Classification & Justification\nLearners inspect 8 Philippine animal cards and complete the scientific classification log:\n1. Philippine Tarsier → Vertebrate (Mammal): Has internal spinal column, fur, and nurses young with milk.\n2. Milkfish (Bangus) → Vertebrate (Fish): Has internal spine, gills, and paired swimming fins.\n3. Rhinoceros Beetle (Uwáng) → Invertebrate (Arthropod): Lacks internal backbone; protected by jointed chitinous exoskeleton.\n4. Giant Clam (Taklobo) → Invertebrate (Mollusk): Soft body protected by a two-valved calcium carbonate shell, no backbone.\n5. Sea Turtle (Pawikan) → Vertebrate (Reptile): Internal bony skeleton, lungs, dry scaly skin, leathery eggs.\n6. Box Jellyfish → Invertebrate (Cnidarian): Soft gelatinous body with stinging tentacles and no spine.\n7. Maya Bird → Vertebrate (Bird): Internal vertebral skeleton, hollow bones, and feather covering.\n8. Earthworm (Bulate) → Invertebrate (Annelid): Segmented cylindrical ringed body with no vertebrae.\n\nKEY TAKEAWAYS:\n• Vertebrates have backbones (fish, amphibians, reptiles, birds, mammals).\n• Invertebrates have no backbones (arthropods, mollusks, annelids, cnidarians, echinoderms).\n• Classification must be justified using observable anatomical evidence.",
        'slides' => [
            [
                'title' => 'Vertebrates & Invertebrates',
                'content' => "Grade 6 — Term 2: Life Science\n(Enhanced DepEd Curriculum)\n\nClassify the Animal Kingdom: Backbone possessors versus the vast invertebrate majority.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Five Vertebrate Classes',
                'content' => "Internal Bony Skeletons & Spinal Columns:\n• Fish: Gills, scales, fins, cold-blooded (Bangus)\n• Amphibians: Moist skin, water-land metamorphosis (Frogs)\n• Reptiles: Dry scales, lungs, leathery eggs (Pawikan, Monitor lizard)\n• Birds: Feathers, hollow flight bones, warm-blooded (Philippine Eagle)\n• Mammals: Hair/fur, mammary glands nursing young (Tarsier, Carabao)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Major Invertebrate Phyla (>95% of Species)',
                'content' => "Creatures Without Backbones:\n• Arthropods: Jointed legs, chitinous exoskeleton (Uwáng beetle, crabs)\n• Mollusks: Soft unsegmented bodies, calcium shells (Taklobo giant clam)\n• Annelids: Segmented rings, hydrostatic skeleton (Earthworms)\n• Cnidarians: Stinging tentacles, radial symmetry (Jellyfish, corals)\n• Echinoderms: Spiny skin, tube feet (Starfish, sea urchins)",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Check for internal backbone (Vertebral column)\nStep 2: Backbone present? -> Classify into 5 Vertebrate classes\nStep 3: No backbone? -> Check exoskeleton, shell, or soft rings\nStep 4: Write anatomical evidence to justify classification!"
            ],
            [
                'title' => 'Hands-on Activity: Specimen Log',
                'content' => "Classifying Philippine Wildlife:\n• Tarsier -> Vertebrate (Mammal): Fur, nurses young\n• Uwáng Beetle -> Invertebrate (Arthropod): Jointed exoskeleton\n• Bangus -> Vertebrate (Fish): Gills, fins, spine\n• Taklobo -> Invertebrate (Mollusk): Soft body, hinged shell",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Animal Classification',
                'content' => "Check Your Understanding (Answer Key: 1-A, 2-A, 3-A, 4-A, 5-A):\n1. Internal vertebral column distinguishes vertebrates from invertebrates (A)\n2. Birds are the only class with feathers and hollow flight bones (A)\n3. Tarsier is a mammal because it nurses young and has fur (A)\n4. Beetles possess a chitinous exoskeleton belonging to Arthropoda (A)\n5. Dichotomous classification separates organisms using paired physical traits (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Vertebrates and Invertebrates', 'difficulty' => 'Easy', 'text' => 'What major anatomical structure distinguishes vertebrates from invertebrates? | A: An internal backbone (vertebral column) B: Large eyes C: Number of legs D: Body color', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Vertebrates and Invertebrates', 'difficulty' => 'Easy', 'text' => 'Which is the only vertebrate class characterized by feathers and hollow flight bones? | A: Birds (Aves) B: Mammals C: Reptiles D: Fish', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Vertebrates and Invertebrates', 'difficulty' => 'Medium', 'text' => 'Why is the Philippine Tarsier classified as a mammal rather than an amphibian or reptile? | A: It has fur, breathes with lungs, is warm-blooded, and nurses its young with milk B: It has scales C: It lays eggs in water D: It has feathers', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Vertebrates and Invertebrates', 'difficulty' => 'Medium', 'text' => 'What type of skeleton protects beetles and crabs, and what phylum do they belong to? | A: Chitinous exoskeleton; Phylum Arthropoda B: Endoskeleton; Chordata C: Shell; Mollusca D: Spines; Echinodermata', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Vertebrates and Invertebrates', 'difficulty' => 'Hard', 'text' => 'Construct a dichotomous key to distinguish a milkfish, cane toad, monitor lizard, owl, and fruit bat. | A: 1. Has feathers -> Owl; 2. Has fur & nurses young -> Fruit bat; 3. Has gills and fins -> Milkfish; 4. Has dry scales and claws -> Monitor lizard; 5. Has moist scaleless skin -> Cane toad B: All are mammals C: All are birds D: All are fish', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '6',
        'quarter' => '2',
        'lesson_number' => '4',
        'topic' => 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)',
        'objectives' => [
            'Analyze interconnected food webs and evaluate trophic cascades triggered by human disruptions.',
            'Differentiate symbiotic relationships (Mutualism, Commensalism, Parasitism, Predation, Competition) in Philippine ecosystems.',
            'Apply ecological concepts to practical real-world situations: mangrove conservation, reef restoration, and community biodiversity projects.'
        ],
        'content' => "GRADE 6 SCIENCE — LESSON 4: Ecosystem Relationships\n(Enhanced DepEd Curriculum — Real-World Practical Situations Suite)\n\nLEARNING COMPETENCY:\nDescribe the interactions among living things and their physical environment in diverse Philippine ecosystems and propose responsible actions to conserve biodiversity.\n\nI. BIOTIC AND ABIOTIC DYNAMICS\n• Biotic Factors (Living Components): Plants, animals, fungi, and decomposing bacteria.\n• Abiotic Factors (Physical Environment): Sunlight intensity, ambient temperature, water salinity, soil pH, dissolved oxygen, and mineral nutrients.\n• Constant Balance: Changing an abiotic factor (e.g. warming ocean water) directly affects biotic life (e.g. coral bleaching).\n\nII. FOOD WEBS & TROPHIC STABILITY\n• Natural ecosystems are not isolated food chains, but interwoven FOOD WEBS.\n• Animals have diverse food sources: If one prey species declines, predators consume alternatives.\n• Keystone Species: Predators (e.g., Reef Sharks, Philippine Eagles) keep herbivore populations balanced, preventing them from overconsuming the plant base.\n\nIII. FIVE SYMBIOTIC RELATIONSHIPS IN PHILIPPINE NATURE:\n1. Mutualism (+ / +): Both species benefit.\n   - Example: Coral polyps and photosynthetic zooxanthellae algae; Bees pollinating coffee blossoms while harvesting nectar.\n2. Commensalism (+ / 0): One benefits, other is unaffected.\n   - Example: Aerial orchids rooted on high bark of Narra trees to reach sunlight without drawing tree sap.\n3. Parasitism (+ / -): Parasite extracts nutrients at host's expense.\n   - Example: Mosquitoes transmitting dengue virus, intestinal roundworms in livestock.\n4. Predation (+ / -): Predator captures and consumes prey.\n   - Example: Philippine Eagle preying on monkeys and monitor lizards.\n5. Competition (- / -): Organisms compete for limited resources (light, water, nesting territory).\n\nIV. THREE PRACTICAL REAL-WORLD SITUATIONS FOR GRADE 6:\n• Situation 1: Mangrove Ecosystem Protection in Laguna/Batangas Coastlines\n  - Problem: Cutting down bakawan (mangroves) for coastal developments destroys juvenile fish nurseries and leaves coastal barangays defenseless against typhoon storm surges.\n  - Practical Action: Community reforestation of Rhizophora mangrove propagules along tidal mudflats restores fish breeding grounds and stabilizes coastlines.\n• Situation 2: The Coral Reef Trophic Cascade\n  - Problem: Illegal blast fishing and cyanide harvesting decimate predatory grouper and snapper fish.\n  - Trophic Consequence: Crown-of-thorns starfish and sea urchins multiply unchecked, devouring living coral heads and turning vibrant reefs into dead rubble.\n  - Practical Action: Establishing Marine Protected Areas (MPAs) where fishing is strictly prohibited lets reef fish populations recover and restore ecological equilibrium.\n• Situation 3: Designing a School Native Pollinator Garden\n  - Practical Action: Grade 6 pupils establish a dedicated school biodiversity sanctuary planting endemic flowering shrubs to support native honeybees and butterfly pollinators.\n\nKEY TAKEAWAYS:\n• Food webs provide ecological stability through multi-pathway energy networks.\n• Symbiotic relationships govern species interactions (mutualism, commensalism, parasitism, predation, competition).\n• Practical human interventions (mangrove planting, marine reserves, pollinator gardens) restore ecosystem health.",
        'slides' => [
            [
                'title' => 'Ecosystem Relationships & Practical Solutions',
                'content' => "Grade 6 — Term 2: Life Science\n(Enhanced DepEd Curriculum)\n\nExplore food webs, symbiotic partnerships, and practical actions to protect Philippine ecosystems.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Biotic & Abiotic Interactions',
                'content' => "The Living and Physical Worlds Intertwined:\n• Biotic Factors: Producers, herbivores, predators, fungi, soil microbes\n• Abiotic Factors: Sunlight, temperature, rainfall, dissolved oxygen, salinity\n• Healthy ecosystems require balance between physical conditions and living communities!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Five Symbiotic Relationships',
                'content' => "How Organisms Live Together:\n• Mutualism (+ / +): Both benefit (Corals & algae; Bees & flowers)\n• Commensalism (+ / 0): One benefits, other unharmed (Orchids on Narra bark)\n• Parasitism (+ / -): Parasite harms host (Ticks, intestinal worms)\n• Predation (+ / -): Hunter catches prey\n• Competition (- / -): Fighting for limited food or territory",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Identify participating species A and B\nStep 2: Does Species A benefit (+)?\nStep 3: Does Species B benefit (+), suffer (-), or stay neutral (0)?\nStep 4: Classify: Mutualism, Commensalism, or Parasitism!"
            ],
            [
                'title' => 'Practical Situation: Mangrove Restoration',
                'content' => "Real-World Philippine Application:\n• Bakawan mangroves serve as natural storm surge wave barriers and fish nurseries\n• Planting native mangrove propagules along muddy coastlines protects barangays from typhoons and restores fisheries\n• Practical science drives community resilience!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Ecosystems & Practical Action',
                'content' => "Check Your Understanding (Answer Key: 1-A, 2-A, 3-A, 4-A, 5-A):\n1. A food web represents an interconnected network of food chains (A)\n2. Mutualism is a symbiotic relationship where both organisms benefit (A)\n3. Algae provide food via photosynthesis while coral polyps provide shelter (A)\n4. Orchids attached to trees represent commensalism because the tree is unharmed (A)\n5. Reforesting mangroves restores juvenile fish nurseries and stops storm surges (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)', 'difficulty' => 'Easy', 'text' => 'What do we call a complex network of interconnected food chains in an ecosystem? | A: Food web B: Food pyramid C: Monoculture D: Energy ring', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)', 'difficulty' => 'Easy', 'text' => 'Which symbiotic relationship benefits both participating organisms (+ / +)? | A: Mutualism B: Parasitism C: Commensalism D: Predation', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)', 'difficulty' => 'Medium', 'text' => 'Describe the mutualistic relationship between coral polyps and microscopic zooxanthellae algae in a coral reef. | A: Algae perform photosynthesis inside coral cells to provide food; coral provides shelter and carbon dioxide B: Coral eats algae C: Algae poisons coral D: Coral destroys algae', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)', 'difficulty' => 'Medium', 'text' => 'Why is an orchid attached to the high bark of a forest tree classified as commensalism? | A: The orchid gains access to sunlight without harming or stealing nutrients from the host tree B: The orchid kills the tree C: The tree eats the orchid D: Both organisms die', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)', 'difficulty' => 'Hard', 'text' => 'In a practical Philippine coastal conservation project, how does reforesting mangrove (bakawan) trees protect both marine life and human communities? | A: Mangrove roots provide calm nursery grounds for juvenile fish and break destructive typhoon storm surge waves B: Mangroves make ocean water fresh C: Mangroves stop all rain D: Mangroves replace coral reefs entirely', 'correct' => 'A']
        ]
    ]
];

echo "<h2>🌿 Seeding Life Science Curriculum across Grades 3 to 6...</h2>\n";

try {
    // 1. Ensure topics table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade VARCHAR(10) NOT NULL,
        topic_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    // SQLite syntax fallback
    $pdo->exec("CREATE TABLE IF NOT EXISTS topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        grade TEXT NOT NULL,
        topic_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

$lessonsInserted = 0;
$slidesInserted = 0;
$questionsInserted = 0;
$topicsRegistered = 0;

foreach ($lifeScienceUnits as $unit) {
    $grade = $unit['grade'];
    $quarter = $unit['quarter'];
    $lessonNum = $unit['lesson_number'];
    $topic = $unit['topic'];
    $content = $unit['content'];
    $objectives = json_encode($unit['objectives'], JSON_UNESCAPED_UNICODE);
    $questionsJson = json_encode($unit['questions'], JSON_UNESCAPED_UNICODE);

    // 1. Check if curriculum lesson exists, update or insert
    $stmtCheck = $pdo->prepare("SELECT id FROM curriculum_lessons WHERE grade = ? AND quarter = ? AND topic = ?");
    $stmtCheck->execute([$grade, $quarter, $topic]);
    $existingId = $stmtCheck->fetchColumn();

    if ($existingId) {
        $lessonId = $existingId;
        $stmtUpd = $pdo->prepare("UPDATE curriculum_lessons SET content = ?, objectives = ?, questions = ?, lesson_number = ? WHERE id = ?");
        $stmtUpd->execute([$content, $objectives, $questionsJson, $lessonNum, $lessonId]);
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->execute([$grade, $quarter, $lessonNum, $topic, $content, $objectives, $questionsJson]);
        $lessonId = $pdo->lastInsertId();
        $lessonsInserted++;
    }

    // 2. Register Topic in topics table if not exists
    $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
    $stmtTCheck->execute([$grade, $topic]);
    if ($stmtTCheck->fetchColumn() == 0) {
        $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
        $stmtTIns->execute([$grade, $topic]);
        $topicsRegistered++;
    }

    // 3. Clear and Insert lesson slides
    $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$lessonId]);
    $stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($unit['slides'] as $sIdx => $s) {
        $stmtSlide->execute([
            $lessonId,
            $sIdx + 1,
            $s['title'],
            $s['content'],
            $s['slide_type'],
            $s['media_type'] ?? null,
            $s['media_url'] ?? null
        ]);
        $slidesInserted++;
    }

    // 4. Seed Questions into questions table (without duplicating)
    $stmtQCheck = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE grade = ? AND topic = ? AND question_text = ?");
    $stmtQIns = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text) VALUES (?, ?, ?, ?)");
    foreach ($unit['questions'] as $q) {
        $stmtQCheck->execute([$q['grade'], $q['topic'], $q['text']]);
        if ($stmtQCheck->fetchColumn() == 0) {
            $stmtQIns->execute([$q['grade'], $q['topic'], $q['difficulty'], $q['text']]);
            $questionsInserted++;
        }
    }
}

echo "<p>✅ <strong>Life Science Curriculum Seeding Complete!</strong></p>\n";
echo "<ul>\n";
echo "<li>Curriculum Lessons Processed: " . count($lifeScienceUnits) . " (21 units across Grades 3 to 6)</li>\n";
echo "<li>Interactive Lesson Slides Populated: $slidesInserted (5 slides per unit)</li>\n";
echo "<li>Question Bank Recitations Seeded: $questionsInserted</li>\n";
echo "<li>Topic Registry Entries Added: $topicsRegistered</li>\n";
echo "</ul>\n";

if (php_sapi_name() === 'cli') {
    echo "Summary:\n";
    echo "- 21 Life Science Lessons active across Grades 3-6\n";
    echo "- $slidesInserted Lesson Slides seeded\n";
    echo "- $questionsInserted Questions added to bank\n";
    echo "- Database: " . (defined('DB_ENGINE') ? DB_ENGINE : 'Connected') . "\n";
}
?>
