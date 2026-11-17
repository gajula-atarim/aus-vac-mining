# Builds spec.json for the Nov 2026 client update (Kris's photos, history,
# specs, Fellow 2026, documents) and applies the same edits to the repo copies
# of the pages. Every edit is a literal replacement with an exact expected
# count; the live deploy script re-checks every count before writing.
import json, re, os
ROOT = os.path.dirname(os.path.abspath(__file__)) + '/..'
U = 'uploads/2026/10/'
C = 'uploads/ausvac-client/'
DOCS = 'https://ausvac-mining.wsdfy.com/wp-content/uploads/ausvac-docs/'

def load(page):
    d = json.load(open(f'{ROOT}/pages/{page}.json', encoding='utf8'))
    return d, json.loads(d['meta']['_elementor_data'])
def widgets(data):
    return {w['id']: w for w in data[0]['elements']}

spec = {'posts': {}, 'settings': [], 'js': []}
def rep(post, wid, old, new, n=1):
    spec['posts'].setdefault(str(post), []).append({'el': wid, 'old': old, 'new': new, 'n': n})

# ---------- Services (76) ----------
rep(76, 'c32d6f6', U+'ausvac-stock-31721779.jpeg" alt="Hero: underground mine" data-slot="sv-hero"',
    U+'ausvac-stock-10016029.jpeg" alt="Hero: underground mine tunnel" data-slot="sv-hero"')
rep(76, '1faed24', U+'ausvac-stock-37520757.jpeg" alt="Photo: vehicle in underground tunnel" data-slot="sv-kambalda"',
    C+'mine-floor-vacuuming-kambalda-2007.jpg" alt="Mine floor vacuuming with the 200 mm suction hose, nickel mine near Kambalda WA, 2007" data-slot="sv-kambalda"')
rep(76, '1faed24', U+'ausvac-stock-29351659.jpeg" alt="Photo: underground drive" data-slot="sv-tunnel"',
    C+'harlequin-floor-sampling-2001.jpg" alt="Floor sampling at CNGC Harlequin Gold Mine, Norseman WA, April 2001" data-slot="sv-tunnel"')
items = ['Ore drives', 'Open stopes (remote-controlled vacuuming)', 'Footwalls of low-angle dipping stopes',
         'Top of the last lift in cut &amp; fill / under-hand cut &amp; fill', 'Tops of pillars left behind for geotechnical reasons',
         'Underground sumps', 'Mills, sumps and machine-cleaning bays on surface', 'Selective mining of high-grade ore in open pits']
panel = ('<div data-reveal="" style="background: rgb(255, 255, 255); border-left: 3px solid rgb(254, 195, 6); padding: 18px 20px; display: grid; gap: 10px; margin-top: 8px; min-width: 0px;">'
         '<span style="font-size: 11px; font-weight: 700; letter-spacing: 0.2em; color: rgb(43, 92, 4);">WHERE FLOOR MATERIAL IS LEFT BEHIND</span>'
         '<ul style="margin: 0px; padding: 0px 0px 0px 18px; display: grid; gap: 4px; font-size: 13px; line-height: 1.6; color: rgb(74, 82, 77);">'
         + ''.join(f'<li>{i}</li>' for i in items) + '</ul></div>')
rep(76, '1faed24', 'nickel grades, average +</span></span></div></div>', 'nickel grades, average +</span></span></div></div>' + panel)

PROJ = {'ausvac-stock-29506757.jpeg': C+'surface-sump-before-cleanup-2003.jpg',
        'ausvac-stock-33774180.jpeg': U+'ausvac-stope-clean-up.jpg',
        'ausvac-stock-14484399.jpeg': U+'ausvac-stock-32193624.jpeg',
        'ausvac-stock-15071425.jpeg': C+'supersucker-towing-nickel-mine-2004.jpg',
        'ausvac-stock-27533504.jpeg': U+'ausvac-stock-18928150.jpeg',
        'ausvac-stock-14484386.jpeg': U+'ausvac-stock-37520757.jpeg',
        'ausvac-stock-2101140.jpeg': C+'discharging-into-bobcat-2007.jpg',
        'ausvac-stock-11347699.jpeg': C+'harlequin-channel-sample-record-2001.jpg'}
_, sv = load('services'); SW = widgets(sv)
proj_html = SW['86ff3cf']['settings']['html']
for old, new in PROJ.items():
    n = proj_html.count(U+old)
    rep(76, '86ff3cf', U+old, new, n)
    rep(36, 'avprojmh', U+old, new, n)          # Home copy of the same section
    spec['js'].append({'old': U+old, 'new': new, 'n': 1})

rep(76, 'e62812f', U+'ausvac-stock-28442180.jpeg" alt="MINING COMPANIES" data-slot="sv-b1"',
    C+'surface-sump-after-cleanup-2003.jpg" alt="MINING COMPANIES" data-slot="sv-b1"')
rep(76, 'e62812f', U+'ausvac-stock-34150292.jpeg" alt="GOVERNMENTS" data-slot="sv-b2"',
    U+'ausvac-broken-ore-recovery.jpg" alt="GOVERNMENTS" data-slot="sv-b2"')
rep(76, 'cdae0f4', U+'ausvac-stock-34150285.jpeg" alt="Background: heavy mining equipment" data-slot="sv-mach-bg"',
    C+'kris-biegaj-kalgoorlie-expo-2003-large.jpg" alt="Background: the Ausvac Mobile Supersucker" data-slot="sv-mach-bg"')
rep(76, 'cdae0f4', '<span>Truly mobile, available since 2003+</span>',
    '<span>Truly mobile – track-mounted, 8.3 t, 2.2 m W × 2.95 m H × 5.3 m L</span>')
rep(76, 'cdae0f4', '<span>Diesel, easy to convert to electric</span>',
    '<span>Diesel, easy to convert to electric; dust control &amp; ROPS/FOPS</span>')
rep(76, 'cdae0f4', 'to address the challenge of potential revenue loss.</p>',
    'to address the challenge of potential revenue loss. Mine floor material recovery should be included in Pre-feasibility/Feasibility Studies, Ore Reserves estimation and Life of Mine Plans – and carried out while ventilation, compressed air, dewatering and all infrastructure are fully operational, not after the mine is flooded or put on care and maintenance.</p>')

# Documents (live state = repo + the October document-popups deploy)
rep(76, '33621ec', DOCS+'ausvac-mine-floor-material-recovery-presentation.pdf',
    DOCS+'ausvac-mine-floor-material-recovery-presentation-15-10-2026.pdf')
rep(76, '33621ec', 'color: rgba(255, 255, 255, 0.75);">Mine Floor Material Recovery Should Become Part of Life of Mine Plan</span>',
    'color: rgba(255, 255, 255, 0.75);">Presentation by Kris Biegaj FAusIMM &amp; Prof. Simon C Dominy · updated 15 10 2026</span>')
rep(76, '33621ec', 'font-size: clamp(24px, 2.8vw, 34px); font-weight: 700; line-height: 1.12; max-width: 18ch;">AusIMM Perth Branch Presentation 11 05 2015</strong>',
    'font-size: clamp(21px, 2.3vw, 28px); font-weight: 700; line-height: 1.15; max-width: 22ch;">Mine Floor Material Recovery Should Become Part of Life of Mine Plan</strong>')
jorc = ('<a href="' + DOCS + 'ausvac-jorc-code-review-update-2021.pdf" target="_blank" rel="noopener" class="av-sv-xa" style="display: grid; grid-template-columns: 60px minmax(0px, 1fr) 44px; gap: 18px; align-items: center; padding: 22px 0px; border-bottom: 1px solid rgba(255, 255, 255, 0.18); color: rgb(255, 255, 255); text-decoration: none; transition: padding 0.3s, background 0.3s;">'
        '<span style="height: 60px; border: 1.5px solid rgb(201, 210, 204); color: rgb(201, 210, 204); display: grid; place-items: center; font-size: 12px; font-weight: 800; letter-spacing: 0.06em;"><span>PDF</span></span>'
        '<span style="display: grid; gap: 4px; min-width: 0px;"><span style="font-size: 10px; font-weight: 700; letter-spacing: 0.2em; color: rgba(255, 255, 255, 0.55);"><span>REFERENCE</span></span>'
        '<strong style="font-size: clamp(15px, 1.5vw, 18px); font-weight: 600; line-height: 1.35;"><span>JORC Code Review Update – JORC, 28 June 2021</span></strong></span>'
        '<span style="width: 44px; height: 44px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, 0.3); display: grid; place-items: center; font-size: 15px;"><span>↗</span></span></a>')
rep(76, '33621ec', '</div></div><a href="/contact/" class="av-sv-xb"', jorc + '</div></div><a href="/contact/" class="av-sv-xb"')

# ---------- Company (75) ----------
rep(75, 'e53ed3d', 'Member since 1992, Fellow since 2025</span>', 'Member since 1992, Fellow since 2026</span>')
rep(75, 'e883f3e', 'Fellow since 2025</span>.</p>', 'Fellow since 2026</span>.</p>')
rep(75, 'e883f3e', 'color: rgb(254, 195, 6);">AUSIMM 1992</span>', 'color: rgb(254, 195, 6);">AUSIMM 1992 · FELLOW 2026</span>')
rep(75, '95d380d', 'open pit gold operations at CNGC in Norseman).</span></p>',
    'open pit gold operations at CNGC in Norseman). As Mining Manager at CNGC in Norseman for five years (then part of WMC Resources), Kris ran one of the world’s first-quartile, lowest-cost gold producers – when the gold price was about twenty times lower than today.</span></p>')
rep(75, 'e3418cc', '<li>Member of the Australasian Institute of Mining &amp; Metallurgy (AusIMM)</li>',
    '<li>Fellow of the Australasian Institute of Mining &amp; Metallurgy (FAusIMM) since 2026 – Member since 1992</li>')
rep(75, 'e3418cc', '<li>Член Австралазийского института горного дела и металлургии (AusIMM)</li>',
    '<li>Действительный член (Fellow) Австралазийского института горного дела и металлургии (FAusIMM) с 2026 г., член с 1992 г.</li>')

# ---------- Publications (77) ----------
fig = ('<figure style="margin: 6px 0px 0px; display: grid; gap: 8px; max-width: 420px; width: 100%;">'
       '<img src="https://ausvac-mining.wsdfy.com/wp-content/' + C + 'bullen-west-orebody-2001.jpg" alt="Bullen West orebody variability, CNGC Norseman, 2001" loading="lazy" decoding="async" style="display: block; width: 100%; height: auto; max-width: 100%; border: 1px solid rgb(214, 217, 210);">'
       '<figcaption style="font-size: 12px; line-height: 1.5; color: rgb(107, 114, 104);">Bullen West orebody variability – CNGC, Norseman (2001). From Kris Biegaj’s presentation.</figcaption></figure>')
rep(77, 'bddfbeb', '7-8 October 1999, Kalgoorlie, Western Australia.</span></span>', '7-8 October 1999, Kalgoorlie, Western Australia.</span></span>' + fig)

# ---------- Contact (78) ----------
rep(78, '4efa6bd', U+'ausvac-stock-39300773-scaled.jpeg" alt="Photo: mining haul truck" data-slot="ct-hero"',
    U+'ausvac-stock-29351659.jpeg" alt="Photo: underground drive" data-slot="ct-hero"')

# ---------- Home (36) native widgets ----------
def img(el, name, alt): spec['settings'].append({'post': 36, 'el': el, 'set': {'image': {'id': '{{A:' + name + '}}', 'url': '{{U:' + name + '}}'}, 'alt': alt}})
def setv(el, key, val): spec['settings'].append({'post': 36, 'el': el, 'set': {key: val}})
img('avs1i', 'discharging-into-bobcat-2007.jpg', 'Discharging vacuum-recovered floor material into a Bobcat, nickel mine, 2007')
img('avs2i', 'mine-floor-vacuuming-kambalda-2007.jpg', 'Mine floor vacuuming near Kambalda, Western Australia, 2007')
setv('avs2t', 'title', 'Mine Floor Vacuuming')
img('avs4i', 'kris-biegaj-kalgoorlie-expo-2003-large.jpg', 'Kris Biegaj with the Mobile Supersucker, Kalgoorlie Mining Expo, 2003')
img('av-service-1p', 'supersucker-towing-nickel-mine-2004.jpg', 'The Mobile Supersucker being towed at a high-grade nickel mine in Western Australia, 2004')
img('av-service-2p', 'surface-sump-after-cleanup-2003-b.jpg', 'Surface sump on a gold mine after clean-up with the Supersucker, 2003')
setv('av-service-2d', 'editor', '<p>Efficient clean-down of mill tanks, surface machine-cleaning bays and underground and surface sumps and ponds &ndash; normally containing a significant amount of precious metals or stones.</p>')
img('av-app-4p', 'surface-sump-before-cleanup-2003-b.jpg', 'Mining equipment surface clean-up sump before clean-up, 2003')
setv('av-app-4d', 'editor', '<p>Efficient clean-down of mill tanks, surface machine-cleaning bays and underground and surface sumps and ponds</p>')
setv('av-step-1d', 'editor', '<p>Stationary vacuum machines used to mine opals underground at Coober Pedy, South Australia.</p>')
setv('av-step-2y', 'title', '1999 &ndash; 2003')
setv('av-step-2t', 'title', 'NORSEMAN')
setv('av-step-2d', 'editor', '<p>Vacuum technology used for shaft sinking by Byrnecut at CNGC Norseman (WMC); the Mobile Supersucker then designed and built in Kalgoorlie.</p>')

spec['uploads'] = ['supersucker-towing-nickel-mine-2004.jpg', 'kris-biegaj-kalgoorlie-expo-2003-large.jpg', 'mine-floor-vacuuming-kambalda-2007.jpg',
                   'discharging-into-bobcat-2007.jpg', 'surface-sump-before-cleanup-2003.jpg', 'surface-sump-after-cleanup-2003.jpg',
                   'surface-sump-before-cleanup-2003-b.jpg', 'surface-sump-after-cleanup-2003-b.jpg', 'harlequin-floor-sampling-2001.jpg',
                   'harlequin-channel-sample-record-2001.jpg', 'bullen-west-orebody-2001.jpg']
json.dump(spec, open(os.path.dirname(os.path.abspath(__file__)) + '/spec.json', 'w', encoding='utf8'), ensure_ascii=False, indent=1)
print('edits', sum(len(v) for v in spec['posts'].values()), 'settings', len(spec['settings']), 'js', len(spec['js']))
