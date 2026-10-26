const { chromium } = require('playwright');
const fs = require('fs');
const S = process.argv[2], SITE = S + '/designzip/ausvac-website', OUT = S + '/build';
const local = {
  'https://unpkg.com/react@18.3.1/umd/react.production.min.js': S + '/node_modules/react/umd/react.production.min.js',
  'https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js': S + '/node_modules/react-dom/umd/react-dom.production.min.js',
  'https://unpkg.com/@babel/standalone@7.29.0/babel.min.js': S + '/node_modules/@babel/standalone/babel.min.js',
};
const PUB_TOPICS = ['safety','floor','floor','sucker','floor','floor','floor','sucker','ops','ops','access','access','sucker'];
const PREFIX = { company: 'co', services: 'sv', publications: 'pb', contact: 'ct' };
(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  for (const page of Object.keys(PREFIX)) {
    const ctx = await b.newContext({ viewport: { width: 1440, height: 900 }, reducedMotion: 'reduce' });
    const p = await ctx.newPage();
    await ctx.route('**/*', r => {
      const u = r.request().url();
      if (local[u]) return r.fulfill({ path: local[u], contentType: 'application/javascript' });
      if (u.startsWith('file://') || u.includes('fonts.g')) return r.continue();
      return r.fulfill({ status: 204, body: '' });
    });
    await p.goto('file://' + SITE + '/' + page + '.html');
    await p.waitForTimeout(3500);
    const res = await p.evaluate(({ pfx, pubTopics }) => {
      // 1. hover/focus rules generated at runtime
      const map = {}; let n = 0; const rules = [];
      for (const sh of document.styleSheets) {
        let rs; try { rs = sh.cssRules; } catch (e) { continue; }
        for (const r of rs) {
          const m = r.selectorText && r.selectorText.match(/^\.(scp[0-9a-z]+)(::?[a-z-]+)$/);
          if (!m) continue;
          const nc = 'av-' + pfx + '-x' + (n++).toString(36);
          map[m[1]] = nc;
          rules.push('.' + nc + m[2] + '{' + r.style.cssText + '}');
        }
      }
      // 2. sections to keep
      const secs = [...document.querySelectorAll('section[data-screen-label]')]
        .filter(s => !s.parentElement.closest('section[data-screen-label]'))
        .filter(s => !/Footer/i.test(s.getAttribute('data-screen-label')));
      const pages = { 'index.html': '/', 'company.html': '/company/', 'services.html': '/services/', 'publications.html': '/publications/', 'contact.html': '/contact/' };
      const out = secs.map(sec => {
        const c = sec.cloneNode(true);
        c.removeAttribute('data-dc-tpl');
        c.querySelectorAll('header[data-screen-label="Nav"]').forEach(h => { h.parentElement.setAttribute('data-av', 'heroinner'); h.remove(); });
        // image-slot -> img
        c.querySelectorAll('image-slot').forEach(sl => {
          const img = document.createElement('img');
          img.src = sl.getAttribute('src') || '';
          img.alt = sl.getAttribute('placeholder') || '';
          if (sl.id) img.setAttribute('data-slot', sl.id);
          img.setAttribute('loading', 'lazy'); img.setAttribute('decoding', 'async');
          img.setAttribute('style', (sl.getAttribute('style') || '') + ';object-fit:cover;display:block;max-width:none;');
          sl.replaceWith(img);
        });
        c.querySelectorAll('*').forEach(el => {
          el.removeAttribute('data-dc-tpl'); el.removeAttribute('data-filled');
          if (el.classList.length) {
            const cl = [...el.classList].map(k => map[k] || (k.startsWith('sc') ? '' : k)).filter(Boolean);
            if (cl.length) el.setAttribute('class', cl.join(' ')); else el.removeAttribute('class');
          }
          const href = el.getAttribute('href');
          if (href) { const base = href.split('#')[0]; if (pages[base] !== undefined) el.setAttribute('href', pages[base] + (href.includes('#') ? '#' + href.split('#')[1] : '')); }
          ['src'].forEach(a => { const v = el.getAttribute(a); if (v && v.startsWith('assets/')) el.setAttribute(a, '{{ASSET:' + v.slice(7) + '}}'); });
        });
        return c;
      });
      // 3. interactive hooks
      const all = document.createElement('div'); out.forEach(c => all.appendChild(c));
      const hw = all.querySelector('section[data-screen-label="01 Hero"] > div[style*="will-change"]'); if (hw) hw.setAttribute('data-av', 'herowrap');
      const fr = all.querySelector('iframe[src="projects-map.html"]');
      if (fr) {
        fr.setAttribute('data-av', 'map'); fr.setAttribute('src', '{{ASSET:projects-map.html}}');
        const box = fr.parentElement; box.parentElement.setAttribute('data-av', 'projwrap');
        box.nextElementSibling.setAttribute('data-av', 'panel');
        const tlBtn = all.querySelector('button[title]'); tlBtn.parentElement.setAttribute('data-av', 'tl');
        const tr = [...all.querySelectorAll('div')].find(d => d.children.length === 16 && /max-content/.test(d.getAttribute('style') || ''));
        tr.setAttribute('data-av', 'track'); tr.parentElement.setAttribute('data-av', 'rail');
        [...tr.children].forEach((bt, i) => bt.setAttribute('data-proj', String(i % 8)));
      }
      const pubList = [...all.querySelectorAll('div')].find(d => /border-top:\s*2px solid/.test(d.getAttribute('style') || '') && d.children.length === 13);
      if (pubList) {
        pubList.setAttribute('data-av', 'publist');
        [...pubList.children].forEach((r, i) => r.setAttribute('data-topic', pubTopics[i]));
        const chipWrap = [...all.querySelectorAll('div')].find(d => d.children.length === 6 && [...d.children].every(x => x.tagName === 'BUTTON'));
        chipWrap.setAttribute('data-av', 'chips');
        ['all', 'floor', 'sucker', 'access', 'ops', 'safety'].forEach((k, i) => chipWrap.children[i].setAttribute('data-topic', k));
      }
      const form = all.querySelector('form'); if (form) form.setAttribute('data-av', 'form');
      return { rules, sections: out.map(c => ({ label: c.getAttribute('data-screen-label'), html: c.outerHTML })) };
    }, { pfx: PREFIX[page], pubTopics: PUB_TOPICS });
    fs.writeFileSync(`${OUT}/${page}.json`, JSON.stringify(res));
    console.log(page, 'sections', res.sections.map(s => s.label + ':' + s.html.length).join(' '), 'rules', res.rules.length);
    await ctx.close();
  }
  await b.close();
})();
