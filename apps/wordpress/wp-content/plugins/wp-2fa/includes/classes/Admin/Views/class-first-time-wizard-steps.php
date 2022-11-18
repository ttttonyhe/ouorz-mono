<?php
/**
 * Settings page render class.
 *
 * @package    wp2fa
 * @subpackage views
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA\Admin\Views;

use WP2FA\WP2FA;
use WP2FA\Admin\Helpers\WP_Helper;
use WP2FA\Admin\Controllers\Settings;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * WP2FA First Wizard Settings view controller
 *
 * @since 1.7
 */
class First_Time_Wizard_Steps {

	/**
	 * Select method step
	 *
	 * @since 1.7.0
	 *
	 * @param boolean $setup_wizard - Boolean - is that first time wizard setup or settings page call.
	 *
	 * @return void
	 */
	public static function select_method( $setup_wizard = false ) {

		ob_start();
		?>
		<h3><?php esc_html_e( 'Which two-factor authentication methods can your users use?', 'wp-2fa' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'When you uncheck any of the below 2FA methods it won\'t be available for your users to use. You can always change this later on from the plugin\'s settings.', 'wp-2fa' ); ?>
		</p>
		<?php
		$data_role = 'data-role="global"';
		if ( ! $setup_wizard ) {
			?>
		<table class="form-table">
			<tbody>
				<tr>
					<th colspan="2"><?php esc_html_e( 'Which of the below 2FA methods can users use?', 'wp-2fa' ); ?></th>
				</tr>
				<tr>
					<th><label for="2fa-method"><?php esc_html_e( 'Select the methods', 'wp-2fa' ); ?></label></th>
					<td>
		<?php } ?>
						<fieldset id="2fa-method-select" class="2fa-method-select">
							<div class="method-title"><em><?php esc_html_e( 'Primary 2FA methods:', 'wp-2fa' ); ?></em></div>
							<br>
							<label for="totp">
								<input type="checkbox" id="totp" name="wp_2fa_policy[enable_totp]" value="enable_totp"
								<?php echo $data_role; // phpcs:ignore ?>
								<?php checked( 'enable_totp', WP2FA::get_wp2fa_setting( 'enable_totp' ), true ); ?>
								>
								<?php esc_html_e( 'One-time code via 2FA App (TOTP) - ', 'wp-2fa' ); ?><a href="https://wp2fa.io/support/kb/configuring-2fa-apps/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=settings+pages" target="_blank" rel=noopener><?php esc_html_e( 'complete list of supported 2FA apps.', 'wp-2fa' ); ?></a>
							</label>
							<?php
							if ( $setup_wizard ) {
								echo '<p class="description">';
								printf(
									/* translators: link to the knowledge base website */
									esc_html__( 'Refer to the %s for more information on how to setup these apps and which apps are supported.', 'wp-2fa' ),
									'<a href="https://wp2fa.io/support/kb/configuring-2fa-apps/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=settings+pages" target="_blank">' . esc_html__( 'guide on how to set up 2FA apps', 'wp-2fa' ) . '</a>'
								);
								echo '</p>';
							}
							?>
							<?php
							/**
							 * Fired right after the TOTP method HTML rendering.
							 *
							 * @param bool $wizard - Is that a wizard call or settings call.
							 * @param string $data_role - String with the JS data to add to form element.
							 * @param string $name - The name of the role.
							 *
							 * @since 2.0.0
							 */
							\do_action( WP_2FA_PREFIX . 'after_totp_setup', $setup_wizard, $data_role, null );
							?>
							<br/>
							<label for="hotp">
								<input type="checkbox" id="hotp" name="wp_2fa_policy[enable_email]" value="enable_email"
								<?php echo $data_role; // phpcs:ignore ?>
								<?php checked( WP2FA::get_wp2fa_setting( 'enable_email' ), 'enable_email' ); ?>
								>
								<?php esc_html_e( 'One-time code via email (HOTP)', 'wp-2fa' ); ?>
							<?php
							?>
							</label>
							<?php if ( ! $setup_wizard ) { ?>
							<div class="use-different-hotp-mail<?php echo \esc_attr( ( false === WP2FA::get_wp2fa_setting( 'enable_email' ) ? ' disabled' : '' ) ); ?>">
								<p class="description" style="margin-bottom: 5px; font-style: normal;">
									<?php esc_html_e( 'Allow user to specify the email address of choice', 'wp-2fa' ); ?>
								</p>
								<fieldset class="email-hotp-options">
								<?php
								$options = array(
									'yes' => array(
										'label' => esc_html__( 'Yes', 'wp-2fa' ),
										'value' => 'specify-email_hotp',
									),
									'no'  => array(
										'label' => esc_html__( 'No', 'wp-2fa' ),
										'value' => '',
									),
								);

								foreach ( $options as $option_key => $option_settings ) {
									?>
									<label for="specify-email_hotp-<?php echo \esc_attr( $option_key ); ?>">
										<input type="radio" name="wp_2fa_policy[specify-email_hotp]" id="specify-email_hotp-<?php echo \esc_attr( $option_key ); ?>" value="<?php echo \esc_attr( $option_settings['value'] ); ?>" class="js-nested"
											<?php checked( Settings::get_role_or_default_setting( 'specify-email_hotp', null ), $option_settings['value'] ); ?>
										>
										<span><?php echo $option_settings['label']; // phpcs:ignore ?></span>
									</label>
										<?php
								}
								?>
								</fieldset>
							</div>
							<?php } ?>
							<br />
							<?php
							if ( ! $setup_wizard ) {
								$class = '';

								if ( '' === trim( Settings::get_role_or_default_setting( 'enable_totp', null, null, true ) ) && '' === trim( Settings::get_role_or_default_setting( 'enable_email', null, null, true ) ) && '' === trim( Settings::get_role_or_default_setting( 'enable_oob_email', null, null, true ) ) ) {
									$class = 'disabled';
								}
								?>
								<div class="method-title"><em><?php esc_html_e( 'Secondary 2FA methods:', 'wp-2fa' ); ?></em></div>
								<br>
								<label for="backup-codes" class="wp-2fa-settings-wrapper <?php echo $class; // phpcs:ignore ?>">
									<input type="checkbox" class="<?php echo \esc_attr( $class ); ?>" id="backup-codes" name="wp_2fa_policy[backup_codes_enabled]" 
									<?php echo $data_role; // phpcs:ignore ?>
									value="yes"
									<?php checked( WP2FA::get_wp2fa_setting( 'backup_codes_enabled' ), 'yes' ); ?>
									>
									<?php
									esc_html_e( 'Backup codes', 'wp-2fa' );
									if ( $setup_wizard ) {
										echo '<p class="description">Note: ';
									} else {
										echo ' - ';
									}
									esc_html_e( 'Backup codes are a secondary method which you can use to log in to the website in case the primary 2FA method is unavailable. Therefore they can\'t be enabled and used as a primary method.', 'wp-2fa' );
									if ( $setup_wizard ) {
										echo '</p>';
									}
									?>
								</label>
								<?php
								/**
								 * Fires after the backup methods HTML rendering is finished.
								 *
								 * @param bool $wizard - Is that wizard ot standard setting.
								 * @param string $data_role - The JS data attribute for the form inputs.
								 * @param string $role - The name of the user role.
								 *
								 * @since 2.0.0
								 */
								\do_action( WP_2FA_PREFIX . 'after_backup_methods_setup', $setup_wizard, $data_role, null );
							} else {
								?>
								<input type="hidden" name="wp_2fa_policy[backup_codes_enabled]" value="yes">
								<input type="hidden" name="wp_2fa_policy[enable-email-backup]" value="enable-email-backup">
								<div class="method-wrapper"></div>
								<div class="method-title"><em><?php esc_html_e( 'Secondary 2FA methods and other settings', 'wp-2fa' ); ?></em></div>
								<p class="description" style="margin-bottom: 5px;"><br>
									<?php esc_html_e( 'You can configure other 2FA method settings and the 2FA backup methods from the plugin settings later on.', 'wp-2fa' ); ?>
								</p>
								<?php
							}
							?>
						</fieldset>
						<?php
						if ( ! $setup_wizard ) {
							?>
					</td>
				</tr>
			</tbody>
		</table>
						<?php } ?>
		<?php
		$output = ob_get_clean();

		/**
		 * At this point, none of the default providers is set / activated. This filter allows additional providers to change the behaviour. Checking the input array for specific values (methods), and based on that we can raise error that none of the allowed methods has bees selected by the user, or dismiss the error otherwise.
		 *
		 * @param string $output - Parsed HTML with the methods.
		 * @param bool $setup_wizard - The type of the wizard (first time wizard / settings).
		 *
		 * @since 2.0.0
		 */
		$output = apply_filters( WP_2FA_PREFIX . 'select_methods', $output, $setup_wizard );

		echo $output; // phpcs:ignore
	}

	/**
	 * Enforcement policy step
	 *
	 * @since 1.7.0
	 *
	 * @param boolean $setup_wizard - Boolean - is that first time wizard setup or settings page call.
	 *
	 * @return void
	 */
	public static function enforcement_policy( $setup_wizard = false ) {
		?>
		<h3 id="enforcement_settings"><?php esc_html_e( 'Do you want to enforce 2FA for some, or all the users? ', 'wp-2fa' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'When you enforce 2FA the users will be prompted to configure 2FA the next time they login. Users have a grace period for configuring 2FA. You can configure the grace period and also exclude user(s) or role(s) in this settings page. ', 'wp-2fa' ); ?> <a href="https://wp2fa.io/support/kb/configure-2fa-policies-enforce/?utm_source=plugin&utm_medium=referral&utm_campaign=WP2FA&utm_content=settings+pages" target="_blank" rel=noopener><?php esc_html_e( 'Learn more.', 'wp-2fa' ); ?></a>
		</p>
			<?php
			if ( ! $setup_wizard ) {
				?>
		<table class="form-table js-enforcement-policy-section">
			<tbody>
				<tr>
					<th><label for="enforcement-policy"><?php esc_html_e( 'Enforce 2FA on', 'wp-2fa' ); ?></label></th>
					<td>
			<?php } ?>
						<fieldset class="contains-hidden-inputs">
							<label for="all-users" style="margin:.35em 0 .5em !important; display: block;">
								<input type="radio" name="wp_2fa_policy[enforcement-policy]" id="all-users" value="all-users"
								<?php checked( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), 'all-users' ); ?>
								>
							<span><?php esc_html_e( 'All users', 'wp-2fa' ); ?></span>
							</label>

							<?php if ( WP_Helper::is_multisite() ) : ?>
								<label for="superadmins-only" style="margin:.35em 0 .5em !important; display: block;">
									<input type="radio" name="wp_2fa_policy[enforcement-policy]" id="superadmins-only" value="superadmins-only"
											<?php checked( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), 'superadmins-only' ); ?> />
									<span><?php esc_html_e( 'Only super admins', 'wp-2fa' ); ?></span>
								</label>
								<label for="superadmins-siteadmins-only" style="margin:.35em 0 .5em !important; display: block;">
									<input type="radio" name="wp_2fa_policy[enforcement-policy]" id="superadmins-siteadmins-only" value="superadmins-siteadmins-only"
											<?php checked( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), 'superadmins-siteadmins-only' ); ?> />
									<span><?php esc_html_e( 'Only super admins and site admins', 'wp-2fa' ); ?></span>
								</label>
							<?php endif; ?>

							<label for="certain-roles-only" style="margin:.35em 0 .5em !important; display: block;">
								<?php $checked = in_array( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), array( 'certain-roles-only', 'certain-users-only' ), true ); ?>
								<input type="radio" name="wp_2fa_policy[enforcement-policy]" id="certain-roles-only" value="certain-roles-only"
								<?php ( $setup_wizard ) ? checked( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), 'certain-roles-only' ) : checked( $checked ); ?>
								data-unhide-when-checked=".certain-roles-only-inputs, .certain-users-only-inputs">
								<span><?php esc_html_e( 'Only for specific users and roles', 'wp-2fa' ); ?></span>
							</label>
							<fieldset class="hidden certain-users-only-inputs">
								<div>
									<p>
										<label for="enforced_users-multi-select"><?php esc_html_e( 'Users :', 'wp-2fa' ); ?></label> <select multiple="multiple" id="enforced_users-multi-select" name="wp_2fa_policy[enforced_users][]" style=" display:none;width:<?php echo ( $setup_wizard ) ? '100' : '50'; ?>%">
										<?php
										$excluded_users = WP2FA::get_wp2fa_setting( 'enforced_users' );
										foreach ( $excluded_users as $user ) {
											?>
												<option selected="selected" value="<?php echo \esc_attr( $user ); ?>"><?php echo \esc_attr( $user ); ?></option>
												<?php
										}
										?>
										</select>
									</p>
								</div>
							</fieldset>
							<fieldset class="hidden certain-roles-only-inputs">
								<div>
									<p style="margin-top: 0;">
										<label for="enforced-roles-multi-select"><?php esc_html_e( 'Roles :', 'wp-2fa' ); ?></label>
										<select multiple="multiple" id="enforced-roles-multi-select" name="wp_2fa_policy[enforced_roles][]" style=" display:none;width:<?php echo ( $setup_wizard ) ? '100' : '50'; ?>%">
										<?php
										$all_roles      = \WP2FA\WP2FA::wp_2fa_get_roles();
										$enforced_roles = WP2FA::get_wp2fa_setting( 'enforced_roles' );
										foreach ( $all_roles as $role => $role_name ) {
											$selected = '';
											if ( in_array( $role, $enforced_roles, true ) ) {
												$selected = 'selected="selected"';
											}
											?>
														<option <?php echo $selected; // phpcs:ignore ?> value="<?php echo \esc_attr( strtolower( $role ) ); ?>"><?php echo \esc_html( $role_name ); ?></option>
												<?php
										}
										?>
										</select>
									</p>
								</div>
									<?php if ( WP_Helper::is_multisite() ) { ?>
								<p class="description">
									<input type="checkbox" name="wp_2fa_policy[superadmins-role-add]" id="superadmins-role-add" value="yes" style="position: relative; top: -3px;" 
											<?php checked( WP2FA::get_wp2fa_setting( 'superadmins-role-add' ), 'yes' ); ?> />
									<label for="superadmins-role-add"><?php esc_html_e( 'Also enforce 2FA on network users with super admin privileges', 'wp-2fa' ); ?></label>
								</p>
								<?php } ?>
							</fieldset>
					<?php if ( WP_Helper::is_multisite() ) { ?>
							<div>
								<label for="enforce-on-multisite" style="margin:.35em 0 .5em !important; display: block;">
									<input type="radio" name="wp_2fa_policy[enforcement-policy]" id="enforce-on-multisite" value="enforce-on-multisite"
										<?php checked( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), 'enforce-on-multisite' ); ?>
									data-unhide-when-checked=".all-sites">
									<span><?php esc_html_e( 'These sub-sites', 'wp-2fa' ); ?></span>
								</label>
								<fieldset class="hidden all-sites">
									<p>
										<label for="slim-multi-select"><?php esc_html_e( 'Sites :', 'wp-2fa' ); ?></label> <select multiple="multiple" id="slim-multi-select" name="wp_2fa_policy[included_sites][]" style="display:none; width:<?php echo ( $setup_wizard ) ? '100' : '50'; ?>%">
											<?php
											$selected_sites = WP2FA::get_wp2fa_setting( 'included_sites' );
											foreach ( WP_Helper::get_multi_sites() as $site ) {
												$args = array(
													'blog_id' => $site->blog_id,
												);

												$current_blog_details = get_blog_details( $args );
												$selected             = '';
												if ( in_array( $site->blog_id, $selected_sites, true ) ) {
													$selected = 'selected="selected"';
												}
												?>
												<option <?php echo $selected; // phpcs:ignore ?> value="<?php echo \esc_attr( $site->blog_id ); ?>"><?php echo \esc_html( $current_blog_details->blogname ); ?></option>
												<?php
											}
											?>
										</select>
									</p>
								</fieldset>
							</div>
					<?php } ?>
							<div>
								<label for="do-not-enforce" style="margin:.35em 0 .5em !important; display: block;">
									<input type="radio" name="wp_2fa_policy[enforcement-policy]" id="do-not-enforce" value="do-not-enforce"
									<?php checked( WP2FA::get_wp2fa_setting( 'enforcement-policy' ), 'do-not-enforce' ); ?>
									>
									<span><?php esc_html_e( 'Do not enforce on any users', 'wp-2fa' ); ?></span>
								</label>
							</div>
							<br/>
						</fieldset>
						<?php
						if ( ! $setup_wizard ) {
							?>
					</td>
				</tr>
			</tbody>
		</table>
							<?php
						}
	}

	/**
	 * Exclude users and groups
	 *
	 * @since 1.7.0
	 *
	 * @param boolean $setup_wizard - Boolean - is that first time wizard setup or settings page call.
	 *
	 * @return void
	 */
	public static function exclude_users( $setup_wizard = false ) {
		?>
		<h3><?php esc_html_e( 'Do you want to exclude any users or roles from 2FA? ', 'wp-2fa' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'If you are enforcing 2FA on all users but for some reason you would like to exclude individual user(s) or users with a specific role, you can exclude them below', 'wp-2fa' ); ?>
		</p>
		<?php
		if ( ! $setup_wizard ) {
			?>
		<table class="form-table js-enforcement-policy-section">
			<tbody>
				<tr>
					<th><label id="exclude-users" for="excluded-users-multi-select"><?php esc_html_e( 'Exclude the following users', 'wp-2fa' ); ?></label></th>
					<td>
		<?php } else { ?>
					<label for="excluded-users-multi-select"><?php esc_html_e( 'Exclude the following users', 'wp-2fa' ); ?>
		<?php } ?>
						<fieldset>
							<div>
								<select multiple="multiple" id="excluded-users-multi-select" name="wp_2fa_policy[excluded_users][]" style=" display:none;width:<?php echo ( $setup_wizard ) ? '100' : '50'; ?>%">
								<?php
								$excluded_users = WP2FA::get_wp2fa_setting( 'excluded_users' );
								foreach ( $excluded_users as $user ) {
									?>
									<option selected="selected" value="<?php echo \esc_attr( $user ); ?>"><?php echo \esc_html( $user ); ?></option>
									<?php
								}
								?>
								</select>
							</div>
						<?php
						if ( ! $setup_wizard ) {
							?>

							</td>
					</tr>
					<tr>
						<th><label for="excluded-roles-multi-select"><?php esc_html_e( 'Exclude the following roles', 'wp-2fa' ); ?></label></th>
						<td>
							<p>
						<?php } else { ?>
							<br>
								<label for="excluded-roles-multi-select"><?php esc_html_e( 'Exclude the following roles', 'wp-2fa' ); ?></label>
							<?php } ?>
									<select multiple="multiple" id="excluded-roles-multi-select" name="wp_2fa_policy[excluded_roles][]" style=" display:none;width:<?php echo ( $setup_wizard ) ? '100' : '50'; ?>%">
									<?php
									$all_roles      = \WP2FA\WP2FA::wp_2fa_get_roles();
									$excluded_roles = WP2FA::get_wp2fa_setting( 'excluded_roles' );
									foreach ( $all_roles as $role => $role_name ) {
										$selected = '';
										if ( in_array( strtolower( $role ), $excluded_roles, true ) ) {
											$selected = 'selected="selected"';
										}
										?>
											<option <?php echo $selected;  // phpcs:ignore ?> value="<?php echo \esc_attr( strtolower( $role ) ); ?>"><?php echo \esc_html( $role_name ); ?></option>
											<?php
									}
									?>
									</select>
							<br>
							<?php if ( WP_Helper::is_multisite() ) { ?>
							<div style="margin-top:10px;">
								<input type="checkbox" name="wp_2fa_policy[superadmins-role-exclude]" id="superadmins-role-exclude" value="yes"
									<?php checked( WP2FA::get_wp2fa_setting( 'superadmins-role-exclude' ), 'yes' ); ?> />
								<label for="superadmins-role-exclude"><?php esc_html_e( 'Also exclude users with super admin privilege', 'wp-2fa' ); ?></label>
							</div>
							<?php } ?>
						</fieldset>
						<?php
						if ( ! $setup_wizard ) {
							?>
					</td>
				</tr>
			</tbody>
		</table>
							<?php } ?>
		<?php
	}

	/**
	 * Which network sites to exclude (for multisite instal)
	 *
	 * @since 1.7.0
	 *
	 * @param boolean $setup_wizard - Boolean - is that first time wizard setup or settings page call.
	 *
	 * @return void
	 */
	public static function excluded_network_sites( $setup_wizard = false ) {
		?>
		<h3><?php esc_html_e( 'Do you want to exclude all the users of a site from 2FA? ', 'wp-2fa' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'If you are enforcing 2FA on all users but for some reason you do not want to enforce it on a specific sub site, specify the sub site name below:', 'wp-2fa' ); ?>
		</p>
		<?php
		if ( ! $setup_wizard ) {
			?>
		<table class="form-table js-enforcement-policy-section">
			<tbody>
				<tr>
					<th><label for="excluded-sites-multi-select"><?php esc_html_e( 'Exclude the following sites', 'wp-2fa' ); ?></label></th>
					<td>
		<?php } ?>
						<fieldset>
						<?php
						if ( $setup_wizard ) {
							?>

						<div class="option-pill">
							<label for="excluded_sites_search"><?php esc_html_e( 'Exclude the following sites', 'wp-2fa' ); ?>
						<?php } ?>
								<select multiple="multiple" id="excluded-sites-multi-select" name="wp_2fa_policy[excluded_sites][]" style=" display:none;width:<?php echo ( $setup_wizard ) ? '100' : '50'; ?>%">
								<?php
									$excluded_sites = WP2FA::get_wp2fa_setting( 'excluded_sites' );
								if ( ! empty( $excluded_sites ) ) {
									foreach ( $excluded_sites as $site_id ) {
										$site = get_blog_details( $site_id )->blogname;
										?>
												<option selected="selected" value="<?php echo \esc_attr( $site_id ); ?>"><?php echo \esc_html( $site ); ?></option>
											<?php
									}
								}
								?>
								</select>
								<?php
								if ( $setup_wizard ) {
									?>
							</label>
						</div>
						<?php } ?>
						</fieldset>
						<?php
						if ( ! $setup_wizard ) {
							?>
					</td>
				</tr>
			</tbody>
		</table>
						<?php } ?>
		<?php
	}

	/**
	 * Set the grace period
	 *
	 * @since 1.7.0
	 *
	 * @param boolean $setup_wizard - Boolean - is that first time wizard setup or settings page call.
	 *
	 * @return void
	 */
	public static function grace_period( $setup_wizard = false ) {
		$grace_period = (int) WP2FA::get_wp2fa_setting( 'grace-period', true );
		/**
		 * Via that, you can change the grace period TTL.
		 *
		 * @param bool - Default at this point is true - no method is selected.
		 */
		$testing = apply_filters( WP_2FA_PREFIX . 'allow_grace_period_in_seconds', false );
		if ( $testing ) {
			$grace_max = 600;
		} else {
			$grace_max = 10;
		}
		?>
		<fieldset class="contains-hidden-inputs">
			<label for="no-grace-period" style="margin-bottom: 10px; display: block;">
				<input type="radio" name="wp_2fa_policy[grace-policy]" id="no-grace-period" value="no-grace-period"
				<?php checked( WP2FA::get_wp2fa_setting( 'grace-policy' ), 'no-grace-period' ); ?>
				>
			<span><?php esc_html_e( 'Users have to configure 2FA straight away.', 'wp-2fa' ); ?></span>
			</label>

			<label for="use-grace-period">
				<input type="radio" name="wp_2fa_policy[grace-policy]" id="use-grace-period" value="use-grace-period"
				<?php checked( WP2FA::get_wp2fa_setting( 'grace-policy' ), 'use-grace-period' ); ?>
				data-unhide-when-checked=".grace-period-inputs">
				<span><?php esc_html_e( 'Give users a grace period to configure 2FA', 'wp-2fa' ); ?></span>
			</label>
			<fieldset class="hidden grace-period-inputs">
				<br/>
				<input type="number" id="grace-period" name="wp_2fa_policy[grace-period]" value="<?php echo esc_attr( $grace_period ); ?>" min="1" max="<?php echo esc_attr( $grace_max ); ?>">
				<label class="radio-inline">
					<input class="js-nested" type="radio" name="wp_2fa_policy[grace-period-denominator]" value="hours"
					<?php checked( WP2FA::get_wp2fa_setting( 'grace-period-denominator' ), 'hours' ); ?>
					>
					<?php esc_html_e( 'hours', 'wp-2fa' ); ?>
				</label>
				<label class="radio-inline">
					<input class="js-nested" type="radio" name="wp_2fa_policy[grace-period-denominator]" value="days"
					<?php checked( WP2FA::get_wp2fa_setting( 'grace-period-denominator' ), 'days' ); ?>
					>
					<?php esc_html_e( 'days', 'wp-2fa' ); ?>
				</label>
				<?php
					/**
					 * Fires after the grace period. Gives the ability to change the parsed code.
					 *
					 * @param string $content - HTML content.
					 * @param string $role - The name of the role.
					 * @param string $name_prefix - Name prefix for the input name, includes the role name if provided.
					 * @param string $data_role - Data attribute - used by the JS.
					 * @param string $role_id - The role name, used to identify the inputs.
					 *
					 * @since 2.0.0
					 */
					$after_grace_content = \apply_filters( WP_2FA_PREFIX . 'after_grace_period', '', '', 'wp_2fa_policy' );
					echo $after_grace_content; // phpcs:ignore
				?>
				<?php
				/**
				 * Via that, you can change the grace period TTL.
				 *
				 * @param bool - Default at this point is true - no method is selected.
				 */
				$testing = apply_filters( WP_2FA_PREFIX . 'allow_grace_period_in_seconds', false );
				if ( $testing ) {
					?>
					<label class="radio-inline">
						<input class="js-nested" type="radio" name="wp_2fa_policy[grace-period-denominator]" value="seconds"
						<?php checked( WP2FA::get_wp2fa_setting( 'grace-period-denominator' ), 'seconds' ); ?>
						>
						<?php esc_html_e( 'Seconds', 'wp-2fa' ); ?>
					</label>
					<?php
				}

				if ( $setup_wizard ) {
					$user                         = wp_get_current_user();
					$last_user_to_update_settings = $user->ID;

					?>
				<input type="hidden" id="2fa_main_user" name="wp_2fa_policy[2fa_settings_last_updated_by]" value="<?php echo esc_attr( $last_user_to_update_settings ); ?>">
				<?php } else { ?>
					<p><?php esc_html_e( 'Note: If users do not configure it within the configured stipulated time, their account will be locked and have to be unlocked manually.', 'wp-2fa' ); ?></p>
				<?php } ?>
			</fieldset>
			<br/>
		</fieldset>
		<?php
	}
}
