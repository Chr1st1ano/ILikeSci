# ILikeSci — Master Development Changelog & Functional Manual (`workme.md`)

> **Interactive Web-Based Science Teaching Material System**  
> Capstone Project — Laguna State Polytechnic University, San Pablo City Campus  
> By: Abril, Millera, Olidan | Updated: September 2026

---

## 📋 Comprehensive Session Summary

### 🌿 Session 14 — September 3, 2026 (DepEd MATATAG "Life Science" Curriculum Suite & Presentation Decks)

#### 1. Official 21-Unit "Life Science" Curriculum Integration
- **Coverage:** Grades 3, 4, 5, and 6 (MATATAG / K-12 Elementary Science Domain: Life Science).
- **Files Created/Modified:** [`seed_life_science.php`](file:///c:/Games/xampp/htdocs/ILikeSci/seed_life_science.php), [`export_life_science_json.php`](file:///c:/Games/xampp/htdocs/ILikeSci/export_life_science_json.php), [`seed_data.php`](file:///c:/Games/xampp/htdocs/ILikeSci/seed_data.php), [`init_db.php`](file:///c:/Games/xampp/htdocs/ILikeSci/init_db.php).
- **21 Curriculum Units Populated:**
  - **Grade 3 (Term 1 & Term 2 — 6 Units):**
    1. *Scientific Inquiry in Life Science* (G3 T1)
    2. *Characteristics and Life Processes of Living Things* (G3 T1)
    3. *Basic Needs of Living Things* (G3 T2)
    4. *Structure and Function of Organisms* (G3 T2)
    5. *Interactions Among Living Things and Their Environment* (G3 T2)
    6. *Environmental Stewardship and Conservation* (G3 T2)
  - **Grade 4 (Term 1, Term 2 & Term 3 — 7 Units):**
    7. *Systems in Animals and Plants* (G4 T1)
    8. *Plant and Animal Habitats* (G4 T1)
    9. *Life Cycles of Plants and Animals* (G4 T2)
    10. *Animals and the Food They Eat* (G4 T2)
    11. *Food Chains* (G4 T2)
    12. *Water and Living Things* (G4 T3)
    13. *Soil and Plant Growth* (G4 T3)
  - **Grade 5 (Term 1 & Term 2 — 4 Units):**
    14. *Human Body Systems (Digestive, Respiratory, Reproductive System)* (G5 T1)
    15. *Classification and Reproduction of Living Things* (G5 T1)
    16. *Life Cycles of Living Things* (G5 T1)
    17. *Plant and Animal Adaptations* (G5 T2)
  - **Grade 6 (Term 1 & Term 2 — 4 Units):**
    18. *Human Body Systems (Circulatory and Nervous Systems)* (G6 T1)
    19. *Reproduction in Plants* (G6 T1)
    20. *Vertebrates and Invertebrates* (G6 T1)
    21. *Ecosystem Relationships (Food Webs, Interaction Among Living Things, Biotic and Abiotic Factors in an Ecosystem)* (G6 T2)

#### 2. Interactive Presentation Slide Decks & Tiered Question Banks
- **105 Presentation Slides (5 per unit):** Structured with Title cards, Core Concepts, Procedural/Lab Steps (`slide_type: step`), Real-World Science Applications, and Interactive Class Check Quizzes (`slide_type: quiz`).
- **105+ Tiered Recitation Questions:** Categorized into Easy, Medium, and Hard difficulty levels with choices, feedback, and oral recitation prompts.
- **Presenter Mode Synchronization:** Seamlessly accessible in [`presenter.html`](file:///c:/Games/xampp/htdocs/ILikeSci/presenter.html) with full speech synthesis, laser pointer, and quiz modals.

#### 3. Standalone JSON Export Packages (Offline Import Ready)
- **Directory:** [`exports/`](file:///c:/Games/xampp/htdocs/ILikeSci/exports/)
- **Packages Created:**
  - `material_life_science_complete.json`: Full 21-unit master package (122KB, 21 lessons, 105 questions).
  - `material_g3_life_science.json`: Grade 3 package (6 lessons, 30 questions).
  - `material_g4_life_science.json`: Grade 4 package (7 lessons, 35 questions).
  - `material_g5_life_science.json`: Grade 5 package (4 lessons, 20 questions).
  - `material_g6_life_science.json`: Grade 6 package (4 lessons, 20 questions).

---

### 🧪 Session 13 — September 3, 2026 (DepEd MATATAG "Matter and Materials" Curriculum Suite & Presentation Decks)

#### 1. Official 16-Unit "Matter & Materials" Curriculum Integration
- **Coverage:** Grades 3, 4, 5, and 6 (MATATAG / K-12 Elementary Science Domain: Matter and Materials).
- **Files Created/Modified:** [`seed_matter_materials.php`](file:///c:/Games/xampp/htdocs/ILikeSci/seed_matter_materials.php), [`export_matter_json.php`](file:///c:/Games/xampp/htdocs/ILikeSci/export_matter_json.php), [`seed_data.php`](file:///c:/Games/xampp/htdocs/ILikeSci/seed_data.php), [`init_db.php`](file:///c:/Games/xampp/htdocs/ILikeSci/init_db.php), [`db.php`](file:///c:/Games/xampp/htdocs/ILikeSci/db.php), [`lessons_api.php`](file:///c:/Games/xampp/htdocs/ILikeSci/lessons_api.php).
- **Curriculum Units Populated:**
  - **Grade 3 (Term 1 & Term 3):**
    1. *Properties and Uses of Materials* (G3 T1)
    2. *Changes in Materials and Environmental Responsibility* (G3 T1)
    3. *Earth Materials and Their Uses* (G3 T3)
  - **Grade 4 (Term 1):**
    4. *Physical Properties of Materials* (G4 T1)
    5. *Chemical Properties of Materials* (G4 T1)
    6. *Effect of Temperature on Materials* (G4 T1)
    7. *Physical and Chemical Changes* (G4 T1)
    8. *Responsible Use and Management of Materials* (G4 T1)
  - **Grade 5 (Term 1):**
    9. *Properties of Matter* (G5 T1)
    10. *States of Matter* (G5 T1)
    11. *Changes in Matter* (G5 T1)
    12. *Scientific Investigation of Matter* (G5 T1)
  - **Grade 6 (Term 1):**
    13. *Changes in Matter* (G6 T1)
    14. *Physical and Chemical Changes* (G6 T1)
    15. *Mixtures and Solutions* (G6 T1)
    16. *Separation of Mixtures* (G6 T1)

#### 2. Interactive Presentation Slide Decks & Tiered Question Banks
- **80 Presentation Slides (5 per unit):** Formatted with Title, Core Concepts, Procedural/Lab Steps (`slide_type: step`), Real-World Science Applications, and Interactive Class Check Quizzes (`slide_type: quiz`).
- **80+ Tiered Recitation Questions:** Categorized into Easy, Medium, and Hard difficulty levels with choices, feedback, and oral recitation prompts.
- **Presenter Mode Synchronization:** Natively loads into [`presenter.html`](file:///c:/Games/xampp/htdocs/ILikeSci/presenter.html) with speech synthesis, laser pointer, and in-presentation quiz modals.

#### 3. Standalone JSON Export Packages (Offline Import Ready)
- **Directory:** [`exports/`](file:///c:/Games/xampp/htdocs/ILikeSci/exports/)
- **Packages Created:**
  - `material_matter_and_materials_complete.json`: Full 16-unit master curriculum package (95KB, 0 external network requests).
  - `material_g3_matter.json`: Grade 3 unit package (3 lessons, 15 questions).
  - `material_g4_matter.json`: Grade 4 unit package (5 lessons, 25 questions).
  - `material_g5_matter.json`: Grade 5 unit package (4 lessons, 20 questions).
  - `material_g6_matter.json`: Grade 6 unit package (4 lessons, 20 questions).
- **1-Click Import:** Ready for direct drag-and-drop into [`materials.html`](file:///c:/Games/xampp/htdocs/ILikeSci/materials.html).

---

### 🎵 Session 12 — September 2, 2026 (Science Curriculum MIDI Suite & Offline Web Audio Synthesizer)

#### 1. DepEd Science Curriculum Standard MIDI Library
- **Directory:** [`audio/`](file:///c:/Games/xampp/htdocs/ILikeSci/audio/)
- **15 Authentic Standard MIDI Files (SMF Format 1):**
  - **Grade 3:** `living_things_song.mid` (Needs of Living Organisms), `matter_states_song.mid` (Solid, Liquid, Gas), `five_senses_song.mid` (The Five Senses).
  - **Grade 4:** `photosynthesis_song.mid` (Photosynthesis & Chlorophyll), `water_cycle_song.mid` (Water Cycle & Precipitation), `force_and_machines_song.mid` (Forces & Simple Machines), `sun_and_earth_song.mid` (Earth Rotation, Daylight & Seasons).
  - **Grade 5:** `electricity_circuits_song.mid` (Electricity & Circuits), `human_body_song.mid` (Human Body Organ Systems), `solar_system_song.mid` (8 Planets & The Sun).
  - **Grade 6:** `mixtures_solutions_song.mid` (Mixtures & Solutions), `vertebrates_invertebrates_song.mid` (Animal Kingdom & Vertebrates), `motion_and_energy_song.mid` (Speed, Velocity & Newton's Laws), `volcanoes_and_earth_song.mid` (Volcanoes & Earth Geology).
  - **Flagship Theme:** `ilikesci_theme.mid` (Official ILikeSci Science Explorer Anthem).

#### 2. Native Offline Web Audio MIDI Synthesizer Engine
- **File Created:** [`midi_player.js`](file:///c:/Games/xampp/htdocs/ILikeSci/midi_player.js)
- **Features:**
  - 100% Offline SMF Type 0 & 1 binary parser (decodes `MThd`, `MTrk`, VLQ delta-times, Note-On/Off, tempos, program changes).
  - Procedural polyphonic Web Audio API oscillator generator with ADSR volume envelopes, low-pass filter acoustic shaping, dynamic compressor, and low-end hardware voice capping (<20KB footprint, 0 external network requests).
  - Real-time HTML5 Canvas musical frequency / note visualizer.
  - Scrub slider, Play/Pause/Stop controls, variable tempo speed modifier (`0.75x`, `1.0x`, `1.25x`, `1.5x`), and volume adjustment.

#### 3. Multimedia Hub & Curriculum Audio Testing Suite
- **Files Modified/Created:** [`multimedia.html`](file:///c:/Games/xampp/htdocs/ILikeSci/multimedia.html), [`test_curriculum_midi.html`](file:///c:/Games/xampp/htdocs/ILikeSci/test_curriculum_midi.html), [`generate_curriculum_midi.py`](file:///c:/Games/xampp/htdocs/ILikeSci/generate_curriculum_midi.py), [`test_midi_validation.py`](file:///c:/Games/xampp/htdocs/ILikeSci/test_midi_validation.py)
- **Features:**
  - Curriculum Grade filter pills (`All`, `Grade 3`, `Grade 4`, `Grade 5`, `Grade 6`, `Anthem & Warmups`).
  - Curriculum song cards with BPM metadata, .MID download buttons, and curriculum learning concepts.
  - Dedicated offline testing suite with batch diagnostics verifying 100% SMF compliance across all 15 songs.

---

### 🔬 Session 11 — September 2, 2026 (Major Interactivity & Functionality Overhaul)

#### 1. Native Offline Interactive Science Simulations Engine
- **File Created:** [`simulations.js`](file:///c:/Games/xampp/htdocs/ILikeSci/simulations.js)
- **Target View:** [`multimedia.html`](file:///c:/Games/xampp/htdocs/ILikeSci/multimedia.html)
- **Features Implemented:**
  - 100% Offline 60fps HTML5 Canvas physics and chemical simulators (zero CDN or external iframe requirements).
  - **Photosynthesis & Plant Growth Lab:** Dynamic sunlight intensity, water moisture, and $\text{CO}_2$ sliders with live oxygen bubbling, stomata gas exchanges, and plant growth stages.
  - **Simple Machines Playground:** Levers (Classes 1, 2, 3), Inclined Planes (Ramps with friction), and Pulley systems (Fixed, Movable, Compound) with real-time Mechanical Advantage ($MA$) calculation.
  - **States of Matter & Molecular Physics Simulator:** Real-time particle lattice vibration (Solids), fluid flow (Liquids), and high-velocity collisions (Gases) with adjustable thermal burner ($^\circ\text{C}$ & $\text{K}$).
  - **Water Cycle & Weather Lab:** Evaporation particle streams, cloud condensation density, rainfall precipitation, and surface runoff cycles.
  - **Electric Circuit Explorer:** DC voltage battery, switch toggle, lightbulb illumination, electron animation, and Ammeter Ohm's law ($I = V/R$) readouts.

#### 2. Web Audio API Procedural Sound Synthesizer
- **File Modified:** [`app.js`](file:///c:/Games/xampp/htdocs/ILikeSci/app.js) (`playChime(type)`)
- **Sound Profiles:**
  - `correct`: Ascending C Major Triad chord chime ($C_5 \to E_5 \to G_5 \to C_6$).
  - `wrong`: Low dual-tone sawtooth buzzer ($150\text{Hz} \to 110\text{Hz}$).
  - `fanfare`: Triumphant 6-note brass chord for student wheel winner and game victories.
  - `coin`: Rapid high-frequency pickup chirp for quick points modification.
  - `tick`: Woodblock tap for countdown timers.
  - `buzzer`: Dual-pulse acoustic alarm for activity timeouts.
- **Resource Footprint:** 0 downloaded audio assets, 0ms network latency.

#### 3. Gamification & Kinesthetic Classroom Engine
- **File Modified:** [`games.html`](file:///c:/Games/xampp/htdocs/ILikeSci/games.html)
- **4 Pics 1 Word (Science Edition):**
  - 25+ Science vocabulary words across Grades 3 to 6 (*Matter, Atom, Gravity, Magnet, Fossil, Ecosystem, Circuit, Volcano, Glacier, Energy, Solute, Conductor, Cell, Galaxy, Light, Heat, Water, Plant, Sun, Force, etc.*).
  - Interactive 12-key scrambled letter tile picker keyboard, letter slot animations, hint system, backspace, and direct recitation point awards (`+3 pts`).
- **Science Team Groupings Generator:**
  - Automatic class roster shuffle into 2 to 6 balanced science teams (*The Protons ⚡, The Neutrons 🛡️, The Supernovas 🌟, The Chloroplasts 🍃, The Dynamos ⚙️, The Asteroids ☄️*).
  - Interactive activity countdown timer with live buzzer and TV broadcast capability.

#### 4. Presenter Mode Upgrades & Teacher Tools
- **File Modified:** [`presenter.html`](file:///c:/Games/xampp/htdocs/ILikeSci/presenter.html)
- **Floating Annotation Toolbar:**
  - **Laser Pointer (`L` key):** Glowing red dot tracking mouse and touch pointers across slides.
  - **Highlighter Pen (`P` key):** Transparent drawing overlay allowing teachers to underline and circle slide elements.
  - **Clear (`C` key):** Instantly clears slide annotations.
- **Quick Class Quiz (`Q` key):** Modal to fetch oral recitation questions from the database matching the current topic without exiting presentation mode.
- **Offline Voice Readout / Speech Synthesis (`V` key):** Native Web Speech API integration to read slide titles and content aloud to young elementary learners.
- **Keyboard Shortcuts Help Modal (`?` or `H` key).**

#### 5. Scoreboard & Student Management Enhancements
- **Files Modified:** [`scoreboard.html`](file:///c:/Games/xampp/htdocs/ILikeSci/scoreboard.html), [`students.html`](file:///c:/Games/xampp/htdocs/ILikeSci/students.html), [`app.js`](file:///c:/Games/xampp/htdocs/ILikeSci/app.js)
- **Scoreboard Live Search & Inline Quick Point Buttons:**
  - Added instant student search filtering.
  - Added inline point modifier buttons (`+1`, `+3`, `+5`, `-1`) with instant leaderboard re-ranking and database synchronization.
  - DepEd report print button (`window.print()`).
- **Bulk Student Roster Importer:**
  - Allows pasting full class rosters (one name per line, e.g. from DepEd SF1 or Excel) to batch create student accounts in 1-click.
- **Reset Quarter Scores:**
  - Safely resets recitation counts for a new grading term while preserving student profiles.

#### 6. Materials & Question Bank Expansion
- **File Modified:** [`materials.html`](file:///c:/Games/xampp/htdocs/ILikeSci/materials.html)
- **Seed Science Questions:** Auto-populates standard DepEd elementary science curriculum questions across Grades 3, 4, 5, and 6.
- **Export Question Bank:** One-click JSON export for offline backup and teacher sharing.

#### 8. Standalone XAMPP Suite & ILaykSay Testing Instance
- **Testing Instance Created:** `c:\Games\xampp\htdocs\ILaykSay`
- **Isolated Testing Database:** `ilayksay_db` (MySQL) & `ilayksay_db.sqlite` (SQLite fallback)
- **Standalone Launchers Created:**
  - [`standalone_control_panel.bat`](file:///c:/Games/xampp/htdocs/ILikeSci/standalone_control_panel.bat): Interactive master control panel menu to start, stop, diagnose, and seed both instances.
  - [`start_ilikesci.bat`](file:///c:/Games/xampp/htdocs/ILikeSci/start_ilikesci.bat): Starts ILikeSci (Main System) on Port **8000** (`http://127.0.0.1:8000`).
  - [`start_ilayksay.bat`](file:///c:/Games/xampp/htdocs/ILikeSci/start_ilayksay.bat): Starts ILaykSay (Testing Instance) on Port **8088** (`http://127.0.0.1:8088`).
  - [`start_both_instances.bat`](file:///c:/Games/xampp/htdocs/ILikeSci/start_both_instances.bat): Boots both servers concurrently for parallel testing.

---

## 🛠️ Complete File Inventory & Upgrade Status

| # | File Path | Component | Upgrade Summary | Status |
|---|-----------|-----------|-----------------|--------|
| 1 | `simulations.js` | Core Engine | 5 HTML5 Canvas Interactive Science Laboratories | ✅ |
| 2 | `multimedia.html` | UI / Views | Integrated Native Lab Modal & 5 Simulation Cards | ✅ |
| 3 | `app.js` | Logic / State | Audio synthesizer, quick points, speech synthesis, bulk import | ✅ |
| 4 | `games.html` | Gamification | 25+ Science words 4 Pics 1 Word & Team Groupings Generator | ✅ |
| 5 | `presenter.html` | Presentation | Laser pointer, highlighter pen, quick quiz modal, voice readout | ✅ |
| 6 | `assessment.html` | Evaluation | Question timer bar, choice cards, 6-item DepEd safety rubric | ✅ |
| 7 | `scoreboard.html` | Scoring | Real-time search filter, inline quick buttons (`+1`, `+3`, `+5`, `-1`) | ✅ |
| 8 | `students.html` | Management | Bulk student roster import modal, quarter reset button | ✅ |
| 9 | `materials.html` | Question Bank | Question seeder, JSON export, speech readout | ✅ |
| 10 | `records.html` | DepEd Records | E-Class Record XLSX import/export, print report styling | ✅ |
| 11 | `index.html` | Dashboard | Expanded Quick Access Cards & responsive learning ladder | ✅ |
| 12 | `styles.css` | Stylesheet | Simulation layouts, letter tiles, toolbar, low-end performance tokens | ✅ |
| 13 | `db.php` | Database | Resilient MySQL connection + SQLite fallback | ✅ |
| 14 | `sync_xampp.php` | Diagnostics | One-click XAMPP & system health diagnostic tool | ✅ |
| 15 | `start_ilikesci.bat` | Launcher | Zero-install ~18MB RAM portable launcher (Port 8000) | ✅ |
| 16 | `start_ilayksay.bat` | Launcher | Zero-install portable launcher for test instance (Port 8088) | ✅ |
| 17 | `start_both_instances.bat` | Launcher | Concurrently launches ILikeSci + ILaykSay side-by-side | ✅ |
| 18 | `standalone_control_panel.bat` | Control Panel | Interactive command-line server dashboard | ✅ |
| 19 | `workme.md` | Documentation | Master development changelog and manual | ✅ |

---

## 🚀 Low-End Hardware & Multi-Instance Deployment Guide

### Option A: Standalone Multi-Instance Mode (Dual Testing)
1. Double-click [`standalone_control_panel.bat`](file:///c:/Games/xampp/htdocs/ILikeSci/standalone_control_panel.bat) or [`start_both_instances.bat`](file:///c:/Games/xampp/htdocs/ILikeSci/start_both_instances.bat).
2. **Main System (ILikeSci):** [http://127.0.0.1:8000](http://127.0.0.1:8000) (DB: `ilikesci_db`).
3. **Testing Instance (ILaykSay):** [http://127.0.0.1:8088](http://127.0.0.1:8088) (DB: `ilayksay_db`).
4. Both instances run side-by-side with total combined RAM usage of **<40 MB**!

### Option B: Traditional XAMPP Setup
1. Start Apache and MySQL in XAMPP Control Panel.
2. Open `http://localhost/ILikeSci/` for main system.
3. Open `http://localhost/ILaykSay/` for testing instance.
4. Run `http://localhost/ILikeSci/sync_xampp.php` to verify environment health.
