/* Ausvac inner pages (Company, Services, Publications, Contact): scroll
   reveals, hero parallax, the Services project explorer, the Publications
   topic filter and the Contact form. Markup comes from the design package;
   the hooks are data-av / data-reveal attributes. */
(function () {
  'use strict';
  var root = document.querySelector('.av-page');
  if (!root) { return; }
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var EASE = 'cubic-bezier(.2,.7,.2,1)';

  /* ---- scroll reveals (everything outside the hero) ---- */
  function reveal() {
    if (reduce || !('IntersectionObserver' in window)) { return; }
    var els = [].slice.call(root.querySelectorAll('section:not([data-screen-label="01 Hero"]) [data-reveal], section:not([data-screen-label="01 Hero"]) [data-reveal-img]'));
    els.forEach(function (el) { if (el.getBoundingClientRect().top > innerHeight) { el.style.opacity = '0'; } });
    var io = new IntersectionObserver(function (ents) {
      ents.forEach(function (en) {
        if (!en.isIntersecting) { return; }
        var el = en.target; io.unobserve(el);
        if (el.style.opacity !== '0') { return; }
        var sibs = el.parentElement ? [].filter.call(el.parentElement.children, function (c) { return c.hasAttribute('data-reveal'); }) : [];
        var d = Math.max(0, sibs.indexOf(el)) * 90;
        var img = el.hasAttribute('data-reveal-img');
        el.style.opacity = '';
        if (!el.animate) { return; }
        el.animate(img ? [{ opacity: 0, transform: 'scale(1.08)', filter: 'blur(6px)' }, { opacity: 1, transform: 'scale(1)', filter: 'blur(0)' }]
                       : [{ opacity: 0, transform: 'translateY(32px)' }, { opacity: 1, transform: 'translateY(0)' }],
          { duration: img ? 1100 : 750, delay: d, easing: EASE, fill: 'backwards' });
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el) { io.observe(el); });
    /* never leave anything hidden (e.g. very tall elements, fast jumps) */
    setTimeout(function () { els.forEach(function (el) { if (el.style.opacity === '0' && el.getBoundingClientRect().top < innerHeight) { el.style.opacity = ''; } }); }, 3000);
  }

  /* ---- hero parallax ---- */
  function parallax() {
    var hw = root.querySelector('[data-av="herowrap"]');
    if (!hw || reduce) { return; }
    var tick = false;
    addEventListener('scroll', function () {
      if (tick) { return; } tick = true;
      requestAnimationFrame(function () {
        tick = false; var y = scrollY;
        if (y < innerHeight * 1.2) { hw.style.transform = 'translateY(' + (y * 0.3) + 'px) scale(' + (1 + y / 6000) + ')'; }
      });
    }, { passive: true });
  }

  /* ---- Services: project explorer ---- */
  var PROJECTS = [{"n": 1, "site": "Mt Pleasant, WA", "when": "May 2003", "metal": "GOLD", "bg": "#FEC306", "kind": "SURFACE", "state": "WA", "note": "Vehicles clean-up sump on surface", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-29506757.jpeg"}, {"n": 2, "site": "Otter-Juan Mine UG, WA", "when": "August 2003", "metal": "NICKEL", "bg": "#C9D2CC", "kind": "UNDERGROUND", "state": "WA", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-33774180.jpeg"}, {"n": 3, "site": "Bendigo Mine UG, Victoria", "when": "October 2003", "metal": "GOLD", "bg": "#FEC306", "kind": "UNDERGROUND", "state": "VIC", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-14484399.jpeg"}, {"n": 4, "site": "Cosmos Mine UG, WA", "when": "December 2003", "metal": "NICKEL", "bg": "#C9D2CC", "kind": "UNDERGROUND", "state": "WA", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-15071425.jpeg"}, {"n": 5, "site": "Agnew Mine UG, WA", "when": "February 2004", "metal": "GOLD", "bg": "#FEC306", "kind": "UNDERGROUND", "state": "WA", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-27533504.jpeg"}, {"n": 6, "site": "Long-Victor UG Complex, WA", "when": "October 2005", "metal": "NICKEL", "bg": "#C9D2CC", "kind": "UNDERGROUND", "state": "WA", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-14484386.jpeg"}, {"n": 7, "site": "Redross UG, Widgiemooltha, WA", "when": "February 2007", "metal": "NICKEL", "bg": "#C9D2CC", "kind": "UNDERGROUND", "state": "WA", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-2101140.jpeg"}, {"n": 8, "site": "Harlequin Mine UG, CNGC, WA", "when": "November 2010 +", "metal": "GOLD", "bg": "#FEC306", "kind": "UNDERGROUND", "state": "WA", "note": "40.02 g/t Au – 131.9 kg in-stope decline floor sample", "src": "https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-11347699.jpeg"}];
  function projects() {
    var panel = root.querySelector('[data-av="panel"]');
    if (!panel) { return; }
    var map = root.querySelector('[data-av="map"]');
    var tl = root.querySelector('[data-av="tl"]');
    var wrap = root.querySelector('[data-av="projwrap"]');
    var img = panel.querySelector('img');
    var big = panel.children[2];
    var body = panel.querySelector('div');
    var tags = body.children[0].children;
    var site = body.children[1], when = body.children[2];
    var note = body.children[3];
    var foot = body.children[4];
    var pos = foot.querySelector('span');
    var btns = foot.querySelectorAll('button');
    var bar = panel.lastElementChild;
    var fill = tl.children[1];
    var nodes = tl.querySelectorAll('button');
    var cur = 0, t = 0, paused = false, last = performance.now();

    function txt(el, v) { var s = el.querySelector('span') || el; s.textContent = v; }
    function pick(i, auto) {
      cur = (i + 8) % 8; if (!auto) { t = 0; }
      var p = PROJECTS[cur];
      img.src = p.src; img.alt = p.site;
      big.style.webkitTextStroke = '1.5px ' + p.bg; big.lastChild.textContent = p.n;
      tags[0].style.background = p.bg; txt(tags[0], p.metal); txt(tags[1], p.kind); txt(tags[2], p.state);
      txt(site, p.site); txt(when, p.when);
      if (p.note) { note.style.display = ''; txt(note, p.note); } else { note.style.display = 'none'; }
      pos.firstChild.textContent = '0' + p.n;
      fill.style.width = (4 + cur / 7 * 92) + '%';
      [].forEach.call(nodes, function (b, k) {
        var on = k === cur, past = k <= cur, q = PROJECTS[k];
        b.style.top = on ? '13px' : '17px';
        var dot = b.children[0];
        dot.style.width = dot.style.height = on ? '22px' : '14px';
        dot.style.background = past ? q.bg : '#1E2622';
        dot.style.boxShadow = on ? '0 0 0 6px rgba(254,195,6,.22)' : 'none';
        b.children[1].children[0].style.color = on ? '#FEC306' : 'rgba(255,255,255,.55)';
      });
      if (map && map.contentWindow) { map.contentWindow.postMessage({ type: 'ausvac-sel', n: cur }, '*'); }
      if (panel.animate && !reduce) { panel.animate([{ opacity: 0.35, transform: 'scale(.985)' }, { opacity: 1, transform: 'none' }], { duration: 500, easing: EASE }); }
    }
    btns[0].addEventListener('click', function () { pick(cur - 1); });
    btns[1].addEventListener('click', function () { pick(cur + 1); });
    [].forEach.call(nodes, function (b, k) { b.addEventListener('click', function () { pick(k); }); });
    root.querySelectorAll('[data-proj]').forEach(function (b) { b.addEventListener('click', function () { pick(+b.getAttribute('data-proj')); }); });
    addEventListener('message', function (e) { if (e.data && e.data.type === 'ausvac-pick') { pick(e.data.n); } });
    if (map) { map.addEventListener('load', function () { pick(cur, true); }); }
    if (wrap) {
      wrap.addEventListener('mouseenter', function () { paused = true; });
      wrap.addEventListener('mouseleave', function () { paused = false; });
    }
    (function step(now) {
      var dt = Math.min(64, now - last); last = now;
      if (!paused && !reduce && !document.hidden) { t += dt / 6000; if (t >= 1) { t = 0; pick(cur + 1, true); } }
      bar.style.transform = 'scaleX(' + t + ')';
      requestAnimationFrame(step);
    })(last);
    PROJECTS.forEach(function (p) { var i = new Image(); i.src = p.src; });
  }

  /* ---- Services: auto-scrolling project rail ---- */
  function rail() {
    var r = root.querySelector('[data-av="rail"]'), tr = root.querySelector('[data-av="track"]');
    if (!r || !tr || reduce) { return; }
    var x = 0, hold = false, last = performance.now();
    r.addEventListener('mouseenter', function () { hold = true; });
    r.addEventListener('mouseleave', function () { hold = false; });
    r.addEventListener('touchstart', function () { hold = true; }, { passive: true });
    r.addEventListener('touchend', function () { setTimeout(function () { hold = false; }, 2500); });
    (function step(now) {
      var dt = Math.min(64, now - last); last = now;
      if (!hold && !document.hidden) {
        var half = tr.scrollWidth / 2; x -= dt * 0.05; if (-x >= half) { x += half; }
        tr.style.transform = 'translateX(' + x + 'px)';
      }
      requestAnimationFrame(step);
    })(last);
  }

  /* ---- Publications: topic filter ---- */
  function filter() {
    var chips = root.querySelector('[data-av="chips"]'), list = root.querySelector('[data-av="publist"]');
    if (!chips || !list) { return; }
    var bs = [].slice.call(chips.children);
    function set(k) {
      bs.forEach(function (b) {
        var on = b.getAttribute('data-topic') === k;
        b.style.background = on ? '#1E2622' : 'transparent';
        b.style.color = on ? '#FFFFFF' : '#1E2622';
        b.style.borderColor = on ? '#1E2622' : '#C9CEC6';
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      [].forEach.call(list.children, function (row) {
        row.style.display = (k === 'all' || row.getAttribute('data-topic') === k) ? '' : 'none';
      });
    }
    bs.forEach(function (b) { b.type = 'button'; b.addEventListener('click', function () { set(b.getAttribute('data-topic')); }); });
    set('all');
  }

  /* ---- Contact form ---- */
  function form() {
    var f = root.querySelector('[data-av="form"]');
    if (!f) { return; }
    var hp = document.createElement('input');
    hp.type = 'text'; hp.name = 'website'; hp.tabIndex = -1; hp.autocomplete = 'off';
    hp.setAttribute('aria-hidden', 'true');
    hp.style.cssText = 'position:absolute;left:-9999px;width:1px;height:1px;opacity:0;';
    f.appendChild(hp);
    var started = Date.now();
    var btn = f.querySelector('button[type="submit"]');
    var msg = document.createElement('p');
    msg.setAttribute('role', 'alert');
    msg.style.cssText = 'margin:0;font-size:13px;line-height:1.6;color:#9A2B1F;display:none;';
    f.appendChild(msg);
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      msg.style.display = 'none';
      var data = new FormData(f);
      data.append('action', 'ausvac_contact');
      data.append('elapsed', String(Date.now() - started));
      btn.disabled = true; btn.style.opacity = '.6';
      fetch(f.getAttribute('data-endpoint') || '/wp-admin/admin-ajax.php', { method: 'POST', body: data, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res || !res.success) { throw new Error(res && res.data ? res.data : 'Something went wrong.'); }
          var ok = document.createElement('div');
          ok.setAttribute('role', 'status');
          ok.style.cssText = 'position:relative;display:grid;gap:10px;padding:24px;border:1.5px solid #FEC306;';
          ok.innerHTML = '<strong style="font-size:20px;font-weight:700;color:#2B5C04;">Thank you</strong><span style="font-size:14px;line-height:1.7;color:#4A524D;">Your message has been sent to Ausvac Mining Pty Ltd.</span>';
          f.replaceWith(ok);
        })
        .catch(function (err) {
          msg.textContent = (err && err.message) || 'Something went wrong. Please call or email us instead.';
          msg.style.display = 'block'; btn.disabled = false; btn.style.opacity = '';
        });
    });
  }

  function init() { reveal(); parallax(); projects(); rail(); filter(); form(); }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();

/* Document popups: a link with data-av-doc-open="KEY" opens the matching
   <template data-av-doc="KEY"> from the page in a dialog (styled by the
   .av-story-modal rules). Used for documents that have no file yet; swap the
   link's href and drop the attribute once the client's PDF is uploaded. */
(function () {
  'use strict';
  var links = document.querySelectorAll('[data-av-doc-open]');
  if (!links.length) { return; }
  var dlg, box, last;

  function close() {
    if (!dlg) { return; }
    if (dlg.close) { dlg.close(); } else { dlg.removeAttribute('open'); onClosed(); }
  }
  function onClosed() {
    document.documentElement.classList.remove('av-modal-open');
    if (last && last.focus) { last.focus(); }
  }
  function build() {
    dlg = document.createElement('dialog');
    dlg.className = 'av-story-modal av-doc-modal';
    dlg.setAttribute('aria-modal', 'true');
    box = document.createElement('div');
    box.className = 'av-story-modal__box';
    dlg.appendChild(box);
    dlg.addEventListener('click', function (e) { if (e.target === dlg) { close(); } });
    dlg.addEventListener('close', onClosed);
    dlg.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !dlg.close) { close(); } });
    document.body.appendChild(dlg);
  }
  function open(key) {
    var tpl = document.querySelector('template[data-av-doc="' + key + '"]');
    if (!tpl) { return false; }
    if (!dlg) { build(); }
    box.innerHTML = '';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'av-story-modal__close';
    btn.setAttribute('aria-label', 'Close');
    btn.innerHTML = '&times;';
    btn.addEventListener('click', close);
    box.appendChild(btn);
    box.appendChild(tpl.content.cloneNode(true));
    var title = box.querySelector('.av-story-modal__title');
    if (title) { title.id = 'av-doc-title'; dlg.setAttribute('aria-labelledby', 'av-doc-title'); }
    document.documentElement.classList.add('av-modal-open');
    if (dlg.showModal) { dlg.showModal(); } else { dlg.setAttribute('open', ''); }
    btn.focus();
    return true;
  }
  [].forEach.call(links, function (a) {
    a.addEventListener('click', function (e) {
      last = a;
      if (open(a.getAttribute('data-av-doc-open'))) { e.preventDefault(); }
    });
  });
})();
