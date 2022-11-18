<?php
/**
 * Contains the main plugin class for the Markdown Editor.
 *
 * @package markdown-editor
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	 die;
}

/**
 * Main plugin class.
 */
class Markdown_Editor {

	/**
	 * Default instance.
	 *
	 * @since 0.1.0
	 * @var string $instance.
	 */
	private static $instance;

	/**
	 * Sets up the Markdown editor.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {

		// Add default post type support.
		add_post_type_support( 'post', 'wpcom-markdown' );

		// Load markdown editor.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'admin_footer', array( $this, 'init_editor' ) );

		// Remove quicktags buttons.
		add_filter( 'quicktags_settings', array( $this, 'quicktags_settings' ), 'content' );

		// Load front-end assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'highlight_scripts_styles' ) );

		// Load Jetpack Markdown module.
		$this->load_jetpack_markdown_module();

	}

	/**
	 * Get instance.
	 *
	 * @since 0.1.0
	 * @return object $instance Plugin instance.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __clone() {
		trigger_error( 'Clone is not allowed.', E_USER_ERROR );
	}

	/**
	 * Filter markdown post types.
	 *
	 * @since  0.1.0
	 * @return bool
	 */
	function get_post_type() {
		return get_current_screen()->post_type;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function enqueue_scripts_styles() {

		// Only enqueue on specified post types.
		if ( ! post_type_supports( $this->get_post_type(), 'wpcom-markdown' ) ) {
			return;
		}

		wp_enqueue_script( 'simplemde-js', PLUGIN_URL . 'assets/scripts/simplemde.min.js' );
		wp_enqueue_style( 'simplemde-css', PLUGIN_URL . 'assets/styles/simplemde.min.css' );
		wp_enqueue_style( 'custom-css', PLUGIN_URL . 'assets/styles/style.css' );

		if ( apply_filters( 'markdown_editor_highlight', true ) ) {

			wp_enqueue_style( 'highlight', PLUGIN_URL . 'assets/styles/highlight.min.css' );
			wp_enqueue_script( 'highlight', PLUGIN_URL . 'assets/scripts/highlight.pack.js' );

			$inline = '
			hljs.initHighlightingOnLoad();
			$(document).ready(function() {
				$(".fa-eye, .fa-columns").on( "click", function() {
					$("pre code").each(function(i, block) {
						hljs.highlightBlock(block);
					});
				})
			});
			';

			wp_add_inline_script( 'highlight', $inline );

		}

	}

	/**
	 * Highlight scripts and styles.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function highlight_scripts_styles() {

		// Only enqueue on single posts/pages.
		if ( ! is_single() ) {
			return;
		}

		if ( apply_filters( 'markdown_editor_highlight', true ) ) {

			wp_enqueue_style( 'frontend', PLUGIN_URL . 'assets/styles/frontend.min.css', array(), PLUGIN_VERSION );
			
			wp_enqueue_script( 'highlight', PLUGIN_URL . 'assets/scripts/highlight.pack.js', '', '9.12.0', true );
			
			wp_add_inline_script( 'highlight', 'hljs.initHighlightingOnLoad();' );

			if ( apply_filters( 'markdown_editor_linenumbers', true ) ) {

				wp_enqueue_script( 'line-numbers', PLUGIN_URL . 'assets/scripts/line-numbers.min.js', '', '2.3.0', true );

				wp_add_inline_script( 'line-numbers', 'hljs.initLineNumbersOnLoad({singleLine: true});' );

			}

			if ( apply_filters( 'markdown_editor_clipboard', true ) ) {

				wp_enqueue_script( 'clipboard', PLUGIN_URL . 'assets/scripts/clipboard.min.js', '', '2.0.0', true );

				wp_enqueue_script( 'frontend', PLUGIN_URL . 'assets/scripts/frontend.min.js', '', PLUGIN_VERSION, true );

			}

		}

	}

	/**
	 * Load Jetpack Markdown Module.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function load_jetpack_markdown_module() {

		// If the module is active, let's make this active for posting. Comments will still be optional.
		if ( class_exists( 'Easy_Markdown' ) ) {
			add_filter( 'pre_option_' . Easy_Markdown::POST_OPTION, '__return_true' );
		}
		add_action( 'admin_init', array( $this, 'jetpack_markdown_posting_always_on' ), 11 );
		add_action( 'plugins_loaded', array( $this, 'jetpack_markdown_load_textdomain' ) );
		add_filter( 'plugin_action_links_' . PLUGIN_NAME, array( $this, 'jetpack_markdown_settings_link' ) );

	}

	/**
	 * Set Jetpack posting to always on.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function jetpack_markdown_posting_always_on() {
		if ( ! class_exists( 'Easy_Markdown' ) ) {
			return;
		}
		global $wp_settings_fields;
		if ( isset( $wp_settings_fields['writing']['default'][ Easy_Markdown::POST_OPTION ] ) ) {
			unset( $wp_settings_fields['writing']['default'][ Easy_Markdown::POST_OPTION ] );
		}
	}

	/**
	 * Load JetPack text domain (already translated).
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function jetpack_markdown_load_textdomain() {
		load_plugin_textdomain( 'jetpack', false, PLUGIN_DIR . 'languages/' );
	}

	/**
	 * Add settings link.
	 *
	 * @since 0.1.0
	 * @param  string $actions Markdown settings.
	 * @return string
	 */
	function jetpack_markdown_settings_link( $actions ) {
		return array_merge(
			array(
				'settings' => sprintf( '<a href="%s">%s</a>', 'options-discussion.php#' . Easy_Markdown::COMMENT_OPTION, __( 'Settings', 'jetpack' ) ),
			),
			$actions
		);
		return $actions;
	}

	/**
	 * Initialize editor.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function init_editor() {

		// Only initialize on specified post types.
		if ( ! post_type_supports( $this->get_post_type(), 'wpcom-markdown' ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			
			// Initialize the editor.
			var simplemde = new SimpleMDE( {
				spellChecker: false,
				element: document.getElementById( 'content' )
			} );

			// Change zIndex when toggle full screen.
			var change_zIndex = function( editor ) {

				// Give it some time to finish the transition.
				setTimeout( function() {
					var cm = editor.codemirror;
					var wrap = cm.getWrapperElement();
					if( /fullscreen/.test( wrap.previousSibling.className ) ) {
						document.getElementById( 'wp-content-editor-container' ).style.zIndex = 999999;
					} else {
						document.getElementById( 'wp-content-editor-container' ).style.zIndex = 1;
					}
				}, 2 );
			}

			var toggleFullScreenButton = document.getElementsByClassName( 'fa-arrows-alt' );
			toggleFullScreenButton[0].onclick = function() {
				SimpleMDE.toggleFullScreen( simplemde );
				change_zIndex( simplemde );
			}

			var toggleSideBySideButton = document.getElementsByClassName( 'fa-columns' );
			toggleSideBySideButton[0].onclick = function() {
				SimpleMDE.toggleSideBySide( simplemde );
				change_zIndex(simplemde);
			}

			var helpButton = document.getElementsByClassName( 'fa-question-circle' );
			helpButton[0].href = 'https://guides.github.com/features/mastering-markdown/';

			if ( typeof jQuery !== 'undefined' ) {
				jQuery( document ).ready( function() {

					// Integrate with WP Media module.
					var original_wp_media_editor_insert = wp.media.editor.insert;
					wp.media.editor.insert = function( html ) {
						original_wp_media_editor_insert( html );
						simplemde.codemirror.replaceSelection( html );
					}
				} );
			}
		</script>
		<?php
	}

	/**
	 * Quick tag settings.
	 *
	 * @since 0.1.0
	 * @param  array $qt_init Quick tag args.
	 * @return array
	 */
	function quicktags_settings( $qt_init ) {

		// Only remove buttons on specified post types.
		if ( ! post_type_supports( $this->get_post_type(), 'wpcom-markdown' ) ) {
			return $qt_init;
		}

		$qt_init['buttons'] = ' ';
		return $qt_init;
	}

	/**
	 * Disable rich editing.
	 *
	 * @since  0.1.1
	 * @param  array $default Default post types.
	 * @return array
	 */
	function disable_rich_editing( $default ) {

		if ( post_type_supports( $this->get_post_type(), 'wpcom-markdown' ) ) {
			return false;
		}

		return $default;
	}
}
