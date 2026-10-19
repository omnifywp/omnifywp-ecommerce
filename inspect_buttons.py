with open('assets/admin/admin.css', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.finditer(r'/\* ([^*]+) \*/\s*\n(\.omnify-[^{]+button[^{]*\{[^}]+\})', text)
for m in matches:
    print(m.group(1).strip())
    print('  ', m.group(2)[:100].replace('\n', ' '))
