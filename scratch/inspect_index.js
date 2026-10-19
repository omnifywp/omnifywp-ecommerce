const fs = require('fs');
const content = fs.readFileSync('docs/index.html', 'utf8');
const lines = content.split('\n');
console.log('Total lines in docs/index.html:', lines.length);

lines.forEach((l, i) => {
  if (l.includes('<section') || l.includes('class="section-title"') || l.includes('id="section-')) {
    console.log((i + 1) + ': ' + l.trim());
  }
});
