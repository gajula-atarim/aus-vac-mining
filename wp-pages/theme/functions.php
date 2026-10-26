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
}, 20 );

add_action( 'wp_body_open', function () {
	echo '<div id="av-progress"></div>';
} );

/**
 * Contact page form (assets/ausvac-pages.js posts here).
 * Mails the site admin address; honeypot + minimum fill time against bots.
 */
function ausvac_contact_submit() {
	$field = function ( $key ) {
		return isset( $_POST[ $key ] ) ? trim( wp_unslash( $_POST[ $key ] ) ) : '';
	};

	if ( '' !== $field( 'website' ) || (int) $field( 'elapsed' ) < 3000 ) {
		wp_send_json_error( 'Your message could not be sent. Please call or email us instead.', 400 );
	}

	$name    = sanitize_text_field( $field( 'name' ) );
	$email   = sanitize_email( $field( 'email' ) );
	$subject = sanitize_text_field( $field( 'subject' ) );
	$message = sanitize_textarea_field( $field( 'message' ) );
	$updates = '' !== $field( 'updates' ) ? 'Yes' : 'No';

	if ( '' === $name || '' === $subject || '' === $message || ! is_email( $email ) ) {
		wp_send_json_error( 'Please fill in all required fields with a valid email address.', 400 );
	}

	$to   = apply_filters( 'ausvac_contact_recipient', get_option( 'admin_email' ) );
	$body = "Name: {$name}\nEmail: {$email}\nEmail updates: {$updates}\n\n{$message}\n";
	$sent = wp_mail(
		$to,
		'[Website enquiry] ' . $subject,
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	if ( ! $sent ) {
		wp_send_json_error( 'Your message could not be sent right now. Please call or email us instead.', 500 );
	}
	wp_send_json_success();
}
add_action( 'wp_ajax_ausvac_contact', 'ausvac_contact_submit' );
add_action( 'wp_ajax_nopriv_ausvac_contact', 'ausvac_contact_submit' );
