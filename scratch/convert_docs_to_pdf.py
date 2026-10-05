import os
import re
import subprocess
import markdown

CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
OUTPUT_DIR = os.path.abspath("documentation")

CSS_STYLES = """
@page {
    size: A4;
    margin: 20mm 18mm 20mm 18mm;
    @bottom-right {
        content: counter(page);
    }
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.6;
    color: #1f2937;
    background-color: #ffffff;
    margin: 0;
    padding: 0;
}

h1 {
    font-size: 20pt;
    color: #0f2c59;
    border-bottom: 2.5px solid #0f2c59;
    padding-bottom: 6px;
    margin-top: 0;
    margin-bottom: 12px;
    page-break-after: avoid;
}

h2 {
    font-size: 14pt;
    color: #1e3a8a;
    border-bottom: 1.5px solid #e2e8f0;
    padding-bottom: 4px;
    margin-top: 22px;
    margin-bottom: 10px;
    page-break-after: avoid;
}

h3 {
    font-size: 12pt;
    color: #1e40af;
    margin-top: 16px;
    margin-bottom: 8px;
    page-break-after: avoid;
}

h4 {
    font-size: 10.5pt;
    color: #374151;
    margin-top: 12px;
    margin-bottom: 6px;
    page-break-after: avoid;
}

p {
    margin: 6px 0 10px 0;
    text-align: justify;
}

ul, ol {
    margin: 6px 0 10px 20px;
    padding: 0;
}

li {
    margin-bottom: 4px;
}

blockquote {
    border-left: 4px solid #2563eb;
    background-color: #f0f7ff;
    padding: 10px 14px;
    margin: 12px 0;
    border-radius: 0 6px 6px 0;
    color: #1e3a8a;
    font-size: 9.5pt;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 14px 0;
    font-size: 9pt;
    page-break-inside: avoid;
}

th {
    background-color: #1e3a5f;
    color: #ffffff;
    font-weight: 600;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
}

td {
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    vertical-align: top;
}

tr:nth-child(even) td {
    background-color: #f8fafc;
}

pre {
    background-color: #0f172a;
    color: #f1f5f9;
    padding: 12px 14px;
    border-radius: 6px;
    font-family: "Cascadia Code", "Fira Code", Consolas, "Courier New", monospace;
    font-size: 8pt;
    line-height: 1.45;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 12px 0;
    page-break-inside: avoid;
    border: 1px solid #334155;
}

code {
    font-family: "Cascadia Code", "Fira Code", Consolas, "Courier New", monospace;
    background-color: #f1f5f9;
    color: #0f172a;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 8.5pt;
}

pre code {
    background-color: transparent;
    color: inherit;
    padding: 0;
}

hr {
    border: 0;
    border-top: 1px solid #e2e8f0;
    margin: 20px 0;
}

.mermaid {
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 14px;
    margin: 14px 0;
    font-family: monospace;
    font-size: 8.5pt;
    white-space: pre-wrap;
}

.document-header {
    text-align: center;
    border-bottom: 2px solid #0f2c59;
    padding-bottom: 14px;
    margin-bottom: 20px;
}

.document-header .univ {
    font-size: 11pt;
    font-weight: 700;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.document-header .college {
    font-size: 9.5pt;
    color: #4b5563;
}

.document-header .title {
    font-size: 18pt;
    font-weight: 800;
    color: #0f2c59;
    margin: 10px 0 4px 0;
}

.document-header .sub {
    font-size: 11pt;
    color: #374151;
    font-weight: 600;
}

.meta-box {
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 10px 14px;
    margin: 12px 0 20px 0;
    font-size: 9pt;
}

.page-break {
    page-break-before: always;
}
"""

def convert_markdown_file(md_path, pdf_filename, doc_title, doc_sub):
    print(f"\nProcessing {md_path} -> {pdf_filename}...")
    with open(md_path, "r", encoding="utf-8") as f:
        md_content = f.read()

    # Pre-process math notation if needed
    md_content = md_content.replace(r"\text{", "").replace(r"}", "")

    # Convert Markdown to HTML
    html_body = markdown.markdown(
        md_content,
        extensions=["tables", "fenced_code", "toc", "nl2br", "sane_lists"]
    )

    full_html = f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{doc_title}</title>
<style>
{CSS_STYLES}
</style>
</head>
<body>
<div class="document-header">
    <div class="univ">Republic of the Philippines &bull; Laguna State Polytechnic University</div>
    <div class="college">San Pablo City Campus &bull; College of Computer Studies &bull; BS in Information Technology (Web & Mobile Application Development)</div>
    <div class="title">{doc_title}</div>
    <div class="sub">{doc_sub}</div>
    <div class="meta-box" style="margin-top:10px; margin-bottom:0;">
        <strong>A Capstone Project</strong> &bull; 
        <strong>Proponents:</strong> Abril, John Dondell A. | Millera, Kristine Andrea H. | Olidan, Christian Angelo E.<br>
        <strong>Capstone Adviser:</strong> Ma’am Criselda Encanto &bull; 
        <strong>Deployment Site:</strong> Bay Central Elementary School (BCES), Bay, Laguna (Grades 3–6)
    </div>
</div>
{html_body}
</body>
</html>"""

    temp_html_path = os.path.abspath(f"scratch/temp_{pdf_filename}.html")
    pdf_out_path = os.path.join(OUTPUT_DIR, pdf_filename)

    with open(temp_html_path, "w", encoding="utf-8") as f:
        f.write(full_html)

    print(f"  [OK] Generated styled HTML: {temp_html_path}")

    # Invoke Chrome Headless
    chrome_cmd = [
        CHROME_PATH,
        "--headless=new",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={pdf_out_path}",
        temp_html_path
    ]

    print("  [INFO] Invoking Google Chrome Headless print-to-pdf...")
    res = subprocess.run(chrome_cmd, capture_output=True, text=True, timeout=60)
    if res.returncode != 0:
        print(f"  [ERROR] Chrome failed: {res.stderr}")
        return False

    if os.path.exists(pdf_out_path):
        size_kb = os.path.getsize(pdf_out_path) / 1024
        print(f"  [SUCCESS] Created PDF: {pdf_out_path} ({size_kb:.1f} KB)")
        return True
    else:
        print(f"  [ERROR] PDF file not found at: {pdf_out_path}")
        return False

if __name__ == "__main__":
    os.makedirs(OUTPUT_DIR, exist_ok=True)
    os.makedirs("scratch", exist_ok=True)

    # 1. Appendix F
    convert_markdown_file(
        "APPENDIX_F_USER_MANUAL.md",
        "APPENDIX_F_USER_MANUAL.pdf",
        "APPENDIX F: USER’S MANUAL",
        "iLikeSci: A Science Teaching Material System for Grades 3 to 6 Science Teachers at Bay Central Elementary School"
    )

    # 2. Appendix G
    convert_markdown_file(
        "APPENDIX_G_DEPLOYMENT_AND_SOURCE_CODE.md",
        "APPENDIX_G_DEPLOYMENT_AND_SOURCE_CODE.pdf",
        "APPENDIX G: SOURCE CODE & DEPLOYMENT AUTOMATION",
        "iLikeSci: A Science Teaching Material System for Grades 3 to 6 Science Teachers at Bay Central Elementary School"
    )

    print("\nAll documents processed successfully!")
