<?php
/*
 * One-off deploy: project card row (Services + Home) swipeable below 1024px.
 * JS: the auto-slide runs only above 1023px. CSS: native horizontal scroll
 * with snap below 1024px, second (loop) copy of the cards hidden there.
 * Edits the live theme files in place with exact counts; backups first.
 */
$theme = get_stylesheet_directory() . '/assets/';
$jsf   = $theme . 'ausvac-pages.js';
$cssf  = $theme . 'ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css';
$js    = file_get_contents( $jsf );
$css   = file_get_contents( $cssf );
if ( false !== strpos( $css, 'Project card row (Services and Home): swipeable' ) ) {
	echo "ABORT: already applied\n";
	return;
}
$o1 = "    var x = 0, hold = false, last = performance.now();\n    r.addEventListener('mouseenter'";
$n1 = "    var x = 0, hold = false, last = performance.now();\n    /* Phones and tablets swipe the row natively (CSS); only desktop auto-slides. */\n    var swipe = window.matchMedia && matchMedia('(max-width: 1023px)');\n    r.addEventListener('mouseenter'";
$o2 = "      if (!hold && !document.hidden) {\n        var half = tr.scrollWidth / 2;";
$n2 = "      if (!hold && !document.hidden && !(swipe && swipe.matches)) {\n        var half = tr.scrollWidth / 2;";
$js2 = str_replace( $o1, $n1, $js, $a );
$js2 = str_replace( $o2, $n2, $js2, $b );
if ( 1 !== $a || 1 !== $b ) {
	echo "ABORT: js matched $a/$b of 1/1\n";
	return;
}
$add = <<<'CSS'


/* ---- Project card row (Services and Home): swipeable on phones and tablets ---- */
/* The row only auto-slid (and not at all with Reduce Motion on), so on a phone
   just two of the eight cards were reachable. Below 1024px it becomes a native
   horizontal scroll with snap; the JS auto-slide runs on desktop only. The
   second copy of the cards exists only for the desktop loop, so it is hidden. */
@media (max-width: 1023px){
  [data-av="rail"]{
    overflow-x: auto !important;
    overscroll-behavior-x: contain;
    scroll-snap-type: x mandatory;
    scroll-padding-inline: clamp(20px, 4vw, 40px);
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
  }
  [data-av="rail"]::-webkit-scrollbar{ display: none; }
  [data-av="track"]{
    transform: none !important;
    padding-inline: clamp(20px, 4vw, 40px);
  }
  [data-av="track"] > button{ scroll-snap-align: start; }
  [data-av="track"] > button:nth-of-type(n+9){ display: none !important; }
}

CSS;
$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
file_put_contents( $bk . '/ausvac-pages.js', $js );
file_put_contents( $bk . '/ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', $css );
file_put_contents( $jsf, $js2 );
file_put_contents( $cssf, rtrim( $css, "\n" ) . $add );
do_action( 'litespeed_purge_all' );
echo "backup: $bk\njs 2 edits; css rule appended\ndone\n";
