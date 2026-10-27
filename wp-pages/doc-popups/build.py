# Builds templates.json: the placeholder popup content for document links that
# have no client file yet. Each entry is inserted into the page's HTML widget
# as <template data-av-doc="KEY">…</template>; ausvac-pages.js opens it.
import json

def tpl(key, eyebrow, title, body, note=None, cta=None):
    h = f'<template data-av-doc="{key}">'
    h += f'<p class="av-story-modal__eyebrow">{eyebrow}</p>'
    h += f'<h2 class="av-story-modal__title">{title}</h2>'
    h += f'<div class="av-story-modal__text av-doc">{body}'
    if note: h += f'<p class="av-doc-note">{note}</p>'
    if cta: h += '<div class="av-doc-actions">' + ''.join(f'<a class="av-doc-btn{" av-doc-btn--ghost" if i else ""}" href="{u}"{x}>{t}</a>' for i, (t, u, x) in enumerate(cta)) + '</div>'
    return h + '</div></template>'

WORD = '/wp-content/uploads/ausvac-docs/kris-biegaj-cv-english.docx'
DL = ' download'

asx = tpl('asx', 'PRESS · ASX APPOINTMENTS · 03 05 2013',
  'Director of Operations – Mongolian Resource Corporation',
  '<p>The ASX appointments notice of 3 May 2013 (pages 13 &amp; 14) covers Krzysztof (Kris) Biegaj joining Mongolian Resource Corporation (ASX: MUB) as Director of Operations for the Sujigtei Gold Project in Mongolia.</p>'
  '<h3>The role</h3><ul>'
  '<li>Re-establish mining and processing operations for the recapitalised business</li>'
  '<li>Liaise with government on permitting and operations</li>'
  '<li>Develop operational strategy and planning, and the interface with the corporate team</li></ul>'
  '<h3>Experience he brought</h3><ul>'
  '<li>Executive Manager – Mining at AngloGold Ashanti’s Obuasi Gold Mine, Ghana (2009 – 2012)</li>'
  '<li>Site Director, Nezhdaninskoye Gold Project, Far-East Siberia, Russia (2002 – 2003)</li>'
  '<li>Mine Manager and Manager Mining roles with WMC Resources in Kambalda and Norseman, WA (1992 – 2002)</li>'
  '<li>First Class Mine Manager’s Certificates of Competency (WA Metalliferous and Coal)</li></ul>',
  'Summary of the notice. A copy of the original ASX pages will be added here soon.',
  [('Request a copy', '/contact/', '')])

qa = tpl('qa', 'PRESS · Q&amp;A · 17 03 2012',
  'Ken Brinsden Q&amp;A – mention of Kris Biegaj',
  '<p>This press clipping from 17 March 2012 is a Q&amp;A with Australian mining executive Ken Brinsden. The reference to Kris Biegaj appears at the top of the second column.</p>'
  '<h3>Where Kris was at the time</h3><ul>'
  '<li>Executive Manager – Mining / Manager Mine Projects at AngloGold Ashanti’s Obuasi Gold Mine in Ghana (2009 – 2012)</li>'
  '<li>Leading a modernisation programme: revised organisational structure, a 12-hour continuous mining roster, upgraded surface and underground facilities, and tele-remote mucking in open stopes</li>'
  '<li>About to move into business improvement and mining consulting with McMahon Mining Contractors and TransSiberian Gold (2012 – 2017)</li></ul>',
  'Summary only. The original clipping will be added here soon.',
  [('Request a copy', '/contact/', '')])

# Placeholder testimonials, written from the achievements in the client's CV.
# Replace with client-approved testimonials (names and permissions) before launch.
quotes = [
  ('The Mobile Supersucker cleaned our ore drive floors down to solid rock and recovered high-grade fines our loaders simply left behind. Kris ran it with two operators and the grades spoke for themselves.',
   'Mine Manager', 'Underground nickel operation, Western Australia'),
  ('Kris brought a genuinely safety-first culture to site. His “Safety at Home” approach changed how our crews thought about risk, both underground and after shift.',
   'Underground Superintendent', 'Gold operation, Kambalda WA'),
  ('His debottlenecking review gave us clear KPIs and a mine design we could actually deliver. Practical, hands-on and respected by the workforce.',
   'Operations Director', 'Mining contractor, Australia &amp; Asia'),
  ('The move to a 12-hour continuous roster and the upgraded underground facilities lifted both productivity and morale. Kris listens to everyone, then gets it done.',
   'Senior Mining Engineer', 'Underground gold mine, West Africa'),
]
tq = ''.join(f'<figure class="av-doc-quote"><blockquote>“{q}”</blockquote><figcaption><strong>{r}</strong><span>{o}</span></figcaption></figure>' for q, r, o in quotes)
testi = ('<!-- PLACEHOLDER testimonials: replace with client-approved quotes before launch -->' +
  tpl('testimonials', 'REFERENCES · TESTIMONIALS', 'What clients and colleagues say',
  '<div class="av-doc-quotes">' + tq + '</div>',
  'Full references are available on request.',
  [('Request references', '/contact/', '')]))

cv_en = tpl('cv-pdf', 'CURRICULUM VITAE · ENGLISH', 'Krzysztof (Kris) Biegaj – CV summary',
  '<p>Senior manager, business and operations consultant and business owner with 45+ years in underground and open pit mining across Australia, Russia, Kazakhstan, Ghana, Mongolia and the Philippines. Founder and Director of Ausvac Mining Pty Ltd.</p>'
  '<h3>Qualifications</h3><ul>'
  '<li>Bachelor of Engineering (Mining) Hons – Wroclaw Technical University, Poland</li>'
  '<li>First Class Mine Manager’s Certificate of Competency – WA Metalliferous</li>'
  '<li>First Class Mine Manager’s Certificate of Competency – WA Coal</li>'
  '<li>Member of the Australasian Institute of Mining &amp; Metallurgy (AusIMM)</li></ul>'
  '<h3>Career highlights</h3><ul class="av-doc-timeline">'
  '<li><b>2020 – present</b> Specialist Mining Contractor (Owner) – Ausvac Mining Pty Ltd, Perth WA</li>'
  '<li><b>2016 – 2020</b> Mine Manager / Project Manager – Ramelius Resources, Mt Magnet WA</li>'
  '<li><b>2012 – 2017</b> Business Improvement / Mining Consultant – McMahon Mining Contractors; TransSiberian Gold</li>'
  '<li><b>2013</b> Director of Operations – Mongolian Resource Corp, Sujigtei Gold Project</li>'
  '<li><b>2009 – 2012</b> Executive Manager – Mining – AngloGold Ashanti, Obuasi Gold Mine, Ghana</li>'
  '<li><b>2003 – 2004</b> Founded Ausvac Mining – designed and built the Mobile Supersucker, Kalgoorlie WA</li>'
  '<li><b>2002 – 2003</b> Site Director – Celtic Resources, Nezhdaninskoye Gold Project, Russia</li>'
  '<li><b>1992 – 2002</b> Mine Manager / Manager Mining – WMC Resources, Kambalda &amp; Norseman WA</li>'
  '<li><b>1987 – 1992</b> District Inspector of Mines – WA Department of Minerals and Energy</li></ul>'
  '<h3>Languages</h3><p>English, Polish and Russian – fluent, spoken and written.</p>',
  'The print-ready PDF will be added here soon. The full CV is available as a Word document now.',
  [('Download full CV (Word)', WORD, DL), ('Contact Kris', '/contact/', '')])

cv_ru = tpl('cv-ru', 'РЕЗЮМЕ · РУССКАЯ ВЕРСИЯ', 'Кшиштоф (Крис) Бегай – краткое резюме',
  '<p>Руководитель горного производства, консультант по повышению эффективности и владелец бизнеса. Более 45 лет опыта подземной и открытой разработки в Австралии, России, Казахстане, Гане, Монголии и на Филиппинах. Основатель и директор компании Ausvac Mining Pty Ltd.</p>'
  '<h3>Образование и квалификация</h3><ul>'
  '<li>Горный инженер (диплом с отличием) – Вроцлавский технический университет, Польша</li>'
  '<li>Сертификат управляющего рудником первого класса – Западная Австралия, металлические рудники</li>'
  '<li>Сертификат управляющего рудником первого класса – Западная Австралия, угольные шахты</li>'
  '<li>Член Австралазийского института горного дела и металлургии (AusIMM)</li></ul>'
  '<h3>Основные этапы карьеры</h3><ul class="av-doc-timeline">'
  '<li><b>2020 – н. в.</b> Специализированный горный подрядчик (владелец) – Ausvac Mining Pty Ltd, Перт</li>'
  '<li><b>2016 – 2020</b> Управляющий рудником / руководитель проекта – Ramelius Resources, Маунт-Магнет</li>'
  '<li><b>2012 – 2017</b> Консультант по повышению эффективности – McMahon Mining Contractors; TransSiberian Gold (Россия)</li>'
  '<li><b>2013</b> Директор по производству – Mongolian Resource Corp, золоторудный проект Суджигтэй, Монголия</li>'
  '<li><b>2009 – 2012</b> Исполнительный менеджер по горным работам – AngloGold Ashanti, рудник Обуаси, Гана</li>'
  '<li><b>2004 – 2005</b> Консультант по производству – Celtic Resources, проект Суздаль, Казахстан</li>'
  '<li><b>2002 – 2003</b> Директор рудника – Celtic Resources, Нежданинское месторождение, Дальний Восток России</li>'
  '<li><b>1992 – 2002</b> Управляющий рудником / менеджер горных работ – WMC Resources, Камбалда и Норсмен</li></ul>'
  '<h3>Языки</h3><p>Английский, польский, русский – свободно, устно и письменно.</p>',
  'Полная версия резюме на русском языке будет добавлена в ближайшее время.',
  [('Скачать резюме (англ., Word)', WORD, DL), ('Связаться', '/contact/', '')])

json.dump({'services': asx + qa + testi, 'company': cv_ru + cv_en}, open('templates.json', 'w'), ensure_ascii=False, indent=0)
print('ok')
