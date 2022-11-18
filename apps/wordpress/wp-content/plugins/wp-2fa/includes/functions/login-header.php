<?php

/**
 * Output the login page header.
 *
 * @param string   $title    Optional. WordPress login Page title to display in the `<title>` element.
 *                           Default 'Log In'.
 * @param string   $message  Optional. Message to display in header. Default empty.
 * @param WP_Error $wp_error Optional. The error to pass. Default is a WP_Error instance.
 */
function login_header( $title = 'Log In', $message = '', $wp_error = null ) {

	global $error, $interim_login, $action;
	// Don't index any of these forms.
	add_action( 'login_head', 'wp_no_robots' );
	add_action( 'login_head', 'wp_login_viewport_meta' );

	if ( ! is_wp_error( $wp_error ) ) {
			$wp_error = new WP_Error();
	}
		// Shake it!
		$shake_error_codes = array( 'empty_password', 'empty_email', 'invalid_email', 'invalidcombo', 'empty_username', 'invalid_username', 'incorrect_password' );
		/**
		 * Filters the error codes array for shaking the login form.
		 *
		 * @since 3.0.0
		 *
		 * @param array $shake_error_codes Error codes that shake the login form.
		 */
		$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );
	if ( $shake_error_codes && $wp_error->get_error_code() && in_array( $wp_error->get_error_code(), $shake_error_codes ) ) {
			add_action( 'login_head', 'wp_shake_js', 12 );
	}
		$login_title = get_bloginfo( 'name', 'display' );
		/* translators: Login screen title. 1: Login screen name, 2: Network or site name */
		$login_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), $title, $login_title );
		/**
		 * Filters the title tag content for login page.
		 *
		 * @since 4.9.0
		 *
		 * @param string $login_title The page title, with extra context added.
		 * @param string $title       The original page title.
		 */
		$login_title = apply_filters( 'login_title', $login_title, $title );
	?><!DOCTYPE html>
		<!--[if IE 8]>
				<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php language_attributes(); ?>>
		<![endif]-->
		<!--[if !(IE 8) ]><!-->
				<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<!--<![endif]-->
		<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
		<title><?php echo $login_title; ?></title>
		<?php
		wp_enqueue_style( 'login' );
		/*
		 * Remove all stored post data on logging out.
		 * This could be added by add_action('login_head'...) like wp_shake_js(),
		 * but maybe better if it's not removable by plugins
		 */
		if ( 'loggedout' == $wp_error->get_error_code() ) {
			?>
				<script>if("sessionStorage" in window){try{for(var key in sessionStorage){if(key.indexOf("wp-autosave-")!=-1){sessionStorage.removeItem(key)}}}catch(e){}};</script>
				<?php
		}
		/**
		 * Enqueue scripts and styles for the login page.
		 *
		 * @since 3.1.0
		 */
		do_action( 'login_enqueue_scripts' );
		/**
		 * Fires in the login page header after scripts are enqueued.
		 *
		 * @since 2.1.0
		 */
		do_action( 'login_head' );
		if ( \WP2FA\Admin\Helpers\WP_Helper::is_multisite() ) {
				$login_header_url   = network_home_url();
				$login_header_title = get_network()->site_name;
		} else {
				$login_header_url   = __( 'https://wordpress.org/' );
				$login_header_title = __( 'Powered by WordPress' );
		}
		/**
		 * Filters link URL of the header logo above login form.
		 *
		 * @since 2.1.0
		 *
		 * @param string $login_header_url Login header logo URL.
		 */
		$login_header_url = apply_filters( 'login_headerurl', $login_header_url );
		/**
		 * Filters the title attribute of the header logo above login form.
		 *
		 * @since 2.1.0
		 *
		 * @param string $login_header_title Login header logo title attribute.
		 */
		$login_header_title = apply_filters( 'login_headertitle', $login_header_title );
		/*
		 * To match the URL/title set above, Multisite sites have the blog name,
		 * while single sites get the header title.
		 */
		if ( \WP2FA\Admin\Helpers\WP_Helper::is_multisite() ) {
				$login_header_text = get_bloginfo( 'name', 'display' );
		} else {
				$login_header_text = $login_header_title;
		}
		$classes = array( 'login-action-' . $action, 'wp-core-ui' );
		if ( is_rtl() ) {
				$classes[] = 'rtl';
		}
		if ( $interim_login ) {
				$classes[] = 'interim-login';
			?>
				<style type="text/css">html{background-color: transparent;}</style>
				<?php
				if ( 'success' === $interim_login ) {
						$classes[] = 'interim-login-success';
				}
		}
		$classes[] = ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );
		/**
		 * Filters the login page body classes.
		 *
		 * @since 3.5.0
		 *
		 * @param array  $classes An array of body classes.
		 * @param string $action  The action that brought the visitor to the login page.
		 */
		$classes = apply_filters( 'login_body_class', $classes, $action );
		?>
		</head>
		<body class="login <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<?php
		/**
		 * Fires in the login page header after the body tag is opened.
		 *
		 * @since 4.6.0
		 */
		do_action( 'login_header' );
		?>
		<div id="login">
				<h1><a href="<?php echo esc_url( $login_header_url ); ?>" title="<?php echo esc_attr( $login_header_title ); ?>" tabindex="-1"><?php echo $login_header_text; ?></a></h1>
		<?php
		unset( $login_header_url, $login_header_title );
		/**
		 * Filters the message to display above the login form.
		 *
		 * @since 2.1.0
		 *
		 * @param string $message Login message text.
		 */
		$message = apply_filters( 'login_message', $message );
		if ( ! empty( $message ) ) {
				echo $message . "\n";
		}
		// In case a plugin uses $error rather than the $wp_errors object.
		if ( ! empty( $error ) ) {
				$wp_error->add( 'error', $error );
				unset( $error );
		}
		if ( $wp_error->get_error_code() ) {
				$errors   = '';
				$messages = '';
			foreach ( $wp_error->get_error_codes() as $code ) {
					$severity = $wp_error->get_error_data( $code );
				foreach ( $wp_error->get_error_messages( $code ) as $error_message ) {
					if ( 'message' == $severity ) {
							$messages .= '  ' . $error_message . "<br />\n";
					} else {
						$errors .= '    ' . $error_message . "<br />\n";
					}
				}
			}
			if ( ! empty( $errors ) ) {
					/**
					 * Filters the error messages displayed above the login form.
					 *
					 * @since 2.1.0
					 *
					 * @param string $errors Login error message.
					 */
					echo '<div id="login_error">' . apply_filters( 'login_errors', $errors ) . "</div>\n";
			}
			if ( ! empty( $messages ) ) {
					/**
					 * Filters instructional messages displayed above the login form.
					 *
					 * @since 2.5.0
					 *
					 * @param string $messages Login messages.
					 */
					echo '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
			}
		}
} // End of login_header().

function wp_login_viewport_meta() {
	?>
		<meta name="viewport" content="width=device-width" />
		<?php
}
