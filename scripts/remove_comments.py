import os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
EXTENSIONS = {'.php', '.css', '.js', '.sql', '.html', '.htm', '.md'}


def strip_comments(text, extension):
    if extension in {'.html', '.htm', '.md'}:
        markers = [('<!--', '-->')]
    elif extension == '.sql':
        markers = [('/*', '*/'), ('--', '\n')]
    else:
        markers = [('/*', '*/'), ('//', '\n')]

    result = []
    i = 0
    quote = None
    escape = False
    while i < len(text):
        if quote is not None:
            ch = text[i]
            result.append(ch)
            if escape:
                escape = False
            elif ch == '\\':
                escape = True
            elif ch == quote:
                quote = None
            i += 1
            continue

        if text[i] in "'\"`":
            quote = text[i]
            result.append(text[i])
            i += 1
            continue

        matched = None
        for start, end in markers:
            if text.startswith(start, i):
                matched = (start, end)
                break
        if matched is None:
            result.append(text[i])
            i += 1
            continue

        _, end = matched
        end_at_newline = end == '\n'
        if end_at_newline:
            close = text.find('\n', i + 2)
            if close < 0:
                result.append('\n' if text.endswith('\n') else '')
                break
            result.append('\n')
            i = close + 1
        else:
            close = text.find(end, i + 2)
            if close < 0:
                break
            removed = text[i:close + len(end)]
            result.extend('\n' for ch in removed if ch == '\n')
            i = close + len(end)
    return ''.join(result)


for directory, _, filenames in os.walk(ROOT):
    if '.git' in directory.split(os.sep):
        continue
    for filename in filenames:
        path = os.path.join(directory, filename)
        if os.path.splitext(filename)[1].lower() not in EXTENSIONS:
            continue
        with open(path, 'r', encoding='utf-8') as handle:
            original = handle.read()
        cleaned = strip_comments(original, os.path.splitext(filename)[1].lower())
        if cleaned != original:
            with open(path, 'w', encoding='utf-8', newline='') as handle:
                handle.write(cleaned)
