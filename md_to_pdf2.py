import markdown
import re
from fpdf import FPDF

with open('ARCHITECTURE_ROADMAP.md', 'r', encoding='utf-8') as f:
    md_content = f.read()

# Конвертируем markdown в HTML
html_content = markdown.markdown(md_content, extensions=['tables', 'fenced_code', 'codehilite', 'toc'])


class MarkdownPDF(FPDF):
    def __init__(self):
        super().__init__()
        # Добавляем Unicode шрифт DejaVu для поддержки кириллицы
        self.add_font('DejaVu', '', 'DejaVuSans.ttf', uni=True)
        self.add_font('DejaVu', 'B', 'DejaVuSans-Bold.ttf', uni=True)
        self.add_font('DejaVu', 'I', 'DejaVuSans-Oblique.ttf', uni=True)
        self.add_font('DejaVu', 'BI', 'DejaVuSans-BoldOblique.ttf', uni=True)
        self.set_auto_page_break(auto=True, margin=20)

    def header(self):
        # Заголовок на всех страницах кроме первой
        if self.page_no() > 1:
            self.set_font('DejaVu', 'I', 8)
            self.set_text_color(128)
            self.cell(0, 5, 'MyBlog \u2014 Architecture Roadmap', align='C')
            self.ln(8)

    def footer(self):
        # Нумерация страниц
        self.set_y(-15)
        self.set_font('DejaVu', 'I', 8)
        self.set_text_color(128)
        self.cell(0, 10, f'Page {self.page_no()}/{{nb}}', align='C')

    def write_html(self, html):
        """Простой HTML-в-PDF конвертер для базовых тегов"""
        # Удаляем скрипты и стили
        html = re.sub(r'<script.*?</script>', '', html, flags=re.DOTALL | re.IGNORECASE)
        html = re.sub(r'<style.*?</style>', '', html, flags=re.DOTALL | re.IGNORECASE)

        # Заголовки
        html = re.sub(r'<h1[^>]*>(.*?)</h1>', r'\n\n# \1\n', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<h2[^>]*>(.*?)</h2>', r'\n\n## \1\n', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<h3[^>]*>(.*?)</h3>', r'\n\n### \1\n', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<h4[^>]*>(.*?)</h4>', r'\n\n#### \1\n', html, flags=re.IGNORECASE | re.DOTALL)

        # Жирный/курсив
        html = re.sub(r'<strong>(.*?)</strong>', r'**\1**', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<b>(.*?)</b>', r'**\1**', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<em>(.*?)</em>', r'*\1*', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<i>(.*?)</i>', r'*\1*', html, flags=re.IGNORECASE | re.DOTALL)

        # Код
        html = re.sub(r'<code>(.*?)</code>', r'`\1`', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<pre>(.*?)</pre>', r'\n```\n\1\n```\n', html, flags=re.IGNORECASE | re.DOTALL)

        # Ссылки
        html = re.sub(r'<a href="([^"]*)"[^>]*>(.*?)</a>', r'\2 (\1)', html, flags=re.IGNORECASE | re.DOTALL)

        # Списки
        html = re.sub(r'<li[^>]*>(.*?)</li>', r'- \1\n', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'</?(ul|ol)[^>]*>', '\n', html, flags=re.IGNORECASE)

        # Параграфы и переносы
        html = re.sub(r'<p[^>]*>(.*?)</p>', r'\n\n\1\n', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<br\s*/?>', '\n', html, flags=re.IGNORECASE)
        html = re.sub(r'<hr\s*/?>', '\n---\n', html, flags=re.IGNORECASE)

        # Цитаты
        html = re.sub(r'<blockquote[^>]*>(.*?)</blockquote>', r'\n> \1\n', html, flags=re.IGNORECASE | re.DOTALL)

        # Удаляем оставшиеся теги
        html = re.sub(r'<[^>]+>', '', html)

        # Декодируем HTML-сущности
        html = html.replace('&nbsp;', ' ').replace('&', '&').replace('<', '<').replace('>', '>').replace('"', '"').replace(''', "'")

        # Чистим множественные переносы
        html = re.sub(r'\n{3,}', '\n\n', html)

        self.write_markdown(html)

    def write_markdown(self, md):
        lines = md.split('\n')
        in_code_block = False
        code_lines = []

        for line in lines:
            # Блоки кода
            if line.strip().startswith('```'):
                if not in_code_block:
                    in_code_block = True
                    code_lines = []
                else:
                    in_code_block = False
                    self.set_font('DejaVu', '', 8)
                    self.set_fill_color(240, 240, 240)
                    for cl in code_lines:
                        self.multi_cell(0, 4, cl, fill=True)
                    self.ln(2)
                continue

            if in_code_block:
                code_lines.append(line)
                continue

            # Заголовки
            if line.startswith('# '):
                self.ln(4)
                self.set_font('DejaVu', 'B', 16)
                self.set_text_color(51, 51, 51)
                self.multi_cell(0, 8, line[2:].strip())
                self.ln(2)
                continue
            elif line.startswith('## '):
                self.ln(3)
                self.set_font('DejaVu', 'B', 14)
                self.set_text_color(51, 51, 51)
                self.multi_cell(0, 7, line[3:].strip())
                self.ln(1)
                continue
            elif line.startswith('### '):
                self.ln(2)
                self.set_font('DejaVu', 'B', 12)
                self.set_text_color(51, 51, 51)
                self.multi_cell(0, 6, line[4:].strip())
                self.ln(1)
                continue
            elif line.startswith('#### '):
                self.ln(1)
                self.set_font('DejaVu', 'B', 11)
                self.set_text_color(51, 51, 51)
                self.multi_cell(0, 6, line[5:].strip())
                self.ln(1)
                continue

            # Горизонтальная линия
            if line.strip() == '---':
                self.ln(2)
                self.set_draw_color(200, 200, 200)
                self.line(10, self.get_y(), 200, self.get_y())
                self.ln(4)
                continue

            # Цитаты
            if line.startswith('> '):
                self.set_font('DejaVu', 'I', 10)
                self.set_text_color(100, 100, 100)
                self.set_x(15)
                self.multi_cell(180, 5, line[2:].strip())
                self.ln(1)
                continue

            # Элементы списка
            if line.strip().startswith('- '):
                self.set_font('DejaVu', '', 10)
                self.set_text_color(51, 51, 51)
                self.set_x(15)
                # Обрабатываем inline форматирование
                self.write_formatted_text(line[2:].strip())
                self.ln(1)
                continue

            # Таблицы - упрощённо
            if '|' in line and line.count('|') > 1:
                self.set_font('DejaVu', '', 8)
                self.set_text_color(51, 51, 51)
                cells = [c.strip() for c in line.split('|') if c.strip()]
                if cells:
                    col_width = 180 / len(cells)
                    for cell in cells:
                        self.cell(col_width, 5, cell[:int(col_width/2)], border=1)
                    self.ln()
                continue

            # Обычный параграф
            if line.strip():
                self.set_font('DejaVu', '', 10)
                self.set_text_color(51, 51, 51)
                self.write_formatted_text(line.strip())
                self.ln(1)
            else:
                self.ln(3)

    def write_formatted_text(self, text):
        """Пишет текст с базовым markdown форматированием (жирный, курсив, код)"""
        # Разбиваем по маркерам форматирования
        parts = re.split(r'(\*\*.*?\*\*|\*.*?\*|`.*?`)', text)
        for part in parts:
            if part.startswith('**') and part.endswith('**'):
                self.set_font('DejaVu', 'B', 10)
                self.write(5, part[2:-2])
            elif part.startswith('*') and part.endswith('*') and not part.startswith('**'):
                self.set_font('DejaVu', 'I', 10)
                self.write(5, part[1:-1])
            elif part.startswith('`') and part.endswith('`'):
                self.set_font('DejaVu', '', 9)
                self.set_fill_color(230, 230, 230)
                self.cell(self.get_string_width(part[1:-1]) + 2, 5, part[1:-1], fill=True)
            else:
                self.set_font('DejaVu', '', 10)
                self.write(5, part)


pdf = MarkdownPDF()
pdf.alias_nb_pages()
pdf.add_page()
pdf.set_title('MyBlog Architecture Roadmap')
pdf.set_author('Valentin')

# Титульная страница
pdf.ln(30)
pdf.set_font('DejaVu', 'B', 24)
pdf.set_text_color(255, 140, 26)
pdf.cell(0, 12, 'MyBlog', align='C', new_x="LMARGIN", new_y="NEXT")
pdf.set_font('DejaVu', '', 16)
pdf.set_text_color(51, 51, 51)
pdf.cell(0, 10, 'Architecture Audit & Roadmap', align='C', new_x="LMARGIN", new_y="NEXT")
pdf.ln(10)
pdf.set_font('DejaVu', '', 11)
pdf.set_text_color(100, 100, 100)
pdf.cell(0, 8, 'Personal Blog, Portfolio & CRM on Custom PHP MVC', align='C', new_x="LMARGIN", new_y="NEXT")
pdf.cell(0, 8, 'Generated: 2024', align='C', new_x="LMARGIN", new_y="NEXT")

# Страница с оглавлением
pdf.add_page()
pdf.set_font('DejaVu', 'B', 16)
pdf.set_text_color(51, 51, 51)
pdf.cell(0, 10, 'Table of Contents', new_x="LMARGIN", new_y="NEXT")
pdf.ln(5)
toc_items = [
    ('1.', 'Current State Overview', 3),
    ('2.', 'Refactoring / Architecture', 4),
    ('3.', 'Frontend - Vue 3 Migration', 8),
    ('4.', 'Performance Optimization', 11),
    ('5.', 'New Features (API, Webhooks, Queue, Tests)', 13),
    ('6.', 'CI/CD & Deployment', 16),
    ('7.', 'Roadmap (9-Week Plan)', 19),
    ('8.', 'Immediate Next Steps', 20),
]
for num, title, page in toc_items:
    pdf.set_font('DejaVu', '', 11)
    pdf.set_text_color(51, 51, 51)
    pdf.cell(10, 7, num)
    pdf.cell(150, 7, title)
    pdf.cell(0, 7, str(page), align='R', new_x="LMARGIN", new_y="NEXT")

# Основной контент
pdf.add_page()
pdf.write_html(html_content)

output_path = 'ARCHITECTURE_ROADMAP.pdf'
pdf.output(output_path)
print(f'PDF created: {output_path}')