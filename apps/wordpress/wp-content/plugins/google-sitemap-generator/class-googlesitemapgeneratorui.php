<?php
/**
 * UI class for the sitemap.
 *
 * @author Arne Brachhold
 * @package sitemap
 */

/**
 * $Id: class-googlesitemapgeneratorui.php 935247 2014-06-19 17:13:03Z arnee $
 */
class GoogleSitemapGeneratorUI {

	/**
	 * The Sitemap Generator Object
	 *
	 * @var GoogleSitemapGenerator
	 */
	private $sg = null;
	/**
	 * Check if woo commerce is active or not .
	 *
	 * @var boolean
	 */
	private $has_woo_commerce = false;
	/**
	 * Constructor function.
	 *
	 * @param GoogleSitemapGenerator $sitemap_builder s .
	 */
	public function __construct( GoogleSitemapGenerator $sitemap_builder ) {
		$this->sg = $sitemap_builder;
	}
	/**
	 * Constructor function.
	 *
	 * @param string $id s .
	 * @param string $title .
	 */
	private function html_print_box_header( $id, $title ) {
		?>
		<div id='<?php echo esc_attr( $id ); ?>' class='postbox'>
			<h3 class='hndle'><span><?php echo esc_html( $title ); ?> </span> </h3>
			<div class='inside'>
			<?php
	}
	/**
	 * Constructor function.
	 */
	private function html_print_box_footer() {
		?>
			</div>
		</div>
		<?php
	}

	/**
	 * Echos option fields for an select field containing the valid change frequencies
	 *
	 * @since 4.0
	 * @param string $current_val mixed The value which should be selected.
	 */
	public function html_get_freq_names( $current_val ) {
		foreach ( $this->sg->get_freq_names() as $k => $v ) {
			echo "<option value='" . esc_attr( $k ) . "' " . esc_attr( self::html_get_selected( $k, $current_val ) ) . '>' . esc_attr( $v ) . ' </option>';
		}
	}

	/**
	 * Echos option fields for an select field containing the valid priorities (0- 1.0)
	 *
	 * @since 4.0
	 * @param string $current_val string The value which should be selected.
	 * @return void
	 */
	public static function html_get_priority_values( $current_val ) {
		$current_val = (float) $current_val;
		for ( $i = 0.0; $i <= 1.0; $i += 0.1 ) {
			$v = number_format( $i, 1, '.', '' );
			echo "<option value='" . esc_attr( $v ) . "' " . esc_attr( self::html_get_selected( $i, $current_val ) ) . '>';
			echo esc_attr( number_format_i18n( $i, 1 ) );
			echo '</option>';
		}
	}

	/**
	 * Returns the checked attribute if the given values match
	 *
	 * @since 4.0
	 * @param string $val string The current value.
	 * @param string $equals string The value to match.
	 * @return string The checked attribute if the given values match, an empty string if not
	 */
	public static function html_get_checked( $val, $equals ) {
		$is_equals = $val === $equals;
		if ( $is_equals ) {
			return self::html_get_attribute( 'checked' );
		} else {
			return '';
		}
	}

	/**
	 * Returns the selected attribute if the given values match
	 *
	 * @since 4.0
	 * @param string $val string The current value.
	 * @param string $equals string The value to match.
	 * @return string The selected attribute if the given values match, an empty string if not
	 */
	public static function html_get_selected( $val, $equals ) {
		if ( is_numeric( $val ) && is_numeric( $equals ) ) {
			$is_equals = ( round( $val * 10 ) === round( $equals * 10 ) );
		} else {
			$is_equals = $val === $equals;
		}
		if ( $is_equals ) {
			return self::html_get_attribute( 'selected' );
		} else {
			return '';
		}
	}
	/**
	 * Active Sitemap listing .
	 */
	public function active_plugins() {
		$plugins = get_plugins();
		foreach ( $plugins as $key => $val ) {
			if ( 'WooCommerce' === $val['Name'] && is_plugin_active( $key ) ) {
				$this->has_woo_commerce = true;
			}
		}
	}
	/**
	 * Returns an formatted attribute. If the value is NULL, the name will be used.
	 *
	 * @since 4.0
	 * @param string $attr string The attribute name.
	 * @param string $value string The attribute value.
	 * @return string The formatted attribute
	 */
	public static function html_get_attribute( $attr, $value = null ) {
		if ( null === $value ) {
			$value = $attr;
		}
		return ' ' . $attr . '=' . esc_attr( $value ) . ' ';
	}

	/**
	 * Returns an array with GoogleSitemapGeneratorPage objects which is generated from POST values
	 *
	 * @since 4.0
	 * @see GoogleSitemapGeneratorPage
	 * @return array An array with GoogleSitemapGeneratorPage objects
	 */
	public function html_apply_pages() {
		//phpcs:disable
		// $pages_ur = ( ( ! isset( $_POST['sm_pages_ur'] ) ) ) && ( ! isset( $_POST['sm_pages_ur'] ) || ! is_array( $_POST['sm_pages_ur'] ) ? array() : esc_url_raw( wp_unslash( $_POST['sm_pages_ur'] ) ) );
			// Array with all priorities.
			$pages_ur = array();
			$pages_pr = array();
			$pages_cf = array();
			$pages_lm = array();
			$pages_ur = ( ! isset( $_POST['sm_pages_ur'] ) || ! is_array( $_POST['sm_pages_ur'] ) ) ? array() : array_map( 'sanitize_text_field', wp_unslash( $_POST['sm_pages_ur'] ) );
		// if ( isset( $_POST['sm_pages_pr'] ) && wp_verify_nonce( array_map( 'sanitize_text_field', ( wp_unslash( $_POST['sm_pages_pr'] ) ) ) ) ) {
			$pages_pr = ( ! isset( $_POST['sm_pages_pr'] ) || ! is_array( $_POST['sm_pages_pr'] ) ? array() : array_map( 'sanitize_text_field', wp_unslash( $_POST['sm_pages_pr'] ) ) );
		// }
		// if ( isset( $_POST['sm_pages_cf'] ) && wp_verify_nonce( array_map( 'sanitize_text_field', ( wp_unslash( $_POST['sm_pages_cf'] ) ) ) ) ) {
			$pages_cf = ( ! isset( $_POST['sm_pages_cf'] ) || ! is_array( $_POST['sm_pages_cf'] ) ? array() : array_map( 'sanitize_text_field', wp_unslash( $_POST['sm_pages_cf'] ) ) );
		// }
		// if ( isset( $_POST['sm_pages_lm'] ) && wp_verify_nonce( array_map( 'sanitize_text_field', ( wp_unslash( $_POST['sm_pages_lm'] ) ) ) ) ) {
			$pages_lm = ( ! isset( $_POST['sm_pages_lm'] ) || ! is_array( $_POST['sm_pages_lm'] ) ? array() : array_map( 'sanitize_text_field', wp_unslash( $_POST['sm_pages_lm'] ) ) );
		// }
		// Array where the new pages are stored.
		$pages = array();
		// Loop through all defined pages and set their properties into an object.
		if ( isset( $_POST['sm_pages_mark'] ) && is_array( $_POST['sm_pages_mark'] ) ) {
			$len = count( $_POST['sm_pages_mark'] );
			// phpcs:enable
			for ( $i = 0; $i < $len; $i++ ) {
				// Create new object.
				$p = new GoogleSitemapGeneratorPage();
				if ( substr( $pages_ur[ $i ], 0, 4 ) === 'www.' ) {
					$pages_ur[ $i ] = 'http://' . $pages_ur[ $i ];
				}
				$p->set_url( $pages_ur[ $i ] );
				$p->set_priority( $pages_pr[ $i ] );
				$p->set_change_freq( $pages_cf[ $i ] );
				// Try to parse last modified, if -1 (note ===) automatic will be used (0).
				$lm = ( ! empty( $pages_lm[ $i ] ) ? strtotime( $pages_lm[ $i ], time() ) : -1 );
				if ( -1 === $lm ) {
					$p->set_last_mod( 0 );
				} else {
					$p->set_last_mod( $lm );
				}
				// Add it to the array.
				array_push( $pages, $p );
			}
		}
		return $pages;
	}
	/**
	 * Escape.
	 *
	 * @param string $v String.
	 */
	public static function escape( $v ) {
		// prevent html tags in strings where they are not required.
		return strtr( $v, '<>', '..' );
	}
	/**
	 * Array_map_r.
	 *
	 * @param array $func .
	 * @param array $arr .
	 */
	public static function array_map_r( $func, $arr ) {
		$new_arr = array();
		foreach ( $arr as $key => $value ) {
			$new_arr[ $key ] = ( is_array( $value ) ? self::array_map_r( $func, $value ) : ( is_array( $func ) ? call_user_func_array( $func, $value ) : $func( $value ) ) );
		}
		foreach ( $new_arr as $k => $v ) {
			echo esc_html( ' [ ' . $k . ' ]   =>   ' . $v );
			echo '<br />';
		}
	}

		/**
		 * Displays the option page
		 *
		 * @since 3.0
		 * @access public
		 * @author Arne Brachhold
		 */
	public function html_show_options_page() {
		$this->active_plugins();
		global $wp_version;
		$snl = false; // SNL.

		$this->sg->initate();

		$message = '';

		if ( ! empty( $_REQUEST['sm_rebuild'] ) ) {
			// Pressed Button: Rebuild Sitemap.
			check_admin_referer( 'sitemap' );

			if ( isset( $_GET['sm_do_debug'] ) && 'true' === $_GET['sm_do_debug'] ) {

				// Check again, just for the case that something went wrong before.
				if ( ! current_user_can( 'administrator' ) || ! is_super_admin() ) {
					echo '<p>Please log in as admin</p>';
					return;
				}

				echo "<div class='wrap'>";
				echo '<h2>' . esc_html( __( 'XML Sitemap Generator for WordPress', 'sitemap' ) ) . ' ' . esc_html( $this->sg->get_version() ) . '</h2>';
				echo '<p>This is the debug mode of the XML Sitemap Generator. It will show all PHP notices and warnings as well as the internal logs, messages and configuration.</p>';
				echo "<p style='font-weight:bold; color:red; padding:5px; border:1px red solid; text-align:center;'>DO NOT POST THIS INFORMATION ON PUBLIC PAGES LIKE SUPPORT FORUMS AS IT MAY CONTAIN PASSWORDS OR SECRET SERVER INFORMATION!</p>";
				echo '<h3>WordPress and PHP Information</h3>';
				echo '<p>WordPress ' . esc_html( $GLOBALS['wp_version'] ) . ' with  DB ' . esc_html( $GLOBALS['wp_db_version'] ) . ' on PHP ' . esc_html( phpversion() ) . '</p>';
				echo '<p>Plugin version: ' . esc_html( $this->sg->get_version() ) . ' (' . esc_html( $this->sg->get_svn_version() ) . ')';
				echo '<h4>Environment</h4>';
				echo '<pre>';
				$sc = $_SERVER;
				$this->sg->get_svn_version();
				unset( $sc['HTTP_COOKIE'] );
				foreach ( $sc as $key => $value ) {
					echo esc_html( ' [ ' . $key . ' ]   =>   ' . $value );
					echo '<br />';
				}
				echo '</pre>';
				echo '<h4>WordPress Config</h4>';
				echo '<pre>';
				$opts = array();
				if ( function_exists( 'wp_load_alloptions' ) ) {
					$opts = wp_load_alloptions();
				} else {
					// @var $wpdb wpdb .
					global $wpdb;
					$os = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options" ); // db call ok; no-cache ok.
					foreach ( (array) $os as $o ) {
						$opts[ $o->option_name ] = $o->option_value;
					}
				}

				$popts = array();
				foreach ( $opts as $k => $v ) {
					// Try to filter out passwords etc...
					if ( preg_match( '/pass|login|pw|secret|user|usr|key|auth|token/si', $k ) ) {
						continue;
					}
					$popts[ $k ] = htmlspecialchars( $v );
				}
				foreach ( $popts as $key => $value ) {
					echo esc_html( ' [ ' . $key . ' ]   =>   ' . $value );
					echo '<br />';
				}
				echo '</pre>';
				echo '<h4>Sitemap Config</h4>';
				echo '<pre>';
				self::array_map_r( 'strip_tags', $this->sg->get_options() );
				echo '</pre>';
				echo '<h3>Sitemap Content and Errors, Warnings, Notices</h3>';
				echo '<div>';

				$sitemaps = $this->sg->simulate_index();

				foreach ( $sitemaps as $sitemap ) {

					// @var $s GoogleSitemapGeneratorSitemapEntry .
					$s = $sitemap['data'];
					echo '<h4>Sitemap: <a href=\'' . esc_url( $s->get_url() ) . '\'>' . esc_html( $sitemap['type'] ) . '/' . ( esc_html( $sitemap['params'] ) ? esc_html( $sitemap['params'] ) : '(No parameters)' ) . '</a> by ' . esc_html( $sitemap['caller']['class'] ) . '</h4>';

					$res = $this->sg->simulate_sitemap( $sitemap['type'], $sitemap['params'] );

					echo "<ul style='padding-left:10px;'>";
					foreach ( $res as $s ) {
						// @var $d GoogleSitemapGeneratorSitemapEntry .
						$d = $s['data'];
						echo '<li>' . esc_html( $d->get_url() ) . '</li>';
					}
					echo '</ul>';
				}

				$status = GoogleSitemapGeneratorStatus::load();
				echo '</div>';
				echo '<h3>MySQL Queries</h3>';
				if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
					echo '<pre>';
					// phpcs:disable.
					var_dump( $GLOBALS['wpdb']->queries );
					// phpcs:enable
					echo '</pre>';

					$total = 0;
					foreach ( $GLOBALS['wpdb']->queries as $q ) {
						$total += $q[1];
					}
					echo '<h4>Total Query Time</h4>';
					echo '<pre>' . count( $GLOBALS['wpdb']->queries ) . ' queries in ' . esc_html( round( $total, 2 ) ) . ' seconds.</pre>';
				} else {
					echo '<p>Please edit wp-db.inc.php in wp-includes and set SAVEQUERIES to true if you want to see the queries.</p>';
				}
				echo '<h3>Build Process Results</h3>';
				echo '<pre>';
				echo '</pre>';
				echo "<p>Done. <a href='" . esc_url( wp_nonce_url( $this->sg->get_back_link() ) . '&sm_rebuild=true&sm_do_debug=true', 'sitemap' ) . "'>Rebuild</a> or <a href='" . esc_url( $this->sg->get_back_link() ) . "'>Return</a></p>";
				echo "<p style='font-weight:bold; color:red; padding:5px; border:1px red solid; text-align:center;'>DO NOT POST THIS INFORMATION ON PUBLIC PAGES LIKE SUPPORT FORUMS AS IT MAY CONTAIN PASSWORDS OR SECRET SERVER INFORMATION!</p>";
				echo '</div>';
				return;
			} else {

				$redir_url = $this->sg->get_back_link() . '&sm_fromrb=true';

				// Redirect so the sm_rebuild GET parameter no longer exists.
				header( 'location: ' . $redir_url );
				// If there was already any other output, the header redirect will fail.
				echo "<script type='text/javascript'>location.replace('" . esc_js( $redir_url ) . "');</script>";
				echo "<noscript><a href='" . esc_url( $redir_url ) . "'>Click here to continue</a></noscript>";
				exit;
			}
		} elseif ( ! empty( $_POST['sm_update'] ) ) { // Pressed Button: Update Config.
			check_admin_referer( 'sitemap' );

			if ( isset( $_POST['sm_b_style'] ) && $_POST['sm_b_style'] === $this->sg->get_default_style() ) {
				$_POST['sm_b_style_default'] = true;
				$_POST['sm_b_style']         = '';
			}

			foreach ( $this->sg->get_options() as $k => $v ) {
				// Skip some options if the user is not super admin...
				if ( ! is_super_admin() && in_array( $k, array( 'sm_b_time', 'sm_b_memory', 'sm_b_style', 'sm_b_style_default' ), true ) ) {
					continue;
				}

				// Check vor values and convert them into their types, based on the category they are in.
				if ( ! isset( $_POST[ $k ] ) ) {
					$_POST[ $k ] = '';
				} // Empty string will get false on 2bool and 0 on 2float
				// Options of the category 'Basic Settings' are boolean, except the filename and the autoprio provider.
				if ( substr( $k, 0, 5 ) === 'sm_b_' ) {
					if ( 'sm_b_prio_provider' === $k || 'sm_b_style' === $k || 'sm_b_memory' === $k || 'sm_b_baseurl' === $k || 'sm_b_sitemap_name' === $k ) {
						if ( 'sm_b_filename_manual' === $k && strpos( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ), '\\' ) !== false ) {
							$_POST[ $k ] = stripslashes( self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) );
						} elseif ( 'sm_b_baseurl' === $k ) {
							$_POST[ $k ] = esc_url_raw( trim( self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) ) );
							if ( ! empty( $_POST[ $k ] ) ) {
								$_POST[ $k ] = untrailingslashit( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
							}
						} elseif ( 'sm_b_style' === $k ) {
							$_POST[ $k ] = esc_url_raw( trim( self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) ) );
							if ( ! empty( $_POST[ $k ] ) ) {
								$_POST[ $k ] = untrailingslashit( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
							}
						} elseif ( 'sm_b_sitemap_name' === $k ) {
							if ( '' === $_POST[ $k ] ) {
								$_POST[ $k ] = 'sitemap';
							} else {
								$_POST[ $k ] = trim( self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) );
								if ( $this->sg->old_file_exists() ) {
									$this->sg->delete_old_files();
								}
							}
						}
						$this->sg->set_option( $k, (string) sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
					} elseif ( 'sm_b_time' === $k ) {
						if ( '' === $_POST[ $k ] ) {
							$_POST[ $k ] = -1;
						}
						$this->sg->set_option( $k, intval( $_POST[ $k ] ) );
					} elseif ( 'sm_i_install_date' === $k ) {
						if ( $this->sg->get_option( 'i_install_date' ) <= 0 ) {
							$this->sg->set_option( $k, time() );
						}
					} elseif ( 'sm_b_exclude' === $k ) {
						$id_ss = array();
						$id_s  = explode( ',', sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
						$len   = count( $id_s );
						for ( $x = 0; $x < $len; $x++ ) {
							$id = intval( trim( $id_s[ $x ] ) );
							if ( $id > 0 ) {
								$id_ss[] = $id;
							}
						}
						$this->sg->set_option( $k, $id_ss );
					} elseif ( 'sm_b_exclude_cats' === $k ) {
						$ex_cats = array();
						if ( isset( $_POST['post_category'] ) ) {
							foreach ( (array) array_map( 'sanitize_text_field', ( wp_unslash( $_POST['post_category'] ) ) ) as $vv ) {
								if ( ! empty( $vv ) && is_numeric( $vv ) ) {
									$ex_cats[] = intval( $vv );
								}
							}
						}
						if ( isset( $_POST['tax_input'] ) && isset( $_POST['tax_input']['product_cat'] ) ) {
							$prod_cat = array_map( 'sanitize_text_field', ( wp_unslash( $_POST['tax_input']['product_cat'] ) ) );
							foreach ( (array) $prod_cat as $vv ) {
								if ( ! empty( $vv ) && is_numeric( $vv ) ) {
									$ex_cats[] = intval( $vv );
								}
							}
						}
						$taxonomies = $this->sg->get_custom_taxonomies();
						foreach ( $taxonomies as $key => $taxonomy ) {
							if ( isset( $_POST['tax_input'] ) && isset( $_POST['tax_input'][ $taxonomy ] ) ) {
								$custom_cat = array_map( 'sanitize_text_field', ( wp_unslash( $_POST['tax_input'][ $taxonomy ] ) ) );
								foreach ( (array) $custom_cat as $vv ) {
									if ( ! empty( $vv ) && is_numeric( $vv ) ) {
										$ex_cats[] = intval( $vv );
									}
								}
							}
						}
						$this->sg->set_option( $k, $ex_cats );
					} else {
						$this->sg->set_option( $k, (bool) $_POST[ $k ] );
					}
					// Options of the category 'Includes' are boolean.
				} elseif ( 'sm_i_tid' === $k ) {
					// $_POST[ $k ] = trim( self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) );
					$this->sg->set_option( $k, trim( self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) ) );
				} elseif ( substr( $k, 0, 6 ) === 'sm_in_' ) {
					if ( 'sm_in_tax' === $k ) {

						$enabled_taxonomies = array();
						$sm_in_tax          = isset( $_POST[ $k ] ) ? (array) array_map( 'sanitize_text_field', ( wp_unslash( is_array( $_POST[ $k ] ) ? $_POST[ $k ] : array() ) ) ) : array();
						foreach ( array_keys( (array) $sm_in_tax ) as $tax_name ) {
							if ( empty( $tax_name ) || ! taxonomy_exists( $tax_name ) ) {
								continue;
							}

							$enabled_taxonomies[] = self::escape( $tax_name );
						}

						$this->sg->set_option( $k, $enabled_taxonomies );
					} elseif ( 'sm_in_customtypes' === $k ) {

						$enabled_post_types = array();
						$sm_in_customtype   = isset( $_POST[ $k ] ) ? (array) array_map( 'sanitize_text_field', wp_unslash( is_array( $_POST[ $k ] ) ? $_POST[ $k ] : array() ) ) : array();
						foreach ( array_keys( (array) $sm_in_customtype ) as $post_type_name ) {
							if ( empty( $post_type_name ) || ! post_type_exists( $post_type_name ) ) {
								continue;
							}

							$enabled_post_types[] = self::escape( $post_type_name );
						}

						$this->sg->set_option( $k, $enabled_post_types );
					} else {
						$this->sg->set_option( $k, (bool) $_POST[ $k ] );
					}
					// Options of the category 'Change frequencies' are string.
				} elseif ( substr( $k, 0, 6 ) === 'sm_cf_' ) {
					$this->sg->set_option( $k, (string) self::escape( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) );
					// Options of the category 'Priorities' are float.
				} elseif ( substr( $k, 0, 6 ) === 'sm_pr_' ) {
					$this->sg->set_option( $k, (float) sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
				} elseif ( 'sm_links_page' === $k ) {
					$links_per_page = sanitize_text_field( wp_unslash( $_POST[ $k ] ) );
					$links_per_page = (int) $links_per_page;
					if ( 0 >= $links_per_page || is_nan( $links_per_page ) ) {
						$links_per_page = 10;
					}
					$this->sg->set_option( $k, (int) $links_per_page );
				} elseif ( substr( $k, 0, 3 ) === 'sm_' ) {
					$this->sg->set_option( $k, (bool) sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
				}
			}

			// Apply page changes from POST.
			if ( is_super_admin() ) {
				$this->sg->set_pages( $this->html_apply_pages() );
			}

			if ( $this->sg->save_options() ) {
				$message .= __( 'Configuration updated', 'sitemap' ) . '<br />';
			} else {
				$message .= __( 'Error while saving options', 'sitemap' ) . '<br />';
			}

			if ( is_super_admin() ) {
				if ( $this->sg->save_pages() ) {
					$message .= __( 'Pages saved', 'sitemap' ) . '<br />';
				} else {
					$message .= __( 'Error while saving pages', 'sitemap' ) . '<br />';
				}
			}
		} elseif ( ! empty( $_POST['sm_reset_config'] ) ) { // Pressed Button: Reset Config.
			check_admin_referer( 'sitemap' );
			$this->sg->init_options();
			$this->sg->save_options();

			$message .= __( 'The default configuration was restored.', 'sitemap' );
		} elseif ( ! empty( $_GET['sm_delete_old'] ) ) { // Delete old sitemap files.
			check_admin_referer( 'sitemap' );

			// Check again, just for the case that something went wrong before.
			if ( ! current_user_can( 'administrator' ) ) {
				echo '<p>Please log in as admin</p>';
				return;
			}
			if ( ! $this->sg->delete_old_files() ) {
				$message = __( 'The old files could NOT be deleted. Please use an FTP program and delete them by yourself.', 'sitemap' );
			} else {
				$message = __( 'The old files were successfully deleted.', 'sitemap' );
			}
		} elseif ( ! empty( $_GET['sm_ping_all'] ) ) {
			check_admin_referer( 'sitemap' );

			// Check again, just for the case that something went wrong before.
			if ( ! current_user_can( 'administrator' ) ) {
				echo '<p>Please log in as admin</p>';
				return;
			}

			?>
			<html>

			<head>
				<style type='text/css'>
					html {
						background: #f1f1f1;
					}

					body {
						color: #444;
						font-family: 'Open Sans', sans-serif;
						font-size: 13px;
						line-height: 1.4em;
						min-width: 600px;
					}

					h2 {
						font-size: 23px;
						font-weight: 400;
						padding: 9px 10px 4px 0;
						line-height: 29px;
					}
				</style>
			</head>

			<body>
				<?php
				echo '<h2>' . esc_html( __( 'Notify Search Engines about all sitemaps', 'sitemap' ) ) . '</h2>';
				echo '<p>' . esc_html( __( 'The plugin is notifying the selected search engines about your main sitemap and all sub-sitemaps. This might take a minute or two.', 'sitemaps' ) ) . '</p>';
				flush();
				$results = $this->sg->send_ping_all();

				echo '<ul>';

				foreach ( $results as $result ) {

					$sitemap_url = $result['sitemap'];
					// @var $status GoogleSitemapGeneratorStatus .
					$status = $result['status'];

					echo esc_html( '<li><a href=\'' . esc_url( $sitemap_url ) . '\'>' . $sitemap_url . '</a><ul>' );
					$services = $status->get_used_ping_services();
					foreach ( $services as $service_id ) {
						echo '<li>';
						echo esc_html( $status->get_service_name( $service_id ) . ': ' . ( $status->get_ping_result( $service_id ) === true ? 'OK' : 'ERROR' ) );
						echo '</li>';
					}
					echo '</ul></li>';
				}
				echo '</ul>';
				echo '<p>' . esc_html( __( 'All done!', 'sitemap' ) ) . '</p>';
				?>

			</body>
			<?php
				exit;
		} elseif ( ! empty( $_GET['sm_ping_main'] ) ) {
			if ( null !== $this->sg->get_option( 'i_tid' ) && '' !== $this->sg->get_option( 'i_tid' ) ) {
				check_admin_referer( 'sitemap' );

					// Check again, just for the case that something went wrong before.
				if ( ! current_user_can( 'administrator' ) ) {
					echo '<p>Please log in as admin</p>';
					return;
				}

				$this->sg->send_ping();
				$message = __( 'Ping was executed, please see below for the result.', 'sitemap' );
			} else {
				?>
				<div class='error'>
						<p>
						<?php
						$arr = array(
							'br'     => array(),
							'p'      => array(),
							'strong' => array(),
						);
						/* translators: %s: search term */
						echo wp_kses( __( 'Please add Google analytics tid in order to notify Google bots.', 'sitemap' ), $arr );
						?>
						</p>
					</div>
				<?php
			}
		}

		// Print out the message to the user, if any.
		if ( '' !== $message ) {
			?>
			<div class='updated'>
				<p><strong>
				<?php
				$arr = array(
					'br'     => array(),
					'p'      => array(),
					'strong' => array(),
				);
				echo wp_kses( $message, $arr );
				?>
				</strong></p>
			</div>
			<?php
		}

		if ( ! $snl ) {

			if ( isset( $_GET['sm_hidedonate'] ) ) {
				$this->sg->set_option( 'i_hide_donated', true );
				$this->sg->save_options();
			}
			if ( isset( $_GET['sm_donated'] ) ) {
				$this->sg->set_option( 'i_donated', true );
				$this->sg->save_options();
			}
			if ( isset( $_GET['sm_hide_note'] ) ) {
				$this->sg->set_option( 'i_hide_note', true );
				$this->sg->save_options();
			}
			if ( isset( $_GET['sm_hide_survey'] ) ) {
				$this->sg->set_option( 'i_hide_survey', true );
				$this->sg->save_options();
			}
			if ( isset( $_GET['sm_hidedonors'] ) ) {
				$this->sg->set_option( 'i_hide_donors', true );
				$this->sg->save_options();
			}
			if ( isset( $_GET['sm_hide_works'] ) ) {
				$this->sg->set_option( 'i_hide_works', true );
				$this->sg->save_options();
			}
			if ( isset( $_GET['sm_disable_supportfeed'] ) ) {
				$this->sg->set_option( 'i_supportfeed', 'true' === $_GET['sm_disable_supportfeed'] ? false : true );
				$this->sg->save_options();
			}

			if ( isset( $_GET['sm_donated'] ) || ( $this->sg->get_option( 'i_donated' ) === true && $this->sg->get_option( 'i_hide_donated' ) !== true ) ) {
				?>
				<!--
				<div class='updated'>
					<strong><p><?php esc_html_e( 'Thank you very much for your donation. You help me to continue support and development of this plugin and other free software!', 'sitemap' ); ?> <a href='<?php echo esc_url( $this->sg->get_back_link() ) . '&amp;sm_hidedonate=true'; ?>'><small style='font-weight:normal;'><?php esc_html_e( 'Hide this notice', 'sitemap' ); ?></small></a></p></strong>
				</div>
				-->
				<?php
			} elseif ( $this->sg->get_option( 'i_donated' ) !== true && $this->sg->get_option( 'i_install_date' ) > 0 && $this->sg->get_option( 'i_hide_note' ) !== true && time() > ( $this->sg->get_option( 'i_install_date' ) + ( 60 * 60 * 24 * 30 ) ) ) {
				?>
				<!--
				<div class="updated">
					<strong><p><?php echo esc_html( str_replace( '%s', $this->sg->get_redirect_link( 'redir/sitemap-donate-note' ), __( 'Thanks for using this plugin! You\'ve installed this plugin over a month ago. If it works and you are satisfied with the results, isn\'t it worth at least a few dollar? <a href="https://8rkh4sskhh.execute-api.us-east-1.amazonaws.com/gsg/v1/sitemap-donate-note">Donations</a> help me to continue support and development of this <i>free</i> software! <a href="https://8rkh4sskhh.execute-api.us-east-1.amazonaws.com/gsg/v1/sitemap-donate-note">Sure, no problem!</a>', 'sitemap' ) ) ); ?> <a href="<?php echo esc_url( $this->sg->get_back_link() ) . '&amp;sm_donated=true'; ?>" style="float:right; display:block; border:none; margin-left:10px;"><small style="font-weight:normal; "><?php esc_html_e( 'Sure, but I already did!', 'sitemap' ); ?></small></a> <a href="<?php echo esc_url( $this->sg->get_back_link() ) . '&amp;sm_hide_note=true'; ?>" style="float:right; display:block; border:none;"><small style="font-weight:normal; "><?php esc_html_e( 'No thanks, please don\'t bug me anymore!', 'sitemap' ); ?></small></a></p></strong>
					<div style="clear:right;"></div>
				</div>
				-->
				<?php
			} elseif ( $this->sg->get_option( 'i_install_date' ) > 0 && $this->sg->get_option( 'i_hide_works' ) !== true && time() > ( $this->sg->get_option( 'i_install_date' ) + ( 60 * 60 * 24 * 15 ) ) ) {
				?>
				<div class='updated'>
					<strong>
					<?php /* translators: %s: search term */ ?>
					<p><?php echo esc_html( str_replace( '%s', esc_html( $this->sg->get_redirect_link( 'redir/sitemap-works-note' ) ), esc_html( 'Thanks for using this plugin! You\'ve installed this plugin some time ago. If it works and your are satisfied, why not ' . esc_html( '<a href=\'%s\'>rate it</a>' ) . ' and <a href=\'%s\'>recommend it</a> to others? :-)' ) ) ); ?> <a href='<?php esc_url( $this->sg->get_back_link() ) . '&amp;sm_hide_works=true'; ?>' style='float:right; display:block; border:none;'><small style='font-weight:normal; '><?php esc_html_e( 'Don\'t show this me anymore', 'sitemap' ); ?></small></a></p>
					</strong>
					<div style='clear:right;'></div>
				</div>
				<?php
			}

			if ( $this->sg->show_survey() ) {
				$this->sg->html_survey();
			}
		}

		?>

		<style type='text/css'>
			li.sm_hint {
				color: green;
			}

			li.sm_optimize {
				color: orange;
			}

			li.sm_error {
				color: red;
			}

			input.sm_warning:hover {
				background: #ce0000;
				color: #fff;
			}

			a.sm_button {
				padding: 4px;
				display: block;
				background-repeat: no-repeat;
				background-position: 5px 50%;
				text-decoration: none;
				border: none;
			}

			a.sm_button:hover {
				border-bottom-width: 1px;
			}

			a.sm_donatePayPal {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-paypal.gif' ); ?>);
			}

			a.sm_donateAmazon {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-amazon.gif' ); ?>);
			}

			a.sm_pluginHome {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-arne.gif' ); ?>);
			}

			a.sm_pluginHelp {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-help.png' ); ?>);
			}

			a.sm_pluginList {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-email.gif' ); ?>);
			}

			a.sm_pluginSupport {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-wordpress.gif' ); ?>);
			}

			a.sm_pluginBugs {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-trac.gif' ); ?>);
			}

			a.sm_resGoogle {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-google.gif' ); ?>);
			}

			a.sm_resYahoo {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-yahoo.gif' ); ?>);
			}

			a.sm_resBing {
				background-image: url(<?php echo esc_url( $this->sg->get_plugin_url() . 'img/icon-bing.gif' ); ?>);
			}

			div.sm-update-nag p {
				margin: 5px;
			}

			.sm-padded .inside {
				margin: 12px !important;
			}

			.sm-padded .inside ul {
				margin: 6px 0 12px 0;
			}

			.sm-padded .inside input {
				padding: 1px;
				margin: 0;
			}

			.hndle {
				cursor: auto !important;
				-webkit-user-select: auto !important;
				-moz-user-select: auto !important;
				-ms-user-select: auto !important;
				user-select: auto !important;
			}


			<?php
			if ( version_compare( $wp_version, '3.4', '<' ) ) : // Fix style for WP 3.4 (dirty way for now..) .
				?>
			.inner-sidebar #side-sortables,
			.columns-2 .inner-sidebar #side-sortables {
				min-height: 300px;
				width: 280px;
				padding: 0;
			}

			.has-right-sidebar .inner-sidebar {
				display: block;
			}

			.inner-sidebar {
				float: right;
				clear: right;
				display: none;
				width: 281px;
				position: relative;
			}

			.has-right-sidebar #post-body-content {
				margin-right: 300px;
			}

			#post-body-content {
				width: auto !important;
				float: none !important;
			}

			<?php endif; ?>
		</style>


		<div class='wrap' id='sm_div'>
			<form method='post' action='<?php echo esc_url( $this->sg->get_back_link() ); ?>'>
				<h2>
					<?php
					esc_html_e( 'XML Sitemap Generator for WordPress', 'sitemap' );
					echo ' ' . esc_html( $this->sg->get_version() );
					?>
				</h2>
				<?php
				$blog_public = (int) get_option( 'blog_public' );
				if ( 1 !== $blog_public ) {
					?>
				<div class='error'>
						<p>
						<?php
						$arr = array(
							'br'     => array(),
							'p'      => array(),
							'a'      => array(
								'href' => array(),
							),
							'strong' => array(),
						);
						/* translators: %s: search term */
						echo wp_kses( str_replace( '%s', 'options-reading.php#blog_public', __( 'Your site is currently blocking search engines! Visit the <a href=\'%s\'>Reading Settings</a> to change this.', 'sitemap' ) ), $arr );
						?>
						</p>
					</div>
					<?php
				}

				?>

				<?php if ( ! $snl ) { ?>
					<div id='poststuff' class='metabox-holder has-right-sidebar'>
						<div class='inner-sidebar'>
							<div id='side-sortables' class='meta-box-sortabless ui-sortable' style='position:relative;'>
							<?php } else { ?>
								<div id='poststuff' class='metabox-holder'>
								<?php } ?>


								<?php if ( ! $snl ) : ?>
									<?php $this->html_print_box_header( 'sm_pnres', __( 'About this Plugin:', 'sitemap' ), true ); ?>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-home' ) ); ?>'><?php esc_html_e( 'Plugin Homepage', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-support' ) ); ?>'><?php esc_html_e( 'Suggest a Feature', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-help' ) ); ?>'><?php esc_html_e( 'Help / FAQ', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-list' ) ); ?>'><?php esc_html_e( 'Notify List', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-support' ) ); ?>'><?php esc_html_e( 'Support Forum', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-bugs' ) ); ?>'><?php esc_html_e( 'Report a Bug', 'sitemap' ); ?></a>
									<?php
									if ( __( 'translator_name', 'sitemap' ) !== 'translator_name' ) {
										?>
										<a class='sm_button sm_pluginSupport' href='<?php esc_html_e( 'translator_url', 'sitemap' ); ?>'><?php esc_html_e( 'translator_name', 'sitemap' ); ?></a><?php } ?>
									<?php $this->html_print_box_footer( true ); ?>

									<?php $this->html_print_box_header( 'sm_smres', __( 'Sitemap Resources:', 'sitemap' ), true ); ?>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-gwt' ) ); ?>'><?php esc_html_e( 'Webmaster Tools', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-gwb' ) ); ?>'><?php esc_html_e( 'Webmaster Blog', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-ywb' ) ); ?>'><?php esc_html_e( 'Search Blog', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-lwt' ) ); ?>'><?php esc_html_e( 'Webmaster Tools', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-lswcb' ) ); ?>'><?php esc_html_e( 'Webmaster Center Blog', 'sitemap' ); ?></a>
									<br />
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-prot' ) ); ?>'><?php esc_html_e( 'Sitemaps Protocol', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'projects/wordpress-plugins/google-xml-sitemaps-generator/help' ) ); ?>'><?php esc_html_e( 'Official Sitemaps FAQ', 'sitemap' ); ?></a>
									<a class='sm_button' href='<?php echo esc_url( $this->sg->get_redirect_link( 'projects/wordpress-plugins/google-xml-sitemaps-generator/help' ) ); ?>'><?php esc_html_e( 'My Sitemaps FAQ', 'sitemap' ); ?></a>
									<?php $this->html_print_box_footer( true ); ?>


								</div>
							</div>
						<?php endif; ?>

						<div class='has-sidebar sm-padded'>

							<div id='post-body-content' class='
							<?php
							if ( ! $snl ) :
								?>
								has-sidebar-content<?php endif; ?>'>

								<div class='meta-box-sortabless'>


									<!-- Rebuild Area -->
									<?php

									$status = GoogleSitemapGeneratorStatus::Load();
									$head   = __( 'Search engines haven\'t been notified yet', 'sitemap' );
									if ( null !== $status && 0 < $status->get_start_time() ) {
										$opt = get_option( 'gmt_offset' );
										$st  = $status->get_start_time() + ( $opt * 3600 );
										/* translators: %s: search term */
										$head = str_replace( '%date%', date_i18n( get_option( 'date_format' ), $st ) . ' ' . date_i18n( get_option( 'time_format' ), $st ), esc_html__( 'Result of the last ping, started on %date%.', 'sitemap' ) );
									}

									$this->html_print_box_header( 'sm_rebuild', $head );
									?>


									<div style='border-left: 1px #DFDFDF solid; float:right; padding-left:15px; margin-left:10px; width:35%;'>
										<strong><?php esc_html_e( 'Recent Support Topics / News', 'sitemap' ); ?></strong>
										<?php
										if ( $this->sg->get_option( 'i_supportfeed' ) ) {

											echo '<small><a href=\'' . esc_url( wp_nonce_url( $this->sg->get_back_link() ) . '&sm_disable_supportfeed=true' ) . '\'>' . esc_html__( 'Disable', 'sitemap' ) . '</a></small>';

											$support_feed = $this->sg->get_support_feed();

											if ( ! is_wp_error( $support_feed ) && $support_feed ) {
												$support_items = $support_feed->get_items( 0, $support_feed->get_item_quantity( 3 ) );

												if ( count( $support_items ) > 0 ) {
													echo '<ul>';
													foreach ( $support_items as $item ) {
														$url   = esc_url( $item->get_permalink() );
														$title = esc_html( $item->get_title() );
														echo '<li><a rel=\'external\' target=\'_blank\' href=' . esc_url( $url ) . '>' . esc_html( $title ) . '</a></li>';
													}
													echo '</ul>';
												}
											} else {
												echo '<ul><li>' . esc_html__( 'No support topics available or an error occurred while fetching them.', 'sitemap' ) . '</li></ul>';
											}
										} else {
											echo '<ul><li>' . esc_html__( 'Support Topics have been disabled. Enable them to see useful information regarding this plugin. No Ads or Spam!', 'sitemap' ) . '  <a href=\'' . esc_url( wp_nonce_url( $this->sg->get_back_link() ) . '&sm_disable_supportfeed=false' ) . '\'>' . esc_html__( 'Enable', 'sitemap' ) . '</a></li></ul>';
										}
										?>
									</div>


									<div style='min-height:150px;'>
										<ul>
											<?php

											if ( $this->sg->old_file_exists() ) {
												/* translators: %s: search term */
												echo '<li class=\'sm_error\'>' . esc_html( str_replace( '%s', ( $this->sg->get_back_link() ) . '&sm_delete_old=true', 'sitemap' ) ), esc_html__( 'There is still a sitemap.xml or sitemap.xml.gz file in your site directory. Please delete them as no static files are used anymore or <a href=\'%s\'>try to delete them automatically</a>.', 'sitemap' ) . '</li>';
											}
											$arr = array(
												'br'     => array(),
												'p'      => array(),
												'a'      => array(
													'href' => array(),
												),
												'strong' => array(),
											);
											/* translators: %s: search term */
											echo '<li>' . wp_kses( str_replace( array( '%1$s', '%2$s' ), $this->sg->get_xml_url(), __( 'The URL to your sitemap index file is: <a href=\'%1$s\'>%2$s</a>.', 'sitemap' ) ), $arr ) . '</li>';
											if ( null === $status || null === $this->sg->get_option( 'i_tid' ) || '' === $this->sg->get_option( 'i_tid' ) ) {
												echo '<li>' . esc_html__( 'Search engines haven\'t been notified yet. Write a post to let them know about your sitemap.', 'sitemap' ) . '</li>';
											} else {

												$services = $status->get_used_ping_services();

												foreach ( $services as $service ) {
													$name = $status->get_service_name( $service );

													if ( $status->get_ping_result( $service ) ) {
														$arr = array(
															'b' => array(),
															'a' => array(
																'href' => array(),
															),
														);
														/* translators: %s: search term */

														echo '<li>' . wp_kses( sprintf( __( '%s was <b>successfully notified</b> about changes.', 'sitemap' ), $name ), $arr ) . '</li>';
														$dur = $status->get_ping_duration( $service );
														if ( $dur > 4 ) {
															echo '<li class=\sm_optimize\'>' . wp_kses( str_replace( array( '%time%', '%name%' ), array( $dur, $name ), __( 'It took %time% seconds to notify %name%, maybe you want to disable this feature to reduce the building time.', 'sitemap' ) ), $arr ) . '</li>';
														}
													} else {
														/* translators: %s: search term */
														echo '<li class=\'sm_error\'>' . wp_kses( str_replace( array( '%s', '%name%' ), array( wp_nonce_url( $this->sg->get_back_link() . '&sm_ping_service=' . $service . '&noheader=true', 'sitemap' ), $name ), __( 'There was a problem while notifying %name%. <a href=\'%s\' target=\'_blank\'>View result</a>', 'sitemap' ) ), $arr ) . '</li>';
													}
												}
											}

											?>
											<?php if ( $this->sg->get_option( 'b_ping' ) ) : ?>
												<li>
													Notify Search Engines about <a href='<?php echo esc_url( wp_nonce_url( $this->sg->get_back_link() . '&sm_ping_main=true', 'sitemap' ) ); ?>'>your sitemap </a> or <a href='<?php echo esc_url( wp_nonce_url( $this->sg->get_back_link() . '&sm_ping_main=true', 'sitemap' ) ); ?>'>your main sitemap and all sub-sitemaps</a> now.
												</li>
											<?php endif; ?>
											<?php
											if ( is_super_admin() ) {
												$arr = array(
													'br' => array(),
													'p'  => array(),
													'a'  => array(
														'href' => array(),
													),
													'strong' => array(),
												);
												/* translators: %s: search term */
												echo '<li>' . wp_kses( str_replace( '%d', wp_nonce_url( $this->sg->get_back_link() . '&sm_rebuild=true&sm_do_debug=true', 'sitemap' ), __( 'If you encounter any problems with your sitemap you can use the <a href="%d">debug function</a> to get more information.', 'sitemap' ) ), $arr ) . '</li>';
											}
											?>
										</ul>
										<ul>
											<li>
												<?php
												$arr = array(
													'br' => array(),
													'p'  => array(),
													'a'  => array(
														'href' => array(),
													),
													'strong' => array(),
												);
												/* translators: %s: search term */
												echo wp_kses( sprintf( __( 'If you like the plugin, please <a target="_blank" href="%s">rate it 5 stars</a>! :)', 'sitemap' ), $this->sg->get_redirect_link( 'redir/sitemap-works-note' ), $this->sg->get_redirect_link( 'redirsitemap-paypal' ) ), $arr );
												?>
											</li>
										</ul>
									</div>
									<?php $this->html_print_box_footer(); ?>

									<?php if ( $this->sg->is_nginx() && $this->sg->is_using_permalinks() ) : ?>
										<?php $this->html_print_box_header( 'ngin_x', __( 'Webserver Configuration', 'sitemap' ) ); ?>
										<?php esc_html_e( 'Since you are using Nginx as your web-server, please configure the following rewrite rules in case you get 404 Not Found errors for your sitemap:', 'sitemap' ); ?>
										<p>
											<code style='display:block; overflow-x:auto; white-space: nowrap;'>
												<?php
												$rules = GoogleSitemapGeneratorLoader::get_ngin_x_rules();
												foreach ( $rules as $rule ) {
													echo esc_html( $rule . '<br />' );
												}
												?>
											</code>
										</p>
										<?php $this->html_print_box_footer(); ?>
									<?php endif; ?>


									<!-- Basic Options -->
									<?php $this->html_print_box_header( 'sm_basic_options', __( 'Basic Options', 'sitemap' ) ); ?>

									<b><?php esc_html_e( 'Update notification:', 'sitemap' ); ?></b> <a href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-help-options-ping' ) ); ?>'><?php esc_html_e( 'Learn more', 'sitemap' ); ?></a>
									<ul>
										<li>
											<input type='checkbox' id='sm_b_ping' name='sm_b_ping' <?php echo ( $this->sg->get_option( 'b_ping' ) === true ? 'checked=\'checked\'' : '' ); ?> />
											<label for='sm_b_ping'><?php esc_html_e( 'Notify Google about updates of your site', 'sitemap' ); ?></label><br />
											<small>
											<?php
											$arr = array(
												'br'     => array(),
												'p'      => array(),
												'a'      => array(
													'href' => array(),
												),
												'strong' => array(),
											);
											/* translators: %s: search term */
											echo wp_kses( str_replace( '%s', $this->sg->get_redirect_link( 'redir/sitemap-gwt' ), __( 'No registration required, but you can join the <a href=\'%s\'>Google Webmaster Tools</a> to check crawling statistics.', 'sitemap' ) ), $arr );
											?>
											</small>
										</li>
										<li>
											<label for='sm_b_robots'>
												<input type='checkbox' id='sm_b_robots' name='sm_b_robots' <?php echo ( $this->sg->get_option( 'b_robots' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Add sitemap URL to the virtual robots.txt file.', 'sitemap' ); ?>
											</label>

											<br />
											<small><?php esc_html_e( 'The virtual robots.txt generated by WordPress is used. A real robots.txt file must NOT exist in the site directory!', 'sitemap' ); ?></small>
										</li>
									</ul>

									<?php if ( is_super_admin() ) : ?>

										<b><?php esc_html_e( 'Advanced options:', 'sitemap' ); ?></b> <a href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-help-options-adv' ) ); ?>'><?php esc_html_e( 'Learn more', 'sitemap' ); ?></a>
										<ul>
											<li>
											<label for="sm_b_memory"><?php esc_html_e( 'Try to increase the memory limit to:', 'sitemap' ); ?> <input type="text" name="sm_b_memory" id="sm_b_memory" style="width:40px;" value="<?php echo esc_attr( $this->sg->get_option( 'b_memory' ) ); ?>" /></label> ( <?php echo esc_html( htmlspecialchars( esc_html__( 'e.g. \'4M\', \'16M\'', 'sitemap' ) ) ); ?>)
											</li>
											<li>
												<label for='sm_b_time'><?php esc_html_e( 'Try to increase the execution time limit to:', 'sitemap' ); ?> <input type='text' name='sm_b_time' id='sm_b_time' style='width:40px;' value='<?php echo esc_attr( ( $this->sg->get_option( 'b_time' ) === -1 ? '' : $this->sg->get_option( 'b_time' ) ) ); ?>' /></label> (<?php echo esc_html( htmlspecialchars( esc_html__( 'in seconds, e.g. \'60\' or \'0\' for unlimited', 'sitemap' ) ) ); ?>)
											</li>
											<li>
												<label for='sm_b_autozip'>
													<input type='checkbox' id='sm_b_autozip' name='sm_b_autozip' <?php echo ( $this->sg->get_option( 'b_autozip' ) === true ? 'checked=\'checked\'' : '' ); ?> />
													<?php esc_html_e( 'Try to automatically compress the sitemap if the requesting client supports it.', 'sitemap' ); ?>
												</label><br />
												<small><?php esc_html_e( 'Disable this option if you get garbled content or encoding errors in your sitemap.', 'sitemap' ); ?></small>
											</li>
											<li>
												<?php $use_def_style = ( $this->sg->get_default_style() && $this->sg->get_option( 'b_style_default' ) === true ); ?>
												<label for='sm_b_style'><?php esc_html_e( 'Include a XSLT stylesheet:', 'sitemap' ); ?> <input <?php echo ( $use_def_style ? 'disabled=\'disabled\' ' : '' ); ?> type='text' name='sm_b_style' id='sm_b_style' value='<?php echo esc_attr( $this->sg->get_option( 'b_style' ) ); ?>' /></label>
												(<?php esc_html_e( 'Full or relative URL to your .xsl file', 'sitemap' ); ?>)
												<?php
												if ( $this->sg->get_default_style() ) :
													?>
													<label for='sm_b_style_default'><input <?php echo ( $use_def_style ? 'checked=\'checked\' ' : '' ); ?> type='checkbox' id='sm_b_style_default' name='sm_b_style_default' onclick='document.getElementById("sm_b_style").disabled = this.checked;' /> <?php esc_html_e( 'Use default', 'sitemap' ); ?></label> <?php endif; ?>
											</li>
											<li>
												<label for='sm_b_baseurl'><?php esc_html_e( 'Override the base URL of the sitemap:', 'sitemap' ); ?> <input type='text' name='sm_b_baseurl' id='sm_b_baseurl' value='<?php echo esc_attr( $this->sg->get_option( 'b_baseurl' ) ); ?>' /></label><br />
												<small><?php esc_html_e( 'Use this if your site is in a sub-directory, but you want the sitemap be located in the root. Requires .htaccess modification.', 'sitemap' ); ?> <a href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-help-options-adv-baseurl' ) ); ?>'><?php esc_html_e( 'Learn more', 'sitemap' ); ?></a></small>
											</li>
											<li>
												<label for='sm_b_sitemap_name'><?php esc_html_e( 'Override the file name of the sitemap:', 'sitemap' ); ?> <input type='text' name='sm_b_sitemap_name' id='sm_b_sitemap_name' value='<?php echo esc_attr( $this->sg->get_option( 'b_sitemap_name' ) ); ?>' /></label><br />
												<small><?php esc_html_e( 'Use this if you want to change the sitemap file name', 'sitemap' ); ?> <a href='<?php echo esc_url( $this->sg->get_redirect_link( 'sitemap-help-options-adv-baseurl' ) ); ?>'><?php esc_html_e( 'Learn more', 'sitemap' ); ?></a></small>
											</li>
											<li>
												<label for='sm_i_tid'><?php esc_html_e( ' Add Google Analytics TID:', 'sitemap' ); ?> <input type='text' name='sm_i_tid' id='sm_i_tid' value='<?php echo esc_attr( $this->sg->get_option( 'i_tid' ) ); ?>' /></label><br />
											</li>
											<li>
												<label for='sm_b_html'>
													<input type='checkbox' id='sm_b_html' name='sm_b_html' 
													<?php
													if ( ! $this->sg->is_xsl_enabled() ) {
														echo 'disabled=\'disabled\'';
													}
													?>
														<?php echo ( $this->sg->get_option( 'b_html' ) === true && $this->sg->is_xsl_enabled() ? 'checked=\'checked\'' : '' ); ?> />
													<?php esc_html_e( 'Include sitemap in HTML format', 'sitemap' ); ?> 
													<?php
													if ( ! $this->sg->is_xsl_enabled() ) {
														esc_html_e( '(The required PHP XSL Module is not installed)', 'sitemap' );
													}
													?>
												</label>
											</li>
											<li>
												<label for='sm_b_stats'>
													<input type='checkbox' id='sm_b_stats' name='sm_b_stats' <?php echo ( $this->sg->get_option( 'b_stats' ) === true ? 'checked=\'checked\'' : '' ); ?> />
													<?php esc_html_e( 'Allow anonymous statistics (no personal information)', 'sitemap' ); ?>
												</label> <label><a href='<?php echo esc_url( $this->sg->get_redirect_link( 'redir/sitemap-help-options-adv-stats' ) ); ?>'><?php esc_html_e( 'Learn more', 'sitemap' ); ?></a></label>
											</li>
										</ul>
									<?php endif; ?>

									<?php $this->html_print_box_footer(); ?>

									<?php if ( is_super_admin() ) : ?>
										<?php $this->html_print_box_header( 'sm_pages', __( 'Additional Pages', 'sitemap' ) ); ?>

										<?php
										$arr = array(
											'br'     => array(),
											'p'      => array(),
											'a'      => array(),
											'strong' => array(),
										);
										echo wp_kses( 'Here you can specify files or URLs which should be included in the sitemap, but do not belong to your Site/WordPress.<br />For example, if your domain is www.foo.com and your site is located on www.foo.com/site you might want to include your homepage at www.foo.com', $arr );
										echo '<ul><li>';
										echo '<strong>' . esc_html__( 'Note', 'sitemap' ) . '</strong>: ';
										esc_html_e( 'If your site is in a subdirectory and you want to add pages which are NOT in the site directory or beneath, you MUST place your sitemap file in the root directory (Look at the &quot;Location of your sitemap file&quot; section on this page)!', 'sitemap' );
										echo '</li><li>';
										echo '<strong>' . esc_html__( 'URL to the page', 'sitemap' ) . '</strong>: ';
										esc_html_e( 'Enter the URL to the page. Examples: http://www.foo.com/index.html or www.foo.com/home ', 'sitemap' );
										echo '</li><li>';
										echo '<strong>' . esc_html__( 'Priority', 'sitemap' ) . '</strong>: ';
										esc_html_e( 'Choose the priority of the page relative to the other pages. For example, your homepage might have a higher priority than your imprint.', 'sitemap' );
										echo '</li><li>';
										echo '<strong>' . esc_html__( 'Last Changed', 'sitemap' ) . '</strong>: ';
										esc_html_e( 'Enter the date of the last change as YYYY-MM-DD (2005-12-31 for example) (optional).', 'sitemap' );

										echo '</li></ul>';
										?>
										<script type='text/javascript'>
											//<![CDATA[
											<?php

											$freq_vals  = implode( ',', array_keys( $this->sg->get_freq_names() ) );
											$freq_names = implode( ',', array_values( $this->sg->get_freq_names() ) );
											?>
											var changeFreqVals  = '<?php echo esc_html( $freq_vals ); ?>'; 
											changeFreqVals = changeFreqVals.split(",")
											var changeFreqNames ='<?php echo esc_html( $freq_names ); ?>'
											changeFreqNames = changeFreqNames.split(",")
											var priorities = [0 
											<?php
											for ( $i = 0.1; $i < 1; $i += 0.1 ) {
																	echo ',' . number_format( $i, 1, '.', '' );
											}
											?>
											];

											var pages = [
												<?php
															$pages = (array) $this->sg->get_pages();
															$fd    = false;
												foreach ( $pages as $page ) {
													if ( $page instanceof GoogleSitemapGeneratorPage ) {
														if ( $fd ) {
															echo ',';
														} else {
															$fd = true;
														}
														$last_mod_date = ! empty( $page->_lastMod ) ? $page->_lastMod : $page->last_mod;
														echo '{url:"' . esc_url( ! empty( $page->_url ) ? $page->_url : $page->url ) . '", priority:' . esc_html( number_format( ! empty( $page->_priority ) ? $page->_priority : $page->priority, 1, '.', '' ) ) . ', changeFreq:\'' . esc_html( ! empty( $page->_changeFreq ) ? $page->_changeFreq : $page->change_freq ) . '\', lastChanged:"' . esc_html( ( $last_mod_date > 0 ? gmdate( 'Y-m-d', $last_mod_date ) : '' ) ) . '"}';
													}
												}
												?>
												];
											//]]>
										</script>
										<?php
										wp_enqueue_script( 'sitemap-script', ( $this->sg->get_plugin_url() . 'img/sitemap.js' ), '', '1.0.0', false );
										?>
										<table width='100%' cellpadding='3' cellspacing='3' id='sm_pageTable'>
											<tr>
												<th scope='col'><?php esc_html_e( 'URL to the page', 'sitemap' ); ?></th>
												<th scope='col'><?php esc_html_e( 'Priority', 'sitemap' ); ?></th>
												<th scope='col'><?php esc_html_e( 'Change Frequency', 'sitemap' ); ?></th>
												<th scope='col'><?php esc_html_e( 'Last Changed', 'sitemap' ); ?></th>
												<th scope='col'><?php esc_html_e( '#', 'sitemap' ); ?></th>
											</tr>
											<?php
											if ( count( $pages ) <= 0 ) {
												?>
												<tr>
													<td colspan='5' align='center'><?php esc_html_e( 'No pages defined.', 'sitemap' ); ?></td>
												</tr>
												<?php
											}
											?>
										</table>
										<a href='javascript:void(0);' onclick='sm_addPage();'><?php esc_html_e( 'Add new page', 'sitemap' ); ?></a>
										<?php $this->html_print_box_footer(); ?>
									<?php endif; ?>


									<!-- AutoPrio Options -->
									<?php $this->html_print_box_header( 'sm_postprio', __( 'Post Priority', 'sitemap' ) ); ?>

									<p><?php esc_html_e( 'Please select how the priority of each post should be calculated:', 'sitemap' ); ?></p>
									<ul>
										<?php
										$provs = $this->sg->get_prio_providers();
										array_unshift( $provs, '' );
										$len = count( $provs );
										for ( $i = 0; $i < $len; $i++ ) {
											if ( 0 === $i ) {
												echo '<li><p><input type=\'radio\' id=\'sm_b_prio_provider_' . esc_html( $i ) . '\' name=\'sm_b_prio_provider\' value=\'' . esc_attr( $provs[ $i ] ) . '\' ' . esc_attr( $this->html_get_checked( $this->sg->get_option( 'b_prio_provider' ), $provs[ $i ] ) ) . ' /> <label for=\'sm_b_prio_provider_' . esc_html( $i ) . '\'>' . esc_html( 'Do not use automatic priority calculation' ) . '</label><br />' . esc_html( 'All posts will have the same priority which is defined in &quot;Priorities&quot;' ) . '</p></li>';
											} else {
												echo '<li><p><input type=\'radio\' id=\'sm_b_prio_provider_' . esc_html( $i ) . '\' name=\'sm_b_prio_provider\' value=\'' . esc_attr( $provs[ $i ] ) . '\' ' . esc_attr( $this->html_get_checked( $this->sg->get_option( 'b_prio_provider' ), $provs[ $i ] ) ) . ' /> <label for=\'sm_b_prio_provider_' . esc_html( $i ) . '\'>' . esc_html( call_user_func( array( $provs[ $i ], 'get_name' ) ) ) . '</label><br />' . esc_html( call_user_func( array( $provs[ $i ], 'get_description' ) ) ) . '</p></li>';
											}
										}
										?>
									</ul>
									<?php $this->html_print_box_footer(); ?>

									<!-- Includes -->
									<?php $this->html_print_box_header( 'sm_includes', __( 'Sitemap Content', 'sitemap' ) ); ?>
									<b><?php esc_html_e( 'WordPress standard content', 'sitemap' ); ?>:</b>
									<ul>
										<li>
											<label for='sm_in_home'>
												<input type='checkbox' id='sm_in_home' name='sm_in_home' <?php echo ( $this->sg->get_option( 'in_home' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include homepage', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_in_posts'>
												<input type='checkbox' id='sm_in_posts' name='sm_in_posts' <?php echo ( $this->sg->get_option( 'in_posts' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include posts', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_in_product_cat'>
												<input type='checkbox' id='sm_in_product_cat' name='sm_in_product_cat' <?php echo ( $this->sg->get_option( 'in_product_cat' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include product categories', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_product_tags'>
												<input type='checkbox' id='sm_product_tags' name='sm_product_tags' <?php echo ( $this->sg->get_option( 'product_tags' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include product tags', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_in_pages'>
												<input type='checkbox' id='sm_in_pages' name='sm_in_pages' <?php echo ( $this->sg->get_option( 'in_pages' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include static pages', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_in_cats'>
												<input type='checkbox' id='sm_in_cats' name='sm_in_cats' <?php echo ( $this->sg->get_option( 'in_cats' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include categories', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_in_arch'>
												<input type='checkbox' id='sm_in_arch' name='sm_in_arch' <?php echo ( $this->sg->get_option( 'in_arch' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include archives', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_in_auth'>
												<input type='checkbox' id='sm_in_auth' name='sm_in_auth' <?php echo ( $this->sg->get_option( 'in_auth' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include author pages', 'sitemap' ); ?>
											</label>
										</li>
										<?php if ( $this->sg->is_taxonomy_supported() ) : ?>
											<li>
												<label for='sm_in_tags'>
													<input type='checkbox' id='sm_in_tags' name='sm_in_tags' <?php echo ( $this->sg->get_option( 'in_tags' ) === true ? 'checked=\'checked\'' : '' ); ?> />
													<?php esc_html_e( 'Include tag pages', 'sitemap' ); ?>
												</label>
											</li>
										<?php endif; ?>
									</ul>

									<?php

									if ( $this->sg->is_taxonomy_supported() ) {
										$taxonomies = $this->sg->get_custom_taxonomies();

										$enabled_taxonomies = $this->sg->get_option( 'in_tax' );

										if ( count( $taxonomies ) > 0 ) {
											?>
											<b>
											<?php
											esc_html_e( 'Custom taxonomies', 'sitemap' );
											?>
											:</b>
											<ul>
											<?php

											foreach ( $taxonomies as $tax_name ) {

												$taxonomy = get_taxonomy( $tax_name );
												$selected = in_array( $taxonomy->name, $enabled_taxonomies, true );
												?>
												<li>
													<label for='sm_in_tax[<?php echo esc_attr( $taxonomy->name ); ?>]'>
														<input type='checkbox' id='sm_in_tax[<?php echo esc_attr( $taxonomy->name ); ?>]' name='sm_in_tax[<?php echo esc_attr( $taxonomy->name ); ?>]' <?php echo $selected ? 'checked=\'checked\'' : ''; ?> />
												<?php /* translators: %s: search term */ ?>
														<?php echo esc_html( str_replace( '%s', $taxonomy->label, __( 'Include taxonomy pages for %s', 'sitemap' ) ) ); ?>
													</label>
												</li>
												<?php
											}

											?>
											</ul>
											<?php

										}
									}

									if ( $this->sg->is_custom_post_types_supported() ) {
										$custom_post_types  = $this->sg->get_custom_post_types();
										$enabled_post_types = $this->sg->get_option( 'in_customtypes' );

										if ( count( $custom_post_types ) > 0 ) {
											?>
											<b>
											<?php esc_html_e( 'Custom post types', 'sitemap' ); ?>:</b>
									<ul>
											<?php

											foreach ( $custom_post_types as $post_type ) {
												$post_type_object = get_post_type_object( $post_type );
												if ( is_array( $enabled_post_types ) ) {
													$selected = in_array( $post_type_object->name, $enabled_post_types, true );
												}

												?>
											<li>
												<label for='sm_in_customtypes[<?php echo esc_html( $post_type_object->name ); ?>]'>
													<input type='checkbox' id='sm_in_customtypes[<?php echo esc_html( $post_type_object->name ); ?>]' name='sm_in_customtypes[<?php echo esc_html( $post_type_object->name ); ?>]' <?php echo $selected ? 'checked=\'checked\'' : ''; ?> />
													<?php /* translators: %s: search term */ ?>
													<?php echo esc_html( str_replace( '%s', $post_type_object->label, __( 'Include custom post type %s', 'sitemap' ) ) ); ?>
												</label>
											</li>
												<?php
											}

											?>
									</ul>
											<?php
										}
									}

									?>

									<b><?php esc_html_e( 'Further options', 'sitemap' ); ?>:</b>
									<ul>
										<li>
											<label for='sm_in_lastmod'>
												<input type='checkbox' id='sm_in_lastmod' name='sm_in_lastmod' <?php echo ( $this->sg->get_option( 'in_lastmod' ) === true ? 'checked=\'checked\'' : '' ); ?> />
												<?php esc_html_e( 'Include the last modification time.', 'sitemap' ); ?>
											</label><br />
											<small>
											<?php
											$arr = array(
												'i' => array(),
											);
											echo wp_kses( __( 'This is highly recommended and helps the search engines to know when your content has changed. This option affects <i>all</i> sitemap entries.', 'sitemap' ), $arr );
											?>
											</small>
										</li>
									</ul>
									<ul>
										<li>
											<label for='sm_links_page'>
												<b><?php esc_html_e( 'Links per page', 'sitemap' ); ?>:</b>
												<input type='number' name='sm_links_page' id='sm_links_page' style='width:50px; margin-left:10px;' value='<?php echo esc_attr( $this->sg->get_option( 'links_page' ) ); ?>' />
											</label>
										</li>
									</ul>

									<?php $this->html_print_box_footer(); ?>

									<!-- Excluded Items -->
									<?php $this->html_print_box_header( 'sm_excludes', __( 'Excluded Items', 'sitemap' ) ); ?>

									<b><?php esc_html_e( 'Excluded categories', 'sitemap' ); ?>:</b>

									<div style='border-color:#CEE1EF; border-style:solid; border-width:2px; height:10em; margin:5px 0px 5px 40px; overflow:auto; padding:0.5em 0.5em;'>
										<ul>
											<?php wp_category_checklist( 0, 0, $this->sg->get_option( 'b_exclude_cats' ), false ); ?>
										</ul>
										<ul>
											<?php
											$defaults = array();
											if ( $this->has_woo_commerce ) {
												$defaults = array(
													'descendants_and_self' => 0,
													'selected_cats' => $this->sg->get_option( 'b_exclude_cats' ),
													'popular_cats' => false,
													'walker' => null,
													'taxonomy' => 'product_cat',
													'checked_ontop' => true,
													'echo' => true,
												);
											} else {
												$defaults = array(
													'selected_cats' => $this->sg->get_option( 'b_exclude_cats' ),
													'echo' => true,
												);
											}

											wp_terms_checklist( 0, $defaults );
											?>
										</ul>
										<?php
										$taxonomies = $this->sg->get_custom_taxonomies();
										foreach ( $taxonomies as $key => $taxonomy ) {
											?>
											<ul>
												<?php
												$defaults = array();
												if ( $this->has_woo_commerce ) {
													$defaults = array(
														'descendants_and_self' => 0,
														'selected_cats' => $this->sg->get_option( 'b_exclude_cats' ),
														'popular_cats' => false,
														'walker' => null,
														'taxonomy' => $taxonomy,
														'checked_ontop' => true,
														'echo' => true,
													);
												} else {
													$defaults = array(
														'selected_cats' => $this->sg->get_option( 'b_exclude_cats' ),
														'echo'     => true,
													);
												}
												wp_terms_checklist( 0, $defaults );
												?>
											</ul>
											<?php

										}
										?>
									</div>
									<b><?php esc_html_e( 'Exclude posts', 'sitemap' ); ?>:</b>
									<div style='margin:5px 0 13px 40px;'>
										<label for='sm_b_exclude'><?php esc_html_e( 'Exclude the following posts or pages:', 'sitemap' ); ?> <small><?php esc_html_e( 'List of IDs, separated by comma', 'sitemap' ); ?></small><br />
										<input name="sm_b_exclude" id="sm_b_exclude" type="text" style="width:400px;" value="<?php echo esc_attr( implode( ',', $this->sg->get_option( 'b_exclude' ) ) ); ?>" /></label><br />
										<cite><?php esc_html_e( 'Note', 'sitemap' ); ?>: <?php esc_html_e( 'Child posts won\'t be excluded automatically!', 'sitemap' ); ?></cite>
									</div>

									<?php $this->html_print_box_footer(); ?>

									<!-- Change frequencies -->
									<?php $this->html_print_box_header( 'sm_change_frequencies', __( 'Change Frequencies', 'sitemap' ) ); ?>

									<p>
										<b><?php esc_html_e( 'Note', 'sitemap' ); ?>:</b>
										<?php esc_html_e( 'Please note that the value of this tag is considered a hint and not a command. Even though search engine crawlers consider this information when making decisions, they may crawl pages marked \'hourly\' less frequently than that, and they may crawl pages marked \'yearly\' more frequently than that. It is also likely that crawlers will periodically crawl pages marked \'never\' so that they can handle unexpected changes to those pages.', 'sitemap' ); ?>
									</p>
									<ul>
										<li>
											<label for='sm_cf_home'>
												<select id='sm_cf_home' name='sm_cf_home'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_home' ) ); ?></select>
												<?php esc_html_e( 'Homepage', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_cf_posts'>
												<select id='sm_cf_posts' name='sm_cf_posts'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_posts' ) ); ?></select>
												<?php esc_html_e( 'Posts', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_cf_pages'>
												<select id='sm_cf_pages' name='sm_cf_pages'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_pages' ) ); ?></select>
												<?php esc_html_e( 'Static pages', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_cf_cats'>
												<select id='sm_cf_cats' name='sm_cf_cats'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_cats' ) ); ?></select>
												<?php esc_html_e( 'Categories', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_cf_product_cat'>
												<select id='sm_cf_product_cat' name='sm_cf_product_cat'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_product_cat' ) ); ?></select>
												<?php esc_html_e( 'Product Categories', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_cf_arch_curr'>
												<select id='sm_cf_arch_curr' name='sm_cf_arch_curr'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_arch_curr' ) ); ?></select>
												<?php esc_html_e( 'The current archive of this month (Should be the same like your homepage)', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_cf_arch_old'>
												<select id='sm_cf_arch_old' name='sm_cf_arch_old'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_arch_old' ) ); ?></select>
												<?php esc_html_e( 'Older archives (Changes only if you edit an old post)', 'sitemap' ); ?>
											</label>
										</li>
										<?php if ( $this->sg->is_taxonomy_supported() ) : ?>
											<li>
												<label for='sm_cf_tags'>
													<select id='sm_cf_tags' name='sm_cf_tags'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_tags' ) ); ?></select>
													<?php esc_html_e( 'Tag pages', 'sitemap' ); ?>
												</label>
											</li>
										<?php endif; ?>
										<li>
											<label for='sm_cf_auth'>
												<select id='sm_cf_auth' name='sm_cf_auth'><?php $this->html_get_freq_names( $this->sg->get_option( 'cf_auth' ) ); ?></select>
												<?php esc_html_e( 'Author pages', 'sitemap' ); ?>
											</label>
										</li>
									</ul>

									<?php $this->html_print_box_footer(); ?>

									<!-- Priorities -->
									<?php $this->html_print_box_header( 'sm_priorities', __( 'Priorities', 'sitemap' ) ); ?>
									<ul>
										<li>
											<label for='sm_pr_home'>
												<select id='sm_pr_home' name='sm_pr_home'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_home' ) ); ?></select>
												<?php esc_html_e( 'Homepage', 'sitemap' ); ?>
											</label>
										</li>
										<li>

											<label for='sm_pr_posts'>
												<select id='sm_pr_posts' name='sm_pr_posts'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_posts' ) ); ?></select>
												<?php esc_html_e( 'Posts (If auto calculation is disabled)', 'sitemap' ); ?>
											</label>
										</li>
										<li>

											<label for='sm_pr_posts_min'>
												<select id='sm_pr_posts_min' name='sm_pr_posts_min'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_posts_min' ) ); ?></select>
												<?php esc_html_e( 'Minimum post priority (Even if auto calculation is enabled)', 'sitemap' ); ?>
											</label>
										</li>
										<li>

											<label for='sm_pr_pages'>
												<select id='sm_pr_pages' name='sm_pr_pages'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_pages' ) ); ?></select>
												<?php esc_html_e( 'Static pages', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_pr_cats'>
												<select id='sm_pr_cats' name='sm_pr_cats'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_cats' ) ); ?></select>
												<?php esc_html_e( 'Categories', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_pr_product_cat'>
												<select id='sm_pr_product_cat' name='sm_pr_product_cat'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_product_cat' ) ); ?></select>
												<?php esc_html_e( 'Product Categories', 'sitemap' ); ?>
											</label>
										</li>
										<li>
											<label for='sm_pr_arch'>
												<select id='sm_pr_arch' name='sm_pr_arch'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_arch' ) ); ?></select>
												<?php esc_html_e( 'Archives', 'sitemap' ); ?>
											</label>
										</li>
										<?php if ( $this->sg->is_taxonomy_supported() ) : ?>
											<li>
												<label for='sm_pr_tags'>
													<select id='sm_pr_tags' name='sm_pr_tags'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_tags' ) ); ?></select>
													<?php esc_html_e( 'Tag pages', 'sitemap' ); ?>
												</label>
											</li>
										<?php endif; ?>
										<li>

											<label for='sm_pr_auth'>
												<select id='sm_pr_auth' name='sm_pr_auth'><?php $this->html_get_priority_values( $this->sg->get_option( 'pr_auth' ) ); ?></select>
												<?php esc_html_e( 'Author pages', 'sitemap' ); ?>
											</label>
										</li>
									</ul>

									<?php $this->html_print_box_footer(); ?>

								</div>
								<div>
									<p class='submit'>
										<?php wp_nonce_field( 'sitemap' ); ?>
										<input type='submit' class='button-primary' name='sm_update' value='<?php esc_html_e( 'Update options', 'sitemap' ); ?>' />
										<input type='submit' onclick='return confirm('Do you really want to reset your configuration?');' class='sm_warning' name='sm_reset_config' value='<?php esc_html_e( 'Reset options', 'sitemap' ); ?>' />
									</p>
								</div>


							</div>
						</div>
						</div>
						<!-- <script type='text/javascript'>
						console.log('type of funiton', typeof sm_loadPages)
							if (typeof(sm_loadPages) == 'function') addLoadEvent(sm_loadPages);
						</script> -->
			</form>
			<form action='https://www.paypal.com/cgi-bin/webscr' method='post' id='sm_donate_form'>
				<?php
				$lc    = array(
					'en'    => array(
						'cc' => 'USD',
						'lc' => 'US',
					),
					'en-GB' => array(
						'cc' => 'GBP',
						'lc' => 'GB',
					),
					'de'    => array(
						'cc' => 'EUR',
						'lc' => 'DE',
					),
				);
				$my_lc = $lc['en'];
				$wpl   = get_bloginfo( 'language' );
				if ( ! empty( $wpl ) ) {
					if ( array_key_exists( $wpl, $lc ) ) {
						$my_lc = $lc[ $wpl ];
					} else {
						$wpl = substr( $wpl, 0, 2 );
						if ( array_key_exists( $wpl, $lc ) ) {
							$my_lc = $lc[ $wpl ];
						}
					}
				}
				?>
				<input type='hidden' name='cmd' value='_donations' />
				<input type='hidden' name='business' value='<?php echo 'xmlsitemapgen' /* N O S P A M */ . '@gmail.com'; ?>' />
				<input type='hidden' name='item_name' value='Sitemap Generator for WordPress. Please tell me if if you don't want to be listed on the donator list.' />
				<input type='hidden' name='no_shipping' value='1' />
				<input type='hidden' name='return' value='<?php echo esc_attr( $this->sg->get_back_link( '&sm_donated=true' ) ); ?>' />
				<input type='hidden' name='currency_code' value='<?php echo esc_attr( $my_lc['cc'] ); ?>' />
				<input type='hidden' name='bn' value='PP-BuyNowBF' />
				<input type='hidden' name='lc' value='<?php echo esc_attr( $my_lc['lc'] ); ?>' />
				<input type='hidden' name='rm' value='2' />
				<input type='hidden' name='on0' value='Your Website' />
				<input type='hidden' name='os0' value='<?php echo esc_attr( get_bloginfo( 'url' ) ); ?>' />
			</form>
		</div>
		<?php
	}
}
