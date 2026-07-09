#!/usr/bin/env python3
"""Generate PDF from Markdown documentation."""

import markdown
from xhtml2pdf import pisa
import os

def convert_md_to_pdf(md_path, pdf_path):
    # Read markdown
    with open(md_path, 'r', encoding='utf-8') as f:
        md_content = f.read()

    # Convert markdown to HTML
    html_body = markdown.markdown(
        md_content,
        extensions=['tables', 'fenced_code', 'toc', 'nl2br']
    )

    # Full HTML with CSS styling
    html = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page {{
        size: A4;
        margin: 2cm 2.5cm;
    }}
    body {{
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.6;
        color: #333;
    }}
    h1 {{
        font-size: 22pt;
        color: #1a56db;
        border-bottom: 3px solid #1a56db;
        padding-bottom: 8px;
        margin-top: 30px;
        page-break-before: auto;
    }}
    h2 {{
        font-size: 16pt;
        color: #1e40af;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
        margin-top: 25px;
        page-break-before: auto;
    }}
    h3 {{
        font-size: 13pt;
        color: #2563eb;
        margin-top: 18px;
    }}
    h4 {{
        font-size: 11pt;
        color: #374151;
        margin-top: 12px;
    }}
    p {{
        margin: 6px 0;
        text-align: justify;
    }}
    table {{
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 9pt;
    }}
    th {{
        background-color: #1a56db;
        color: white;
        padding: 8px 10px;
        text-align: left;
        font-weight: bold;
    }}
    td {{
        padding: 6px 10px;
        border-bottom: 1px solid #e5e7eb;
    }}
    tr:nth-child(even) td {{
        background-color: #f9fafb;
    }}
    code {{
        background-color: #f3f4f6;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 9pt;
        color: #dc2626;
    }}
    pre {{
        background-color: #f3f4f6;
        padding: 12px;
        border-radius: 6px;
        font-family: 'Courier New', monospace;
        font-size: 9pt;
        overflow: hidden;
        white-space: pre-wrap;
    }}
    pre code {{
        background: none;
        padding: 0;
        color: #333;
    }}
    strong {{
        color: #111;
    }}
    ul, ol {{
        margin: 6px 0;
        padding-left: 25px;
    }}
    li {{
        margin: 3px 0;
    }}
    hr {{
        border: none;
        border-top: 2px solid #e5e7eb;
        margin: 20px 0;
    }}
    blockquote {{
        border-left: 4px solid #1a56db;
        margin: 10px 0;
        padding: 8px 15px;
        background-color: #eff6ff;
        font-style: italic;
    }}
    a {{
        color: #1a56db;
        text-decoration: none;
    }}
</style>
</head>
<body>
{html_body}
</body>
</html>"""

    # Generate PDF
    with open(pdf_path, "w+b") as f:
        status = pisa.CreatePDF(html, dest=f)
        if status.err:
            print(f"Error generating PDF: {status.err}")
            return False

    print(f"PDF generated successfully: {pdf_path}")
    print(f"File size: {os.path.getsize(pdf_path) / 1024:.1f} KB")
    return True

if __name__ == '__main__':
    base_dir = os.path.dirname(os.path.abspath(__file__))
    md_path = os.path.join(base_dir, 'ERP-User-Guide.md')
    pdf_path = os.path.join(base_dir, 'ERP-User-Guide.pdf')
    convert_md_to_pdf(md_path, pdf_path)
