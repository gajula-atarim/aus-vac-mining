<?php
/*
 * One-off deploy: copy the Services "04 Projects" map section onto Home,
 * between the Field proof section (#av-proof) and Areas We Serve (#av-areas).
 * The new container carries the av-page class, so ausvac-pages.js runs the
 * project explorer (map, card, timeline, rail) there too. Both neighbours
 * end/start white, so the section's torn edges are recoloured to #FFFFFF.
 */
$src = json_decode( get_post_meta( 76, '_elementor_data', true ), true );
$html = null;
$find = function ( $els ) use ( &$find, &$html ) {
	foreach ( $els as $el ) {
		if ( isset( $el['settings']['html'] ) && false !== strpos( $el['settings']['html'], 'data-screen-label="04 Projects"' ) ) {
			$html = $el['settings']['html'];
		}
		if ( ! empty( $el['elements'] ) ) {
			$find( $el['elements'] );
		}
	}
};
$find( $src );
if ( ! $html ) {
	echo "ABORT: Services projects section not found\n";
	return;
}
$html = str_replace( 'fill="#F4F5F2"', 'fill="#FFFFFF"', $html, $n );
if ( 2 !== $n ) {
	echo "ABORT: expected 2 wave edges, found $n\n";
	return;
}

$raw = get_post_meta( 36, '_elementor_data', true );
$d   = json_decode( $raw, true );
$at  = null;
foreach ( $d as $i => $el ) {
	if ( 'avprojmap' === $el['id'] ) {
		echo "ABORT: section already on Home\n";
		return;
	}
	if ( 'avproof' === $el['id'] ) {
		$at = $i;
	}
}
if ( null === $at || ! isset( $d[ $at + 1 ] ) || 'avareas' !== $d[ $at + 1 ]['id'] ) {
	echo "ABORT: Home layout not as expected (#av-proof followed by #av-areas)\n";
	return;
}
$section = array(
	'id'       => 'avprojmap',
	'elType'   => 'container',
	'settings' => array(
		'content_width' => 'full',
		'_element_id'   => 'av-projects',
		'css_classes'   => 'av-page av-home-projects',
	),
	'elements' => array(
		array(
			'id'         => 'avprojmh',
			'elType'     => 'widget',
			'widgetType' => 'html',
			'settings'   => array( 'html' => $html, '_title' => 'Projects map' ),
			'elements'   => array(),
		),
	),
	'isInner'  => false,
);
array_splice( $d, $at + 1, 0, array( $section ) );

$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
file_put_contents( $bk . '/elementor-36.json', $raw );
update_post_meta( 36, '_elementor_data', wp_slash( wp_json_encode( $d ) ) );
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\ninserted avprojmap after avproof (" . strlen( $html ) . " bytes)\ndone\n";
