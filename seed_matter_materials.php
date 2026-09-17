<?php
/**
 * ILikeSci — Official MATATAG / DepEd "Matter and Materials" Curriculum Seeder
 * Populates all 16 curriculum units across Grades 3 to 6:
 * - Grade 3 (Term 1 & Term 3): 3 Units
 * - Grade 4 (Term 1): 5 Units
 * - Grade 5 (Term 1): 4 Units
 * - Grade 6 (Term 1): 4 Units
 * Total: 16 Curriculum Lessons, 80 Detailed Presentation Slides, 80+ Tiered Questions.
 * Compatible with both XAMPP MySQL and SQLite fallback.
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
}

require_once __DIR__ . '/db.php';

// Complete 16 Matter and Materials Curriculum Units
$matterUnits = [
    // === GRADE 3 ===
    [
        'grade' => '3',
        'quarter' => '1',
        'lesson_number' => '1',
        'topic' => 'Properties and Uses of Materials',
        'objectives' => [
            'Describe and compare the observable physical properties of solid materials, such as hardness, shininess, roughness, smoothness, flexibility, and stretchability.',
            'Explain how changing the shape or form of solid materials, such as shaping, pressing, hammering, joining, or cutting, can make them useful for different purposes.',
            'Describe the observable properties and everyday uses of common metal materials in Philippine homes, schools, and communities, such as iron or steel, galvanized iron sheets or yero, aluminum, copper, and stainless steel.'
        ],
        'content' => "TERM 1 LESSON 1: Properties and Uses of Materials\n\nLearning Competencies:\n1. Describe and compare observable physical properties of solid materials: hardness, shininess, roughness, smoothness, flexibility, and stretchability.\n2. Explain how changing the shape or form of solid materials (shaping, pressing, hammering, joining, cutting) can make them useful for different purposes.\n3. Describe the observable properties and everyday uses of common metal materials in Philippine homes, schools, and communities (iron or steel, galvanized iron sheets or yero, aluminum, copper, and stainless steel).\n\nLET'S EXPLORE\nLook around the classroom. What objects can you see? What are they made of?\nMaterials have different properties that we can observe and describe.\n\nDISCUSSION: Observable Properties of Materials\n• Hardness — how hard a material is (e.g., Rock -> hard)\n• Shininess — how much light a material reflects (e.g., Metal spoon -> shiny)\n• Roughness — having an uneven surface (e.g., Sandpaper -> rough)\n• Smoothness — having an even surface (e.g., Glass -> smooth)\n• Flexibility — ability to bend (e.g., Rubber -> flexible)\n• Stretchability — ability to stretch when pulled (e.g., Rubber band -> stretchable)\n\nCHANGING MATERIALS\nThe shape or form of a solid material can be changed by:\nshaping • pressing • hammering • joining • cutting\nThese changes can make materials useful for different purposes.\n\nMETALS AROUND US (Philippine Context)\nCommon metals found in homes, schools, and communities include:\n• Iron or steel — chairs, gates, tools\n• Galvanized iron sheets or yero — roofing\n• Aluminum — containers and household objects\n• Copper — electrical wires\n• Stainless steel — spoons, forks, and other utensils\n\nINTERACTIVE ACTIVITY: What Property Is It?\nA rubber band becomes longer when pulled. What property does it show?\nAnswer: C — Stretchability\n\nKEY TAKEAWAYS:\n• Materials have different observable properties.\n• Materials can be changed in shape or form to make them useful.\n• Metals have different properties and everyday uses.",
        'slides' => [
            [
                'title' => 'Properties and Uses of Materials',
                'content' => "Term 1 — Lesson 1: Properties and Uses of Materials\n\nLook around your classroom:\n• Desks, windows, armchairs, utensils...\nEvery object is made of specific materials chosen for their unique observable properties!",
                'slide_type' => 'title',
                'media_type' => 'image',
                'media_url' => 'https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?w=800'
            ],
            [
                'title' => '6 Observable Properties of Materials',
                'content' => "How can we describe and compare materials?\n\n1. Hardness: How hard a material is (Rock -> hard)\n2. Shininess: How much light a material reflects (Metal spoon -> shiny)\n3. Roughness: Having an uneven surface (Sandpaper -> rough)\n4. Smoothness: Having an even surface (Glass -> smooth)\n5. Flexibility: Ability to bend without breaking (Rubber -> flexible)\n6. Stretchability: Ability to stretch when pulled (Rubber band -> stretchable)",
                'slide_type' => 'content',
                'media_type' => 'step',
                'media_url' => "Property 1: Hardness (Rock)\nProperty 2: Shininess (Metal spoon)\nProperty 3: Roughness (Sandpaper)\nProperty 4: Smoothness (Glass)\nProperty 5: Flexibility (Rubber)\nProperty 6: Stretchability (Rubber band)"
            ],
            [
                'title' => 'Changing Solid Materials for Use',
                'content' => "The shape or form of a solid material can be changed by:\n• Shaping\n• Pressing\n• Hammering\n• Joining\n• Cutting\n\nThese changes make materials useful for different purposes (e.g. cutting cloth for uniforms, hammering steel for tools)!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Action 1: Shaping\nAction 2: Pressing\nAction 3: Hammering\nAction 4: Joining\nAction 5: Cutting"
            ],
            [
                'title' => 'Metals Around Us in the Philippines',
                'content' => "Common metals found in homes, schools, and communities:\n\n• Iron or Steel — school chairs, security gates, construction tools\n• Galvanized Iron Sheets (Yero) — sturdy typhoon roofing\n• Aluminum — food containers, pots, window frames\n• Copper — electrical wires powering classroom lights\n• Stainless Steel — spoons, forks, and kitchen utensils",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Interactive Activity & Quick Assessment',
                'content' => "Interactive Activity: What Property Is It?\n\nQuestion: A rubber band becomes longer when pulled. What property does it show?\nA. Hardness\nB. Shininess\nC. Stretchability\nD. Roughness\n\nCorrect Answer: C — Stretchability!\n\nKey Takeaway: Materials are carefully chosen and shaped to suit their everyday functions.",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Properties and Uses of Materials', 'difficulty' => 'Easy', 'text' => 'Which property means a material can bend? | A: Hardness B: Flexibility C: Shininess D: Transparency', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Properties and Uses of Materials', 'difficulty' => 'Easy', 'text' => 'Which material is stretchable? | A: Rubber band B: Glass C: Rock D: Wood', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Properties and Uses of Materials', 'difficulty' => 'Medium', 'text' => 'Which metal is commonly used for electrical wires? | A: Copper B: Yero C: Stainless steel D: Lead', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Properties and Uses of Materials', 'difficulty' => 'Medium', 'text' => 'Which material is commonly used for roofing in Philippine homes? | A: Rubber B: Yero (Galvanized iron) C: Paper D: Cardboard', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Properties and Uses of Materials', 'difficulty' => 'Hard', 'text' => 'Why do people change the shape of solid materials? | A: To make them useful for different purposes B: To make them disappear C: To make them useless D: To break them permanently', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '1',
        'lesson_number' => '2',
        'topic' => 'Changes in Materials and Environmental Responsibility',
        'objectives' => [
            'Describe physical changes in materials when bent, pressed, hammered, or cut.',
            'Observe and describe the effects of heating and cooling on common substances.',
            'Practice the 5Rs of waste management (Reduce, Reuse, Recycle, Repair, Refuse) to care for the environment.'
        ],
        'content' => "Materials can change in size, shape, or state without forming new substances. Cutting paper, bending a wire, pressing clay, or hammering an aluminum can changes how they look, but the material remains the same.\n\nHeating and cooling cause noticeable changes. Heat melts solid ice into liquid water and softens candle wax. Cooling water in a freezer turns it back into solid ice.\n\nImproper disposal of materials pollutes the land, rivers, and oceans. Practicing the 5Rs—Reduce waste, Reuse containers, Recycle paper and plastics, Repair broken items, and Refuse single-use plastics—keeps our community clean and protects wildlife.",
        'slides' => [
            [
                'title' => 'Changes in Materials & Environmental Care',
                'content' => "Grade 3 — Term 1 Science\n\nLearn how materials change shape, size, and state, and how we can protect our surroundings.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Physical Actions on Materials',
                'content' => "How can we change materials without creating new substances?\n• Bending: Soft metal wire or rubber bends easily\n• Pressing: Modeling clay changes shape under finger pressure\n• Hammering: Flattening metal sheets or soda cans\n• Cutting: Scissors slicing paper or cloth into smaller pieces",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Temperature: Heating and Cooling',
                'content' => "Thermal changes in everyday life:\n• Heating ice: Solid ice melts into liquid water\n• Heating wax: Solid candle wax softens and turns into liquid\n• Cooling water: Liquid water freezes into solid ice at 0°C\n• Cooling wax: Liquid wax hardens back into a solid block",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Place ice cube in sun\nStep 2: Observe melting into water\nStep 3: Place water in freezer\nStep 4: Observe refreezing into ice"
            ],
            [
                'title' => 'The 5Rs of Environmental Responsibility',
                'content' => "Protecting our community and planet:\n1. Reduce: Use less disposable packaging\n2. Reuse: Refill water bottles and jars\n3. Recycle: Turn paper and plastic into new goods\n4. Repair: Fix broken school bags and shoes\n5. Refuse: Say no to single-use plastic bags and straws",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: Protecting Our Planet',
                'content' => "Which action shows the \"Reuse\" practice of the 5Rs?\nA) Throwing old plastic bottles into the river\nB) Turning an old glass peanut butter jar into a pencil holder\nC) Burning dried leaves and plastic wrappers\nD) Buying a new plastic bag every day\n\nCorrect Answer: B (Repurposing containers gives them new life!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Changes in Materials and Environmental Responsibility', 'difficulty' => 'Easy', 'text' => 'What phase change happens when solid ice is left under the warm sun? | A: Freezing B: Melting C: Condensation D: Sublimation', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Changes in Materials and Environmental Responsibility', 'difficulty' => 'Easy', 'text' => 'Which of the 5Rs means converting waste materials into new useful products? | A: Refuse B: Repair C: Recycle D: Reduce', 'correct' => 'C'],
            ['grade' => '3', 'topic' => 'Changes in Materials and Environmental Responsibility', 'difficulty' => 'Medium', 'text' => 'When you fold a sheet of paper into an origami bird, what type of change took place? | A: Chemical change B: Physical change in shape C: Burning change D: Melting change', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Changes in Materials and Environmental Responsibility', 'difficulty' => 'Medium', 'text' => 'How does repairing a broken desk rather than discarding it benefit the school environment? | A: It creates more waste B: It conserves wood resources and reduces landfill waste C: It produces toxic gases D: It increases school expenses', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Changes in Materials and Environmental Responsibility', 'difficulty' => 'Hard', 'text' => 'Contrast pressing modeling clay versus dropping a glass mirror. What explains why one bends safely while the other shatters? | A: Clay is malleable while glass is brittle B: Glass is flexible while clay is hard C: Both produce new chemical substances D: Clay dissolves in air', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '3',
        'quarter' => '3',
        'lesson_number' => '1',
        'topic' => 'Earth Materials and Their Uses',
        'objectives' => [
            'Identify common Earth materials such as soil, rocks, sand, water, and minerals.',
            'Compare the characteristics of different soil types (clay, sand, loam).',
            'Explain how living things rely on Earth materials for food, shelter, and construction.'
        ],
        'content' => "The Earth provides natural materials that support all living things. These include rocks, minerals, water, and different types of soil.\n\nSoil is formed from broken rocks and decayed plant and animal matter. Sand has large, gritty particles that drain water quickly. Clay has tiny, sticky particles that hold a lot of water. Loam is a balanced mixture of sand, clay, and rich organic humus, making it the best soil for growing crops and garden plants.\n\nRocks and minerals are quarried for building roads, houses, bridges, and tools. Water from rivers and underground springs provides drinking water and nourishes agricultural crops.",
        'slides' => [
            [
                'title' => 'Earth Materials and Their Uses',
                'content' => "Grade 3 — Term 3 Science\n\nDiscover the natural resources beneath our feet: rocks, minerals, water, and fertile soil.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Earth\'s Natural Treasures',
                'content' => "Our planet provides essential raw materials:\n• Rocks & Minerals: Basalt, limestone, granite, quartz\n• Soil: The loose upper layer of Earth\'s surface\n• Water: Streams, rivers, lakes, and aquifers\n• Fossil Fuels & Ores: Energy and metals",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Comparing the Three Main Soil Types',
                'content' => "Characteristics of soils:\n• Sand: Large, rough, gritty particles; water drains very quickly\n• Clay: Very fine, smooth particles; holds lots of water and becomes sticky\n• Loam: Balanced mix of sand, silt, clay, and rich organic humus; holds moisture while draining well",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Feel soil texture with fingers\nStep 2: Pour 100mL water over each soil\nStep 3: Measure drainage time\nStep 4: Check organic plant growth"
            ],
            [
                'title' => 'Practical Uses in Society',
                'content' => "How we use Earth materials:\n• Construction: Gravel, sand, and cement build roads, bridges, and school buildings\n• Agriculture: Loam soil grows rice, corn, fruits, and vegetables\n• Ceramics: Clay is molded and baked into bricks, flowerpots, and roof tiles\n• Daily Life: Natural water sustains health and sanitation",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: The Best Soil',
                'content' => "Which soil type is dark, nutrient-rich, and best for planting vegetables in school gardens?\nA) Pure dry sand\nB) Sticky dense clay\nC) Rich organic loam\nD) Crushed volcanic rock\n\nCorrect Answer: C (Loam holds the right balance of water, air, and nutrients!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '3', 'topic' => 'Earth Materials and Their Uses', 'difficulty' => 'Easy', 'text' => 'Which type of soil contains rich humus and is ideal for growing vegetables? | A: Loam soil B: Coarse gravel C: Pure beach sand D: Hard dry clay', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Earth Materials and Their Uses', 'difficulty' => 'Easy', 'text' => 'Which Earth material is molded and baked to make pottery, flowerpots, and bricks? | A: Clay B: Sand C: Basalt D: Gravel', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Earth Materials and Their Uses', 'difficulty' => 'Medium', 'text' => 'Why does pure sandy soil struggle to support healthy leafy vegetable crops? | A: It holds too much water and drowns roots B: It drains water and minerals away too quickly C: It contains too much organic humus D: It is too sticky for roots', 'correct' => 'B'],
            ['grade' => '3', 'topic' => 'Earth Materials and Their Uses', 'difficulty' => 'Medium', 'text' => 'What is the role of decayed plants and animals (humus) in healthy garden soil? | A: It adds essential nutrients and helps retain moisture B: It makes soil turn into solid rock C: It repels earthworms D: It prevents water absorption', 'correct' => 'A'],
            ['grade' => '3', 'topic' => 'Earth Materials and Their Uses', 'difficulty' => 'Hard', 'text' => 'How does soil erosion by heavy rain harm both farmers and downstream river ecosystems? | A: It improves plant growth downstream B: It strips away fertile topsoil and pollutes rivers with muddy silt C: It turns river water into drinking water D: It hardens rock formations', 'correct' => 'B']
        ]
    ],

    // === GRADE 4 (MATTER AND MATERIALS — VALIDATED DEPED CURRICULUM) ===
    // Validated by Ma. Concepcion Nathalie A. Quintos (Teacher III) & Dr. Maricar A. Afuang (Principal III)
    // Bay Central Elementary School — Overall Rating: 9.5/10 (Classroom Ready)
    [
        'grade' => '4',
        'quarter' => '1',
        'lesson_number' => '1',
        'topic' => 'Physical Properties of Materials',
        'objectives' => [
            'Describe and classify materials based on observable physical properties: color, texture, hardness, flexibility, transparency, absorbency, magnetism, and ability to float or sink.',
            'Connect physical properties of materials with everyday applications (e.g., cloth for absorbency, clear glass for transparency, metals for durable tools).',
            'Perform guided classroom investigations applying physical properties to select materials for specific real-life purposes.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 1: Physical Properties of Materials\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nClassify materials based on observable physical properties and select materials for specific real-life purposes.\n\nWHAT ARE PHYSICAL PROPERTIES?\nPhysical properties are characteristics of a material that can be observed or measured without changing the material into something else.\n\nKEY OBSERVABLE PROPERTIES:\n• Color and Texture: Smooth glass, rough sandpaper, soft cotton.\n• Hardness: A material's resistance to scratching or indentation (bending is related to flexibility). Example: Hard metals resist scratching and are used for tools.\n• Flexibility: Ability to bend without breaking. Example: Rubber bands and plastic rulers.\n• Transparency: Allowing light to pass through. Example: Clear glass window panes allow us to see outside.\n• Absorbency: Ability to take in liquid into internal pores. Example: Cotton cloth absorbs water quickly for bath towels.\n• Magnetism: Being attracted to a magnet. Example: Iron nails and steel paperclips are magnetic.\n• Ability to Float or Sink: Depends on density and buoyancy. Objects less dense than water float (wood, oil), while denser objects sink (iron nails, stones).\n\nAPPLICATIONS IN DAILY LIFE:\n• Cloth → High absorbency for bath towels and kitchen wiping cloths.\n• Clear Glass → High transparency for classroom windows and spectacles.\n• Metals → High hardness and strength for construction hammers and security gates.\n\nINTERACTIVE ACTIVITY: Choose the Right Material!\nSituation: You need to clean spilled juice on your study table. Which material should you pick?\nA) Plastic sheet  B) Cotton cloth  C) Glass pane\nAnswer: B — Cotton cloth, because of its high absorbency!\n\nKEY TAKEAWAYS:\n• Observable physical properties help us identify and classify materials.\n• Hardness is resistance to scratching and denting.\n• Floating and sinking depend on density and buoyant force in water.\n• We select materials based on matching their properties to their everyday functions.",
        'slides' => [
            [
                'title' => 'Physical Properties of Materials',
                'content' => "Grade 4 — Quarter 1: Matter and Materials\n(Validated Curriculum — Bay Central Elementary School)\n\nDiscover how color, texture, hardness, flexibility, transparency, absorbency, and buoyancy help us select materials for daily life.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Observable Physical Properties',
                'content' => "Characteristics observed without altering chemical identity:\n• Hardness: Resistance to scratching/indentation (bending is flexibility!)\n• Flexibility: Ability to bend without snapping\n• Transparency: Letting light pass through clearly (glass windows)\n• Absorbency: Drawing liquids into pores (cotton bath towels)\n• Magnetism: Attracted to magnetic poles (iron, steel)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Density & Buoyancy: Floating vs Sinking',
                'content' => "Why do some objects float while others sink?\n• Floating or sinking depends on density (mass per volume) and buoyant force!\n• Objects with lower density than water float (wood, hollow plastic bottles)\n• Objects denser than water sink (solid iron nails, pebbles)\n• Steel ships float because their hollow hull encloses air, lowering average density.",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Fill clear basin with water\nStep 2: Gently place wood block -> Floats (low density)\nStep 3: Drop solid iron nail -> Sinks (high density)\nStep 4: Shape clay into a boat -> Floats due to buoyancy!"
            ],
            [
                'title' => 'Matching Properties to Practical Uses',
                'content' => "Why we choose specific materials:\n• Bath Towels: Cotton cloth for high absorbency\n• Window Panes: Clear glass for high optical transparency\n• Construction Tools: Iron and steel for high hardness and strength\n• Electrical Grips: Rubber for flexible insulation",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Physical Properties',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-A, 3-C, 4-C, 5-A):\n1. Hardness measures resistance to scratching/indentation (B)\n2. Bath towels require high absorbency (A)\n3. Floating occurs when buoyant force balances weight due to lower density (C)\n4. Window panes require optical transparency (C)\n5. Construction hammers require high hardness and durability (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Physical Properties of Materials', 'difficulty' => 'Easy', 'text' => 'Which property describes a material\'s resistance to scratching or indentation? | A: Flexibility B: Hardness C: Transparency D: Absorbency', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Physical Properties of Materials', 'difficulty' => 'Easy', 'text' => 'Why is cloth or cotton suitable for making bath towels? | A: High absorbency B: High hardness C: Low flexibility D: High shininess', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Physical Properties of Materials', 'difficulty' => 'Medium', 'text' => 'An object stays on top of the water when placed in a basin. What property explains why it floats? | A: It is made of copper B: It dissolves quickly C: Its buoyant force balances its weight due to lower density D: It is magnetic', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Physical Properties of Materials', 'difficulty' => 'Medium', 'text' => 'Which material is best suited for classroom window panes because of its transparency? | A: Wood B: Iron C: Clear glass D: Cardboard', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Physical Properties of Materials', 'difficulty' => 'Hard', 'text' => 'Why is metal commonly chosen for manufacturing construction tools like hammers? | A: High hardness and durability B: High flexibility to bend easily C: High absorbency D: Transparency', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '1',
        'lesson_number' => '2',
        'topic' => 'Chemical Properties of Materials',
        'objectives' => [
            'Identify and describe chemical properties of everyday materials: burning, rusting, reacting, decay, degradation, and biodegradation.',
            'Differentiate natural decay and biodegradation from non-biodegradable synthetic persistence.',
            'Practice classroom safety guidelines regarding reactive and flammable materials under teacher guidance.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 2: Chemical Properties of Materials\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nIdentify and describe chemical properties of everyday materials.\n\nWHAT IS A CHEMICAL PROPERTY?\nA chemical property describes how a material behaves when it interacts with another substance or undergoes a chemical change, transforming into a new substance.\n\nKEY CHEMICAL PROPERTIES:\n• Burning (Flammability): How easily a material catches fire in the presence of oxygen. Paper and dry wood burn, producing smoke and ash.\n• Rusting (Corrosion): Iron and steel react slowly with oxygen and moisture from the air to form reddish-brown rust (iron oxide).\n• Reacting with Other Substances: Baking soda reacts vigorously with vinegar, releasing bubbling carbon dioxide gas.\n• Decay and Degradation: Breakdown of materials over time through exposure to weather and chemical action.\n• Biodegradation: The breakdown of organic matter (fruit peels, leaves, food scraps) through the natural action of living microorganisms like bacteria and fungi, returning nutrients to the soil.\n\nCLASSROOM SAFETY NOTICE:\nLearners must NEVER conduct open burning experiments! All demonstrations involving fire, hot surfaces, or reactive chemicals must be strictly teacher-guided using safe multimedia visuals and videos.\n\nINTERACTIVE ACTIVITY: Identify the Chemical Property!\n1. An iron gate turns reddish-brown after rainy weeks. → Rusting (reaction with moisture & oxygen)\n2. Fallen mango leaves rot and disappear into garden soil. → Biodegradation / Decay\n3. Dry paper ignites when exposed to a flame. → Burning / Flammability\n\nKEY TAKEAWAYS:\n• Chemical properties involve how materials react to form new substances.\n• Rusting requires both moisture and oxygen.\n• Biodegradation is a biological breakdown process by microorganisms.\n• Always follow classroom safety rules around reactive materials.",
        'slides' => [
            [
                'title' => 'Chemical Properties of Materials',
                'content' => "Grade 4 — Quarter 1: Matter and Materials\n(Validated Curriculum — Bay Central Elementary School)\n\nInvestigate how materials react, burn, rust, and biodegrade into new substances.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Chemical Property vs Chemical Change',
                'content' => "Defining the reaction potential:\n• Chemical Property: Describes a material's potential behavior when interacting with other substances (e.g., flammability, rust potential)\n• Chemical Change: The actual process of changing into one or more new substances with different chemical properties!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Rusting & Reactivity in Daily Life',
                'content' => "Common Chemical Reactions:\n• Rusting: Iron and steel react with oxygen and moisture in humid Philippine air\n• Reacting: Vinegar + baking soda creates bubbling carbon dioxide gas\n• Painting fences prevents rust by blocking oxygen and rainwater!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Bare iron nail exposed to air\nStep 2: Rainwater and oxygen touch iron surface\nStep 3: Chemical reaction produces reddish iron oxide (rust)\nStep 4: Protective paint stops the reaction"
            ],
            [
                'title' => 'Decay vs Biodegradation & Safety',
                'content' => "Understanding decomposition:\n• Biodegradation: Broader scientific term for breakdown of organic matter through microorganisms (leaves, fruit peels)\n• Safety First: Learners must never burn materials! Visuals and teacher demonstrations ensure 100% classroom safety.",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Chemical Properties',
                'content' => "Check Your Understanding (Validated Answer Key: 1-C, 2-A, 3-B, 4-A, 5-C):\n1. Iron and steel have the ability to rust when exposed to oxygen and moisture (C)\n2. Biodegradation is the biological breakdown of organic matter (A)\n3. Pupils avoid burning activities because open flames pose fire and smoke hazards (B)\n4. A chemical property describes behavior when interacting with another substance (A)\n5. Food scraps decay through biodegradation to enrich garden soil (C)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Chemical Properties of Materials', 'difficulty' => 'Easy', 'text' => 'Which of the following is a chemical property of iron and steel? | A: Flexibility B: Shininess C: Ability to rust when exposed to oxygen and moisture D: Hardness', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Chemical Properties of Materials', 'difficulty' => 'Easy', 'text' => 'What process describes organic matter like fallen leaves breaking down naturally through biological activity? | A: Biodegradation B: Freezing C: Melting D: Magnetism', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Chemical Properties of Materials', 'difficulty' => 'Medium', 'text' => 'Why are pupils instructed NOT to conduct open burning experiments in the classroom? | A: Burning produces ice B: Open flames pose fire hazards and release smoke C: Fire turns paper into gold D: Wood cannot catch fire', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Chemical Properties of Materials', 'difficulty' => 'Medium', 'text' => 'Which statement correctly defines a chemical property? | A: How a material behaves when it interacts with another substance or undergoes chemical change B: The color or shape of an object C: The weight of an object on a scale D: The speed at which an object drops', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Chemical Properties of Materials', 'difficulty' => 'Hard', 'text' => 'What happens to food scraps left in an outdoor compost pit over several weeks? | A: They turn into plastic B: They melt into glass C: They decay through biodegradation to enrich the soil D: They turn into metal', 'correct' => 'C']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '1',
        'lesson_number' => '3',
        'topic' => 'Effects of Heat, Cooling, Air, and Water on Materials',
        'objectives' => [
            'Observe and describe physical and chemical changes caused by heat, cooling, air, and water.',
            'Explain phase changes: melting, freezing (water to ice), evaporation, and drying.',
            'Identify observable transformations including rust formation, decay of organic matter, and burning.'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 3: Effects of Heat, Cooling, Air, and Water on Materials\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nDescribe observable changes in materials caused by heat, cooling, air, and water.\n\nENVIRONMENTAL CONDITIONS CAUSING CHANGES:\n1. Heat (Heating):\n• Melting: Solid turns to liquid when heated (butter melting in a warm pan, candle wax softening).\n• Evaporation / Drying: Heat causes liquid water to change into invisible water vapor and leave the material. Hanging wet laundry in the sun dries because water evaporates into the air!\n\n2. Cooling (Freezing):\n• Freezing: When liquid water is cooled to 0°C, it turns into solid ice. (Scientific note: For water turning to ice, the precise term is FREEZING, while 'hardening' applies to melted solids solidifying).\n\n3. Air and Moisture (Water):\n• Rusting: Moisture and oxygen in air cause iron nails and tin sheets to corrode into reddish-brown rust.\n• Decaying / Spoiling: Food and bread left exposed to humid air develop mold and decay due to moisture and microscopic spores.\n\n4. Burning (Teacher Demonstration Only):\n• High heat in the presence of air burns dry wood and paper into ash and carbon dioxide gas.\n\nINTERACTIVE ACTIVITY: Before and After!\n• Water placed in a freezer → Liquid becomes solid ice (Freezing)\n• Wet shirt hung under the sun → Water changes to vapor and leaves shirt (Evaporation/Drying)\n• Solid candle wax warmed gently → Solid becomes liquid (Melting)\n• Iron nail soaked in moist air → Gray metal forms reddish-brown coating (Rusting)\n\nKEY TAKEAWAYS:\n• Heating causes melting and evaporation (drying).\n• Cooling liquid water causes freezing into ice.\n• Air and water together cause iron rusting and organic decay.\n• Scientists observe before-and-after conditions to track material changes.",
        'slides' => [
            [
                'title' => 'Effects of Heat, Cooling, Air, and Water',
                'content' => "Grade 4 — Quarter 1: Matter and Materials\n(Validated Curriculum — Bay Central Elementary School)\n\nTrack how temperature and environmental factors transform everyday substances.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Heat: Melting & Evaporative Drying',
                'content' => "Observable Effects of Adding Heat:\n• Melting: Solid butter or ice turns to liquid when heated\n• Evaporation & Drying: Heat gives water molecules energy to turn into water vapor and escape into the air\n• That is how wet clothes dry on the clothesline!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Cooling: Freezing Water into Ice',
                'content' => "Observable Effects of Removing Heat:\n• Freezing: Liquid water cooled in a freezer turns into solid ice\n• Precise scientific term: FREEZING (not hardening)\n• Freezing is a reversible physical phase change!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Pour liquid water into ice tray\nStep 2: Place in freezer below 0°C\nStep 3: Thermal energy leaves the water\nStep 4: Molecules lock into solid ice crystals (Freezing)"
            ],
            [
                'title' => 'Air & Water: Rusting and Decay',
                'content' => "Environmental Conditions at Work:\n• Rusting: Iron nails left outdoors react with atmospheric oxygen and water droplets\n• Decay: Bread and fruits exposed to moist warm air provide ideal conditions for fungal mold growth\n• Both represent changes fueled by air and moisture!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Temperature & Environment',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-A, 3-B, 4-A, 5-A):\n1. Liquid water cooling into solid ice is FREEZING (B)\n2. Wet laundry dries because water evaporates into water vapor and leaves (A)\n3. Solid butter heating in a pan undergoes MELTING (B)\n4. Iron nails turn reddish-brown due to exposure to moisture and air (A)\n5. Bread spoils in warm moisture because it decays and grows mold (A)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Effects of Heat, Cooling, Air, and Water on Materials', 'difficulty' => 'Easy', 'text' => 'What phase change occurs when liquid water is cooled and turns into solid ice? | A: Hardening B: Freezing C: Evaporation D: Rusting', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Effects of Heat, Cooling, Air, and Water on Materials', 'difficulty' => 'Easy', 'text' => 'How does hanging wet laundry outside in the warm breeze help it dry? | A: Water evaporates into water vapor and leaves the fabric B: Heat turns liquid water into ice C: Air pushes water into the ground D: The fabric destroys the water', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Effects of Heat, Cooling, Air, and Water on Materials', 'difficulty' => 'Medium', 'text' => 'What observable change occurs when a stick of solid butter is heated in a warm pan? | A: Freezing B: Melting into liquid C: Rusting D: Condensation', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Effects of Heat, Cooling, Air, and Water on Materials', 'difficulty' => 'Medium', 'text' => 'What environmental factors cause an iron nail left outdoors to gradually turn reddish-brown? | A: Exposure to moisture and air over time B: Extreme freezing in ice C: Complete darkness D: Lack of soil', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Effects of Heat, Cooling, Air, and Water on Materials', 'difficulty' => 'Hard', 'text' => 'What happens when bread is left in a warm, moist container for several days? | A: It decays and grows mold due to moisture and warmth B: It freezes into solid ice C: It turns into pure water D: It becomes completely transparent', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '1',
        'lesson_number' => '4',
        'topic' => 'Physical and Chemical Changes',
        'objectives' => [
            'Distinguish physical changes from chemical changes using observable evidence.',
            'Explain that in physical changes, size, shape, or state may change, but no new material is formed.',
            'Identify chemical changes characterized by the formation of new materials (burning, rusting, decay).'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 4: Physical and Chemical Changes\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\nUse observable evidence to distinguish physical changes from chemical changes.\n\nTHE CENTRAL DISTINCTION:\n• Physical Change: The size, shape, or state/form of the material may change, but NO NEW MATERIAL IS FORMED.\n  - Cutting paper: Small pieces of paper are still paper.\n  - Folding clothes: Folded fabric is still fabric.\n  - Crushing an aluminum can: Crushed metal is still aluminum.\n  - Melting ice: Liquid water is chemically identical to solid ice.\n\n• Chemical Change: A NEW MATERIAL IS FORMED with properties different from the original substances.\n  - Burning wood: Cellulose breaks down into gray ash, carbon dioxide, and smoke.\n  - Rusting iron: Iron and oxygen create a completely new substance: iron oxide (rust).\n  - Decaying fruit: Rotten fruit produces new foul-smelling compounds and soft organic matter.\n\nCOMPARISON TABLE:\nFeature | Physical Change | Chemical Change\nNew substance formed? | NO | YES\nReversible? | Often reversible | Difficult or impossible to reverse\nExamples | Cutting, folding, melting, freezing | Burning, rusting, decaying, cooking\n\nINTERACTIVE ACTIVITY: Physical or Chemical?\n1. Slicing an apple into four wedges → Physical Change (still apple)\n2. Leaving the apple slice out until it turns brown and rots → Chemical Change (decay/new substance)\n3. Crushing a dry chalk stick into fine powder → Physical Change (still chalk)\n4. Striking a matchstick to produce flame and smoke → Chemical Change (combustion)\n\nKEY TAKEAWAYS:\n• Physical changes alter size, shape, or state without forming new materials.\n• Chemical changes produce new substances with observable evidence (color change, gas, odor, heat).\n• Melting and freezing are physical changes; burning and rusting are chemical changes.",
        'slides' => [
            [
                'title' => 'Physical and Chemical Changes',
                'content' => "Grade 4 — Quarter 1: Matter and Materials\n(Validated Curriculum — Bay Central Elementary School)\n\nLearn to spot the crucial scientific difference: Has a new substance been formed?",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Physical Change: No New Material',
                'content' => "The core rule of physical changes:\n• The size, shape, or state/form of the material may change, but NO NEW MATERIAL IS FORMED.\n• Cutting, folding, tearing, crushing\n• Phase changes: Melting ice, freezing water, boiling soup\n• Dissolving: Sugar dissolved in water can be recovered by evaporation!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Chemical Change: New Substance Created',
                'content' => "Evidence of chemical transformation:\n• Burning: Wood turns into smoke, heat, and ash\n• Rusting: Shiny steel turns into crumbly reddish-brown iron oxide\n• Decaying: Organic matter breaks down into foul gases and humus\n• The original substance cannot simply be folded or frozen back!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Check original material\nStep 2: Observe the process (heat, gas, color shift)\nStep 3: Test: Is a brand new material formed?\nStep 4: YES = Chemical Change; NO = Physical Change"
            ],
            [
                'title' => 'Side-by-Side Comparison',
                'content' => "Quick Identification Guide:\n• Cutting paper -> Physical (still paper fibers)\n• Burning paper -> Chemical (produces ash and smoke)\n• Melting chocolate -> Physical (cooling solidifies it again)\n• Spoiled milk -> Chemical (acids create sour curd)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Physical vs Chemical',
                'content' => "Check Your Understanding (Validated Answer Key: 1-B, 2-C, 3-D, 4-A, 5-B):\n1. Folding paper into an airplane is a physical change (B)\n2. Physical changes: size, shape, or state changes, but no new material is formed (C)\n3. Wood burning into smoke and ash is clear evidence of chemical change (D)\n4. Rusting iron is chemical because iron and oxygen form a new substance (A)\n5. Dissolving sugar is physical because no new substance is formed (B)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Easy', 'text' => 'Which of the following describes a physical change? | A: Rust forming on an iron pipe B: Folding a sheet of paper into a paper airplane C: Burning a dried leaf to ash D: Souring of fresh milk', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Easy', 'text' => 'What is the defining characteristic of a physical change in matter? | A: A brand-new substance is created B: Odor and heat are always released C: The size, shape, or state/form may change, but no new material is formed D: Chemical bonds are permanently broken', 'correct' => 'C'],
            ['grade' => '4', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Medium', 'text' => 'Which situation provides clear observable evidence of a chemical change? | A: Melting ice cubes in juice B: Tearing cardboard C: Slicing an apple into wedges D: Wood burning into smoke and ash', 'correct' => 'D'],
            ['grade' => '4', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Medium', 'text' => 'When an iron gate rusts after rainy days, why is this classified as a chemical change? | A: Iron combines with oxygen and water to form a new substance (rust) B: Only the color of the paint changed C: The iron can be wiped clean like water D: No new material is formed', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Hard', 'text' => 'Why is dissolving sugar in a glass of warm water considered a physical change rather than a chemical change? | A: Sugar permanently turns into alcohol B: No new substance is formed and sugar can be recovered by evaporating the water C: Sugar disappears forever D: The water turns into acid', 'correct' => 'B']
        ]
    ],
    [
        'grade' => '4',
        'quarter' => '1',
        'lesson_number' => '5',
        'topic' => 'Responsible Use and Management of Materials',
        'objectives' => [
            'Identify ways to minimize harmful effects of improper use and management of materials in school and community.',
            'Explain how science helps identify materials-related problems (pollution, clogged drainage) and suggest responsible actions.',
            'Practice safe handling and storage of household materials (avoid burning waste, keep original containers, ask adults for help).'
        ],
        'content' => "GRADE 4 SCIENCE — LESSON 5: Responsible Use and Management of Materials\n(Validated DepEd MATATAG Curriculum — Bay Central Elementary School)\n\nLEARNING COMPETENCY:\n1. Identify ways to minimize harmful effects of improper use and management of materials.\n2. Identify materials-related issues in school/community and explain how science can help address them.\n\nHARMFUL EFFECTS OF IMPROPER USE AND MANAGEMENT OF MATERIALS:\n• Burning Household Waste: Releases toxic fumes, smoke particles, and greenhouse gases that trigger asthma and air pollution. Refinement rule: Avoid burning household waste and follow proper waste-management practices!\n• Improper Waste Disposal: Dumping plastic wrappers into streets and canals causes clogged drainage systems and severe community flooding during monsoon rains.\n• Unsafe Chemical Storage: Keeping pesticides or bleach in unlabeled beverage bottles risks accidental poisoning among young children.\n\nSAFE PRACTICES FOR PUPILS:\n1. Follow instructions on product labels carefully.\n2. Never taste, smell, or handle unknown substances.\n3. Keep cleaning products in original labeled containers.\n4. Always ask an adult, parent, or teacher for assistance when using household chemicals.\n\nHOW SCIENCE HELPS OUR COMMUNITY:\nScience helps identify pollution problems, invents recyclable materials, and designs composting systems to convert organic waste into rich soil fertilizer.\n\nINTERACTIVE ACTIVITY: Problem → Safe Action!\n• Situation 1: Scattered plastic wrappers in the canal → Action: Segregate and place in recycling bins to prevent clogged drains.\n• Situation 2: Smoldering pile of dried leaves → Action: Extinguish safely and transfer leaves to an organic compost pit.\n• Situation 3: Bleach stored in an old soda bottle → Action: Ask an adult to transfer to a labeled safety bottle out of children's reach.\n• Situation 4: Spent flashlight batteries → Action: Place in special hazardous e-waste collection bin.\n• Situation 5: Leftover vegetable peels from cooking → Action: Mix with soil and brown leaves in the garden compost.\n\nKEY TAKEAWAYS:\n• Avoid burning waste; practice the 5Rs (Reduce, Reuse, Recycle, Repair, Refuse).\n• Proper waste disposal prevents clogged canals and street floods.\n• Safe storage in original containers prevents chemical accidents.\n• Science empowers us to make responsible decisions for our environment.",
        'slides' => [
            [
                'title' => 'Responsible Use and Management',
                'content' => "Grade 4 — Quarter 1: Matter and Materials\n(Validated Curriculum — Bay Central Elementary School)\n\nLearn how safe handling, proper disposal, and environmental science protect homes and communities.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Harmful Effects of Improper Disposal',
                'content' => "Why improper management hurts us:\n• Burning Waste: Releases toxic smoke that damages lungs and pollutes air\n• Clogged Drainage: Plastic debris blocks canals, causing rapid flash flooding\n• Safe action: Avoid burning household waste and follow proper waste-management practices!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Core Safety Rules with Chemicals',
                'content' => "Protecting yourself and family:\n1. Keep all materials in original containers with clear warning labels\n2. Never taste, inhale, or play with unknown substances\n3. Store cleaners in high cabinets away from food\n4. Always ask an adult or teacher for help!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Check container label\nStep 2: Never transfer chemicals into juice bottles\nStep 3: Wear protective gear if instructed\nStep 4: Ask an adult for guidance"
            ],
            [
                'title' => 'Science Solutions: The 5Rs & Composting',
                'content' => "How science helps our community:\n• Segregate Waste: Green bin (biodegradable) & Blue bin (recyclable)\n• Composting: Turning food scraps into rich fertilizer instead of throwing them away\n• 5Rs: Reduce, Reuse, Recycle, Repair, Refuse single-use plastics!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Assessment: Responsible Management',
                'content' => "Check Your Understanding (Validated Answer Key: 1-A, 2-B, 3-B, 4-A, 5-C):\n1. Burning plastic waste releases toxic smoke and dangerous pollutants into air (A)\n2. Dumping plastics in gutters causes clogged drainage and community flooding (B)\n3. Safest practice: Keep cleaners in original labeled containers and ask adults (B)\n4. Science helps by identifying harmful waste effects and developing recycling (A)\n5. Segregating waste into recycling bins demonstrates the 5Rs at school (C)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '4', 'topic' => 'Responsible Use and Management of Materials', 'difficulty' => 'Easy', 'text' => 'Why is burning household plastic waste harmful to health and the environment? | A: It releases toxic smoke, pollutants, and dangerous gases into the air B: It produces clean rain C: It enriches the soil D: It cools the atmosphere', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Responsible Use and Management of Materials', 'difficulty' => 'Easy', 'text' => 'What community problem is directly caused by dumping plastic wrappers and garbage into street gutters? | A: Clean drinking water B: Clogged drainage and street flooding during heavy rains C: Faster plant growth D: Cleaner sidewalks', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Responsible Use and Management of Materials', 'difficulty' => 'Medium', 'text' => 'What is the safest practice when handling household cleaning chemicals like bleach or detergent? | A: Taste them to check freshness B: Keep them in original labeled containers and ask an adult or teacher for help C: Mix different chemicals together D: Store them beside food and cooking oil', 'correct' => 'B'],
            ['grade' => '4', 'topic' => 'Responsible Use and Management of Materials', 'difficulty' => 'Medium', 'text' => 'How can science help communities manage materials responsibly? | A: By identifying harmful effects of wastes and developing methods to recycle and reuse materials B: By creating more single-use plastic wrappers C: By encouraging people to burn all trash D: By hiding garbage in rivers', 'correct' => 'A'],
            ['grade' => '4', 'topic' => 'Responsible Use and Management of Materials', 'difficulty' => 'Hard', 'text' => 'Which action demonstrates the 5Rs and responsible waste management at school? | A: Throwing juice cartons into open gutters B: Burning paper wrappers in the schoolyard C: Segregating paper, plastic, and food waste into designated recycling bins D: Leaving old batteries in garden soil', 'correct' => 'C']
        ]
    ],

    // === GRADE 5 ===
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '1',
        'topic' => 'Properties of Matter',
        'objectives' => [
            'Differentiate intensive properties (density, boiling point, hardness) from extensive properties (mass, volume, length).',
            'Measure and calculate mass, volume, and density (d = m/V) using laboratory instruments.',
            'Explain why intensive properties are essential for identifying unknown materials and substances.'
        ],
        'content' => "Matter is defined by physical and chemical properties that can be quantified and compared.\n\nExtensive properties depend on the amount of matter in a sample. Examples include mass (grams), volume (milliliters/cubic centimeters), and length. If you double the amount of water, its mass and volume double.\n\nIntensive properties do NOT depend on the amount of matter. Examples include density, boiling point, melting point, color, and electrical conductivity. 1 drop of pure water and 1 gallon of pure water both have a density of 1.0 g/cm³ and boil at 100°C at sea level.\n\nCalculating density (d = mass / volume) allows scientists to identify unknown minerals and materials accurately.",
        'slides' => [
            [
                'title' => 'Properties of Matter',
                'content' => "Grade 5 — Term 1 Science\n\nMaster the distinction between extensive and intensive properties and calculate density (d = m/V).",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Extensive vs Intensive Properties',
                'content' => "Comparing the two property classes:\n• Extensive Properties: Depend on sample size\n  - Mass (g, kg)\n  - Volume (mL, cm³)\n  - Length & Total Energy\n• Intensive Properties: Independent of sample size\n  - Density (1.0 g/cm³ for water)\n  - Boiling point (100°C for water)\n  - Color, Odor, Hardness, and Conductivity",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Density Formula in Practice',
                'content' => "Formula: d = m / V (Density = Mass ÷ Volume)\n\n• Mass is measured using a beam balance or electronic digital scale (grams).\n• Volume is measured using a graduated cylinder via liquid displacement:\n  V_object = V_final - V_initial\n• Units: g/cm³ (for solids) or g/mL (for liquids).",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Weigh sample on digital scale (m)\nStep 2: Fill graduated cylinder to 50mL\nStep 3: Submerge object, record new level (V)\nStep 4: Divide mass by volume (d = m/V)"
            ],
            [
                'title' => 'Laboratory Mystery: Real Gold vs Pyrite',
                'content' => "How can density identify \"Fool's Gold\"?\n\n• Sample Mass: 38.6 g, Volume: 2.0 mL\n• Calculated Density: 38.6 / 2.0 = 19.3 g/cm³\n• Pure Gold density is exactly 19.3 g/cm³.\n• Iron Pyrite (Fool's Gold) density is only 5.0 g/cm³.\n• Conclusion: The sample is genuine pure gold!",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Classroom Calculation Check',
                'content' => "A rock has a mass of 120 grams and displaces 40 mL of water. What is its density?\nA) 3.0 g/cm³\nB) 0.33 g/cm³\nC) 4,800 g/cm³\nD) 80 g/cm³\n\nCorrect Answer: A (120 g / 40 mL = 3.0 g/cm³)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Properties of Matter', 'difficulty' => 'Easy', 'text' => 'Which of the following is an intensive property that does NOT change when sample size changes? | A: Mass B: Volume C: Density D: Length', 'correct' => 'C'],
            ['grade' => '5', 'topic' => 'Properties of Matter', 'difficulty' => 'Easy', 'text' => 'What laboratory instrument is used to accurately measure liquid volume and displaced solid volume? | A: Spring scale B: Graduated cylinder C: Meter stick D: Barometer', 'correct' => 'B'],
            ['grade' => '5', 'topic' => 'Properties of Matter', 'difficulty' => 'Medium', 'text' => 'A metal cube has a mass of 150 grams and a volume of 30 cm³. What is its density? | A: 5.0 g/cm³ B: 0.2 g/cm³ C: 4,500 g/cm³ D: 180 g/cm³', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Properties of Matter', 'difficulty' => 'Medium', 'text' => 'Why does a 1-liter bottle of pure water have the exact same density as a 10-milliliter test tube of pure water? | A: Density is an intensive property defined by the ratio of mass to volume, which remains constant B: Water has no mass C: Smaller samples always have higher volume D: Temperature changes density to zero', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Properties of Matter', 'difficulty' => 'Hard', 'text' => 'A jeweler tests a crown with a mass of 965g and displaced volume of 65mL. Is it pure gold (d = 19.3 g/cm³)? | A: No, its density is 14.85 g/cm³, meaning it is mixed with less dense metals like copper B: Yes, 965 is larger than 19.3 C: Yes, all heavy items are gold D: Cannot be calculated', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '2',
        'topic' => 'States of Matter',
        'objectives' => [
            'Describe the Kinetic Molecular Theory of matter (particle arrangement, motion, and attraction).',
            'Compare the physical and molecular properties of solids, liquids, and gases.',
            'Explain the fourth state of matter (plasma) and where it is found in the cosmos.'
        ],
        'content' => "All matter is made of tiny, constantly moving particles (atoms and molecules). The kinetic energy of these particles determines the state of matter.\n\nIn solids, particles are packed tightly in fixed, orderly arrangements; they vibrate in place and have strong intermolecular attractions, giving solids definite shape and volume.\n\nIn liquids, particles have more energy, slide past one another fluidly, and have moderate attraction; liquids have definite volume but take the shape of their container.\n\nIn gases, particles possess high kinetic energy, fly freely at high speeds, and have negligible attraction; gases expand to fill the entire volume and shape of their container.\n\nPlasma is an ionized gas of free electrons and positive ions formed at ultra-high temperatures. It is found in stars, lightning bolts, auroras, and neon lights, making it the most abundant state of matter in the universe.",
        'slides' => [
            [
                'title' => 'States of Matter & Molecular Physics',
                'content' => "Grade 5 — Term 1 Science\n\nExamine matter at the particulate level: solids, liquids, gases, and high-energy plasma.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Kinetic Molecular Theory (KMT)',
                'content' => "Fundamental Principles of KMT:\n1. All matter consists of extremely tiny particles (atoms/molecules)\n2. Particles are in constant, random motion\n3. Higher temperature = Higher average kinetic energy and velocity\n4. Attractive forces hold particles together unless overcome by heat",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Solid vs Liquid vs Gas at Particle Level',
                'content' => "Comparative Particle Matrix:\n• Solid: Rigid lattice, tightly packed, vibrates in place, definite shape and volume\n• Liquid: Close together, free to slide past one another, definite volume, takes container shape\n• Gas: Far apart, high-speed chaotic collisions, no definite shape, no definite volume (compressible)",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Solid: Low energy, locked lattice\nLiquid: Medium energy, sliding particles\nGas: High energy, high velocity collisions\nPlasma: Extreme energy, stripped ions & electrons"
            ],
            [
                'title' => 'Plasma — The Fourth State of Matter',
                'content' => "Beyond gas lies Plasma:\n• When gas is heated to super-high temperatures, electrons are stripped away from nuclei\n• Consists of positively charged ions and free electrons\n• Conducts electricity and responds strongly to magnetic fields\n• Found in: The Sun and stars (99% of visible universe), lightning bolts, neon signs, and auroras",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Thinking Challenge: Tire Pressure',
                'content' => "Why does automobile tire pressure increase significantly after driving for hours on a hot asphalt highway?\nA) More air entered the tire from the road\nB) Heat increased the kinetic energy of air molecules inside, causing more frequent, forceful collisions against tire walls\nC) The rubber contracted into a smaller size\nD) The tire absorbed water vapor\n\nCorrect Answer: B (Higher temperature increases gas pressure!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'States of Matter', 'difficulty' => 'Easy', 'text' => 'In which state of matter do particles possess the lowest kinetic energy and vibrate in fixed positions? | A: Solid B: Liquid C: Gas D: Plasma', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'States of Matter', 'difficulty' => 'Easy', 'text' => 'What is the most abundant state of matter in the visible universe, found in stars and lightning? | A: Solid B: Liquid C: Plasma D: Gas', 'correct' => 'C'],
            ['grade' => '5', 'topic' => 'States of Matter', 'difficulty' => 'Medium', 'text' => 'Why can gases be compressed into much smaller storage cylinders, whereas liquids and solids cannot? | A: Gases have vast empty spaces between moving molecules that can be squeezed together B: Gas particles are softer C: Solids have no mass D: Liquids turn into plasma when squeezed', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'States of Matter', 'difficulty' => 'Medium', 'text' => 'According to Kinetic Molecular Theory, what happens to water molecules when liquid water is heated to boiling? | A: Molecules gain kinetic energy and overcome attractive forces to escape as water vapor gas B: Molecules freeze into place C: Molecules disappear D: Molecules stop moving', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'States of Matter', 'difficulty' => 'Hard', 'text' => 'Describe how a neon sign produces bright glowing light using the properties of plasma. | A: High electric voltage ionizes neon gas into plasma, exciting electrons that emit light photons as they return to lower energy states B: Neon catches fire in air C: Neon melts into a glowing liquid D: Neon absorbs heat from the room', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '3',
        'topic' => 'Changes in Matter',
        'objectives' => [
            'Classify changes in matter as endothermic (heat-absorbing) or exothermic (heat-releasing).',
            'Explain energy transfers during phase changes (melting, vaporization, condensation, freezing).',
            'Investigate everyday chemical changes such as cellular respiration, digestion, and combustion.'
        ],
        'content' => "Changes in matter are accompanied by energy transformations. Heat energy flows between substances and their surroundings.\n\nEndothermic changes absorb heat from the surroundings. When ice melts or water boils, particles absorb thermal energy to break intermolecular attractions. As a result, the immediate surroundings feel cooler.\n\nExothermic changes release heat into the surroundings. When water freezes or water vapor condenses, particles release thermal energy to lock into place. Combustion (burning wood or gas) releases enormous amounts of thermal energy and light.\n\nBiological systems rely on continuous changes in matter: our digestive system breaks food into glucose molecules, which our cells burn with oxygen during cellular respiration to release metabolic energy (ATP).",
        'slides' => [
            [
                'title' => 'Changes in Matter & Energy Transfers',
                'content' => "Grade 5 — Term 1 Science\n\nTrack heat energy flow: endothermic (absorbing heat) vs exothermic (releasing heat).",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Endothermic vs Exothermic Reactions',
                'content' => "Direction of Thermal Energy Flow:\n• Endothermic (Heat Enters):\n  - System absorbs heat from surroundings\n  - Surroundings feel COLD\n  - Examples: Ice melting, water boiling, cold packs, photosynthesis\n• Exothermic (Heat Exits):\n  - System releases heat to surroundings\n  - Surroundings feel HOT\n  - Examples: Fire burning, fireworks, hand warmers, freezing",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Phase Changes & Latent Heat',
                'content' => "Thermal shifts during phase transitions:\n• Melting & Boiling are ENDOTHERMIC: Heat energy breaks particle bonds\n• Freezing & Condensation are EXOTHERMIC: Heat energy is released as bonds form\n• While changing state, temperature stays constant at the plateau point!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Solid (-10°C) -> Add heat -> Melts at 0°C\nLiquid (0°C to 100°C) -> Add heat -> Boils at 100°C\nGas (>100°C) -> Freely expanding vapor"
            ],
            [
                'title' => 'Biological Energy Changes: Cellular Respiration',
                'content' => "Energy in living cells:\nGlucose (C6H12O6) + 6O2 -> 6CO2 + 6H2O + Energy (ATP)\n\n• Chemical transformation in mitochondria\n• Exothermic biological reaction providing body warmth and muscular movement",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: Cold Rubbing Alcohol',
                'content' => "When you apply rubbing alcohol to your skin, why does your arm immediately feel cold?\nA) Alcohol freezes on your skin\nB) Alcohol absorbs heat from your skin as it rapidly evaporates (an endothermic change for the liquid)\nC) Alcohol turns into ice\nD) Alcohol chemically reacts to produce cold acid\n\nCorrect Answer: B (Evaporative cooling absorbs your body heat!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Changes in Matter', 'difficulty' => 'Easy', 'text' => 'What type of reaction releases heat into the surrounding environment, making it feel hot? | A: Exothermic reaction B: Endothermic reaction C: Nuclear fusion only D: Photosynthesis', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Changes in Matter', 'difficulty' => 'Easy', 'text' => 'Is the melting of an ice cube an endothermic or exothermic process for the ice? | A: Endothermic, because the ice absorbs heat from surroundings B: Exothermic, because it makes things cold C: Neither D: Both simultaneously', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Changes in Matter', 'difficulty' => 'Medium', 'text' => 'Why does your skin feel cold when rubbing alcohol evaporates quickly from your hand? | A: The evaporating alcohol absorbs thermal energy from your skin to break liquid bonds B: The alcohol enters your bloodstream instantly C: Alcohol freezes skin cells D: Alcohol creates a chemical chill', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Changes in Matter', 'difficulty' => 'Medium', 'text' => 'During cellular respiration, human cells combine glucose with oxygen. What form of energy is produced? | A: ATP metabolic energy and heat B: Nuclear radiation C: Light only D: Cold energy', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Changes in Matter', 'difficulty' => 'Hard', 'text' => 'Why does steam at 100°C produce significantly more dangerous burns than liquid boiling water at 100°C? | A: Steam releases substantial additional latent heat of condensation upon turning back to liquid on skin B: Steam is hotter than 100°C C: Steam contains acid D: Boiling water has no heat', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '5',
        'quarter' => '1',
        'lesson_number' => '4',
        'topic' => 'Scientific Investigation of Matter',
        'objectives' => [
            'Formulate testable hypotheses, identify independent, dependent, and controlled variables.',
            'Design and conduct a fair test investigation on the physical and chemical properties of matter.',
            'Collect quantitative experimental data, construct data tables, and draw valid conclusions.'
        ],
        'content' => "Science advances through systematic inquiry. The scientific method provides a structured approach: Problem -> Hypothesis -> Controlled Experiment -> Data Collection -> Analysis -> Conclusion.\n\nA fair test requires controlling variables:\n- Independent Variable (IV): the single condition the scientist deliberately changes (e.g., water temperature).\n- Dependent Variable (DV): the observable outcome that is measured (e.g., time taken for sugar to dissolve).\n- Controlled Variables (CV): all other factors kept strictly identical (volume of water, amount of sugar, stirring speed).\n\nReplicating trials (running at least 3 trials) ensures results are reliable, objective, and reproducible.",
        'slides' => [
            [
                'title' => 'Scientific Investigation of Matter',
                'content' => "Grade 5 — Term 1 Science\n\nLearn the scientific method: formulating hypotheses, controlling variables, and analyzing fair tests.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Scientific Method Steps',
                'content' => "Systematic Inquiry Cycle:\n1. Question / Problem: What are we investigating?\n2. Hypothesis: Testable \"If... then... because...\" prediction\n3. Controlled Experiment: Designing the fair test\n4. Data Gathering: Quantitative measurements (numbers) and qualitative notes (observations)\n5. Analysis & Conclusion: Does evidence support the hypothesis?",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Variables in a Fair Test',
                'content' => "Understanding the three variable types:\n• Independent Variable (IV): The ONE factor you change (e.g., Water Temperature: 20°C, 50°C, 80°C)\n• Dependent Variable (DV): What you measure (e.g., Dissolving time in seconds)\n• Controlled Variables (CV): Kept EXACTLY equal (volume of water, mass of solute, stirring rate)",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Set up 3 identical beakers\nStep 2: Add 100mL water at 20°C, 50°C, 80°C\nStep 3: Add 10g sugar simultaneously\nStep 4: Time until complete dissolution"
            ],
            [
                'title' => 'Data Tables, Replications, & Graphs',
                'content' => "Why run 3 experimental trials?\n\n• Human error and slight timing variations happen.\n• Calculating the average of Trials 1, 2, and 3 produces trustworthy scientific data:\n  Average = (T1 + T2 + T3) / 3\n• Plotting temperature vs time reveals the inverse dissolving curve.",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: Variable Identification',
                'content' => "In an experiment testing which brand of paper towel absorbs the most water, what is the Independent Variable?\nA) The volume of water absorbed\nB) The brand of paper towel used\nC) The temperature of the room\nD) The size of the measuring cup\n\nCorrect Answer: B (The brand is the single factor deliberately tested and changed!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '5', 'topic' => 'Scientific Investigation of Matter', 'difficulty' => 'Easy', 'text' => 'In a scientific experiment, what is the variable that the investigator deliberately changes? | A: Independent Variable B: Dependent Variable C: Controlled Constant D: Random Variable', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Scientific Investigation of Matter', 'difficulty' => 'Easy', 'text' => 'Why must all variables except the independent variable be kept strictly identical in a fair test? | A: To ensure that any observed changes are solely caused by the independent variable B: To save laboratory equipment C: To make the test faster D: To avoid taking measurements', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Scientific Investigation of Matter', 'difficulty' => 'Medium', 'text' => 'A student tests whether salt dissolves faster in hot water than cold water. Identify the dependent variable. | A: The temperature of the water B: The time required for the salt to completely dissolve C: The brand of salt D: The size of the spoon', 'correct' => 'B'],
            ['grade' => '5', 'topic' => 'Scientific Investigation of Matter', 'difficulty' => 'Medium', 'text' => 'Why do scientists perform multiple experimental trials (e.g. 3 trials) rather than relying on a single test? | A: To reduce experimental error and calculate a dependable scientific average B: Because equipment fails C: To use up chemicals D: To change hypotheses halfway', 'correct' => 'A'],
            ['grade' => '5', 'topic' => 'Scientific Investigation of Matter', 'difficulty' => 'Hard', 'text' => 'A team hypothesizes that adding salt raises water\'s boiling point. Outline the variables and fair test protocol. | A: IV: Salt concentration; DV: Measured boiling temperature; CV: 100mL water volume, identical beaker, same burner intensity, 3 trials per concentration B: IV: Water volume; DV: Salt brand C: Change thermometer type every trial D: Boil water without measuring temperature', 'correct' => 'A']
        ]
    ],

    // === GRADE 6 ===
    [
        'grade' => '6',
        'quarter' => '1',
        'lesson_number' => '1',
        'topic' => 'Changes in Matter',
        'objectives' => [
            'Describe factors affecting the rate of changes in matter (temperature, surface area, concentration, catalysts).',
            'Explain the Law of Conservation of Mass in both open and closed systems.',
            'Analyze natural biochemical decay, fermentation, and rust formation processes.'
        ],
        'content' => "The rate at which matter undergoes physical and chemical transformations depends on specific kinetic factors:\n- Temperature: Higher temperatures increase particle collisions and reaction speed.\n- Surface Area: Crushing a solid into powder exposes more particles to reactants, speeding up the reaction.\n- Concentration: More reactant particles in a given volume increases the frequency of collisions.\n- Catalysts: Substances like enzymes that accelerate reactions without being consumed.\n\nThe Law of Conservation of Mass states that matter can neither be created nor destroyed in any physical or chemical change. The total mass of reactants always equals the total mass of products.\n\nIn open systems, gases (like carbon dioxide from vinegar and baking soda) escape into the air, making it seem like mass was lost. In a sealed closed container, the measured mass remains exactly identical before and after the reaction.",
        'slides' => [
            [
                'title' => 'Changes in Matter: Reaction Kinetics',
                'content' => "Grade 6 — Term 1 Science\n\nExplore factors controlling reaction rates and master the Law of Conservation of Mass.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Four Factors Affecting Reaction Rates',
                'content' => "How to speed up or slow down changes in matter:\n1. Temperature: Heat increases molecular speed and collision frequency\n2. Surface Area: Powder reacts faster than solid blocks\n3. Concentration: More reactant molecules per volume = more collisions\n4. Catalysts: Biological enzymes or chemical agents that lower activation energy",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Law of Conservation of Mass',
                'content' => "Fundamental Law of Chemistry:\nMass of Reactants = Mass of Products\n\n• Atoms are neither created nor destroyed during chemical reactions; they are merely rearranged.\n• In open systems: Gases escape into atmosphere, appearing to lose mass.\n• In closed systems: Sealed mass before reaction = sealed mass after reaction!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Weigh flask with vinegar + balloon with baking soda\nStep 2: Tip balloon to start reaction\nStep 3: Gas inflates balloon inside closed system\nStep 4: Re-weigh: Mass remains exactly unchanged!"
            ],
            [
                'title' => 'Everyday Biochemical Changes',
                'content' => "Real-world reactions in action:\n• Fermentation: Yeast converts sugar into alcohol and CO2 gas (makes bread dough rise!)\n• Food Spoilage: Bacterial enzymes oxidize organic food molecules\n• Refrigeration: Lowers temperature to dramatically slow decay reaction rates",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: Fireplace Mass Mystery',
                'content' => "A 10kg wooden log burns down to 500g of ash in a fireplace. Has mass been destroyed?\nA) Yes, 9.5kg of matter was permanently destroyed\nB) No, 9.5kg of mass escaped as carbon dioxide gas, water vapor, and smoke into the atmosphere\nC) The fire absorbed the mass\nD) Ash is denser than wood\n\nCorrect Answer: B (Conservation of Mass applies; gas escaped into the open system!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Changes in Matter', 'difficulty' => 'Easy', 'text' => 'According to the Law of Conservation of Mass, what happens to the total mass of matter during a chemical reaction? | A: It remains constant and conserved B: It decreases by 50% C: It increases exponentially D: It disappears completely', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Changes in Matter', 'difficulty' => 'Easy', 'text' => 'Which form of an effervescent antacid tablet dissolves and reacts fastest in water? | A: Crushed fine powder B: Whole solid tablet C: Half tablet D: Coarse chunk', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Changes in Matter', 'difficulty' => 'Medium', 'text' => 'Why does food spoil significantly faster on a hot tropical kitchen counter than inside a refrigerator? | A: High temperature increases the rate of enzymatic and microbial chemical decomposition reactions B: Refrigerator destroys bacteria instantly C: Light dissolves food molecules D: Heat adds extra mass to food', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Changes in Matter', 'difficulty' => 'Medium', 'text' => 'When vinegar reacts with baking soda in an open glass beaker, the scale reading drops. Why? | A: Carbon dioxide gas escaped into the open air B: Mass was destroyed C: The acid dissolved the scale D: Liquid turned into solid ice', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Changes in Matter', 'difficulty' => 'Hard', 'text' => 'How can you experimentally verify the Law of Conservation of Mass for a gas-producing reaction? | A: Perform the reaction inside a tightly sealed container (closed system) like a flask with a stretched balloon, weighing before and after B: Perform the reaction outside in wind C: Measure volume instead of mass D: Burn the substances openly', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '6',
        'quarter' => '1',
        'lesson_number' => '2',
        'topic' => 'Physical and Chemical Changes',
        'objectives' => [
            'Compare physical and chemical changes at the atomic, molecular, and energetic levels.',
            'Identify reactants, products, and energy transformations in chemical reactions.',
            'Evaluate how understanding physical and chemical changes enables food preservation and material protection.'
        ],
        'content' => "At the microscopic scale, physical changes rearrange molecules without altering their intramolecular chemical bonds. Liquid water and ice are both composed of H2O molecules.\n\nChemical reactions break chemical bonds between reactant atoms and form new bonds to create products with completely novel chemical formulas and properties:\nReactants -> Products\n\nEnergy is involved in bond reorganization: breaking bonds requires energy input, while creating new bonds releases energy.\n\nHuman civilization applies these principles: canning and pickling alter acidity to prevent chemical decay; galvanizing iron coats it with zinc to sacrifice-protect it against oxidation; refining crude oil uses fractional distillation (physical separation) followed by catalytic cracking (chemical change).",
        'slides' => [
            [
                'title' => 'Physical & Chemical Changes: Deep Dive',
                'content' => "Grade 6 — Term 1 Science\n\nExamine atomic rearrangements, chemical equations, and industrial applications.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Atomic Realities: Bonds & Molecules',
                'content' => "What happens to atoms?\n• Physical Change: Molecules stay intact; only spacing and orientation shift (e.g., Ice -> Water -> Steam is still H2O)\n• Chemical Change: Covalent or ionic bonds break; atoms recombine into new molecular structures with unique formulas:\nCH4 + 2O2 -> CO2 + 2H2O + Heat",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Reactants, Products, & Energy Shifts',
                'content' => "Key Anatomy of a Reaction:\n• Reactants: Starting chemical compounds on the left\n• Products: Newly formed chemical compounds on the right\n• Activation Energy: Minimum energy required to trigger reaction\n• Bond breaking = Energy absorbed\n• Bond formation = Energy released",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Reactants collide with sufficient energy\nStep 2: Old chemical bonds break\nStep 3: Atoms rearrange\nStep 4: New bonds form into Products"
            ],
            [
                'title' => 'Industrial & Everyday Technologies',
                'content' => "Harnessing changes in materials:\n• Galvanization: Coating steel with zinc prevents oxidation\n• Pickling & Canning: Acidity denatures bacterial enzymes chemically\n• Water Desalination: Physical evaporation followed by condensation produces drinking water\n• Polymerization: Chemical bonding creates synthetic fibers and medical polymers",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: Molecular Recognition',
                'content' => "Which event involves breaking and forming chemical bonds to produce a completely new substance?\nA) Melting a block of copper metal into liquid copper\nB) Dissolving salt in warm water\nC) The combustion of gasoline in a car engine producing CO2 and H2O\nD) Shredding paper into fine confetti\n\nCorrect Answer: C (Combustion is a profound chemical reaction!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Easy', 'text' => 'In a chemical reaction equation, what are the starting substances on the left side called? | A: Reactants B: Products C: Catalysts D: Solutes', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Easy', 'text' => 'Which of the following processes represents an irreversible chemical change? | A: Dissolving sugar in iced tea B: Melting an ice cube C: Rusting of an iron ship hull D: Cutting a piece of fabric', 'correct' => 'C'],
            ['grade' => '6', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Medium', 'text' => 'How does the process of pickling vegetables in vinegar (acetic acid) chemically prevent bacterial decay? | A: Low pH creates an acidic environment that chemically deactivates bacterial enzymes B: Vinegar freezes the vegetables C: Vinegar removes all water D: Vinegar turns vegetables into plastic', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Medium', 'text' => 'Why is galvanizing (coating iron with a layer of zinc) effective at preventing rust? | A: Zinc reacts preferentially with oxygen and shields the underlying iron from water and air B: Zinc dissolves iron C: Zinc attracts water D: Zinc turns iron into gold', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Physical and Chemical Changes', 'difficulty' => 'Hard', 'text' => 'Contrast fractional distillation of crude oil with catalytic cracking in petroleum refining. | A: Distillation is a physical separation based on boiling points; cracking is a chemical change breaking long hydrocarbon chains into smaller fuels B: Both are physical C: Both are chemical D: Distillation creates new molecules', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '6',
        'quarter' => '1',
        'lesson_number' => '3',
        'topic' => 'Mixtures and Solutions',
        'objectives' => [
            'Differentiate homogeneous mixtures (solutions) from heterogeneous mixtures (suspensions and colloids).',
            'Identify the components of a solution: solute and solvent.',
            'Explain factors affecting solubility and demonstrate the Tyndall Effect.'
        ],
        'content' => "A mixture consists of two or more substances physically combined in any proportion where each substance retains its individual chemical identity.\n\nHeterogeneous mixtures have non-uniform composition and visible distinct phases (e.g., fruit salad, oil and water, muddy water).\n\nHomogeneous mixtures (solutions) have a completely uniform appearance throughout. The substance that dissolves is the solute; the dissolving medium is the solvent (water is called the 'universal solvent').\n\nColloids have intermediate particle sizes that do not settle out and scatter beams of light (Tyndall Effect), such as milk, mayonnaise, and fog. Suspensions have large particles that eventually settle to the bottom if left undisturbed, such as calamine lotion or river sediment.\n\nSolubility factors: Solutes dissolve faster with heating (for solids), stirring (agitation), and crushing (reducing particle size).",
        'slides' => [
            [
                'title' => 'Mixtures and Solutions',
                'content' => "Grade 6 — Term 1 Science\n\nClassify matter: homogeneous solutions, heterogeneous mixtures, colloids, and suspensions.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Heterogeneous vs Homogeneous',
                'content' => "The Big Division:\n• Heterogeneous: Non-uniform; visible separate phases\n  - Examples: Halo-halo, oil and vinegar, granite, salad\n• Homogeneous (Solutions): Uniform throughout; single visible phase\n  - Solute: Substance dissolved (lesser amount, e.g., salt, sugar)\n  - Solvent: Dissolving medium (greater amount, e.g., water, alcohol)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'The Spectrum: Solution, Colloid, Suspension',
                'content' => "Particle Size Comparison:\n• Solution (<1 nm): Salt water, air, brass (never settles, clear)\n• Colloid (1–1000 nm): Milk, gelatin, fog, mayonnaise (does not settle, cloudy)\n• Suspension (>1000 nm): Muddy water, calamine, cough syrup (settles upon standing, must shake before use)",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Shine flashlight through water (invisible beam)\nStep 2: Shine flashlight through salt solution (invisible beam)\nStep 3: Shine flashlight through diluted milk (bright beam!)\nStep 4: Confirm Tyndall Effect in colloid"
            ],
            [
                'title' => 'Factors Affecting Rate of Dissolving',
                'content' => "How to speed up solid solute dissolution:\n1. Temperature: Increases molecular kinetic collisions\n2. Agitation (Stirring): Brings fresh solvent molecules into contact with solute\n3. Surface Area (Crushing): Exposes more solute particles to solvent",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Quick Quiz: The Tyndall Effect',
                'content' => "A teacher shines a laser pointer through two beakers. In Beaker A, the beam is invisible. In Beaker B, the beam glows brightly through the liquid. What are Beakers A and B?\nA) Beaker A is a colloid; Beaker B is a true solution\nB) Beaker A is a true solution; Beaker B is a colloid showing the Tyndall Effect\nC) Both are suspensions\nD) Both are pure water\n\nCorrect Answer: B (Colloidal particles scatter light beams!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Mixtures and Solutions', 'difficulty' => 'Easy', 'text' => 'In a sugar-water solution, what do we call the sugar and what do we call the water? | A: Sugar is the solute; Water is the solvent B: Sugar is solvent; Water is solute C: Both are solutes D: Both are suspensions', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Mixtures and Solutions', 'difficulty' => 'Easy', 'text' => 'Which of the following is considered a homogeneous mixture? | A: Freshly brewed clear tea B: Fruit salad C: Oil and water mixture D: Sand in water', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Mixtures and Solutions', 'difficulty' => 'Medium', 'text' => 'What is the Tyndall Effect, and how does it differentiate a true solution from a colloid? | A: The scattering of light beams by colloidal particles, which does not happen in true solutions B: The settling of heavy solids C: The evaporation of liquids D: The change in temperature', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Mixtures and Solutions', 'difficulty' => 'Medium', 'text' => 'Why does a powdered sugar crystal dissolve much faster in hot tea than an equal mass rock candy crystal in iced tea? | A: Higher temperature increases kinetic collisions and powdered sugar has far greater surface area B: Hot tea has less volume C: Rock candy is insoluble in water D: Cold tea repels sugar molecules', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Mixtures and Solutions', 'difficulty' => 'Hard', 'text' => 'Classify human blood: why is it considered both a solution, a colloid, and a suspension? | A: Blood plasma is a solution of dissolved salts/glucose, contains colloidal albumin proteins, and suspends red/white blood cells that settle if centrifuged B: Blood is purely a solid C: Blood has no water D: Blood cannot be separated', 'correct' => 'A']
        ]
    ],
    [
        'grade' => '6',
        'quarter' => '1',
        'lesson_number' => '4',
        'topic' => 'Separation of Mixtures',
        'objectives' => [
            'Explain principles behind physical separation methods: filtration, decantation, evaporation, magnetic separation, sieving, and distillation.',
            'Select and execute appropriate separation techniques based on the physical properties of mixture components.',
            'Apply separation processes to municipal water purification and scrap metal recycling.'
        ],
        'content' => "Because substances in a mixture are only physically combined, they can be separated back into pure components using differences in their physical properties (particle size, density, boiling point, magnetic attraction).\n\nTechniques:\n- Decantation: Pouring off a less dense liquid layer from a settled denser sediment or immiscible liquid (e.g., oil from water).\n- Filtration: Passing a mixture through a porous barrier (filter paper) to trap insoluble solid residue while liquid filtrate passes through.\n- Evaporation: Heating a solution until the liquid solvent vaporizes into air, leaving solid solute crystals behind (e.g., harvesting sea salt).\n- Magnetic Separation: Using magnets to extract magnetic materials (iron, nickel, cobalt) from non-magnetic mixtures (e.g., scrap metal sorting).\n- Sieving & Winnowing: Separating particles by size using a mesh screen, or by density using wind currents (e.g., separating rice grain from chaff).\n- Distillation: Vaporizing a liquid and condensing its vapor back into pure liquid in a separate container, separating liquids with different boiling points or purifying freshwater from saltwater.\n\nReal-world applications: Water treatment plants use screening, sedimentation, filtration, and chlorination to provide safe drinking water to cities.",
        'slides' => [
            [
                'title' => 'Separation of Mixtures',
                'content' => "Grade 6 — Term 1 Science\n\nTechniques for purifying matter: filtration, evaporation, decantation, distillation, and magnetic separation.",
                'slide_type' => 'title',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Common Separation Techniques',
                'content' => "Exploiting physical differences:\n• Decantation: Pouring off top liquid from settled sediment (density difference)\n• Filtration: Using filter paper to trap solid residue from liquid filtrate (particle size)\n• Evaporation: Heating solution to leave dry solute behind (boiling point difference)\n• Magnetic Separation: Pulling out iron/steel with magnets (magnetic attraction)",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Distillation: Collecting the Solvent',
                'content' => "How simple distillation works:\n1. Boiling flask heats saltwater solution\n2. Water vapor rises, leaving salt behind\n3. Steam passes through water-cooled condenser\n4. Pure condensed liquid water drips into receiving flask\n• Used to desalinate ocean water and produce distilled water!",
                'slide_type' => 'step',
                'media_type' => 'step',
                'media_url' => "Step 1: Heat saltwater to boiling (100°C)\nStep 2: Steam travels into Liebig condenser\nStep 3: Cold water cools steam back to liquid\nStep 4: Collect pure distilled freshwater"
            ],
            [
                'title' => 'Municipal Water Treatment Plants',
                'content' => "How river water becomes safe tap water:\n• Coagulation & Flocculation: Alum binds dirt particles together\n• Sedimentation: Heavy flocs settle to tank floor\n• Sand/Charcoal Filtration: Traps microscopic suspended particles\n• Chlorination: Kills harmful bacteria and pathogens",
                'slide_type' => 'content',
                'media_type' => 'none',
                'media_url' => ''
            ],
            [
                'title' => 'Classroom Separation Challenge',
                'content' => "How would you separate a mixture of sand, salt, and iron filings?\nA) Heat it until everything burns\nB) 1: Magnet extracts iron; 2: Add water to dissolve salt; 3: Filter out sand; 4: Evaporate water to recover salt\nC) Throw it away\nD) Freeze it into ice\n\nCorrect Answer: B (A logical 4-step physical separation protocol!)",
                'slide_type' => 'quiz',
                'media_type' => 'none',
                'media_url' => ''
            ]
        ],
        'questions' => [
            ['grade' => '6', 'topic' => 'Separation of Mixtures', 'difficulty' => 'Easy', 'text' => 'Which separation method is used to harvest solid sea salt from ocean seawater? | A: Evaporation B: Magnetic separation C: Decantation D: Freezing', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Separation of Mixtures', 'difficulty' => 'Easy', 'text' => 'How can scrap metal recycling facilities quickly separate iron cans from aluminum cans? | A: By using large industrial electromagnets B: By melting all cans C: By hand picking D: By floating them in water', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Separation of Mixtures', 'difficulty' => 'Medium', 'text' => 'What is the main difference between evaporation and simple distillation of saltwater? | A: Evaporation loses water vapor to the air; distillation condenses and collects the pure water B: Distillation produces toxic gas C: Evaporation requires zero heat D: Distillation only works on solids', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Separation of Mixtures', 'difficulty' => 'Medium', 'text' => 'Explain why pouring muddy water through filter paper leaves clean filtrate while the mud stays on top. | A: Mud particles are larger than the microscopic pores in filter paper, while water molecules pass through B: Paper dissolves mud C: Paper is magnetic D: Mud turns into air', 'correct' => 'A'],
            ['grade' => '6', 'topic' => 'Separation of Mixtures', 'difficulty' => 'Hard', 'text' => 'Formulate a 4-step protocol to completely separate a dry mixture of iron filings, table salt, sawdust, and small pebbles without destroying any component. | A: 1: Magnet removes iron; 2: Sieve removes pebbles; 3: Add water so sawdust floats (skim off); 4: Filter sand and evaporate saltwater to get pure salt B: Burn the mixture C: Dissolve everything in acid D: Throw into ocean', 'correct' => 'A']
        ]
    ]
];

echo "<h2>🧪 Seeding Matter and Materials Curriculum across Grades 3 to 6...</h2>\n";

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

foreach ($matterUnits as $unit) {
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

echo "<p>✅ <strong>Matter & Materials Curriculum Seeding Complete!</strong></p>\n";
echo "<ul>\n";
echo "<li>Curriculum Lessons Processed: " . count($matterUnits) . " (16 units across Grades 3 to 6)</li>\n";
echo "<li>Interactive Lesson Slides Populated: $slidesInserted (5 slides per unit)</li>\n";
echo "<li>Question Bank Recitations Seeded: $questionsInserted</li>\n";
echo "<li>Topic Registry Entries Added: $topicsRegistered</li>\n";
echo "</ul>\n";

if (php_sapi_name() === 'cli') {
    echo "Summary:\n";
    echo "- 16 Matter & Materials Lessons active across Grades 3-6\n";
    echo "- $slidesInserted Lesson Slides seeded\n";
    echo "- $questionsInserted Questions added to bank\n";
    echo "- Database: " . (defined('DB_ENGINE') ? DB_ENGINE : 'Connected') . "\n";
}
?>
