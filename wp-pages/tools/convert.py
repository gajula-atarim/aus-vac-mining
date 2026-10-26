import json, re
UP = 'https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/'
THEME = '/wp-content/themes/ausvac-child/assets/'
media = json.load(open('media_map.json'))
assets = {'kris-supersucker.png': UP + 'kris-biegaj-supersucker.jpg', 'projects-map.html': THEME + 'projects-map.html'}
allrules = []; out = {}
for pg in ['company', 'services', 'publications', 'contact']:
    d = json.load(open(pg + '.json'))
    allrules += d['rules']
    secs = []
    for s in d['sections']:
        h = s['html']
        h = re.sub(r'https://images\.pexels\.com/photos/(\d+)/pexels-photo-\d+\.jpeg(\?[^"\s)&]*(?:&amp;[^"\s)&]*)*)?',
                   lambda m: UP + media[m.group(1)], h)
        h = re.sub(r'\{\{ASSET:([^}]+)\}\}', lambda m: assets[m.group(1)], h)
        assert 'pexels' not in h and '{{' not in h, (pg, s['label'])
        # tidy: collapse whitespace between tags
        h = re.sub(r'>\s+<', '><', h).replace(';;', ';')
        secs.append({'label': s['label'], 'html': h})
    out[pg] = secs
    print(pg, sum(len(x['html']) for x in secs))
json.dump(out, open('pages_out.json', 'w'))
open('hover_rules.css', 'w').write('\n'.join(allrules))
print(len(allrules), 'rules')
