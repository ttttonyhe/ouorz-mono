<?php
/**
 * Responsible for modal dialogs generation.
 *
 * @package    wp2fa
 * @subpackage utils
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Utils;

use \WP2FA\WP2FA as WP2FA;

/**
 * Utility class for creating modal popup markup.
 *
 * @package WP2FA\Utils
 * @since 1.4.2
 */
class Generate_Modal {

	/**
	 * General modals based on given args.
	 *
	 * @param  string $modal_id              Unique ID for the modal.
	 * @param  string $modal_title           (Optional) Modal title.
	 * @param  string $modal_content         The HTML content we want to show in the modal.
	 * @param  array  $modal_footer_buttons  The HTML content we want to show at the footer of the modal, usually buttons.
	 * @param  string $should_modal_autoopen (Optional) if anything is passed we will open the modal automatically.
	 * @param  string $max_width             (Optional) Max possible width of modal.
	 */
	public static function generate_modal( $modal_id, $modal_title, $modal_content, $modal_footer_buttons = array(), $should_modal_autoopen = '', $max_width = '' ) {

		$buttons = '';
		$modal   = '';
		$title   = ( ! empty( $modal_title ) ) ? '<header class="modal__header"><h4 class="modal__title" id="modal-' . esc_attr( $modal_id ) . '-title">' . $modal_title . '</h4></header>' : false;

		if ( ! empty( $modal_footer_buttons ) ) {
			foreach ( $modal_footer_buttons as $button_markup ) {
				$buttons .= $button_markup;
			}
		}

		$styling_class = ( empty( WP2FA::get_wp2fa_white_label_setting( 'enable_wizard_styling' ) ) ) ? 'default_styling' : 'enable_styling';

		if ( ! empty( $should_modal_autoopen ) ) {
			$modal_class = 'wp2fa-modal micromodal-slide is-open ' . $styling_class;
			$hidden      = 'false';
		} else {
			$modal_class = 'wp2fa-modal micromodal-slide ' . $styling_class;
			$hidden      = 'true';
		}

		$max_width_styles = ( ! empty( $max_width ) ) ? 'style="max-width:' . esc_attr( $max_width ) . '; min-width: 0;"' : false;

		$modal = '
	<div class="' . $modal_class . '" id="' . esc_attr( $modal_id ) . '" aria-hidden="' . esc_attr( $hidden ) . '">
	  <div class="modal__overlay" tabindex="-1">
		<div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-' . esc_attr( $modal_id ) . '-title" ' . $max_width_styles . '>
			' . $title . '
		  <main class="modal__content wp2fa-form-styles" id="modal-' . esc_attr( $modal_id ) . '-content">
			' . wpautop( $modal_content ) . '
		  </main>
		  <footer class="modal__footer">
			' . $buttons . '
		  </footer>
		</div>
	  </div>
	</div>
	';

		return $modal;
	}

}
