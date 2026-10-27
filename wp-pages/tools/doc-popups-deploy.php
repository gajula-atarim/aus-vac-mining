<?php
/*
 * One-off deploy: client documents + placeholder document popups.
 * Run through atarim-execute-php (eval'd after a sha1 check). Every fetched
 * file is pinned to $commit and checked against its sha1. Nothing is written
 * unless every replacement matches exactly.
 */
$commit = '377655d048a43d7f57380bfb6fc257ffa5c7ac83';
$raw    = 'https://raw.githubusercontent.com/gajula-atarim/aus-vac-mining/' . $commit . '/wp-pages/';
$files  = array(
	'pdf'  => array( 'assets/docs/ausvac-mine-floor-material-recovery-presentation.pdf', '94d1a817fd7496a1a622a6dd227c95969bbae7e4' ),
	'xlsx' => array( 'assets/docs/ausvac-gold-nickel-prices-13-08-2026.xlsx', '9a18eb6589f4797fbdc6030abb82350bdc48e43c' ),
	'docx' => array( 'assets/docs/kris-biegaj-cv-english.docx', '1bd89de993363aa6d8f40e3880197ccb5d2b16ad' ),
	'tpl'  => array( 'doc-popups/templates.json', '03121c30b440f2d0bdefefb646eca0ea639ad8fc' ),
	'js'   => array( 'theme/assets/ausvac-pages.js', '7feaae7aa342f21de782169917eb933fadda8b39' ),
	'css'  => array( 'theme/assets/ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css', '29050e29d3972865555ec22d26f3c804ec123d1d' ),
);
$got = array();
foreach ( $files as $k => $f ) {
	$r = wp_remote_get( $raw . $f[0], array( 'timeout' => 60 ) );
	$b = is_wp_error( $r ) ? '' : wp_remote_retrieve_body( $r );
	if ( sha1( $b ) !== $f[1] ) {
		echo "ABORT: sha1 mismatch for {$f[0]}\n";
		return;
	}
	$got[ $k ] = $b;
}
$tpl = json_decode( $got['tpl'], true );

// Media library: wp-content/uploads/ausvac-docs/<name>.
$up  = wp_upload_dir();
$dir = trailingslashit( $up['basedir'] ) . 'ausvac-docs';
wp_mkdir_p( $dir );
require_once ABSPATH . 'wp-admin/includes/image.php';
$urls = array();
foreach ( array( 'pdf', 'xlsx', 'docx' ) as $k ) {
	$name = basename( $files[ $k ][0] );
	$path = $dir . '/' . $name;
	file_put_contents( $path, $got[ $k ] );
	$urls[ $k ] = trailingslashit( $up['baseurl'] ) . 'ausvac-docs/' . $name;
	$type       = wp_check_filetype( $name );
	$existing   = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_wp_attached_file', 'meta_value' => 'ausvac-docs/' . $name, 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( ! $existing ) {
		$titles = array( 'pdf' => 'Mine Floor Material Recovery Should Become Part of Life of Mine Plan (presentation)', 'xlsx' => 'Gold and Nickel prices 13 08 2026 (spreadsheet)', 'docx' => 'Kris Biegaj CV - English (Word)' );
		$id     = wp_insert_attachment( array( 'post_mime_type' => $type['type'], 'post_title' => $titles[ $k ], 'post_status' => 'inherit' ), $path );
		update_post_meta( $id, '_wp_attached_file', 'ausvac-docs/' . $name );
	}
}

// Page edits, prepared in memory first.
$edits = array(
	76 => array( 'el' => '33621ec', 'fn' => function ( $h ) use ( $urls, $tpl ) {
		$n = 0;
		$h = str_replace( '<a href="#" class="av-sv-x9"', '<a href="' . $urls['pdf'] . '" class="av-sv-x9" target="_blank" rel="noopener"', $h, $c ); $n += $c;
		$i = 0;
		$map = array(
			'<a href="' . $urls['xlsx'] . '" download class="av-sv-xa"',
			'<a href="#asx" data-av-doc-open="asx" class="av-sv-xa"',
			'<a href="#qa" data-av-doc-open="qa" class="av-sv-xa"',
			'<a href="#testimonials" data-av-doc-open="testimonials" class="av-sv-xa"',
		);
		$h = preg_replace_callback( '/<a href="#" class="av-sv-xa"/', function () use ( &$i, $map ) { return $map[ $i++ ] ?? 'X'; }, $h );
		$n += $i;
		$h = str_replace( 'Gold and Nickel prices updated on 23 11 2024', 'Gold and Nickel prices updated on 13 08 2026', $h, $c ); $n += $c;
		$p = strrpos( $h, '</section>' );
		$h = substr( $h, 0, $p ) . $tpl['services'] . substr( $h, $p );
		return array( $h, $n, 7 );
	} ),
	75 => array( 'el' => 'e3418cc', 'fn' => function ( $h ) use ( $urls, $tpl ) {
		$i   = 0;
		$map = array(
			'<a data-reveal="" href="' . $urls['docx'] . '" download class="av-co-x3"',
			'<a data-reveal="" href="#cv-ru" data-av-doc-open="cv-ru" class="av-co-x3"',
			'<a data-reveal="" href="#cv-pdf" data-av-doc-open="cv-pdf" class="av-co-x3"',
		);
		$h = preg_replace_callback( '/<a data-reveal="" href="#" class="av-co-x3"/', function () use ( &$i, $map ) { return $map[ $i++ ] ?? 'X'; }, $h );
		$p = strrpos( $h, '</section>' );
		$h = substr( $h, 0, $p ) . $tpl['company'] . substr( $h, $p );
		return array( $h, $i, 3 );
	} ),
);
$new = array();
foreach ( $edits as $pid => $e ) {
	$raw_data = get_post_meta( $pid, '_elementor_data', true );
	$d        = json_decode( $raw_data, true );
	$hit      = false;
	$walk     = function ( &$els ) use ( &$walk, $e, &$hit, $pid ) {
		foreach ( $els as &$el ) {
			if ( $el['id'] === $e['el'] ) {
				list( $h, $n, $want ) = $e['fn']( $el['settings']['html'] );
				if ( $n !== $want || false !== strpos( $h, 'class="X' ) ) {
					echo "ABORT: post $pid matched $n of $want\n";
					return;
				}
				$el['settings']['html'] = $h;
				$hit                    = true;
			}
			if ( ! empty( $el['elements'] ) ) {
				$walk( $el['elements'] );
			}
		}
	};
	$walk( $d );
	if ( ! $hit ) {
		echo "ABORT: widget {$e['el']} not updated on post $pid\n";
		return;
	}
	$new[ $pid ] = array( $raw_data, $d );
}

// Write: backups, then theme files and page data.
$bk = WP_CONTENT_DIR . '/ausvac-backup-' . gmdate( 'Ymd-His' );
wp_mkdir_p( $bk );
$theme = get_stylesheet_directory() . '/assets/';
foreach ( array( 'js' => 'ausvac-pages.js', 'css' => 'ausvac-zzzzzzzzzzzzzzzzzzzzzzzzzzz-site-fixes.css' ) as $k => $f ) {
	copy( $theme . $f, $bk . '/' . $f );
	file_put_contents( $theme . $f, $got[ $k ] );
}
foreach ( $new as $pid => $v ) {
	file_put_contents( $bk . "/elementor-$pid.json", $v[0] );
	update_post_meta( $pid, '_elementor_data', wp_slash( wp_json_encode( $v[1] ) ) );
}
if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
do_action( 'litespeed_purge_all' );
echo "backup: $bk\n";
foreach ( $urls as $k => $u ) {
	echo "$k: $u\n";
}
echo "done\n";
