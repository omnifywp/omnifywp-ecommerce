const fs = require('fs');
const path = require('path');

const themeDir = 'c:/Users/rashe/Local Sites/wp/app/public/wp-content/themes/omnify';
const themeJsPath = path.join(themeDir, 'assets/js/theme.js');
const styleCssPath = path.join(themeDir, 'style.css');

// 1. Update theme.js
let js = fs.readFileSync(themeJsPath, 'utf8');
const changelogJsSnippet = `
  // ─── 15. Interactive Changelog Controller ──────────────────────────────────
  ( function initChangelogController() {
    const filters = document.querySelectorAll( '.om-ch-filter' );
    const entries = document.querySelectorAll( '.om-ch-entry' );
    if ( ! filters.length || ! entries.length ) return;

    filters.forEach( function ( btn ) {
      btn.addEventListener( 'click', function () {
        filters.forEach( function ( f ) {
          f.classList.remove( 'is-active' );
          f.style.background = '#FFFFFF';
          f.style.color = '#64748B';
          f.style.borderColor = '#E2E8F0';
          f.style.fontWeight = '600';
        } );
        btn.classList.add( 'is-active' );
        btn.style.background = '#EFF8F2';
        btn.style.color = '#0B5135';
        btn.style.borderColor = '#D4E8DC';
        btn.style.fontWeight = '700';

        const version = btn.getAttribute( 'data-version' );
        entries.forEach( function ( entry ) {
          const entryVer = entry.getAttribute( 'data-version' );
          if ( version === 'all' || entryVer === version ) {
            entry.style.display = '';
            entry.style.opacity = '1';
          } else {
            entry.style.display = 'none';
          }
        } );
      } );
    } );
  } )();
`;

if (!js.includes('initChangelogController')) {
  const marker = '// ─── Reusable eCommerce Animation Helpers ───';
  if (js.includes(marker)) {
    js = js.replace(marker, changelogJsSnippet + '\n  ' + marker);
    fs.writeFileSync(themeJsPath, js, 'utf8');
    console.log('Successfully added initChangelogController to theme.js');
  } else {
    console.log('Could not find marker in theme.js');
  }
} else {
  console.log('initChangelogController already present in theme.js');
}

// 2. Update style.css
let css = fs.readFileSync(styleCssPath, 'utf8');
const changelogCssSnippet = `
/* ============================================================
 * CHANGELOG & DOCUMENTATION PAGE ENHANCEMENTS
 * ============================================================ */
.om-ch-filter {
  transition: all 0.2s ease;
}
.om-ch-filter:hover {
  background: #EFF8F2 !important;
  color: #0B5135 !important;
  border-color: #D4E8DC !important;
}
.om-ch-entry {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.om-ch-entry:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(11, 81, 53, 0.08) !important;
}
.om-doc-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.om-doc-card:hover {
  transform: translateY(-3px);
  border-color: #22A06B !important;
  box-shadow: 0 8px 24px rgba(11, 81, 53, 0.08) !important;
}
.om-doc-section {
  scroll-margin-top: 90px;
}
.om-doc-section code {
  font-family: var(--om-font-mono);
  background: #F1F5F9;
  color: #0B5135;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.85em;
  border: 1px solid #E2E8F0;
}
.om-doc-section table td code {
  background: #ECFDF5;
  color: #047857;
  border-color: #D1FAE5;
}
`;

if (!css.includes('CHANGELOG & DOCUMENTATION PAGE ENHANCEMENTS')) {
  css += '\n' + changelogCssSnippet + '\n';
  fs.writeFileSync(styleCssPath, css, 'utf8');
  console.log('Successfully appended changelog and docs styles to style.css');
} else {
  console.log('Changelog styles already present in style.css');
}
