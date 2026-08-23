# ILikeSci — admin.html Flowchart

> **Scope:** This document covers only `admin.html` — the Administrator Panel page of the ILikeSci system.
> Each section has a **plain-language explanation** followed by the **flowchart diagram** for visual reference.

---

## 1. Page Entry Flow

### 📝 In Plain Words

When someone opens `admin.html` in their browser, the very first thing that happens is the browser downloads and assembles all the building blocks of the page — the stylesheet (`styles.css`), icon library (Font Awesome), the Google font (Outfit), and the two JavaScript files (`app.js` and `mobile-optimizations.js`). Once everything is loaded, the page draws the navigation bar at the top, then renders the main admin panel below it. By default, the **Dashboard tab** is the first one shown, and the system immediately calls `app.loadAdminStats()` to start pulling live data from the server.

```mermaid
flowchart TD
    A([User navigates to admin.html]) --> B[Browser loads HTML/CSS/JS]
    B --> C[Load external resources:\n- styles.css\n- Outfit Google Font\n- Font Awesome 6.4.0\n- app.js\n- mobile-optimizations.js]
    C --> D[Render Top Navigation Bar]
    D --> E[Render Admin Panel Section]
    E --> F[Default active tab: Dashboard]
    F --> G[app.loadAdminStats is called\nvia app.js on page load]
```

---

## 2. Top Navigation Bar

### 📝 In Plain Words

The navigation bar at the top is split into three parts. On the **left** is the ILikeSci brand logo (an atom icon). In the **center** are three buttons that take the user to other pages: Learn, Practice, and Leaderboard. On the **right** side are personal controls: clicking the avatar goes to the profile page, the moon icon toggles light/dark theme, and the door icon logs the user out.

```mermaid
flowchart LR
    NAV[Top Navigation Bar]

    NAV --> LEFT[Left: Brand Logo\nILikeSci atom icon]
    NAV --> CENTER[Center: Nav Tabs]
    NAV --> RIGHT[Right: User Controls]

    CENTER --> C1[Learn → index.html]
    CENTER --> C2[Practice → assessment.html]
    CENTER --> C3[Leaderboard → scoreboard.html]

    RIGHT --> R1[User Avatar → profile.html]
    RIGHT --> R2[Theme Toggle\napp.toggleTheme]
    RIGHT --> R3[Logout\napp.handleLogout]
```

---

## 3. Admin Panel Tab Switching (`switchAdminTab`)

### 📝 In Plain Words

The admin panel has **six tabs** at the top: Dashboard, Users, Questions, Files, E-Class, and Settings. When the admin clicks any tab, the system first clears all currently active highlights, then highlights the tab the admin just clicked and reveals its matching content panel. After that, depending on which tab was selected, it automatically fetches the right data. For example, clicking **Users** triggers `app.loadAdminUsers()`, while clicking **Settings** triggers both `app.loadSystemSettings()` and `loadAIAdminStatus()`. The **Files** tab is the only one that does NOT auto-load anything — the admin has to click a button manually.

```mermaid
flowchart TD
    START([User clicks an Admin Tab]) --> CLEAR[Remove .active from all tabs\nRemove .active from all panels]
    CLEAR --> ACTIVATE[Add .active to clicked tab\nAdd .active to matching panel]
    ACTIVATE --> CHECK{Which tab was selected?}

    CHECK -->|dashboard| DASH[app.loadAdminStats]
    CHECK -->|users| USERS[app.loadAdminUsers]
    CHECK -->|questions| QUES[app.renderQuestionBank]
    CHECK -->|files| FILES[No auto-load —\nuser clicks buttons]
    CHECK -->|eclass| ECLASS[app.previewEClassRecord]
    CHECK -->|settings| SETT[app.loadSystemSettings\n+ loadAIAdminStatus]
```

---

## 4. TAB: Dashboard

### 📝 In Plain Words

When the Dashboard tab is open (or when the page first loads), a spinning loader appears while the system silently contacts the server in the background. Once the server responds, the loader disappears and is replaced by a grid of **stat cards** — small boxes showing important numbers like total students, questions in the bank, sessions today, and total scores. Below the stat cards is a list of the **most recent recitations** so the admin can quickly see what students have been doing lately. If the server fails to respond, the page shows an empty or error state instead.

```mermaid
flowchart TD
    D_START([Dashboard Tab Active]) --> D_LOAD[Show loading spinner\n#admin-stats-loading]
    D_LOAD --> D_API[app.loadAdminStats\ncalls admin API via app.js]
    D_API --> D_DONE{API responds?}

    D_DONE -->|Yes| D_SHOW[Hide spinner\nShow #admin-stats-content]
    D_SHOW --> D_GRID[Render Stat Grid\n#admin-stat-grid\nwith stat cards]
    D_GRID --> D_CARDS["Stat Cards:\n• Total Students\n• Questions in Bank\n• Sessions Today\n• Total Scores\n(populated by app.js)"]
    D_CARDS --> D_REC[Render Recent Recitations\n#admin-recent-recitations\nas list items]

    D_DONE -->|No| D_ERR[Show error state\nor empty list]
```

---

## 5. TAB: Users

### 📝 In Plain Words

The Users tab has two main areas. At the **top** is the Add New User form — it is always visible and lets the admin type in a username, display name, optional email, password, and choose a role (Teacher or Admin). When the admin clicks **Add**, the system checks if all required fields are filled in. If valid, it sends the new user data to the server and refreshes the table. If not, it shows a validation error.

At the **bottom** is the full user table. When the tab opens, a spinner shows while user records are fetched from the server. Once loaded, the table displays every user with their details. Each row has two action buttons: **Edit** (opens a popup modal to change the user's info) and **Delete** (shows a confirmation prompt before removing the user permanently).

```mermaid
flowchart TD
    U_START([Users Tab Active]) --> U_LOAD[app.loadAdminUsers\nfetch all users from API]

    U_LOAD --> U_SPINNER[Show loading spinner\n#admin-users-loading]
    U_SPINNER --> U_DONE{API responds?}

    U_DONE -->|Yes| U_HIDE[Hide spinner\nShow #admin-users-table]
    U_HIDE --> U_TABLE[Populate table body\n#admin-users-tbody\nwith rows: # / Username /\nDisplay Name / Email / Role / Created / Actions]

    U_TABLE --> U_ROW{Per row actions}
    U_ROW --> U_EDIT[Edit button\napp.adminEditUser\nOpens edit modal overlay]
    U_ROW --> U_DEL[Delete button\napp.adminDeleteUser\nConfirmation prompt → API call]

    U_DONE -->|No| U_ERR[Show error message]

    U_START --> U_FORM[Add New User Form\nalways visible above table]
    U_FORM --> U_FIELDS["Fields:\n• Username (text)\n• Display Name (text)\n• Email (optional)\n• Password (password)\n• Role (Teacher / Admin)"]
    U_FIELDS --> U_BTN[Add button\napp.adminCreateUser]
    U_BTN --> U_VALIDATE{Fields valid?}
    U_VALIDATE -->|Yes| U_POST[POST to API\nCreate user in DB]
    U_VALIDATE -->|No| U_VALWARN[Show validation error]
    U_POST --> U_REFRESH[Reload user table]
```

### 5a. Edit User Modal

### 📝 In Plain Words

When the admin clicks the **Edit** button on any user row, a dark overlay appears over the whole screen with a small popup form in the center. The admin can change the user's display name, email, role, or set a new password (the password field is optional — leaving it blank keeps the old one). If the admin clicks **Save**, the data is sent to the server and the table reloads. If they click **Cancel**, the popup closes and nothing changes.

```mermaid
flowchart TD
    E_OPEN([Edit button clicked]) --> E_MODAL[Show .edit-modal-overlay]
    E_MODAL --> E_FORM["Modal form fields:\n• Display Name\n• Email\n• Role\n• New Password (optional)"]
    E_FORM --> E_ACTION{User action}
    E_ACTION -->|Save| E_SAVE[PUT/POST to API\nUpdate user record]
    E_ACTION -->|Cancel| E_CLOSE[Close modal overlay]
    E_SAVE --> E_RELOAD[Reload user table]
    E_CLOSE --> E_END([Modal dismissed])
```

---

## 6. TAB: Questions (Question Bank)

### 📝 In Plain Words

The Questions tab is where the admin manages all the science questions stored in the system. When the tab opens, the full list of questions loads automatically. The admin can narrow the list down using two live filters: a **grade-level dropdown** (All, Grade 3–6) and a **keyword search box** (searches by topic or question text). Both filters react immediately as the admin types or selects — no need to click a search button. Each question in the list shows the question text, its grade, difficulty, and type, with Edit and Delete actions per item. There is also an **Export CSV** button that downloads all currently filtered questions as a spreadsheet file.

```mermaid
flowchart TD
    Q_START([Questions Tab Active]) --> Q_LOAD[app.renderQuestionBank\nLoad questions from app.js state/API]

    Q_LOAD --> Q_FILTER[Apply filters]
    Q_FILTER --> Q_GRADE["Grade filter (#qb-filter-grade):\nAll / Grade 3 / 4 / 5 / 6"]
    Q_FILTER --> Q_SEARCH["Keyword search (#qb-search):\nSearch by topic or keyword"]

    Q_GRADE --> Q_LIST[Render filtered list\nin #admin-question-list]
    Q_SEARCH --> Q_LIST

    Q_LIST --> Q_ITEMS["Each item shows:\n• Question text\n• Grade level\n• Difficulty\n• Question type\n• Edit / Delete actions"]

    Q_LIST --> Q_EXPORT[Export CSV button\napp.exportQuestionsCSV\nDownloads .csv file]

    Q_FILTER --> Q_LIVE[Filters are live:\nonchange / oninput re-renders list]
```

---

## 7. TAB: Files (File Manager)

### 📝 In Plain Words

The Files tab is the data management hub. Nothing loads automatically when you switch to it — the admin must interact manually. There are four buttons in the first card:

- **Export Data** — opens a modal where you pick a grade and format, then downloads a filtered file.
- **Import File** — opens a modal to upload a file and merge it into the system.
- **Quick Backup (JSON)** — instantly downloads the entire system data as a single JSON file.
- **Quick Restore (JSON)** — opens a file picker to upload a previously saved JSON backup and restore data from it.

A second card handles **Database Sync** — the admin can either **Push** (send local data up to the MySQL database) or **Pull** (fetch the database records back down to the local state).

```mermaid
flowchart TD
    F_START([Files Tab Active]) --> F_VIEW[Show File Manager panel\nNo auto-load on tab switch]

    F_VIEW --> F_BTN1[Export Data\napp.showExportModal\nOpens export options modal]
    F_VIEW --> F_BTN2[Import File\napp.showImportModal\nOpens file picker modal]
    F_VIEW --> F_BTN3[Quick Backup JSON\napp.exportData\nDownloads full JSON backup]
    F_VIEW --> F_BTN4[Quick Restore JSON\nOpens hidden file input\n#import-file-legacy\napp.importData on change]

    F_VIEW --> F_DB[Database Sync section]
    F_DB --> F_PUSH[Push to Database\nsyncToCloud\nPOST local state → MySQL]
    F_DB --> F_PULL[Pull from Database\nrestoreFromCloud\nGET MySQL → local state]

    F_BTN1 --> F_MODAL_EXP[Export Modal:\nSelect grade + format\nDownload filtered file]
    F_BTN2 --> F_MODAL_IMP[Import Modal:\nPick file\nParse + merge data]
```

---

## 8. TAB: E-Class Record Export

### 📝 In Plain Words

This tab is specifically for generating the official **DepEd E-Class Record** report. As soon as the admin opens this tab, the system automatically calls `app.previewEClassRecord()` to show a preview based on the default filter values. The admin can change three filters — **Grade**, **Section** (A/B/C/D), and **Quarter** (1–4) — and a preview table updates to show the matching students and their computed grades. The grade calculation strictly follows **DepEd Order 8, s. 2015**: Written Work counts for 40%, Performance Tasks for 40%, and the Quarterly Assessment for 20%. These are combined into an Initial Grade, which is then transmuted into the final grade. Once the admin is happy with the preview, clicking **Download CSV** saves the table as a properly formatted spreadsheet ready for submission.

```mermaid
flowchart TD
    EC_START([E-Class Tab Active]) --> EC_PREV[app.previewEClassRecord\ncalled automatically]

    EC_PREV --> EC_FORM[Render filter controls]
    EC_FORM --> EC_GRADE["Grade selector (#eclass-grade):\nAll / Grade 3–6"]
    EC_FORM --> EC_SECT["Section selector (#eclass-section):\nSection A / B / C / D"]
    EC_FORM --> EC_QTR["Quarter selector (#eclass-quarter):\nQ1 / Q2 / Q3 / Q4"]

    EC_GRADE --> EC_DATA[Fetch matching student records]
    EC_SECT --> EC_DATA
    EC_QTR --> EC_DATA

    EC_DATA --> EC_CALC["Apply DepEd Order 8 s.2015 formula:\n• Written Work: 40%\n• Performance Task: 40%\n• Quarterly Assessment: 20%\nCompute Initial Grade → Transmuted Grade"]

    EC_CALC --> EC_TABLE[Show preview table\n#eclass-preview\n#eclass-preview-head\n#eclass-preview-body]

    EC_TABLE --> EC_DL[Download CSV button\napp.downloadEClassRecord\nDownloads formatted .csv]
```

---

## 9. TAB: Settings

### 📝 In Plain Words

The Settings tab is divided into three cards, all of which load data when the tab is opened.

**Card 1 — System Settings:** The admin can set how many points each difficulty level is worth (Easy, Medium, Hard). The defaults are 1, 3, and 5. Clicking **Save Settings** sends these values to the server/localStorage so the whole system respects them.

**Card 2 — Aggregated Reports:** The admin selects a grade level and clicks **Generate Report** to produce a text-based summary of student performance for that grade. The output appears in a scrollable box below the button.

**Card 3 — AI Settings:** This card checks the status of the connected AI service (Groq or Gemini) by calling `ai_api.php`. It shows the provider name, model, language, rate limit, and how many responses are cached. If no API key has been configured in `ai_config.php`, a red warning banner appears. Two buttons let the admin **Test Connection** (runs the status check again) or **Clear AI Cache** (after a confirmation dialog, deletes all stored AI responses and shows a success or error alert).

```mermaid
flowchart TD
    S_START([Settings Tab Active]) --> S_LOAD[app.loadSystemSettings\n+ loadAIAdminStatus]

    S_LOAD --> S_POINTS[System Settings card]
    S_POINTS --> S_FIELDS["Points per Difficulty:\n• Easy  (default: 1)\n• Medium (default: 3)\n• Hard   (default: 5)"]
    S_FIELDS --> S_SAVE[Save Settings button\napp.saveSystemSettings\nPOST settings to API/localStorage]

    S_LOAD --> S_REPORT[Aggregated Reports card]
    S_REPORT --> S_RGRADE["Grade filter (#admin-report-grade):\nAll / Grade 3–6"]
    S_RGRADE --> S_GEN[Generate Report\napp.generateReport]
    S_GEN --> S_OUT[Show text output in\n#admin-report-output]

    S_LOAD --> S_AI[AI Settings card]
    S_AI --> S_AISTATUS[loadAIAdminStatus\nfetch ai_api.php?action=status\nfetch ai_api.php POST cache_stats]
    S_AISTATUS --> S_AISHOW["Display in #ai-admin-status:\n• Status (Connected / No Key)\n• Provider (Groq / Gemini)\n• Model name\n• Language\n• Rate Limit / min\n• Cached Responses count"]

    S_AISHOW --> S_AIKEY{Has API Key?}
    S_AIKEY -->|No| S_AIKEYWARN[Show warning banner:\nAdd key to ai_config.php]
    S_AIKEY -->|Yes| S_AIOK[Status shows Connected]

    S_AI --> S_TEST[Test Connection button\ntestAIConnection\nRe-runs loadAIAdminStatus]
    S_AI --> S_CLEAR[Clear AI Cache button\nclearAICache]
    S_CLEAR --> S_CONFIRM{Confirm dialog}
    S_CONFIRM -->|Cancel| S_ABORT([Aborted])
    S_CONFIRM -->|OK| S_CLEARAPI[POST ai_api.php\naction: clear_cache]
    S_CLEARAPI --> S_CLEAROK{Response?}
    S_CLEAROK -->|success| S_ALERT_OK[Alert: ✅ AI cache cleared!]
    S_CLEAROK -->|error| S_ALERT_ERR[Alert: ❌ Error message]
    S_ALERT_OK --> S_RELOAD[Reload AI status display]
    S_ALERT_ERR --> S_RELOAD
```

---

## 10. TV Mode

### 📝 In Plain Words

At the top of the admin panel page, next to the subtitle text, there is a small **TV Mode** button. Clicking it calls `app.openTVDisplay()`, which opens a new browser window or tab in a special full-screen presentation view. This view is designed to be shown on a classroom TV or projector, displaying live student data in a large, readable format.

```mermaid
flowchart TD
    TV_BTN([TV Mode button clicked\nin page subtitle bar]) --> TV_CALL[app.openTVDisplay]
    TV_CALL --> TV_WIN[Opens a new browser window/tab\nin TV presentation mode\nwith live student data]
```

---

## 11. Script Dependencies Overview

### 📝 In Plain Words

`admin.html` does not contain most of its logic directly — it delegates almost everything to other files. The page has a small **inline script block** that defines only four functions used specifically on this page: `switchAdminTab()`, `loadAIAdminStatus()`, `testAIConnection()`, and `clearAICache()`. All the heavy lifting (user management, stats, question bank, grade export, etc.) lives inside **`app.js`**, which is loaded as a separate file. **`mobile-optimizations.js`** handles touch and responsive behaviour. The two backend APIs (`admin_api.php` for user/stats data and `ai_api.php` for AI features) are called over HTTP by both `app.js` and the inline script.

```mermaid
flowchart LR
    HTML[admin.html]

    HTML -->|inline script| INLINE["switchAdminTab()\nloadAIAdminStatus()\ntestAIConnection()\nclearAICache()"]
    HTML -->|loads| APPJS["app.js\nCore logic:\nloadAdminStats\nloadAdminUsers\nadminCreateUser\nadminEditUser\nadminDeleteUser\nrenderQuestionBank\nexportQuestionsCSV\nshowExportModal\nshowImportModal\nexportData / importData\ndownloadEClassRecord\npreviewEClassRecord\nloadSystemSettings\nsaveSystemSettings\ngenerateReport\ntoggleTheme\nhandleLogout\nopenTVDisplay"]
    HTML -->|loads| MOBJS[mobile-optimizations.js\nTouch / responsive helpers]
    HTML -->|links| CSS[styles.css\nGlobal design tokens]
    HTML -->|links| MANIFEST[manifest.json\nPWA manifest]

    APPJS -->|HTTP calls| API_ADMIN[admin_api.php\nUser CRUD + Stats]
    APPJS -->|HTTP calls| API_AI[ai_api.php\nAI status + cache]
    INLINE -->|HTTP calls| API_AI
```

---

## 12. Full Page Flow Summary

### 📝 In Plain Words

To sum up the whole page in one picture: the admin opens `admin.html`, the page loads and draws itself, and the Dashboard is shown by default with live stats already being fetched. From that point on, the admin is in full control — they can click any of the six tabs to switch to a different function. Each tab is independent: switching to it shows different content and triggers a different data-load. The admin can freely move between tabs at any time, and the system always fetches fresh data when a tab becomes active. There is no fixed order or forced workflow — the admin panel is a free-navigation control room.

```mermaid
flowchart TD
    ENTRY([admin.html loaded]) --> NAV_RENDER[Render Top Nav]
    NAV_RENDER --> PANEL_RENDER[Render Admin Panel\nwith 6 tabs]
    PANEL_RENDER --> DEFAULT[Default: Dashboard tab active]
    DEFAULT --> STATS_LOAD[app.loadAdminStats called]

    STATS_LOAD --> USER_CHOICE{Admin clicks a tab}

    USER_CHOICE -->|Dashboard| D[Show stats + recent recitations]
    USER_CHOICE -->|Users| U[Show add-user form + user table]
    USER_CHOICE -->|Questions| Q[Show filtered question bank]
    USER_CHOICE -->|Files| F[Show export/import + DB sync]
    USER_CHOICE -->|E-Class| E[Show DepEd grade export]
    USER_CHOICE -->|Settings| S[Show point settings + reports + AI config]

    D --> USER_CHOICE
    U --> USER_CHOICE
    Q --> USER_CHOICE
    F --> USER_CHOICE
    E --> USER_CHOICE
    S --> USER_CHOICE
```

---

*Last updated: 2026-05-26 | File: `admin.html` | Project: ILikeSci*
