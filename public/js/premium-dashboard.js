// Premium Dashboard JS
// Lightweight enhancements: header shadow, table zebra fallback, focus-visible helper

(function(){
  const doc = document;

  // Header shadow on scroll: add .has-shadow to elements with .header-premium
  const headers = Array.from(doc.querySelectorAll('.header-premium'));
  if (headers.length) {
    const onScroll = () => {
      const scrolled = window.scrollY > 2;
      headers.forEach(h => h.classList.toggle('has-shadow', scrolled));
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Table zebra fallback and ARIA roles when .table-premium present
  const tables = Array.from(doc.querySelectorAll('table.table-premium'));
  tables.forEach(table => {
    table.setAttribute('role', 'table');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    if (thead) thead.setAttribute('role', 'rowgroup');
    if (tbody) tbody.setAttribute('role', 'rowgroup');
    Array.from(table.querySelectorAll('tr')).forEach(tr => tr.setAttribute('role', 'row'));
    Array.from(table.querySelectorAll('th')).forEach(th => th.setAttribute('role', 'columnheader'));
    Array.from(table.querySelectorAll('td')).forEach(td => td.setAttribute('role', 'cell'));

    // If no zebra styles applied, add alternating backgrounds
    if (tbody) {
      const rows = Array.from(tbody.querySelectorAll('tr'));
      rows.forEach((tr, i) => {
        tr.classList.add('row-hover');
        if (!tr.style.backgroundColor) {
          if (i % 2 === 0) {
            tr.style.backgroundColor = 'transparent';
          } else {
            tr.style.backgroundColor = 'rgba(2, 6, 23, 0.02)';
          }
        }
      });
    }
  });

  // Focus-visible helper: add .focus-ring to clickable elements missing visible focus styles
  const focusableSelector = 'a, button, [role="button"], input, select, textarea';
  const clickable = Array.from(doc.querySelectorAll(focusableSelector));
  clickable.forEach(el => {
    // Avoid overriding Tailwind-native focus utilities if present
    const hasCustomFocus = el.className && /focus:|ring-|outline-/.test(el.className);
    if (!hasCustomFocus) el.classList.add('focus-ring');
  });
})();
