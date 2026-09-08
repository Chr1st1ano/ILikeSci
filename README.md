# ILikeSci — Interactive Science & Classroom Learning Engine

> **A High-Performance, Hybrid Offline/Online Science Learning Platform for Elementary Classrooms (Grades 3–6 & Junior High)**  
> Aligned with the Philippine Department of Education (DepEd) K-12 Science Curriculum. Designed for ultra-low RAM footprint, instant execution, and zero-configuration portable operation.

---

## 🚀 Highlights & Features

- **Dual-Engine Portability**: Automatically runs on standalone **SQLite** (`ilikesci_db.sqlite`) with zero external software required, or seamlessly connects to **XAMPP MySQL** (port 3306) when available.
- **Low-End Hardware Optimization**: Enforces SQLite Write-Ahead Logging (`WAL`) mode with memory caching and indexed foreign keys (average query latency: **0.014 ms / query**).
- **Interactive Slide Presenter**: Fullscreen lesson presenter with live drawing annotations, slide thumbnails, and laser pen.
- **DepEd Order No. 8, s. 2015 E-Class Records**: Built-in automated calculation and live score transmutation for Science (**Written Work 40% | Performance Tasks 40% | Quarterly Assessment 20%**).
- **Gamified Classroom Recitation**: Interactive student recitation tracker with randomizer wheel and real-time score calculation.
- **Offline HTML5 Canvas Laboratory**: Native 60fps simulations (Photosynthesis, States of Matter, Circuit Builder, Ecosystem Food Webs).
- **100% Offline MIDI Audio Engine**: Built-in Web Audio synthesizer with 15 DepEd curriculum science songs.
- **PWA & Tablet Ready**: Installable Progressive Web App with offline service worker caching.

---

## ⚡ Quick Start

### Option A: Zero-Install Portable Launcher (Recommended)
Double-click:
```bat
start_ilikesci.bat
```
This automatically finds the local PHP binary, initializes the database, and launches the application at:
👉 **http://127.0.0.1:8000**

### Option B: Standalone Server Control Panel
Double-click:
```bat
standalone_control_panel.bat
```
Provides an interactive menu to start/stop the server, run diagnostics, and initialize database seeders.

### Option C: Traditional XAMPP (Apache + MySQL)
1. Copy the folder to `xampp/htdocs/ILikeSci`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/ILikeSci/index.html`.

---

## 🔑 Default Credentials

| Role | Username | Password | Access Level |
|---|---|---|---|
| **Administrator** | `oyo` | `admin123` | Full System & Database Admin |
| **Teacher** | `tine` | `teacher123` | Classroom & Grading Sheet |

*(New teacher accounts can also be created via `signup.html`)*

---

## 📂 Core Navigation & Modules

| Module | URL / Path | Description |
|---|---|---|
| **Dashboard** | `index.html` | Learning ladder, grade filters (3–6), and quick-access launcher |
| **Lesson Presenter** | `presenter.html?lesson=1` | Slide presentations with laser pointer and timer |
| **Lesson Builder** | `lessons.html` | Create, customize, and deliver slide decks |
| **Recitation & Quiz** | `assessment.html` | Flash quiz, random student wheel, rubric evaluation |
| **Student Roster** | `students.html` | Manage student roster, sections, and bulk name import |
| **DepEd Records** | `records.html` | E-Class grading sheet with official DepEd transmutation |
| **Simulations** | `multimedia.html` | Offline HTML5 Canvas interactive lab simulations |
| **Leaderboard** | `scoreboard.html` | Class rankings, participation metrics, and export |
| **Admin Suite** | `admin.html` | User management, system health, and database metrics |
| **TV Classroom Mode** | `tv_display.html` | Big-screen classroom display for projector/smart TV |

---

## 🧪 Testing & Verification

Run the automated test suite directly via CLI:
```bash
php test_production_readiness.php
```
*Executes all 28 automated database, security, API, DepEd transmutation, and performance checks.*

Run the lesson builder automation suite:
```bash
python test_lesson_builder.py
```

Run the curriculum MIDI audio test suite:
```bash
python test_midi_validation.py
```
