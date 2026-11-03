<?php
/*
 * One-off: client feedback "40 years not 25 years" (Company page).
 * Changes only these exact phrases on post 75, and proves nothing else moved:
 *   Profile widget e53ed3d : "25 years+"    -> "40 years+"    (heading, paragraph, card: 3)
 *   CV widget e3418cc      : "with 45+ years in" -> "with 40+ years in" (English CV popup: 1)
 *                            "Более 45 лет" -> "Более 40 лет" (Russian CV popup: 1)
 * ">25,000 People" in the paragraph is a different number and is left alone.
 */
$plan = array(
	'e53ed3d' => array( array( '25 years+', '40 years+', 3 ) ),
	'e3418cc' => array( array( 'with 45+ years in', 'with 40+ years in', 1 ), array( 'Более 45 лет', 'Более 40 лет', 1 ) ),
);
$raw  = get_post_meta( 75, '_elementor_data', true );
$old  = json_decode( $raw, true );
$new  = $old;
$done = array();
$walk = function ( &$els ) use ( &$walk, $plan, &$done ) {
	foreach ( $els as &$el ) {
		if ( isset( $plan[ $el['id'] ] ) ) {
			$h = $el['settings']['html'];
			foreach ( $plan[ $el['id'] ] as $p ) {
				$c = substr_count( $h, $p[0] );
				if ( $c !== $p[2] ) {
					$done['ERR'] = "{$el['id']}: '{$p[0]}' found $c, expected {$p[2]}";
					return;
				}
				$h = str_replace( $p[0], $p[1], $h );
				$done[] = "{$el['id']}: '{$p[0]}' -> '{$p[1]}' x$c";
			}
			$el['settings']['html'] = $h;
		}
		if ( ! empty( $el['elements'] ) ) {
			$walk( $el['elements'] );
		}
	}
};
$walk( $new );
if ( isset( $done['ERR'] ) ) {
	echo 'ABORT: ' . $done['ERR'] . "\n";
	return;
}
if ( 3 !== count( $done ) ) {
	echo "ABORT: not every widget was found\n";
	return;
}
// Proof: undo the replacements on the new data and it must equal the old data exactly.
$back = wp_json_encode( $new );
foreach ( $plan as $ps ) {
	foreach ( $ps as $p ) {
		$back = str_replace( wp_json_encode( $p[1] ) === false ? $p[1] : trim( wp_json_encode( $p[1] ), '"' ), trim( wp_json_encode( $p[0] ), '"' ), $back );
	}
}
if ( $back !== wp_json_encode( $old ) ) {
	echo "ABORT: data differs beyond the planned phrases\n";
	return;
}
$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
file_put_contents( $bk . '/elementor-75.json', $raw );
update_post_meta( 75, '_elementor_data', wp_slash( wp_json_encode( $new ) ) );
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\n" . implode( "\n", $done ) . "\nproof: reverting the 5 phrases reproduces the old page data byte-for-byte\ndone\n";
