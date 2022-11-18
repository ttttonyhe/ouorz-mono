<?php
/**
 * Responsible for email templates generation.
 *
 * @package    wp2fa
 * @copyright  2021 WP White Security
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace WP2FA;

/**
 * Plain old PHP object to hold data for an email template.
 *
 * @package WP2FA
 */
class Email_Template {

	/**
	 * Template ID used for most settings form fields and setting keys.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * The title of the email
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Email template description
	 *
	 * @var string
	 */
	private $description;

	/**
	 * ID used for identifying the subject and body of the email. Defaults to $id.
	 *
	 * @var string ID used for identifying the subject and body of the email. Defaults to $id.
	 */
	private $email_content_id;

	/**
	 * True if the email can be turned on or off in the plugin settings.
	 *
	 * @var bool
	 */
	private $can_be_toggled = true;

	/**
	 * Email_Template constructor.
	 *
	 * @param string $id - The template ID.
	 * @param string $title - The title.
	 * @param string $description - The description.
	 */
	public function __construct( string $id, string $title, string $description ) {
		$this->id               = $id;
		$this->title            = $title;
		$this->description      = $description;
		$this->email_content_id = $id;
	}

	/**
	 * Can it be toggled
	 *
	 * @return bool
	 */
	public function can_be_toggled(): bool {
		return $this->can_be_toggled;
	}

	/**
	 * Sets the toggled flag for the template
	 *
	 * @param bool $can_be_toggled - Can it be toggled.
	 */
	public function set_can_be_toggled( $can_be_toggled ) {
		$this->can_be_toggled = $can_be_toggled;
	}

	/**
	 * Returns the template ID
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Returns the title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * Returns the description
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Returns the mail content
	 *
	 * @return string
	 */
	public function get_email_content_id(): string {
		return $this->email_content_id;
	}

	/**
	 * Set content ID
	 *
	 * @param string $email_content_id - the ID of the content.
	 */
	public function set_email_content_id( string $email_content_id ) {
		$this->email_content_id = $email_content_id;
	}
}
