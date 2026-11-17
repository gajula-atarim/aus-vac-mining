<?php
/*
 * One-off deploy: November 2026 client update (Kris Biegaj's photos and documents).
 * Every file is pinned to $commit and sha1-checked. Every text edit must match its
 * exact expected count and every Home widget must be found, or nothing is written.
 */
$commit = 'b652936f49160d129f24b8d5f4a57a7f8cd7acc5';
$raw    = 'https://raw.githubusercontent.com/gajula-atarim/aus-vac-mining/' . $commit . '/wp-pages/';
$files  = array(
	'spec' => array( 'client-update/spec.json', '2a43d81a8f04c0030d7495704039e335692d0524' ),
	'js' => array( 'theme/assets/ausvac-pages.js', 'c24bb332c9b3fa0595e935081438d71649224bb9' ),
	'css' => array( 'theme/assets/ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', '5ad7f298fb49808a64c348e59844692d991f636c' ),
	'doc:ausvac-mine-floor-material-recovery-presentation-15-10-2026.pdf' => array( 'assets/docs/ausvac-mine-floor-material-recovery-presentation-15-10-2026.pdf', 'e872d357d25942af185c89a0ce42d579c2935fe1' ),
	'doc:ausvac-gold-nickel-prices-13-08-2026.xlsx' => array( 'assets/docs/ausvac-gold-nickel-prices-13-08-2026.xlsx', '73a120fb5525817a46aea265fc298d4ebee6d0f3' ),
	'doc:ausvac-jorc-code-review-update-2021.pdf' => array( 'assets/docs/ausvac-jorc-code-review-update-2021.pdf', 'e7cc4cd75094889ded6a23a9d031a859e59d955a' ),
	'img:supersucker-towing-nickel-mine-2004.jpg' => array( 'assets/client-photos/supersucker-towing-nickel-mine-2004.jpg', 'dc93163b2a52479bb46cdfd55aee30844aea1461' ),
	'img:kris-biegaj-kalgoorlie-expo-2003-large.jpg' => array( 'assets/client-photos/kris-biegaj-kalgoorlie-expo-2003-large.jpg', '65791ebb2de745d2458a071b5fda981732447f86' ),
	'img:mine-floor-vacuuming-kambalda-2007.jpg' => array( 'assets/client-photos/mine-floor-vacuuming-kambalda-2007.jpg', 'c87d14eb442938949c88911f477209b8d393a9d7' ),
	'img:discharging-into-bobcat-2007.jpg' => array( 'assets/client-photos/discharging-into-bobcat-2007.jpg', '96a8609e5811c353b90106bc835a19f55d6bf0fb' ),
	'img:surface-sump-before-cleanup-2003.jpg' => array( 'assets/client-photos/surface-sump-before-cleanup-2003.jpg', '3610430a99251af9a5b0ac69924ca85bf3b4cd0b' ),
	'img:surface-sump-after-cleanup-2003.jpg' => array( 'assets/client-photos/surface-sump-after-cleanup-2003.jpg', '4e4cac32370f71889b6b2e42e1dd7f5f796e8464' ),
	'img:surface-sump-before-cleanup-2003-b.jpg' => array( 'assets/client-photos/surface-sump-before-cleanup-2003-b.jpg', '379efd0a473c8003b5b3b3166067852305c7a380' ),
	'img:surface-sump-after-cleanup-2003-b.jpg' => array( 'assets/client-photos/surface-sump-after-cleanup-2003-b.jpg', 'e86b9f25d221cb4b1f844a714f016022dbed16cb' ),
	'img:harlequin-floor-sampling-2001.jpg' => array( 'assets/client-photos/harlequin-floor-sampling-2001.jpg', 'bfe5c90cdf1313757d80688d8e5133f3794edf91' ),
	'img:harlequin-channel-sample-record-2001.jpg' => array( 'assets/client-photos/harlequin-channel-sample-record-2001.jpg', 'aad88a253a3563843e6053c04ee0e8df3548e7e7' ),
	'img:bullen-west-orebody-2001.jpg' => array( 'assets/client-photos/bullen-west-orebody-2001.jpg', '0fbd5aec9037a98e314d1f13474e96de76924c61' )
);
$got = array();
foreach ( $files as $k => $f ) {
	$r = wp_remote_get( $raw . $f[0], array( 'timeout' => 90 ) );
	$b = is_wp_error( $r ) ? '' : wp_remote_retrieve_body( $r );
	if ( sha1( $b ) !== $f[1] ) {
		echo "ABORT: sha1 mismatch for {$f[0]}\n";
		return;
	}
	$got[ $k ] = $b;
}
$spec = json_decode( $got['spec'], true );

// 1. Prepare page edits in memory.
$new  = array();
$err  = '';
$posts = array_unique( array_merge( array_keys( $spec['posts'] ), array( '36' ) ) );
foreach ( $posts as $pid ) {
	$raw_data = get_post_meta( (int) $pid, '_elementor_data', true );
	$d        = json_decode( $raw_data, true );
	$edits    = isset( $spec['posts'][ $pid ] ) ? $spec['posts'][ $pid ] : array();
	$seen     = array();
	$walk     = function ( &$els ) use ( &$walk, $edits, &$err, &$seen ) {
		foreach ( $els as &$el ) {
			foreach ( $edits as $i => $e ) {
				if ( $el['id'] !== $e['el'] ) {
					continue;
				}
				$c = substr_count( $el['settings']['html'], $e['old'] );
				if ( $c !== $e['n'] ) {
					$err = "{$e['el']}: expected {$e['n']}, found $c for: " . substr( $e['old'], 0, 80 );
					return;
				}
				$el['settings']['html'] = str_replace( $e['old'], $e['new'], $el['settings']['html'] );
				$seen[ $i ]             = true;
			}
			if ( ! empty( $el['elements'] ) ) {
				$walk( $el['elements'] );
			}
		}
	};
	$walk( $d );
	if ( $err || count( $seen ) !== count( $edits ) ) {
		echo 'ABORT: post ' . $pid . ' ' . ( $err ? $err : 'not every edit applied' ) . "\n";
		return;
	}
	$new[ $pid ] = array( $raw_data, $d );
}
// Home widget settings: check every target exists before anything is written.
$targets = array();
foreach ( $spec['settings'] as $s ) {
	$targets[ $s['el'] ] = false;
}
$find = function ( $els ) use ( &$find, &$targets ) {
	foreach ( $els as $el ) {
		if ( isset( $targets[ $el['id'] ] ) ) {
			$targets[ $el['id'] ] = true;
		}
		if ( ! empty( $el['elements'] ) ) {
			$find( $el['elements'] );
		}
	}
};
$find( $new['36'][1] );
if ( in_array( false, $targets, true ) ) {
	echo 'ABORT: Home widget missing: ' . implode( ',', array_keys( $targets, false, true ) ) . "\n";
	return;
}

// 2. Backups.
$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
foreach ( $new as $pid => $v ) {
	file_put_contents( $bk . "/elementor-$pid.json", $v[0] );
}
$theme = get_stylesheet_directory() . '/assets/';
foreach ( array( 'ausvac-pages.js', 'ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css' ) as $f ) {
	copy( $theme . $f, $bk . '/' . $f );
}
$up = wp_upload_dir();
if ( file_exists( $up['basedir'] . '/ausvac-docs/ausvac-gold-nickel-prices-13-08-2026.xlsx' ) ) {
	copy( $up['basedir'] . '/ausvac-docs/ausvac-gold-nickel-prices-13-08-2026.xlsx', $bk . '/ausvac-gold-nickel-prices-13-08-2026.xlsx' );
}

// 3. Uploads: photos into the Media Library, documents beside the existing ones.
require_once ABSPATH . 'wp-admin/includes/image.php';
$ids = array();
$urls = array();
wp_mkdir_p( $up['basedir'] . '/ausvac-client' );
foreach ( $spec['uploads'] as $n ) {
	$rel  = 'ausvac-client/' . $n;
	$path = $up['basedir'] . '/' . $rel;
	file_put_contents( $path, $got[ 'img:' . $n ] );
	$ex = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_wp_attached_file', 'meta_value' => $rel, 'numberposts' => 1, 'fields' => 'ids' ) );
	$id = $ex ? $ex[0] : wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => ucfirst( str_replace( '-', ' ', preg_replace( '/\.jpg$/', '', $n ) ) ), 'post_status' => 'inherit' ), $path );
	update_post_meta( $id, '_wp_attached_file', $rel );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	$ids[ $n ]  = (int) $id;
	$urls[ $n ] = $up['baseurl'] . '/' . $rel;
}
wp_mkdir_p( $up['basedir'] . '/ausvac-docs' );
foreach ( $got as $k => $b ) {
	if ( 0 === strpos( $k, 'doc:' ) ) {
		file_put_contents( $up['basedir'] . '/ausvac-docs/' . substr( $k, 4 ), $b );
	}
}

// 4. Home widget settings (images now have ids).
$apply = function ( &$els ) use ( &$apply, $spec, $ids, $urls ) {
	foreach ( $els as &$el ) {
		foreach ( $spec['settings'] as $s ) {
			if ( $el['id'] !== $s['el'] ) {
				continue;
			}
			foreach ( $s['set'] as $key => $val ) {
				if ( 'image' === $key ) {
					$n   = substr( $val['id'], 4, -2 );
					$val = array( 'id' => $ids[ $n ], 'url' => $urls[ $n ] );
				}
				$el['settings'][ $key ] = $val;
			}
		}
		if ( ! empty( $el['elements'] ) ) {
			$apply( $el['elements'] );
		}
	}
};
$apply( $new['36'][1] );

// 5. Write pages, theme files, lazy-load exclusions; clear caches.
foreach ( $new as $pid => $v ) {
	update_post_meta( (int) $pid, '_elementor_data', wp_slash( wp_json_encode( $v[1] ) ) );
}
file_put_contents( $theme . 'ausvac-pages.js', $got['js'] );
file_put_contents( $theme . 'ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', $got['css'] );
$exc = get_option( 'litespeed.conf.media-lazy_exc' );
$exc = is_array( $exc ) ? $exc : (array) json_decode( (string) $exc, true );
foreach ( array( 'ausvac-stock-10016029', 'ausvac-stock-29351659' ) as $x ) {
	if ( ! in_array( $x, $exc, true ) ) {
		$exc[] = $x;
	}
}
if ( class_exists( '\LiteSpeed\Conf' ) ) {
	\LiteSpeed\Conf::cls()->update_confs( array( 'media-lazy_exc' => $exc ) );
}
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\n";
echo 'pages: ' . implode( ', ', array_keys( $new ) ) . "\n";
echo 'media: ' . count( $ids ) . " photos\n";
echo "done\n";
