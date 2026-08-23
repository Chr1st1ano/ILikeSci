# Development Log: ILikeSci File Importer

## Summary of Changes

To allow users (teachers) to import questions directly from their extracted curriculum files (like `Q1_LE_Science4_Lesson2-Week-2.txt` or worksheets), a new **File Importer** feature was built into the `materials.html` page and powered by `app.js`.

### 1. UI Updates (`materials.html`)
- Added a new **File Import Section** below the "Add Question" block.
- Implemented a drag-and-drop zone (`<div id="drop-zone">`) that accepts `.txt`, `.csv`, and `.json` files.
- Added controls to set a **Default difficulty** and an option to filter for **Multiple-choice only**.
- Added a **Preview Table** (`#import-preview`) that displays parsed questions before saving them. This table includes checkboxes to select/deselect specific questions and dropdowns to adjust their difficulty.
- Added action buttons: "Toggle All", "Import Selected to DB", and "Clear".

### 2. Parsing Logic (`app.js`)
Added several new functions to handle file reading and parsing:
- **`handleImportFile(file)`**: Reads the uploaded file and routes it to the appropriate parser based on extension.
- **`parseTXTQuestions(text, filename)`**: The core parser for DepEd curriculum text files. It uses Regex to detect:
  - **Pattern 1:** Numbered multiple-choice questions (e.g., "1. What is...") along with their options (a., b., c., d.), whether listed on separate lines or inline.
  - **Pattern 2:** Guide/Discussion Questions grouped under headers.
  - **Pattern 3:** Standalone open-ended questions ending in "?".
- **`parseCSVQuestions(content, filename)`**: Parses standard CSV files, requiring at least a `text` or `question` column.
- **`parseJSONQuestions(content, filename)`**: Parses JSON arrays of question objects.
- **Helper Functions**: 
  - `extractGradeFromText()`: Auto-detects the grade level from the filename or early lines.
  - `extractTopicFromText()`: Auto-detects the topic from "Lesson Title / Topic:" headers.
  - `isBoilerplate()`: Skips standard DepEd headers/footers to avoid false positives.
  - `guessDifficulty()`: Looks for keywords (e.g., "explain" vs "identify") to guess Easy/Hard difficulty.

### 3. Import Execution (`app.js`)
- **`renderImportPreview()`**: Generates the HTML table for the user to review the parsed questions.
- **`importSelectedQuestions()`**: Loops through the checked questions and sends them via POST to `questions_api.php`, injecting them directly into the MySQL database.
- **Proxy Registration**: Exposed `handleImportFile`, `toggleImportQ`, `setImportDiff`, `toggleAllImport`, `importSelectedQuestions`, and `clearImportPreview` to the `window.app` object so they can be triggered by the UI buttons.

## Status
The feature is fully implemented and ready for testing. Teachers can now bulk-import extracted text directly into their question bank.
