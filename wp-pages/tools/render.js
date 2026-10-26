const { chromium } = require('playwright');
const fs = require('fs'); const path = require('path');
const S = process.argv[2], SITE = S + '/designzip/ausvac-website', OUT = S + '/render';
const local = {
  'https://unpkg.com/react@18.3.1/umd/react.production.min.js': S + '/node_modules/react/umd/react.production.min.js',
  'https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js': S + '/node_modules/react-dom/umd/react-dom.production.min.js',
  'https://unpkg.com/@babel/standalone@7.29.0/babel.min.js': S + '/node_modules/@babel/standalone/babel.min.js',
};
(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  for (const page of ['company', 'services', 'publications', 'contact']) {
    for (const [w, h] of [[1440, 900], [390, 844]]) {
      const ctx = await b.newContext({ viewport: { width: w, height: h }, reducedMotion: 'reduce' });
      const p = await ctx.newPage(); const errs = [];
      p.on('pageerror', e => errs.push(e.message));
      await ctx.route('**/*', r => {
        const u = r.request().url();
        if (local[u]) return r.fulfill({ path: local[u], contentType: 'application/javascript' });
        if (u.startsWith('file://')) return r.continue();
        if (u.includes('fonts.g')) return r.continue();
        return r.fulfill({ status: 204, body: '' });
      });
      await p.goto('file://' + SITE + '/' + page + '.html');
      await p.waitForTimeout(3500);
      await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 400) { scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); } scrollTo(0, 0); });
      await p.waitForTimeout(1500);
      if (w === 1440) fs.writeFileSync(`${OUT}/${page}.rendered.html`, await p.content());
      await p.screenshot({ path: `${OUT}/${page}-${w}.png`, fullPage: true });
      const info = await p.evaluate(() => ({ h: document.body.scrollHeight, sections: [...document.querySelectorAll('[data-screen-label]')].map(s => s.getAttribute('data-screen-label')) }));
      console.log(page, w, JSON.stringify(info), errs.length ? 'ERR ' + errs.slice(0, 2).join(' | ') : '');
      await ctx.close();
    }
  }
  await b.close();
})();
