<?php
/**
 * External interface .
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */

/**
 * Interface for all priority providers
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
interface Google_Sitemap_Generator_Prio_Provider_Base {

	/**
	 * Initializes a new priority provider
	 *
	 * @param int $total_comments int The total number of comments of all posts .
	 * @param int $total_posts int The total number of posts .
	 * @since 3.0
	 */
	public function __construct( $total_comments, $total_posts );

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public static function get_name();

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public static function get_description();

	/**
	 * Returns the priority for a specified post
	 *
	 * @param int $post_id int The ID of the post .
	 * @param int $comment_count int The number of comments for this post .
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public function get_post_priority( $post_id, $comment_count );
}

