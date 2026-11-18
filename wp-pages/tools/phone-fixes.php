<?php
/*
 * One-off deploy: phone fixes (18 Nov 2026).
 *  1. Contact: WhatsApp row shows the number alone on phones (markup: prefix in
 *     its own span, number kept together); 48px instead of 96px above the footer.
 *  2. Services recovery: 40.02 g/t box moves off the second photo on phones.
 *  3. Company timeline: year on one line, level with the dot.
 *  4. Project card row: last card snaps to the right edge, no end bounce.
 * Exact counts are checked before anything is written; backups first.
 */
$cssf = get_stylesheet_directory() . '/assets/ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css';
$css  = file_get_contents( $cssf );
if ( false !== strpos( $css, 'Phone fixes, 18 Nov 2026' ) ) {
	echo "ABORT: already applied\n";
	return;
}
$add = <<<'CSSEND'


/* ---- Phone fixes, 18 Nov 2026: contact, recovery photo box, timeline years, card row end ---- */
@media (max-width: 767px){
  /* Contact: the section's 96px bottom padding left an empty band above the footer */
  #form > div[style*="padding: clamp(96px, 10vw, 130px)"]{ padding-bottom: 48px !important; }

  /* Contact: WhatsApp row shows the number alone on one line ("WHATSAPP" is the label) */
  .av-ct-wa-pre{ display: none; }

  /* Services recovery: the 40.02 g/t box sat on top of the second photo. On phones
     it moves into the empty bottom-left corner of the photo grid (5 of 12 columns,
     4 of 12 rows) with a solid background. */
  div:has(> img[data-slot="sv-tunnel"]) + div[style*="bottom: 12%"]{
    left: 0 !important; bottom: 0 !important;
    width: calc(41.66% - 10px); box-sizing: border-box;
    padding: 12px !important;
    background: rgb(30, 38, 34) !important;
    -webkit-backdrop-filter: none !important; backdrop-filter: none !important;
  }
  div:has(> img[data-slot="sv-tunnel"]) + div[style*="bottom: 12%"] > strong{ font-size: 22px !important; white-space: nowrap; }
  div:has(> img[data-slot="sv-tunnel"]) + div[style*="bottom: 12%"] > span{ font-size: 9px !important; letter-spacing: .1em !important; line-height: 1.4; }

  /* Company timeline: year on one line, level with the timeline dot */
  .av-page [data-av="tl-year"]{ padding-top: 21px; padding-right: 6px !important; }
  .av-page [data-av="tl-year"] strong{ font-size: 13px !important; white-space: nowrap; }
}

/* Project card row: the last card snaps to the right edge and the end does not
   bounce, so swiping can never stop with empty space after card 08. */
@media (max-width: 1023px){
  [data-av="rail"]{ overscroll-behavior-x: none !important; }
  [data-av="track"] > button:nth-of-type(8){ scroll-snap-align: end; }
}
CSSEND;
$old = '<span>Available on +61 (0) 409 883 020</span>';
$new = '<span><span class="av-ct-wa-pre">Available on </span><span style="white-space: nowrap;">+61 (0) 409 883 020</span></span>';
$raw = get_post_meta( 78, '_elementor_data', true );
$d   = json_decode( $raw, true );
$n   = -1;
$walk = function ( &$els ) use ( &$walk, $old, $new, &$n ) {
	foreach ( $els as &$el ) {
		if ( 'c07d3aa' === $el['id'] ) {
			$el['settings']['html'] = str_replace( $old, $new, $el['settings']['html'], $n );
		}
		if ( ! empty( $el['elements'] ) ) {
			$walk( $el['elements'] );
		}
	}
};
$walk( $d );
if ( 1 !== $n ) {
	echo "ABORT: contact WhatsApp text matched $n of 1\n";
	return;
}
$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
file_put_contents( $bk . '/ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', $css );
file_put_contents( $bk . '/elementor-78.json', $raw );
update_post_meta( 78, '_elementor_data', wp_slash( wp_json_encode( $d ) ) );
file_put_contents( $cssf, rtrim( $css, "\n" ) . rtrim( $add, "\n" ) . "\n" );
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\ncontact WhatsApp x1; css block appended\ndone\n";
