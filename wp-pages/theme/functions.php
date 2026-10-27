<?php
/**
 * Ausvac Mining child theme.
 */

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	wp_enqueue_style( 'hello-elementor-parent', get_template_directory_uri() . '/style.css', array(), null );
	wp_enqueue_style( 'ausvac-child', $uri . '/style.css', array( 'hello-elementor-parent' ), filemtime( $dir . '/style.css' ) );
	wp_enqueue_style( 'ausvac-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap', array(), null );

	$order = array( 'ausvac-brand.css', 'ausvac-sections.css', 'ausvac-layout.css', 'ausvac-widgets.css' );
	$found = array();
	foreach ( (array) glob( $dir . '/assets/*.css' ) as $f ) {
		$found[] = basename( $f );
	}
	sort( $found );
	$list = array();
	foreach ( array_merge( $order, $found ) as $f ) {
		if ( ! in_array( $f, $list, true ) && file_exists( $dir . '/assets/' . $f ) ) {
			$list[] = $f;
		}
	}

	$prev = 'ausvac-child';
	foreach ( $list as $file ) {
		$handle = 'av-' . sanitize_title( $file );
		wp_enqueue_style( $handle, $uri . '/assets/' . $file, array( $prev ), filemtime( $dir . '/assets/' . $file ) );
		$prev = $handle;
	}

	wp_enqueue_script( 'ausvac-home', $uri . '/assets/ausvac-home.js', array(), filemtime( $dir . '/assets/ausvac-home.js' ), true );

	// "Read the full story" popup.
	if ( file_exists( $dir . '/assets/ausvac-story-modal.js' ) ) {
		wp_enqueue_script( 'ausvac-story-modal', $uri . '/assets/ausvac-story-modal.js', array( 'ausvac-home' ), filemtime( $dir . '/assets/ausvac-story-modal.js' ), true );
	}

	// Inner pages (Company, Services, Publications, Contact); exits early elsewhere.
	if ( file_exists( $dir . '/assets/ausvac-pages.js' ) ) {
		wp_enqueue_script( 'ausvac-pages', $uri . '/assets/ausvac-pages.js', array(), filemtime( $dir . '/assets/ausvac-pages.js' ), true );
	}

	// Contact Form 7: "Thank you" box after a successful send.
	if ( defined( 'WPCF7_VERSION' ) && file_exists( $dir . '/assets/ausvac-cf7.js' ) ) {
		wp_enqueue_script( 'ausvac-cf7', $uri . '/assets/ausvac-cf7.js', array(), filemtime( $dir . '/assets/ausvac-cf7.js' ), true );
	}
}, 20 );

add_action( 'wp_body_open', function () {
	echo '<div id="av-progress"></div>';
} );

/**
 * Contact Form 7: keep the form markup exactly as written (no auto <p>/<br>),
 * and run its shortcode inside Elementor HTML widgets (the Contact page form).
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

add_filter( 'elementor/widget/render_content', function ( $content, $widget ) {
	if ( 'html' === $widget->get_name() && false !== strpos( $content, '[contact-form-7' ) ) {
		return do_shortcode( $content );
	}
	return $content;
}, 10, 2 );
