import markdown
from weasyprint import HTML, CSS

with open('ARCHITECTURE_ROADMAP.md', 'r', encoding='utf-8') as f:
    md_content = f.read()

html_content = markdown.markdown(md_content, extensions=['tables', 'fenced_code', 'codehilite', 'toc'])

css = CSS(string='''
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; max-width: 900px; margin: 0 auto; padding: 2rem; }
    h1, h2, h3, h4 { color: #333; }
    h1 { border-bottom: 2px solid #ff8c1a; padding-bottom: 0.5rem; }
    h2 { border-bottom: 1px solid #eee; padding-bottom: 0.3rem; margin-top: 2rem; }
    code { background: #f4f4f4; padding: 0.2em 0.4em; border-radius: 3px; }
    pre { background: #1e1e1e; color: #d4d4d4; padding: 1rem; border-radius: 5px; overflow-x: auto; }
    pre code { background: none; padding: 0; }
    table { border-collapse: collapse; width: 100%; margin: 1rem 0; }
    th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
    th { background: #f5f5f5; }
    blockquote { border-left: 4px solid #ff8c1a; padding-left: 1rem; color: #666; margin: 1rem 0; }
    a { color: #ff8c1a; }
''')

full_html = '<!DOCTYPE html>\n<html>\n<head>\n<meta charset="utf-8">\n</head>\n<body>\n' + html_content + '\n</body>\n</html>'

HTML(string=full_html).write_pdf('ARCHITECTURE_ROADMAP.pdf', stylesheets=[css])
print('PDF created successfully!')