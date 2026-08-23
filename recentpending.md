# ILikeSci — Recent Changes & System Focus

## ✅ Session 8 — May 17, 2026

### Core System Focus (from Ma'am's Feedback)

The **PRIMARY focus** of ILikeSci is:
- **Interactive Multimedia and Simulations** — videos, animations, simulations (plant growth, simple machines, weather systems)
- **Teacher-centered instruction** — classroom presentation tool
- **Interactive visual teaching support** — replaces/upgrades traditional PowerPoint
- **Curriculum-based Science lessons (Grades 4–6)** — structured, aligned to DepEd K-12
- **Science videos and animations** — to address lack of interest, limited materials, difficulty understanding

The system addresses these problems:
1. Lack of interest in science
2. Limited learning materials
3. Difficulty understanding concepts
4. Lack of experiments

**Recitation is an ADDITIONAL feature, not the core.** The core is interactive lesson delivery.

---

### Changes Made This Session

| # | File | Change |
|---|------|--------|
| 1 | **`scoreboard.html`** | **REBUILT** — Sheet-style table with clickable column headers, ascending/descending sort toggle, section filter, performance insight cards (Active/Passive/Zero participation), participation bar visualizations, status badges |
| 2 | **`app.js` — `renderScoreboard()`** | **REBUILT** — Supports sort by name/grade/section/points/recitations/proficiency, ascending/descending direction, section filter, calculates active/passive/zero counts, participation bar widths |
| 3 | **`students.html`** | Added sort dropdown (Name/Grade/Points/Recitations) + ascending/descending toggle button |
| 4 | **`app.js` — `renderStudents()`** | Added sorting support — reads sort field and direction from page, shows recitation count in cards |
| 5 | **`records.html`** | Added ascending/descending toggle button to existing sort controls |
| 6 | **`app.js` — `renderRecords()`** | Updated sort logic to support ascending/descending direction via `getRecordsSortDir()` |
| 7 | **`pwabuilder-adv-sw.js`** | Bumped to **v11** |

### Sorting Available On Every Page

| Page | Sort Fields | Direction Toggle |
|------|-------------|-----------------|
| **Scoreboard** | Points, Name, Grade, Section, Recitations, Proficiency | ✅ Asc/Desc button |
| **Students** | Name, Grade, Points, Recitations | ✅ Asc/Desc button |
| **Records** | Transmuted Grade, Points, Recitations, Name | ✅ Asc/Desc button |
| **Admin Users** | Table columns (via admin_api) | Server-side |

### Participation Tracking Features (Scoreboard)

- **Performance Insight Cards**: Total Students, Total Recitations, Active (≥3), Passive (<3), Never Participated
- **Fairness Alert**: Banner showing students who haven't participated
- **Status Column**: Active ✅ / Low ⚠️ / None ❌ per student
- **Participation Bar**: Visual bar showing relative participation level
- **Suggest Next Student**: Recommends least-participated student

---

### Previous Sessions Summary

- **Session 7** — Per-user profiles (DB-stored avatars), admin-only panel, PPTX import & playback
- **Session 6** — Full admin panel rebuild (DB-driven user CRUD, tabbed dashboard)
- **Session 5** — Canva-style lesson presenter, app.js cleanup
- **Session 4** — Curriculum import, question bank API
- **Session 3** — DB authentication, init_db schema
