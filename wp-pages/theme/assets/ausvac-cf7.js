/* Contact page (Contact Form 7): after a successful send, show the design's
   "Thank you" box in place of the form. */
(function () {
  'use strict';
  document.addEventListener('wpcf7mailsent', function (e) {
    var wrap = e.target && e.target.closest ? e.target.closest('.av-cf7') : null;
    if (!wrap) { return; }
    var ok = document.createElement('div');
    ok.setAttribute('role', 'status');
    ok.style.cssText = 'position:relative;display:grid;gap:10px;padding:24px;border:1.5px solid #FEC306;';
    ok.innerHTML = '<strong style="font-size:20px;font-weight:700;color:#2B5C04;">Thank you</strong>' +
      '<span style="font-size:14px;line-height:1.7;color:#4A524D;">Your message has been sent to Ausvac Mining Pty Ltd.</span>';
    wrap.replaceChildren(ok);
  });
})();
