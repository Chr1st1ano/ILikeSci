#!/usr/bin/env python3
"""
PDF to PNG Slide Converter for ILikeSci
Converts each page in a presentation .pdf file to a high-resolution, lightweight PNG image.
Extracts slide text, titles, and lesson metadata for seamless presenter and classroom delivery.

Usage: python pdf_to_images.py <input.pdf> <output_dir> [dpi]
Output: slide_1.png, slide_2.png, ... in the output directory
Prints JSON result to stdout.
"""

import sys
import os
import json
import re

DEFAULT_DPI = 110  # Optimal balance: crisp 1080p projection with low memory (<300KB/slide)


def extract_metadata_from_text(full_text, filename):
    grade = ""
    quarter = "1"
    topic = ""

    # Check grade
    g_match = re.search(r'GRADE\s*(\d)', full_text, re.I)
    if g_match:
        grade = g_match.group(1)
    else:
        fn_g = re.search(r'G(?:rade)?[-_\s]*(\d)', filename, re.I)
        if fn_g:
            grade = fn_g.group(1)

    # Check quarter
    q_match = re.search(r'Q(?:uarter)?[-_\s]*(\d)', full_text, re.I)
    if q_match:
        quarter = q_match.group(1)
    else:
        fn_q = re.search(r'Q(?:uarter)?[-_\s]*(\d)', filename, re.I)
        if fn_q:
            quarter = fn_q.group(1)

    # Topic cleanup from filename
    clean_topic = os.path.splitext(os.path.basename(filename))[0]
    clean_topic = re.sub(r'GRADE\s*\d+[-_\s]*', '', clean_topic, flags=re.I)
    clean_topic = re.sub(r'LESSON\s*\d+[-_\s]*', '', clean_topic, flags=re.I)
    clean_topic = re.sub(r'EPISODE\s*\d+[-_\s]*', '', clean_topic, flags=re.I)
    clean_topic = clean_topic.replace('_', ' ').replace('-', ' ').strip()
    if clean_topic:
        topic = clean_topic

    return grade, quarter, topic


def determine_slide_type(text, index):
    if index == 0:
        return 'title'
    text_lower = text.lower()
    if any(k in text_lower for k in ['quiz', 'check', 'question', 'choose the', 'which of the following', 'recitation', 'true or false']):
        return 'quiz'
    if any(k in text_lower for k in ['procedure', 'step 1', 'step 2', 'activity', 'investigation', 'experiment', 'let\'s explore', 'instructions']):
        return 'step'
    return 'content'


def render_with_pymupdf(pdf_path, output_dir, dpi):
    import pymupdf
    doc = pymupdf.open(pdf_path)
    total_pages = len(doc)
    slides_info = []
    full_text = ""

    os.makedirs(output_dir, exist_ok=True)

    for i, page in enumerate(doc):
        slide_num = i + 1
        out_filename = f"slide_{slide_num}.png"
        out_path = os.path.join(output_dir, out_filename)

        # Render pixmap at target DPI
        pix = page.get_pixmap(dpi=dpi)
        pix.save(out_path)

        # Extract text
        page_text = page.get_text() or ""
        full_text += "\n" + page_text

        lines = [l.strip() for l in page_text.split('\n') if l.strip()]
        # Skip pure page numbers
        content_lines = [l for l in lines if not re.match(r'^\d+(\s*/\s*\d+)?$', l)]
        title = content_lines[0] if content_lines else f"Slide {slide_num}"
        slide_type = determine_slide_type(page_text, i)

        slides_info.append({
            "slide": slide_num,
            "filename": out_filename,
            "title": title[:120],
            "content": page_text.strip(),
            "slide_type": slide_type,
            "width": pix.width,
            "height": pix.height,
            "size": os.path.getsize(out_path) if os.path.exists(out_path) else 0
        })

    grade, quarter, topic = extract_metadata_from_text(full_text, pdf_path)

    return {
        "status": "success",
        "engine": "pymupdf",
        "total_slides": total_pages,
        "slides": slides_info,
        "detected_grade": grade,
        "detected_quarter": quarter,
        "detected_topic": topic
    }


def render_with_pypdfium2(pdf_path, output_dir, dpi):
    import pypdfium2 as pdfium
    doc = pdfium.PdfDocument(pdf_path)
    total_pages = len(doc)
    slides_info = []
    full_text = ""

    os.makedirs(output_dir, exist_ok=True)

    scale = dpi / 72.0

    for i in range(total_pages):
        slide_num = i + 1
        page = doc.get_page(i)
        out_filename = f"slide_{slide_num}.png"
        out_path = os.path.join(output_dir, out_filename)

        image = page.render(scale=scale).to_pil()
        image.save(out_path, format="PNG")

        textpage = page.get_textpage()
        page_text = textpage.get_text_range() or ""
        full_text += "\n" + page_text

        lines = [l.strip() for l in page_text.split('\n') if l.strip()]
        content_lines = [l for l in lines if not re.match(r'^\d+(\s*/\s*\d+)?$', l)]
        title = content_lines[0] if content_lines else f"Slide {slide_num}"
        slide_type = determine_slide_type(page_text, i)

        slides_info.append({
            "slide": slide_num,
            "filename": out_filename,
            "title": title[:120],
            "content": page_text.strip(),
            "slide_type": slide_type,
            "width": image.width,
            "height": image.height,
            "size": os.path.getsize(out_path) if os.path.exists(out_path) else 0
        })

    grade, quarter, topic = extract_metadata_from_text(full_text, pdf_path)

    return {
        "status": "success",
        "engine": "pypdfium2",
        "total_slides": total_pages,
        "slides": slides_info,
        "detected_grade": grade,
        "detected_quarter": quarter,
        "detected_topic": topic
    }


def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python pdf_to_images.py <input.pdf> <output_dir> [dpi]"
        }))
        sys.exit(1)

    pdf_path = os.path.abspath(sys.argv[1])
    output_dir = os.path.abspath(sys.argv[2])
    dpi = int(sys.argv[3]) if len(sys.argv) > 3 else DEFAULT_DPI

    if not os.path.exists(pdf_path):
        print(json.dumps({
            "status": "error",
            "message": f"PDF file not found: {pdf_path}"
        }))
        sys.exit(1)

    # Try pymupdf first, then pypdfium2
    result = None
    errors = []

    try:
        result = render_with_pymupdf(pdf_path, output_dir, dpi)
    except Exception as e:
        errors.append(f"pymupdf failed: {e}")
        try:
            result = render_with_pypdfium2(pdf_path, output_dir, dpi)
        except Exception as e2:
            errors.append(f"pypdfium2 failed: {e2}")

    if result:
        print(json.dumps(result))
        sys.exit(0)
    else:
        print(json.dumps({
            "status": "error",
            "message": "All PDF conversion engines failed: " + "; ".join(errors)
        }))
        sys.exit(1)


if __name__ == "__main__":
    main()
