`# ILikeSci — Development Changelog

> Interactive Web-Based Science Teaching Material System  
> Capstone Project — Laguna State Polytechnic University, San Pablo City Campus  
> By: Abril, Millera, Olidan | May 2026

---

## Session 10 — May 19, 2026 (Current)

### 🤖 AI Integration — Smart Teaching Assistant (with Offline Fallback)

**Major feature:** AI-powered teaching assistant integrated across the platform using free-tier APIs (Groq/Google Gemini). Every AI feature falls back gracefully to existing systems when offline — AI is an enhancement layer, not a dependency.

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`ai_config.php`** | **NEW** — AI configuration: provider (Groq/Gemini), API keys, model, rate limit, language, cache TTL | ✅ |
| 2 | **`ai_api.php`** | **NEW** — Server-side AI proxy with 7 endpoints: `generate_questions`, `explain_topic`, `study_hint`, `performance_insight`, `rephrase_question`, `cache_stats`, `clear_cache`. Response caching in MySQL, rate limiting, timeout handling. | ✅ |
| 3 | **`init_db.php`** | Added `ai_cache` table (prompt hash → response) and `ai_rate_limits` table | ✅ |
| 4 | **`app.js`** | Added 13 AI functions: `checkAIStatus()`, `aiGenerateQuestions()`, `aiExplainTopic()`, `aiStudyHint()`, `aiPerformanceInsight()`, `aiRephraseQuestion()`, `showAIGenerateModal()`, `executeAIGenerate()`, `saveAIQuestions()`, `showAIHint()`, `showAIExplain()`, `showAIInsights()`. All registered in `window.app` proxy. | ✅ |
| 5 | **`materials.html`** | Added **🤖 AI Generate** button — opens modal to generate curriculum-aligned questions by grade/topic | ✅ |
| 6 | **`assessment.html`** | Added **🤖 Hint** button — after incorrect answer, generates AI-powered study hint | ✅ |
| 7 | **`presenter.html`** | Added **🤖 Explain** button + keyboard shortcut (`E`) — generates kid-friendly explanation of current slide | ✅ |
| 8 | **`records.html`** | Added **🧠 AI Insights** button — analyzes student performance data with natural language insights | ✅ |
| 9 | **`admin.html`** | Added **AI Settings** section in Settings tab — status, provider info, cache stats, test/clear buttons | ✅ |
| 10 | **`pwabuilder-adv-sw.js`** | Bumped to **v13** — `ai_api.php` covered by existing `.php → network-only` rule | ✅ |

**AI Features:**

| Feature | Where | Online | Offline Fallback |
|---------|-------|--------|-----------------|
| Question Generator | Materials | AI generates DepEd-aligned MC/ID/Enum questions | Shows existing DB questions |
| Topic Explainer | Presenter | AI creates kid-friendly explanation | Shows raw slide text |
| Study Hints | Assessment | AI explains incorrect answer | "Review this topic" generic hint |
| Performance Insights | Records | AI narrates grade analysis | Shows computed stats |
| Quiz Rephraser | API ready | AI rewords questions | Returns original text |

**How it works:**
1. Teacher clicks any 🤖 button → request sent to `ai_api.php` (PHP proxy)
2. PHP proxy calls Groq or Gemini API → gets AI response
3. Response cached in `ai_cache` table + localStorage → available offline forever
4. If API unavailable → uses cached response → if no cache → falls back to existing DB data

**Setup:** Paste your free API key in `ai_config.php` → re-run `init_db.php` → done.

**Dependencies:** None new (AI calls are standard PHP cURL to external API)

---

## Session 9 — May 19, 2026

### 📽️ PPTX Visual Slide Viewer — PowerPoint-Like Presentation Mode

**Major feature:** Uploaded `.pptx` files are now converted to PNG slide images and displayed visually in the presenter — showing actual images, text, and formatting like Microsoft PowerPoint. Previously, only extracted text was shown.

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`pptx_to_images.py`** | **NEW** — Python script that converts each PPTX slide to a 1280×720 PNG image using `python-pptx` + `Pillow`. Extracts embedded images, renders text with font/color/size, handles backgrounds, word-wraps text within shape bounds. | ✅ |
| 2 | **`pptx_api.php`** | **REBUILT** — After upload, calls `pptx_to_images.py` to generate slide images. New `?action=slide_image&id=X&slide=N` endpoint serves individual PNGs. Added `slides_dir` and `has_images` columns to `pptx_uploads`. Falls back to text extraction if Python unavailable. | ✅ |
| 3 | **`presenter.html`** | **UPDATED** — Two rendering modes: `?pptx=ID` loads slide images from `pptx_api.php` (visual mode), `?id=ID` loads text slides from `lessons_api.php` (text mode). Image thumbnails in sidebar. Preloads next 2 slides for smooth navigation. | ✅ |
| 4 | **`app.js` — PPTX functions** | **NEW** — Added 4 missing functions: `showPPTXUploadModal()`, `uploadPPTX()`, `loadPPTXList()`, `deletePPTX()`. These were referenced in `window.app` proxy but never defined. | ✅ |
| 5 | **`app.js` — `loadPPTXList()`** | Play button now uses `?pptx={upload.id}` URL (visual image mode). Shows **Visual** / **Text** badge on each presentation. | ✅ |

**Dependencies installed:** `python-pptx`, `pdf2image`, `XlsxWriter` (via pip)

**How it works:**
1. Teacher uploads `.pptx` → file saved to `uploads/pptx/`
2. Python converts each slide to PNG → images saved to `uploads/pptx/slides/{id}/`
3. Text also extracted (for search/fallback) → stored in `curriculum_lessons` + `lesson_slides`
4. Click **Play** → `presenter.html?pptx={id}` → slide images displayed with sidebar thumbnails, keyboard nav (←→), fullscreen (F), timer, progress bar

**Schema changes:**
- `pptx_uploads` — Added `slides_dir VARCHAR(255)` and `has_images TINYINT` columns (auto-migrated)

---

### 🔧 Session 9B — TV Display, Search/Sort, Multi-File, XLSX Records

**4 enhancements applied across the platform:**

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`students.html`** | Added search bar (by name), TV Mode button | ✅ |
| 2 | **`records.html`** | Added search bar, section filter, TV Mode button, **DepEd E-Class Record** section with Q1-Q4 tabs, Import/Export XLSX buttons, SheetJS CDN | ✅ |
| 3 | **`materials.html`** | Added search bar (questions), TV Mode button | ✅ |
| 4 | **`admin.html`** | Added TV Mode button to header | ✅ |
| 5 | **`lessons.html`** | Added search bar + sort dropdown (Date/Name/Grade/Slides) + asc/desc toggle to Presentations tab | ✅ |
| 6 | **`app.js` — `renderStudents()`** | Search filter from `#student-search` input | ✅ |
| 7 | **`app.js` — `renderRecords()`** | Search filter + section filter support | ✅ |
| 8 | **`app.js` — `loadPPTXList()`** | Client-side search + sort (date/name/grade/slides) + asc/desc direction | ✅ |
| 9 | **`app.js` — `showPPTXUploadModal()`** | Multi-file PPTX upload (`multiple` attribute), sequential upload with per-file progress | ✅ |
| 10 | **`app.js` — `uploadPPTX()`** | Loops through all selected files, uploads each sequentially | ✅ |
| 11 | **`xlsx_records_api.php`** | **NEW** — Backend API for `student_grades` table. CRUD + batch save + DepEd transmutation computation (WW 40%, PT 40%, QA 20%) | ✅ |
| 12 | **`init_db.php`** | Added `student_grades` table with WW/PT/QA scores (JSON), computed totals, transmuted grades, unique student-quarter constraint | ✅ |

**TV Display now available on:** `lessons.html`, `assessment.html`, `multimedia.html`, `games.html`, `scoreboard.html`, `students.html`, `records.html`, `materials.html`, `admin.html` — **ALL pages**

**Search bars added to:** Students, Records, Presentations, Materials (Question Bank)

**XLSX E-Class Record features:**
- Import DepEd `GRADE-4-6_SCIENCE.xlsx` — parses Q1-Q4 sheets, extracts student names + WW/PT/QA scores + HPS
- Auto-computes: Total → PS (Percentage Score) → WS (Weighted Score) → Initial Grade → Transmuted Grade
- Quarterly tabs (Q1-Q4), Grade/Section filters
- Export to XLSX with all computed columns
- Add individual students with manual score entry
- Color-coded transmuted grades (green=O, cyan=VS, yellow=S, orange=FS, red=DNME)

**Dependencies added:** SheetJS (xlsx 0.20.0) via CDN for client-side XLSX parsing and generation

---

### 🔄 Session 9C — Service Worker Cache Strategy Fix

**Problem:** Pages served stale cached HTML after code updates. Teachers had to manually append `?v=2` to URLs or hard-refresh to see changes — the service worker's cache-first strategy kept returning old files.

**Root cause:** `pwabuilder-adv-sw.js` used a blanket **cache-first** strategy for all non-PHP resources. Once `records.html` (or any HTML page) was cached, the SW always served the stale copy, ignoring server-side updates.

**Fix:** Rewrote the service worker with resource-appropriate caching strategies:

| Resource Type | Old Strategy | New Strategy | Why |
|---|---|---|---|
| HTML pages | Cache-first ❌ | **Network-first** ✅ | Always gets latest from server; falls back to cache only when offline |
| PHP APIs | Network-only | Network-only (unchanged) | Dynamic data must never be cached |
| Static assets (CSS/JS/fonts) | Cache-first ❌ | **Stale-while-revalidate** ✅ | Fast from cache, updates in background so next load is fresh |

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`pwabuilder-adv-sw.js`** | **REBUILT** — Three-tier caching: network-first for HTML, network-only for PHP, stale-while-revalidate for static assets. Bumped to **v12**. | ✅ |

**Result:** All pages now load the latest version without `?v=2` hacks or `Ctrl+Shift+R`. PWA still works fully offline.

---

## Session 8 — May 17, 2026

### 📊 Scoreboard Rebuild + Sorting Everywhere + System Focus Alignment

**Core Focus Realignment:** Per adviser feedback, the primary focus is **interactive multimedia lessons and classroom presentation** — NOT recitation. Recitation is an additional feature. The system addresses: lack of interest in science, limited learning materials, difficulty understanding concepts, and lack of experiments.

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`scoreboard.html`** | **REBUILT** — Sheet-style table with clickable headers, sort by 6 fields (Points/Name/Grade/Section/Recitations/Proficiency), Asc/Desc toggle, section filter, insight cards (Active/Passive/Zero), participation bars, status badges | ✅ |
| 2 | **`app.js` — `renderScoreboard()`** | **REBUILT** — Full sort/filter/direction support, insight card updates, participation bars | ✅ |
| 3 | **`students.html`** | Added sort dropdown (Name/Grade/Points/Recitations) + Asc/Desc toggle | ✅ |
| 4 | **`app.js` — `renderStudents()`** | Added sorting support, shows recitation count in student cards | ✅ |
| 5 | **`records.html`** | Added Asc/Desc toggle button | ✅ |
| 6 | **`app.js` — `renderRecords()`** | Sort direction support via `getRecordsSortDir()` | ✅ |
| 7 | **`pwabuilder-adv-sw.js`** | Bumped to **v11** | ✅ |

---

## Session 7 — May 17, 2026

### 👤 Per-User Profiles + 🔒 Admin Role Gating + 📊 PPTX Import & Playback

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`login.html`** | `loginSuccess()` now stores `role` in state + loads per-user profile (avatar, bio, border) from `profile_api.php` on login | ✅ |
| 2 | **`app.js` — `defaultState`** | Added `currentRole: 'teacher'` field to state | ✅ |
| 3 | **`app.js` — DOMContentLoaded** | Loads per-user profile from DB on every page load (each user sees their own avatar/bio) | ✅ |
| 4 | **`app.js` — admin guard** | Redirects teachers away from `admin.html` if `state.currentRole !== 'admin'` | ✅ |
| 5 | **`app.js` — hamburger menu** | Filters out "Admin" link for non-admin users — teachers only see Teacher Tools | ✅ |
| 6 | **`pptx_api.php`** | **NEW** — PPTX upload & slide extraction. Parses `.pptx` (ZIP/OpenXML), extracts text from each `ppt/slides/slideX.xml`, auto-detects grade/quarter from filename (e.g., `PPT_SCIENCE_G4_Q3_W4.pptx`), stores in `curriculum_lessons` + `lesson_slides` tables | ✅ |
| 7 | **`app.js` — PPTX functions** | **NEW** — `showPPTXUploadModal()`, `uploadPPTX()`, `loadPPTXList()`, `deletePPTX()` | ✅ |
| 8 | **`lessons.html`** | Added **Presentations** tab with PPTX upload button + uploaded presentations list with Play/Delete buttons | ✅ |
| 9 | **`pwabuilder-adv-sw.js`** | Bumped to **v10** | ✅ |

**Profile per user:** Each user (oyo, tine, dondell, coney, or any teacher) now has their own profile image stored in the `teacher_profiles` table. When a user logs in, their unique avatar/bio/border is loaded from the database. Switching users loads a different profile.

**Admin-only panel:** The Admin link only appears in the hamburger menu for users with `role = 'admin'`. Teachers who navigate directly to `admin.html` are redirected to the dashboard.

**PPTX workflow:** Lessons → Presentations tab → Import PPTX → auto-extracts slides → opens in Canva-style presenter (`presenter.html`) with keyboard nav, fullscreen, sidebar, timer, and progress bar.

---

## Session 6 — May 17, 2026

### 🛡️ Full Admin Panel Rebuild — DB-Driven User Management + DepEd E-Class Export

**Major overhaul:** Rebuilt `admin.html` from a single-page flat layout into a **tabbed admin dashboard** with 6 panels: Dashboard, Users, Questions, Files, E-Class, Settings. All user management now goes through `admin_api.php` → MySQL instead of localStorage.

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | **`admin.html`** | **REBUILT** — Tabbed interface (Dashboard / Users / Questions / Files / E-Class / Settings) with stat cards, user table, edit modals | ✅ |
| 2 | **`app.js` — `loadAdminStats()`** | **NEW** — Fetches system stats from `admin_api.php?action=stats` (user/student/question/lesson/recitation counts, per-grade breakdowns, recent recitations) | ✅ |
| 3 | **`app.js` — `loadAdminUsers()`** | **NEW** — Fetches all users from `admin_api.php?action=users`, renders in HTML table with ID, username, display name, email, role, created date, edit/delete buttons | ✅ |
| 4 | **`app.js` — `adminCreateUser()`** | **NEW** — Creates users via `admin_api.php POST action=create_user` with username, display name, email, password, role (teacher/admin) | ✅ |
| 5 | **`app.js` — `adminEditUser()`** | **NEW** — Opens modal to edit display name, email, password, role; saves via `admin_api.php POST action=update_user` | ✅ |
| 6 | **`app.js` — `adminDeleteUser()`** | **NEW** — Deletes users via `admin_api.php POST action=delete_user` (prevents deleting last admin) | ✅ |
| 7 | **`app.js` — `downloadEClassRecord()`** | **NEW** — Opens `admin_api.php?action=export_eclass` in new tab to download DepEd E-Class Record CSV with transmutation grades | ✅ |
| 8 | **`app.js` — `previewEClassRecord()`** | **NEW** — Renders live E-Class preview table with DepEd WW/PT/QA weights, transmuted grades, and proficiency levels | ✅ |
| 9 | **`app.js` — DOMContentLoaded** | Updated to detect new admin panel (`admin-stat-grid`) and load stats+users+settings on page load | ✅ |
| 10 | **`app.js` — proxy object** | Added 8 new admin functions to `window.app` proxy | ✅ |
| 11 | **`pwabuilder-adv-sw.js`** | Bumped to **v9** | ✅ |

**Admin Panel Tabs:**

- **Dashboard** — Live stat cards (users, students, questions, lessons, recitations) + recent recitation log
- **Users** — Full CRUD table with inline edit modal (change name, email, password, role)
- **Questions** — Question Bank Manager with grade filter, search, CSV export
- **Files** — Export/Import data (JSON/CSV) + DB sync (push/pull)
- **E-Class** — DepEd E-Class Record export (grade/section/quarter selector + CSV download + live preview)
- **Settings** — Point values per difficulty + aggregated participation reports

---

## Session 5 — May 17, 2026

### 🧹 app.js Cleaned + Canva-style Presenter

**Major refactor:** Removed ~190 lines of hardcoded topics/lessons/questions from `defaultState`. All content now loads exclusively from the MySQL database.

| # | Change | Details |
|---|--------|---------|
| 1 | **Stripped `defaultState`** | Removed hardcoded `topics` (5 grades), `lessons` (20 topics × 5 slides each), `questions` (4), and sample `students` (4) |
| 2 | **`updateDashboardTopics()`** | Now fetches from `curriculum_lessons` DB; falls back to `questions` DB topics |
| 3 | **`renderDashboardLessons()`** | Fetches lessons from DB; links to `presenter.html?id=` instead of `lessons.html` |
| 4 | **`updateMaterialTopics()`** | Now loads topics from `dbQuestions` instead of `state.topics` |
| 5 | **`promptNewTopic()`** | Creates topic via DB question INSERT instead of localStorage push |
| 6 | **Assessment fallback** | Loads topics from `dbQuestions` when no curriculum lessons exist |
| 7 | **Lesson delivery fallback** | Shows "no slides" message instead of referencing dead `state.lessons` |
| 8 | **`seed_data.php`** | **NEW** — Seeds 19 lessons, 22 questions, 4 sample students into DB |
| 9 | **`presenter.html`** | **NEW** — Canva-style dark presentation viewer with sidebar, keyboard nav, timer, progress bar, fullscreen |
| 10 | **Service Worker v7** | Added `presenter.html` to cache, PHP APIs still network-first |

**Presenter keyboard shortcuts:** `→`/`Space` = next, `←` = prev, `F` = fullscreen, `S` = toggle sidebar, `Esc` = exit fullscreen

---

## Session 4 — May 17, 2026

### 🔍 Full System Audit + 7 Fixes Applied

**Audit Scope:** Database tables vs. PHP APIs vs. HTML navigation links

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | `recitation_api.php` | **NEW** — Writes flash quiz & rubric scores to `recitation_records` table (was orphaned) | ✅ |
| 2 | `student_api.php` | **NEW** — Direct student CRUD (add/remove instantly syncs to DB, no manual sync needed) | ✅ |
| 3 | `app.js` — `recordQuizResult()` | Now calls `recitation_api.php` after saving local state | ✅ |
| 4 | `app.js` — `recordRubricResult()` | Now calls `recitation_api.php` after saving local state | ✅ |
| 5 | `app.js` — `addStudent()` | Now calls `student_api.php POST` immediately on add | ✅ |
| 6 | `app.js` — `removeStudent()` | Now calls `student_api.php DELETE` immediately on remove | ✅ |
| 7 | `sync.php` | Added `section` field to student INSERT + ON DUPLICATE KEY UPDATE | ✅ |
| 8 | `lessons_api.php` | Fixed routing bug — `?slides=` check now comes BEFORE generic GET block | ✅ |
| 9 | All 14 HTML pages | Removed hardcoded `MC` avatar text — `applyProfileUI()` now fills dynamically | ✅ |
| 10 | `pwabuilder-adv-sw.js` | Bumped to **v6**, added `student_api.php` and `recitation_api.php` to cache list | ✅ |

> **Note:** All DB writes are wrapped in `.catch()` — app continues to work offline if MySQL is unavailable.

---

## Session 3 — May 17, 2026 (Current)

### 🔐 Hardcoded Users Removed — Database-Only Auth

All authentication now goes through `ilikesci_db` MySQL database. No credentials exist in any JS/HTML file.

| # | File | Change | Status |
|---|------|--------|--------|
| 1 | `init_db.php` | Rebuilt — creates 4 admin accounts with `password_hash()`. Adds `display_name` and `created_at` columns. Uses `ALTER TABLE` fallback for existing tables. | ✅ |
| 2 | `auth.php` | Rebuilt — uses ONLY `password_verify()` against hashed DB passwords. Removed plain-text fallback. Returns `display_name` on success. | ✅ |
| 3 | `login.html` | Removed all hardcoded admin arrays. `handleLogin()` calls `auth.php` exclusively. Shows "XAMPP MySQL not running" on network error. | ✅ |
| 4 | `app.js` — `adminUsers` | Changed from hardcoded array of 4 users → empty array `[]`. | ✅ |
| 5 | `app.js` — `handleLogin()` | Replaced 50+ line function with redirect stub → `login.html`. | ✅ |
| 6 | `app.js` — `handleOfflineLogin()` | Removed all hardcoded fallback users. Shows DB-required message. | ✅ |
| 7 | `register.php` | Created — handles teacher registration with `password_hash()`. | ✅ |

### 👤 Admin Accounts (in database)

| Username | Password | Display Name | Role |
|----------|----------|-------------|------|
| `oyo` | `oyo` | Oyo | admin |
| `tine` | `tine` | Tine | admin |
| `dondell` | `dondell` | Dondell | admin |
| `coney` | `coney` | Coney Alcantara Quintos | admin |

> **Note:** The old `admin` / `oyo` account has been deleted.

### 🔧 phpMyAdmin Configuration Storage Fix

| # | File | Change | Status |
|---|------|--------|--------|
| 8 | `fix_phpmyadmin.php` | Created — builds `phpmyadmin` DB with all 18 `pma__*` tables | ✅ |
| 9 | `C:\Games\xampp\phpMyAdmin\config.inc.php` | Changed `controluser` from `pma` → `root`. Removed deprecated `designer_coords`. | ✅ |

**Why it was broken:** The config referenced a MySQL user `pma` that doesn't exist in default XAMPP. Changed to `root`. Also removed the deprecated `pma__designer_coords` table reference (not in phpMyAdmin 5.2.1).

---

## Session 2 — May 17, 2026 (Earlier)

### 🚨 Critical Flicker/Redirect Loop Fix

**Root cause:** `login.html` saved login state to localStorage key `ilikesci_state`, but `app.js` read from `ilikesci_hybrid_state`. This caused an infinite redirect loop between the two pages.

| # | File | Change | Status |
|---|------|--------|--------|
| 10 | `login.html` | Changed all `ilikesci_state` → `ilikesci_hybrid_state` (3 places) | ✅ |
| 11 | `login.html` | Added old key migration — auto-cleans stale `ilikesci_state` data | ✅ |
| 12 | `login.html` | Added redirect loop guard (sessionStorage timestamp, stops loops within 2s) | ✅ |
| 13 | `app.js` — auth guard | Added matching redirect loop guard | ✅ |

### 🔑 Login Separation

| # | File | Change | Status |
|---|------|--------|--------|
| 14 | `login.html` | **NEW** — Standalone dark-themed login page, no credential hints, button says "Login" | ✅ |
| 15 | `index.html` | Removed login overlay — now pure dashboard (requires auth) | ✅ |
| 16 | `app.js` — auth guard | Redirects unauthenticated users to `login.html` (not `index.html`) | ✅ |
| 17 | `app.js` — logout modal | "Yes, Log Out" redirects to `login.html` | ✅ |

### 📋 Hamburger Menu

| # | File | Change | Status |
|---|------|--------|--------|
| 18 | `app.js` — `renderSharedPageButtons()` | Replaced inline "More Teacher Tools" with slide-in hamburger panel | ✅ |
| 19 | `styles.css` | Added `.hamburger-panel`, `.hamburger-item`, animations, light mode support | ✅ |

### 👩‍🏫 Student Section Field

| # | File | Change | Status |
|---|------|--------|--------|
| 20 | `students.html` | Added Section filter (A/B/C/D) + section in Add Student form | ✅ |
| 21 | `app.js` — `renderStudents()` | Filters by both grade AND section; shows "Section X" on cards | ✅ |
| 22 | `app.js` — `addStudent()` | Includes section in student data object | ✅ |

### 🎨 Signup & Profile

| # | File | Change | Status |
|---|------|--------|--------|
| 23 | `signup.html` | Redesigned with consistent dark theme, purple accent, DB registration | ✅ |
| 24 | `profile_api.php` | **NEW** — Saves/loads teacher profiles to MySQL (name, bio, avatar, border) | ✅ |
| 25 | `app.js` — `saveProfile()` | Now persists to DB via `profile_api.php` + localStorage fallback | ✅ |

### 📺 Lessons UI

| # | File | Change | Status |
|---|------|--------|--------|
| 26 | `lessons.html` | Rebuilt — segmented tabs for Delivery/Builder, grouped TV controls | ✅ |

### ⚙️ Service Worker

| # | File | Change | Status |
|---|------|--------|--------|
| 27 | `pwabuilder-adv-sw.js` | Added `login.html` + `signup.html` to cache list, bumped to **v5** | ✅ |

---

## Setup Instructions

### First-Time Setup

```
1. Start XAMPP (Apache + MySQL)
2. Navigate to http://127.0.0.1/ILikeSci/fix_phpmyadmin.php
3. Navigate to http://127.0.0.1/ILikeSci/init_db.php
4. Navigate to http://127.0.0.1/ILikeSci/login.html
5. Log in with any admin account (e.g., oyo/oyo)
```

### After Code Updates

```
1. Hard refresh browser: Ctrl+Shift+R
2. Clear site data if needed: DevTools → Application → Storage → Clear
3. Re-run init_db.php if database schema changed
```

---

## File Inventory

| File | Purpose |
|------|---------|
| `login.html` | Entry point — standalone login page |
| `index.html` | Dashboard (requires auth) |
| `admin.html` | Admin panel — tabbed (Dashboard/Users/Questions/Files/E-Class/Settings) |
| `app.js` | Master logic — state, navigation, API orchestration |
| `styles.css` | Central styling — DepEd badges, hamburger menu, assessment modes |
| `auth.php` | Login API — database-only authentication |
| `register.php` | Teacher registration API |
| `admin_api.php` | Admin API — user CRUD, system stats, DepEd E-Class CSV export |
| `profile_api.php` | Profile save/load API |
| `questions_api.php` | Question CRUD API |
| `student_api.php` | Student CRUD API |
| `recitation_api.php` | Recitation record API |
| `lessons_api.php` | Curriculum lessons + slides API |
| `init_db.php` | Database schema + admin seeder |
| `seed_data.php` | Seeds 19 lessons, 22 questions, 4 sample students |
| `db.php` | PDO connection to `ilikesci_db` |
| `sync.php` | Full state sync (push/pull) |
| `file_manager.php` | Import/export backend (CSV/JSON/multimedia) |
| `presenter.html` | Canva-style lesson presenter with keyboard nav |
| `fix_phpmyadmin.php` | One-time phpMyAdmin config storage fix |
| `pwabuilder-adv-sw.js` | Service worker (cache v12) — network-first HTML, stale-while-revalidate assets |
