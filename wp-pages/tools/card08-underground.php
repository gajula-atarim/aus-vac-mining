<?php
/*
 * One-off deploy: projects card 08 (Harlequin, tagged UNDERGROUND) shows an
 * open-pit aerial photo. Swap it for an underground photo already in the
 * Media Library (ausvac-stock-32594486, miner in an underground drive).
 * Services explorer + rail (x2), Home copy (x2), JS PROJECTS (x1).
 * Every count must match before anything is written. Backups first.
 */
$old = 'https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-11347699.jpeg';
$new = 'https://ausvac-mining.wsdfy.com/wp-content/uploads/2026/10/ausvac-stock-32594486.jpeg';
$want = array( 76 => '86ff3cf', 36 => 'avprojmh' );
$out  = array();
foreach ( $want as $pid => $wid ) {
	$raw = get_post_meta( $pid, '_elementor_data', true );
	$d   = json_decode( $raw, true );
	$n   = -1;
	$walk = function ( &$els ) use ( &$walk, $wid, $old, $new, &$n ) {
		foreach ( $els as &$el ) {
			if ( $el['id'] === $wid ) {
				$el['settings']['html'] = str_replace( $old, $new, $el['settings']['html'], $n );
			}
			if ( ! empty( $el['elements'] ) ) {
				$walk( $el['elements'] );
			}
		}
	};
	$walk( $d );
	if ( 2 !== $n ) {
		echo "ABORT: post $pid widget $wid matched $n of 2\n";
		return;
	}
	$out[ $pid ] = array( $raw, $d );
}
$theme = get_stylesheet_directory() . '/assets/ausvac-pages.js';
$js    = file_get_contents( $theme );
$js2   = str_replace( $old, $new, $js, $n );
if ( 1 !== $n ) {
	echo "ABORT: js matched $n of 1\n";
	return;
}
$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
file_put_contents( $bk . '/ausvac-pages.js', $js );
foreach ( $out as $pid => $v ) {
	file_put_contents( $bk . "/elementor-$pid.json", $v[0] );
}
foreach ( $out as $pid => $v ) {
	update_post_meta( $pid, '_elementor_data', wp_slash( wp_json_encode( $v[1] ) ) );
}
file_put_contents( $theme, $js2 );
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\npages 76, 36 (x2 each); js x1\ndone\n";
