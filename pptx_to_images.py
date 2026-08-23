#!/usr/bin/env python3
"""
PPTX to PNG Slide Converter for ILikeSci
Converts each slide in a .pptx file to a PNG image.
Uses python-pptx to extract content and Pillow to render.

Usage: python pptx_to_images.py <input.pptx> <output_dir>
Output: slide_1.png, slide_2.png, ... in the output directory
Prints JSON result to stdout.
"""

import sys
import os
import json
import io
from pathlib import Path

try:
    from pptx import Presentation
    from pptx.util import Inches, Pt, Emu
    from pptx.enum.shapes import MSO_SHAPE_TYPE
    from pptx.dml.color import RGBColor
    from PIL import Image, ImageDraw, ImageFont
except ImportError as e:
    print(json.dumps({"status": "error", "message": f"Missing dependency: {e}. Run: pip install python-pptx Pillow"}))
    sys.exit(1)


DEFAULT_DPI = 144


def emu_to_px(emu, dpi=DEFAULT_DPI):
    """Convert EMU (English Metric Units) to pixels at target DPI."""
    return int(emu / 914400 * dpi)


def get_color_rgb(color_obj):
    """Extract RGB tuple from a pptx color object."""
    try:
        if color_obj and hasattr(color_obj, 'rgb') and color_obj.rgb:
            rgb = color_obj.rgb
            return (rgb[0], rgb[1], rgb[2])
    except:
        pass
    return None


def get_font_size_px(font, dpi=DEFAULT_DPI):
    """Get font size in pixels from a pptx font object scaled to DPI."""
    try:
        if font.size:
            return int(font.size.pt * (dpi / 72.0))
    except:
        pass
    return int(20 * (dpi / 72.0))  # default 20pt scaled


def try_load_font(size):
    """Try to load a TrueType font, fall back to default."""
    font_paths = [
        "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/calibri.ttf",
        "C:/Windows/Fonts/segoeui.ttf",
        "C:/Windows/Fonts/tahoma.ttf",
    ]
    for fp in font_paths:
        try:
            return ImageFont.truetype(fp, size)
        except:
            continue
    try:
        return ImageFont.truetype("arial.ttf", size)
    except:
        return ImageFont.load_default()


def try_load_bold_font(size):
    """Try to load a bold TrueType font."""
    bold_paths = [
        "C:/Windows/Fonts/arialbd.ttf",
        "C:/Windows/Fonts/calibrib.ttf",
        "C:/Windows/Fonts/segoeuib.ttf",
        "C:/Windows/Fonts/tahomabd.ttf",
    ]
    for fp in bold_paths:
        try:
            return ImageFont.truetype(fp, size)
        except:
            continue
    return try_load_font(size)


def get_slide_background_color(slide):
    """Try to extract background color from a slide."""
    try:
        bg = slide.background
        fill = bg.fill
        if fill.type is not None:
            if hasattr(fill, 'fore_color') and fill.fore_color:
                rgb = fill.fore_color.rgb
                return (rgb[0], rgb[1], rgb[2])
    except:
        pass
    return (255, 255, 255)  # default white


def render_slide(slide, slide_width, slide_height, slide_num):
    """Render a single slide to a PIL Image."""
    w_px = emu_to_px(slide_width)
    h_px = emu_to_px(slide_height)

    # Create canvas with background color
    bg_color = get_slide_background_color(slide)
    img = Image.new('RGB', (w_px, h_px), bg_color)
    draw = ImageDraw.Draw(img)

    # Check for background image in slide background
    try:
        bg = slide.background
        if hasattr(bg, 'fill') and bg.fill:
            fill = bg.fill
            if hasattr(fill, 'pattern') or hasattr(fill, 'background'):
                pass  # Complex backgrounds - skip for now
    except:
        pass

    # Process shapes in order (back to front)
    for shape in slide.shapes:
        try:
            left = emu_to_px(shape.left) if shape.left else 0
            top = emu_to_px(shape.top) if shape.top else 0
            width = emu_to_px(shape.width) if shape.width else w_px
            height = emu_to_px(shape.height) if shape.height else h_px

            # Handle picture shapes
            if shape.shape_type == MSO_SHAPE_TYPE.PICTURE or hasattr(shape, 'image'):
                try:
                    image_blob = shape.image.blob
                    shape_img = Image.open(io.BytesIO(image_blob))
                    # Convert to RGB if needed
                    if shape_img.mode in ('RGBA', 'LA', 'P'):
                        # Create white background for transparency
                        bg = Image.new('RGB', shape_img.size, bg_color)
                        if shape_img.mode == 'P':
                            shape_img = shape_img.convert('RGBA')
                        if shape_img.mode in ('RGBA', 'LA'):
                            bg.paste(shape_img, mask=shape_img.split()[-1])
                            shape_img = bg
                        else:
                            shape_img = shape_img.convert('RGB')

                    # Resize to fit shape bounds
                    shape_img = shape_img.resize((max(1, width), max(1, height)), Image.LANCZOS)
                    img.paste(shape_img, (left, top))
                except Exception as e:
                    pass  # Skip broken images

            # Handle placeholder images (some placeholders contain images)
            elif shape.shape_type == MSO_SHAPE_TYPE.PLACEHOLDER:
                try:
                    if hasattr(shape, 'image') and shape.image:
                        image_blob = shape.image.blob
                        shape_img = Image.open(io.BytesIO(image_blob))
                        if shape_img.mode in ('RGBA', 'LA', 'P'):
                            bg_layer = Image.new('RGB', shape_img.size, bg_color)
                            if shape_img.mode == 'P':
                                shape_img = shape_img.convert('RGBA')
                            if shape_img.mode in ('RGBA', 'LA'):
                                bg_layer.paste(shape_img, mask=shape_img.split()[-1])
                                shape_img = bg_layer
                            else:
                                shape_img = shape_img.convert('RGB')
                        shape_img = shape_img.resize((max(1, width), max(1, height)), Image.LANCZOS)
                        img.paste(shape_img, (left, top))
                except:
                    pass

            # Handle text
            if shape.has_text_frame:
                tf = shape.text_frame
                y_offset = top + 8

                for para in tf.paragraphs:
                    if not para.text.strip():
                        y_offset += 8
                        continue

                    for run in para.runs:
                        text = run.text
                        if not text.strip():
                            continue

                        font_size = get_font_size_px(run.font)
                        is_bold = run.font.bold
                        color = get_color_rgb(run.font.color)
                        if not color:
                            # Default text color - dark on light bg, light on dark bg
                            brightness = (bg_color[0] * 299 + bg_color[1] * 587 + bg_color[2] * 114) / 1000
                            color = (40, 40, 40) if brightness > 128 else (240, 240, 240)

                        if is_bold:
                            font = try_load_bold_font(font_size)
                        else:
                            font = try_load_font(font_size)

                        # Word wrap text within shape bounds
                        max_text_width = width - 16
                        lines = []
                        words = text.split()
                        current_line = ""
                        for word in words:
                            test_line = f"{current_line} {word}".strip()
                            bbox = font.getbbox(test_line)
                            text_w = bbox[2] - bbox[0]
                            if text_w <= max_text_width or not current_line:
                                current_line = test_line
                            else:
                                lines.append(current_line)
                                current_line = word
                        if current_line:
                            lines.append(current_line)

                        for line in lines:
                            if y_offset < top + height - 4:
                                draw.text((left + 8, y_offset), line, fill=color, font=font)
                                bbox = font.getbbox(line)
                                line_h = bbox[3] - bbox[1]
                                y_offset += line_h + 4

                    y_offset += 6  # paragraph spacing

        except Exception as e:
            continue  # Skip problematic shapes

    return img


def convert_pptx_to_images(pptx_path, output_dir, max_width=1920):
    """Convert a PPTX file to individual slide PNG images."""
    pptx_path = Path(pptx_path)
    output_dir = Path(output_dir)
    output_dir.mkdir(parents=True, exist_ok=True)

    prs = Presentation(str(pptx_path))
    slide_width = prs.slide_width
    slide_height = prs.slide_height
    slides = list(prs.slides)
    total = len(slides)

    results = []
    for i, slide in enumerate(slides):
        try:
            slide_img = render_slide(slide, slide_width, slide_height, i + 1)

            # Resize if too large for performance
            if slide_img.width > max_width:
                ratio = max_width / slide_img.width
                new_h = int(slide_img.height * ratio)
                slide_img = slide_img.resize((max_width, new_h), Image.LANCZOS)

            filename = f"slide_{i+1}.png"
            filepath = output_dir / filename
            slide_img.save(str(filepath), 'PNG', optimize=True)

            results.append({
                "slide": i + 1,
                "filename": filename,
                "width": slide_img.width,
                "height": slide_img.height,
                "size": os.path.getsize(str(filepath))
            })

            # Progress indicator
            print(f"PROGRESS:{i+1}/{total}", file=sys.stderr)

        except Exception as e:
            results.append({
                "slide": i + 1,
                "filename": None,
                "error": str(e)
            })

    return {
        "status": "success",
        "total_slides": total,
        "slides": results,
        "slide_width": emu_to_px(slide_width),
        "slide_height": emu_to_px(slide_height)
    }


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Usage: python pptx_to_images.py <input.pptx> <output_dir>"}))
        sys.exit(1)

    pptx_path = sys.argv[1]
    output_dir = sys.argv[2]

    if not os.path.exists(pptx_path):
        print(json.dumps({"status": "error", "message": f"File not found: {pptx_path}"}))
        sys.exit(1)

    result = convert_pptx_to_images(pptx_path, output_dir)
    print(json.dumps(result))
