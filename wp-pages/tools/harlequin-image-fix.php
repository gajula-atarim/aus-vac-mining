<?php
/*
 * One-off deploy: put real photos back where document images were shown.
 *  - Projects card 08 (Services explorer + rail, Home copy, JS PROJECTS):
 *    Harlequin channel-sample record -> original stock underground photo.
 *  - Services recovery overlap photo (sv-tunnel): slide 9 -> underground drive.
 *  - Remove the CSS rule that sat those two images whole on white.
 * Edits are literal, applied to the live data in memory, and every count must
 * match before anything is written. Backups first.
 */
$C = 'https://ausvac-mining.wsdfy.com/wp-content/uploads/ausvac-client/';
$U = 'https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/';
$rec_old = $C . 'harlequin-channel-sample-record-2001.jpg';
$rec_new = $U . 'ausvac-stock-11347699.jpeg';
$tun_old = $C . 'harlequin-floor-sampling-2001.jpg" alt="Floor sampling at CNGC Harlequin Gold Mine, Norseman WA, April 2001" data-slot="sv-tunnel"';
$tun_new = $U . 'ausvac-stock-29351659.jpeg" alt="Photo: underground drive" data-slot="sv-tunnel"';

$edits = array(
	76 => array(
		'86ff3cf' => array( array( $rec_old, $rec_new, 2 ) ),
		'1faed24' => array( array( $tun_old, $tun_new, 1 ) ),
	),
	36 => array(
		'avprojmh' => array( array( $rec_old, $rec_new, 2 ) ),
	),
);
$new = array();
foreach ( $edits as $pid => $wids ) {
	$raw  = get_post_meta( $pid, '_elementor_data', true );
	$d    = json_decode( $raw, true );
	$done = array();
	$err  = '';
	$walk = function ( &$els ) use ( &$walk, $wids, &$done, &$err ) {
		foreach ( $els as &$el ) {
			if ( isset( $wids[ $el['id'] ] ) ) {
				$h = $el['settings']['html'];
				foreach ( $wids[ $el['id'] ] as $e ) {
					$h = str_replace( $e[0], $e[1], $h, $n );
					if ( $n !== $e[2] ) {
						$err .= "{$el['id']}: matched $n of {$e[2]}\n";
					}
				}
				$el['settings']['html'] = $h;
				$done[]                 = $el['id'];
			}
			if ( ! empty( $el['elements'] ) ) {
				$walk( $el['elements'] );
			}
		}
	};
	$walk( $d );
	if ( $err || count( $done ) !== count( $wids ) ) {
		echo "ABORT: post $pid\n$err" . 'widgets found: ' . implode( ',', $done ) . "\n";
		return;
	}
	$new[ $pid ] = array( $raw, $d );
}

$theme = get_stylesheet_directory() . '/assets/';
$js    = file_get_contents( $theme . 'ausvac-pages.js' );
$js2   = str_replace( $rec_old, $rec_new, $js, $n );
if ( 1 !== $n ) {
	echo "ABORT: js matched $n of 1\n";
	return;
}
$css  = file_get_contents( $theme . 'ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css' );
$rule = "\n/* ---- Client documents shown as images: keep the whole page visible ---- */\n"
	. "/* The Harlequin sampling record and floor-sampling slide are document scans;\n"
	. "   cover-cropping would cut them, so they sit whole on white. */\n"
	. "img[src*=\"harlequin-channel-sample-record\"],\n"
	. "img[data-slot=\"sv-tunnel\"]{ object-fit: contain !important; background: #FFFFFF !important; }";
$css2 = str_replace( $rule, '', $css, $n );
if ( 1 !== $n ) {
	echo "ABORT: css rule matched $n of 1\n";
	return;
}

$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
file_put_contents( $bk . '/ausvac-pages.js', $js );
file_put_contents( $bk . '/ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', $css );
foreach ( $new as $pid => $v ) {
	file_put_contents( $bk . "/elementor-$pid.json", $v[0] );
}
foreach ( $new as $pid => $v ) {
	update_post_meta( $pid, '_elementor_data', wp_slash( wp_json_encode( $v[1] ) ) );
}
file_put_contents( $theme . 'ausvac-pages.js', $js2 );
file_put_contents( $theme . 'ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', $css2 );
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\npages: 76 (86ff3cf x2, 1faed24 x1), 36 (avprojmh x2); js x1; css rule removed\ndone\n";
