<?php
/**
 * Class responsible for caching and saving cache relations.
 *
 * @link: https://www.acato.nl
 * @since 2018.3
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Caching
 */

namespace WP_Rest_Cache_Plugin\Includes\Caching;

use WP_Rest_Cache_Plugin\Includes\Util;

/**
 * Class responsible for caching and saving cache relations.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Caching
 * @author     Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Caching {

	/**
	 * The current version of the database tables, used to determine if they need updating after plugin update.
	 *
	 * @var string DB_VERSION The current version of the database tables.
	 */
	const DB_VERSION = '2026.1.0';

	/**
	 * The table name for the table where caches are stored together with their statistics.
	 *
	 * @var string TABLE_CACHES The table name for the table where caches are stored.
	 */
	const TABLE_CACHES = 'wrc_caches';

	/**
	 * The table name for the table where cache relations are stored.
	 *
	 * @var string TABLE_RELATIONS The table name for the table where cache relations are stored.
	 */
	const TABLE_RELATIONS = 'wrc_relations';

	/**
	 * Delete caches by an endpoint path matching only this exact same endpoint path (and query params).
	 *
	 * @var string FLUSH_STRICT
	 */
	const FLUSH_STRICT = 'strict';

	/**
	 * Delete caches by an endpoint path matching the exact same endpoint path with any query params that might have
	 * been used.
	 *
	 * @var string FLUSH_PARAMS
	 */
	const FLUSH_PARAMS = 'params';

	/**
	 * Delete caches by an endpoint path matching any cache that was called starting with the endpoint path (ignoring
	 * any query params or subpaths following the given path).
	 *
	 * @var string FLUSH_LOOSE
	 */
	const FLUSH_LOOSE = 'loose';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    \WP_Rest_Cache_Plugin\Includes\Caching\Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * The complete table name for the table where caches are stored. A combination of the database prefix and the
	 * constant TABLE_CACHES.
	 *
	 * @access private
	 * @var    string $db_table_caches The complete table name for the table where caches are stored.
	 */
	private $db_table_caches;

	/**
	 * The complete table name for the table where cache relations are stored. A combination of the database prefix and
	 * the constant TABLE_RELATIONS.
	 *
	 * @access private
	 * @var    string $db_table_relations The complete table name for the table where cache relations are stored.
	 */
	private $db_table_relations;

	/**
	 * A boolean defining if the current cache is a single item cache or a multi-item endpoint cache.
	 *
	 * @access private
	 * @var    bool $is_single Whether the current cache is a single item cache.
	 */
	private $is_single;

	/**
	 * Class constructor.
	 *
	 * Set the database table variables.
	 */
	private function __construct() {
		global $wpdb;

		$this->db_table_caches    = $wpdb->prefix . self::TABLE_CACHES;
		$this->db_table_relations = $wpdb->prefix . self::TABLE_RELATIONS;
	}

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return \WP_Rest_Cache_Plugin\Includes\Caching\Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Caching();
		}

		return self::$instance;
	}

	/**
	 * Get a cached item from the transient cache and register a cache hit.
	 *
	 * @param string $cache_key The cache key for the requested cache.
	 *
	 * @return mixed The cache item.
	 */
	public function get_cache( $cache_key ) {
		$expiration = $this->get_cache_expiration( $cache_key ) ?? '';
		if ( 1 === strtotime( $expiration ) ) {
			return false;
		}
		$cache = get_transient( $this->transient_key( $cache_key ) );
		if ( $cache ) {
			$cache_hit_recording = get_option( 'wp_rest_cache_hit_recording', true );
			if ( $cache_hit_recording ) {
				$hit = $this->register_cache_hit( $cache_key );
				if ( false === $hit || 0 === $hit ) {
					// Weird situation where there is a transient but nothing in the cache tables. Return no cache.
					$cache = false;
				}
			}
		}

		return $cache;
	}

	/**
	 * Set the transient cache and register the cache + its relations.
	 *
	 * @param string               $cache_key The cache key for the cache.
	 * @param mixed                $value The item to be cached.
	 * @param string               $type The type of cache (endpoint).
	 * @param string               $uri The requested uri for this cache if available.
	 * @param string               $object_type The object type for this cache if available.
	 * @param array<string,string> $request_headers An array of cacheable request headers.
	 * @param string               $request_method The request method for this call.
	 *
	 * @return void
	 */
	public function set_cache( $cache_key, $value, $type, $uri = '', $object_type = '', $request_headers = [], $request_method = 'GET' ) {
		if ( 'endpoint' !== $type ) {
			_deprecated_argument( __FUNCTION__, '2020.3.0', 'Only \'endpoint\' is allowed for $type.' );

			return;
		}

		$this->register_endpoint_cache( $cache_key, $value, $uri, $request_headers, $request_method );

		$result = set_transient(
			$this->transient_key( $cache_key ),
			$value,
			$this->get_timeout(
				true,
				[
					'uri'             => $uri,
					'object_type'     => $object_type,
					'request_headers' => $request_headers,
					'request_method'  => $request_method,
				]
			)
		);

		if ( false === $result && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// translators: %1$s: The endpoint URI, %2$s: The cache key.
			$message = sprintf( __( 'WP REST Cache: Failed to set cache for endpoint: %1$s (cache_key: %2$s)', 'wp-rest-cache' ), $uri, $cache_key );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $message );
		}
	}

	/**
	 * Delete a cached item. Possibly also delete cache statistics.
	 *
	 * @param string $cache_key The cache key for the cache.
	 * @param bool   $force Whether to delete the cache statistics.
	 *
	 * @return void
	 */
	public function delete_cache( $cache_key, $force = false ) {
		global $wpdb;
		delete_transient( $this->transient_key( $cache_key ) );

		$cache_id = $this->get_cache_row_id( $cache_key );

		if ( is_null( $cache_id ) ) {
			return;
		}

		$sql =
			"DELETE FROM `{$this->db_table_relations}` 
                WHERE `cache_id` = %d";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $wpdb->prepare( $sql, $cache_id ) );

		if ( $force ) {
			$sql =
				"DELETE FROM `{$this->db_table_caches}`
                    WHERE `cache_id` = %d";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $wpdb->prepare( $sql, $cache_id ) );
		} else {
			$this->update_cache_expiration( $cache_id, date_i18n( 'Y-m-d H:i:s', 1 ), true );
		}
	}

	/**
	 * Delete caches by endpoint path.
	 *
	 * Delete caches by an endpoint path either strict (\WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_STRICT)
	 * matching only this exact same endpoint path (and query params), params (\WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_PARAMS)
	 * matching the exact same endpoint path with any query params that might have been used, or loose
	 * (\WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_LOOSE) matching any cache that was called starting with
	 * the endpoint path (ignoring any query params or subpaths following the given path).
	 *
	 * @param string $endpoint The endpoint path to match.
	 * @param string $strictness The match type. (Can be either \WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_STRICT, \WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_PARAMS or \WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_LOOSE).
	 * @param bool   $force Should the caches be deleted ($force = true) or just flushed ($force = false).
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_cache_by_endpoint( $endpoint, $strictness = self::FLUSH_STRICT, $force = false ) {
		global $wpdb;

		$uri_parts    = wp_parse_url( $endpoint );
		$request_path = rtrim( $uri_parts['path'] ?? '', '/' );

		if ( self::FLUSH_STRICT === $strictness && isset( $uri_parts['query'] ) && ! empty( $uri_parts['query'] ) ) {
			parse_str( $uri_parts['query'], $params );
			ksort( $params );
			$request_path .= '?' . http_build_query( $params );
		}

		$sql              = "UPDATE `{$this->db_table_caches}`
		SET `expiration` = %s";
		$prepare_params[] = date_i18n( 'Y-m-d H:i:s', 1 );
		if ( $force ) {
			$sql .= ', `deleted` = 1';
		}
		$sql .= ' WHERE ';
		switch ( $strictness ) {
			case self::FLUSH_STRICT:
				$sql             .= ' `request_uri` = %s ';
				$prepare_params[] = $request_path;
				break;
			case self::FLUSH_PARAMS:
				$sql             .= ' `request_uri` LIKE %s ';
				$prepare_params[] = $request_path . '?%';
				break;
			case self::FLUSH_LOOSE:
				$sql             .= ' `request_uri` LIKE %s ';
				$prepare_params[] = $request_path . '%';
				break;
			default:
				return new \WP_Error( 'wp_rest_cache_invalid_strictness', __( 'Invalid strictness', 'wp-rest-cache' ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$affected_rows = $wpdb->query( $wpdb->prepare( $sql, $prepare_params ) );

		if ( 0 !== $affected_rows && false !== $affected_rows ) {
			$this->schedule_cleanup();
		}

		return 0 !== $affected_rows && false !== $affected_rows;
	}

	/**
	 * Clear all saved caches. Possibly also delete all statistics.
	 *
	 * @param bool $force Whether to delete statistics.
	 *
	 * @return bool True if there were caches to delete.
	 */
	public function clear_caches( $force = false ) {
		global $wpdb;

		$sql =
			"SELECT `cache_key`
            FROM `{$this->db_table_caches}`";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$caches = $wpdb->get_results( $sql );

		if ( $caches ) {
			foreach ( $caches as $cache ) {
				$this->delete_cache( $cache->cache_key, $force );
			}

			return true;
		}

		return false;
	}

	/**
	 * Fired upon WordPress 'save_post' hook. On post update delete all related caches, on post creation delete all
	 * non-single endpoint caches for this post type.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool     $update Whether this is an existing post being updated or not.
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post, $update ) {
		if ( 'auto-draft' === $post->post_status ) {
			return;
		}
		if ( $update ) {
			$this->delete_related_caches( $post_id, $post->post_type );
		} else {
			$this->delete_object_type_caches( $post->post_type );
		}
	}

	/**
	 * Fired upon WordPress 'delete_post' hook. Delete all related caches, including all single cache statistics.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function delete_post( $post_id ) {
		$post = get_post( $post_id );
		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		$this->delete_related_caches( $post_id, $post->post_type, true );

		if ( post_type_supports( $post->post_type, 'comments' ) ) {
			$this->delete_comment_type_related_caches();
		}
	}

	/**
	 * Fired upon WordPress 'transition_post_status' hook. Delete all non-single endpoint caches for this post type if
	 * the new or the old status (but not both) is 'publish'.
	 *
	 * @param string   $new_status The new status of the post.
	 * @param string   $old_status The old status of the post.
	 * @param \WP_Post $post The post which status has been transitioned.
	 *
	 * @return void
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( $new_status === $old_status || ( 'publish' !== $new_status && 'publish' !== $old_status ) ) {
			return;
		}

		$this->delete_object_type_caches( $post->post_type );

		if ( 'publish' === $old_status && post_type_supports( $post->post_type, 'comments' ) ) {
			$this->delete_comment_type_related_caches();
		}
	}

	/**
	 * Fired upon WordPress 'updated_post_meta' hook. Delete all related caches.
	 *
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 *
	 * @return void
	 */
	public function updated_post_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$this->updated_meta( 'post', $meta_id, $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Delete related caches when metadata is updated.
	 *
	 * @param string $meta_type The meta type, can be any of post, comment, term or user.
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 *
	 * @return void
	 */
	private function updated_meta( $meta_type, $meta_id, $object_id, $meta_key, $_meta_value ) {
		/**
		 * Should caches be flushed on meta update?
		 *
		 * Allows external determination if caches should be flushed when meta is updated.
		 *
		 * @param boolean $flush Whether the cache should be flushed (true) or not (false)
		 * @param string $meta_type The meta type, can be any of post, comment, term or user.
		 * @param int $meta_id ID of updated metadata entry.
		 * @param int $object_id ID of the object metadata is for.
		 * @param string $meta_key Metadata key.
		 * @param mixed $_meta_value Metadata value.
		 *
		 * @since 2026.1.0
		 */
		$flush = apply_filters( 'wp_rest_cache/flush_on_meta_update', false, $meta_type, $meta_id, $object_id, $meta_key, $_meta_value );

		/**
		 * Should caches be flushed on meta update? Based on meta type / meta key.
		 *
		 * Allows external determination if caches should be flushed when meta is updated.
		 *
		 * @param boolean $flush Whether the cache should be flushed (true) or not (false)
		 * @param int $meta_id ID of updated metadata entry.
		 * @param int $object_id ID of the object metadata is for.
		 * @param mixed $_meta_value Metadata value.
		 *
		 * @since 2026.1.0
		 */
		$flush = apply_filters( "wp_rest_cache/flush_on_meta_update/{$meta_type}/{$meta_key}", $flush, $meta_id, $object_id, $_meta_value );
		if ( true === $flush ) {
			$meta_subtype = get_object_subtype( $meta_type, $object_id );
			$this->delete_related_caches( $object_id, $meta_subtype );
		}
	}

	/**
	 * Fired upon WordPress 'add_attachment' hook. Delete all non-single endpoint caches for attachments.
	 *
	 * @return void
	 */
	public function add_attachment() {
		$this->delete_object_type_caches( 'attachment' );
	}

	/**
	 * Fired upon WordPress 'edit_attachment' hook. Delete all related caches for this attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 *
	 * @return void
	 */
	public function edit_attachment( $attachment_id ) {
		$this->delete_related_caches( $attachment_id, 'attachment' );
	}

	/**
	 * Fired upon WordPress 'created_term' hook. Delete all non-single endpoint caches for this taxonomy.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return void
	 */
	public function created_term( $term_id, $tt_id, $taxonomy ) {
		$this->delete_object_type_caches( $taxonomy );
	}

	/**
	 * Fired upon WordPress 'edited_term' hook. Delete all related caches for this term.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return void
	 */
	public function edited_term( $term_id, $tt_id, $taxonomy ) {
		$this->delete_related_caches( $term_id, $taxonomy );
	}

	/**
	 * Fired upon WordPress 'delete_term' hook. Delete all related caches for this term, including all single cache
	 * statistics.
	 *
	 * @param int    $term Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return void
	 */
	public function delete_term( $term, $tt_id, $taxonomy ) {
		$this->delete_related_caches( $term, $taxonomy, true );
	}

	/**
	 * Fired upon WordPress 'updated_term_meta' hook. Delete all related caches.
	 *
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 *
	 * @return void
	 */
	public function updated_term_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$this->updated_meta( 'term', $meta_id, $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Fired upon WordPress 'set_object_terms' hook. Delete all related caches.
	 *
	 * @since 2026.1.2
	 *
	 * @param int               $object_id  ID of the object.
	 * @param array<int,string> $terms      An array of object term IDs or slugs.
	 * @param array<int,int>    $tt_ids     An array of term taxonomy IDs.
	 * @param string            $taxonomy   Taxonomy slug.
	 * @param bool              $append     Whether to append new terms to the old terms.
	 * @param array<int,int>    $old_tt_ids Old array of term taxonomy IDs.
	 *
	 * @return void
	 */
	public function set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		/**
		 * Should caches be flushed on setting object terms?
		 *
		 * Allows to disable cache flushing when terms are set on an object.
		 *
		 * @since 2026.1.2
		 *
		 * @param bool               $flush       Whether the cache should be flushed (true) or not (false).
		 * @param int                $object_id   ID of the object.
		 * @param array<int,string>  $terms       An array of object term IDs or slugs.
		 * @param array<int,int>     $tt_ids      An array of term taxonomy IDs.
		 * @param string             $taxonomy    Taxonomy slug.
		 * @param bool               $append      Whether to append new terms to the old terms.
		 * @param array<int,int>     $old_tt_ids  Old array of term taxonomy IDs.
		 */
		$flush = apply_filters( 'wp_rest_cache/flush_on_set_terms', true, $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids );

		if ( true !== $flush ) {
			return;
		}

		// Early exit if no terms changed (compare term taxonomy IDs directly to avoid database queries).
		$tt_ids_sorted     = array_map( 'intval', (array) $tt_ids );
		$old_tt_ids_sorted = array_map( 'intval', (array) $old_tt_ids );
		sort( $tt_ids_sorted );
		sort( $old_tt_ids_sorted );
		if ( $tt_ids_sorted === $old_tt_ids_sorted ) {
			return;
		}

		// Calculate affected terms using the difference between old and new term taxonomy IDs.
		$added_tt_ids   = array_diff( $tt_ids_sorted, $old_tt_ids_sorted );
		$removed_tt_ids = array_diff( $old_tt_ids_sorted, $tt_ids_sorted );

		if ( $append ) {
			$affected_tt_ids = $added_tt_ids;
		} else {
			$affected_tt_ids = array_unique( array_merge( $added_tt_ids, $removed_tt_ids ) );
		}

		if ( empty( $affected_tt_ids ) ) {
			return;
		}

		// Get affected term IDs from term taxonomy IDs.
		$affected_term_ids = get_terms(
			[
				'taxonomy'         => $taxonomy,
				'term_taxonomy_id' => $affected_tt_ids,
				'hide_empty'       => false,
				'fields'           => 'ids',
			]
		);

		if ( is_wp_error( $affected_term_ids ) || empty( $affected_term_ids ) ) {
			return;
		}

		$term_ids_to_invalidate = array_map( 'intval', $affected_term_ids );

		// Get all ancestor term IDs for hierarchical taxonomies in a single query.
		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			$ancestor_ids = $this->get_all_term_ancestors( $affected_term_ids, $taxonomy );
			if ( ! empty( $ancestor_ids ) ) {
				$term_ids_to_invalidate = array_unique( array_merge( $term_ids_to_invalidate, $ancestor_ids ) );
			}
		}

		// Batch delete all related caches in a single query.
		$this->delete_related_caches_batch( $term_ids_to_invalidate, $taxonomy );
	}

	/**
	 * Get all ancestor term IDs for a set of terms in a single query.
	 *
	 * @since 2026.1.3
	 *
	 * @param array<int,int> $term_ids  Array of term IDs to get ancestors for.
	 * @param string         $taxonomy  Taxonomy slug.
	 *
	 * @return array<int,int> Array of ancestor term IDs.
	 */
	private function get_all_term_ancestors( $term_ids, $taxonomy ) {
		global $wpdb;

		if ( empty( $term_ids ) ) {
			return [];
		}

		$term_ids       = array_map( 'intval', $term_ids );
		$ancestors      = [];
		$terms_to_check = $term_ids;

		// Get the taxonomy's hierarchical term data in a single query.
		// We'll traverse up the parent chain iteratively to avoid recursive queries.
		$placeholders = implode( ',', array_fill( 0, count( $term_ids ), '%d' ) );

		// Get all terms in this taxonomy that could be ancestors (have no parent or are parents of our terms).
		$all_terms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.term_id, tt.parent
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s AND tt.parent > 0",
				$taxonomy
			)
		);

		// Build a parent lookup map.
		$parent_map = [];
		foreach ( $all_terms as $term ) {
			$parent_map[ (int) $term->term_id ] = (int) $term->parent;
		}

		// Traverse up the parent chain for each affected term.
		foreach ( $term_ids as $term_id ) {
			$current = $term_id;
			while ( isset( $parent_map[ $current ] ) && $parent_map[ $current ] > 0 ) {
				$parent = $parent_map[ $current ];
				if ( ! in_array( $parent, $ancestors, true ) ) {
					$ancestors[] = $parent;
				}
				$current = $parent;
			}
		}

		return $ancestors;
	}

	/**
	 * Delete related caches for multiple object IDs of the same type in a single query.
	 *
	 * @since 2026.1.3
	 *
	 * @param array<int,int> $ids         Array of object IDs.
	 * @param string         $object_type The type of the objects.
	 *
	 * @return int The number of affected cache rows.
	 */
	private function delete_related_caches_batch( $ids, $object_type ) {
		global $wpdb;

		if ( empty( $ids ) ) {
			return 0;
		}

		$ids = array_map( 'intval', $ids );

		/**
		 * Fires before caches related to objects are flushed in batch.
		 *
		 * @since 2026.1.3
		 *
		 * @param array<int,int> $ids         The object IDs.
		 * @param string         $object_type The object type.
		 */
		do_action( 'wp_rest_cache/pre_delete_related_caches_batch', $ids, $object_type );

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%s' ) );

		$sql = "UPDATE `{$this->db_table_caches}` AS `c`
			INNER JOIN `{$this->db_table_relations}` AS `r`
				ON `r`.`cache_id` = `c`.`cache_id`
			SET `c`.`expiration` = %s
			WHERE `r`.`object_id` IN ({$placeholders})
			AND `r`.`object_type` = %s";

		$prepare_args   = [ date_i18n( 'Y-m-d H:i:s', 1 ) ];
		$prepare_args   = array_merge( $prepare_args, array_map( 'strval', $ids ) );
		$prepare_args[] = $object_type;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$affected_rows = $wpdb->query( $wpdb->prepare( $sql, $prepare_args ) );

		if ( 0 !== $affected_rows && false !== $affected_rows ) {
			$this->schedule_cleanup();
		}

		return $affected_rows;
	}

	/**
	 * Fired upon WordPress 'profile_update' hook. Delete all related caches for this user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function profile_update( $user_id ) {
		$this->delete_related_caches( $user_id, 'user' );
	}

	/**
	 * Fired upon WordPress 'user_register' hook. Delete all non-single endpoint caches for users.
	 *
	 * @return void
	 */
	public function user_register() {
		$this->delete_object_type_caches( 'users' );
	}

	/**
	 * Fired upon WordPress 'deleted_user' hook. Delete all related caches for this user, including all single cache
	 * statistics.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function deleted_user( $user_id ) {
		$this->delete_related_caches( $user_id, 'user', true );
	}

	/**
	 * Fired upon WordPress 'updated_user_meta' hook. Delete all related caches.
	 *
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 *
	 * @return void
	 */
	public function updated_user_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$this->updated_meta( 'user', $meta_id, $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Fired upon WordPress 'deleted_comment', 'trashed_comment' and 'spammed_comment' hooks. Delete all related caches
	 * for this comment, including all single cache statistics if comment is deleted.
	 *
	 * @param int $comment_id Comment ID.
	 *
	 * @return void
	 */
	public function delete_comment_related_caches( $comment_id ) {
		switch ( current_filter() ) {
			case 'deleted_comment':
				$force_single_delete = true;
				break;
			default:
				$force_single_delete = false;
				break;
		}
		$this->delete_related_caches( $comment_id, 'comment', $force_single_delete );
	}

	/**
	 * Fired upon WordPress 'edit_comment', 'untrashed_comment', 'unspammed_comment', 'wp_insert_comment' and
	 * 'comment_post' hooks. Delete all non-single endpoint caches for comments.
	 *
	 * @return void
	 */
	public function delete_comment_type_related_caches() {
		$this->delete_object_type_caches( 'comment' );
	}

	/**
	 * Fired upon WordPress 'updated_comment_meta' hook. Delete all related caches.
	 *
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 *
	 * @return void
	 */
	public function updated_comment_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$this->updated_meta( 'comment', $meta_id, $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Delete all caches.
	 *
	 * @param bool         $delete       True if caches need to be deleted instead of flushed.
	 * @param string|false $cache_filter Optional filter identifier passed to wp_rest_cache/filtered_cache_keys.
	 *
	 * @return int  The number of deleted caches.
	 */
	public function delete_all_caches( $delete, $cache_filter = false ) {
		global $wpdb;

		$deleted = "( CASE
						WHEN `object_type` = 'unknown' THEN 1
						ELSE `deleted`
						END )";
		if ( $delete ) {
			$deleted = '1';
		}

		$where_clause = '';

		// Apply cache filter if specified.
		if ( false !== $cache_filter ) {
			/**
			 * Filter to get cache keys matching a specific filter type.
			 *
			 * Returns an array of cache keys that match the filter criteria.
			 * Used for filtering which caches to clear based on custom criteria.
			 * If an empty array is returned, no caches will be cleared for this filter.
			 *
			 * @since 2026.2.0
			 *
			 * @param array  $cache_keys   Array of cache keys matching the filter (empty by default).
			 * @param string $cache_filter The filter type identifier.
			 */
			$filtered_cache_keys = apply_filters( 'wp_rest_cache/filtered_cache_keys', [], $cache_filter );

			if ( ! empty( $filtered_cache_keys ) ) {
				// Sanitize cache keys - they should be MD5 hashes (32 hex chars).
				$safe_keys = array_filter(
					$filtered_cache_keys,
					function ( $key ) {
						return preg_match( '/^[a-f0-9]{32}$/i', $key );
					}
				);

				if ( ! empty( $safe_keys ) ) {
					$placeholders = implode( ',', array_fill( 0, count( $safe_keys ), '%s' ) );
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
					$where_clause = $wpdb->prepare( "WHERE `cache_key` IN ({$placeholders})", $safe_keys );
				} else {
					// All keys were filtered out as invalid, nothing to clear.
					return 0;
				}
			} else {
				// Filter returned empty array, nothing to clear for this filter type.
				return 0;
			}
		}

		$sql =
			"UPDATE `{$this->db_table_caches}`
				SET `expiration` = %s,
					`deleted` = {$deleted}
				{$where_clause}";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$affected_rows = $wpdb->query( $wpdb->prepare( $sql, date_i18n( 'Y-m-d H:i:s', 1 ) ) );

		if ( 0 !== $affected_rows && false !== $affected_rows ) {
			$this->schedule_cleanup();
		}

		return $affected_rows;
	}

	/**
	 * Delete all related caches for an object ID and object type. Possibly also delete cache statistics for single
	 * endpoint caches.
	 *
	 * @param int|string $id The ID of the object.
	 * @param string     $object_type The type of the object.
	 * @param bool       $force_single_delete Whether to delete cache statistics for single endpoint caches.
	 * @param bool       $delete True if caches need to be deleted instead of flushed.
	 *
	 * @return int       The number of deleted caches.
	 */
	public function delete_related_caches( $id, $object_type, $force_single_delete = false, $delete = false ) {
		global $wpdb;

		/**
		 * Fires before caches related to an object are flushed.
		 *
		 * @since 2024.1.0
		 *
		 * @param int    $id          The object ID.
		 * @param string $object_type The object type.
		 */
		do_action( 'wp_rest_cache/pre_delete_related_caches', $id, $object_type );

		$set_clause = '`c`.`expiration` = %s';

		if ( $delete ) {
			$set_clause .= ', `c`.`deleted` = 1';
		} elseif ( $force_single_delete ) {
			$set_clause .= ', `c`.`deleted` = `c`.`is_single`';
		}

		$sql =
			"UPDATE `{$this->db_table_caches}` AS `c`
                JOIN `{$this->db_table_relations}` AS `r`
                    ON `r`.`cache_id` = `c`.`cache_id`
                SET {$set_clause}
                WHERE `r`.`object_id` = %s
                AND `r`.`object_type` = %s";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$affected_rows = $wpdb->query( $wpdb->prepare( $sql, date_i18n( 'Y-m-d H:i:s', 1 ), $id, $object_type ) );

		if ( 0 !== $affected_rows && false !== $affected_rows ) {
			$this->schedule_cleanup();
		}

		return $affected_rows;
	}

	/**
	 * Delete all non-single caches for an object type.
	 *
	 * @param string $object_type The type of the object.
	 * @param bool   $delete True if caches need to be deleted instead of flushed.
	 *
	 * @return int      The number of deleted caches.
	 */
	public function delete_object_type_caches( $object_type, $delete = false ) {
		global $wpdb;

		/**
		 * Fires before all non-single caches for an object type are flushed.
		 *
		 * @since 2024.1.0
		 *
		 * @param string $object_type The object type.
		 */
		do_action( 'wp_rest_cache/pre_delete_object_type_caches', $object_type );

		if ( $delete ) {
			$set_clause = '`expiration` = %s,
                	`deleted` = 1';
		} else {
			$set_clause =
				'`expiration` = %s';
		}

		$sql =
			"UPDATE `{$this->db_table_caches}`
				SET {$set_clause}
                WHERE `cache_type` = %s 
                AND `object_type` = %s
                AND `is_single` = %d";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$affected_rows = $wpdb->query( $wpdb->prepare( $sql, date_i18n( 'Y-m-d H:i:s', 1 ), 'endpoint', $object_type, false ) );

		if ( 0 !== $affected_rows && false !== $affected_rows ) {
			$this->schedule_cleanup();
		}

		return $affected_rows;
	}

	/**
	 * Get the cache row ID for a specific cache key.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return null|int The ID of the cache row.
	 */
	private function get_cache_row_id( $cache_key ) {
		global $wpdb;

		$sql =
			"SELECT `cache_id`
                FROM `{$this->db_table_caches}`
                WHERE `cache_key` = %s 
                LIMIT 1";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $wpdb->prepare( $sql, $cache_key ) );
	}

	/**
	 * Get the cache expiration for a specific cache key.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return null|string The expiration of the cache.
	 */
	private function get_cache_expiration( $cache_key ) {
		global $wpdb;

		$sql =
			"SELECT `expiration`
                FROM `{$this->db_table_caches}`
                WHERE `cache_key` = %s 
                LIMIT 1";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $wpdb->prepare( $sql, $cache_key ) );
	}

	/**
	 * Insert a new cache into the database.
	 *
	 * @param string               $cache_key The cache key.
	 * @param string               $cache_type The cache type (endpoint).
	 * @param string               $uri The requested URI.
	 * @param string               $object_type The object type for the cache.
	 * @param bool                 $is_single Whether it is a single item cache.
	 * @param array<string,string> $request_headers An array of cacheable request headers.
	 * @param string               $request_method The request method for this call.
	 *
	 * @return int The ID of the inserted row.
	 */
	private function insert_cache_row( $cache_key, $cache_type, $uri, $object_type, $is_single = true, $request_headers = [], $request_method = 'GET' ) {
		global $wpdb;

		if ( 'endpoint' !== $cache_type ) {
			_deprecated_argument( __FUNCTION__, '2020.3.0', 'Only \'endpoint\' is allowed for $cache_type.' );
		}

		$expiration = $this->get_timeout(
			true,
			[
				'uri'             => $uri,
				'object_type'     => $object_type,
				'request_headers' => $request_headers,
				'request_method'  => $request_method,
			]
		);
		if ( 0 !== $expiration && ! $this->get_memcache_used() ) {
			// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$expiration += current_time( 'timestamp' );
		}

		$wpdb->insert(
			$this->db_table_caches,
			[
				'cache_key'       => $cache_key,
				'cache_type'      => $cache_type,
				'request_uri'     => $uri,
				'request_headers' => wp_json_encode( $request_headers ),
				'request_method'  => $request_method,
				'object_type'     => $object_type,
				'cache_hits'      => 1,
				'is_single'       => $is_single,
				'expiration'      => date_i18n( 'Y-m-d H:i:s', $expiration ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s' ]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get the cache row data for a single cache.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return array<string,mixed> The cache row data.
	 */
	private function get_cache_row( $cache_key ) {
		global $wpdb;

		$sql =
			"SELECT *
                FROM `{$this->db_table_caches}`
                WHERE `cache_key` = %s
                LIMIT 1";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $cache_key ), ARRAY_A );

		if ( ! $result ) {
			return null;
		}

		$result['is_active'] = ( false !== get_transient( $this->transient_key( $result['cache_key'] ) ) && 1 !== strtotime( $result['expiration'] ) );
		if ( ! $result['is_active'] ) {
			if ( 1 === strtotime( $result['expiration'] ) ) {
				$result['expiration'] = __( 'Flushed', 'wp-rest-cache' );
			} else {
				$result['expiration'] = __( 'Expired', 'wp-rest-cache' );
			}
		} elseif ( 0 === strtotime( $result['expiration'] ) ) {
			$result['expiration'] = __( 'Unlimited', 'wp-rest-cache' );
		}

		return $result;
	}

	/**
	 * Update the expiration date/time for a specific cache.
	 *
	 * @param int                 $cache_id The ID of the cache row.
	 * @param null|string         $expiration The specific expiration date/time. If none supplied it will be calculated.
	 * @param bool                $cleaned True if this is called when the transient is actually deleted.
	 * @param array<string,mixed> $options An array of options for the wp_rest_cache/timeout filter.
	 *
	 * @return void
	 */
	private function update_cache_expiration( $cache_id, $expiration = null, $cleaned = false, $options = [] ) {
		global $wpdb;

		$is_renew = is_null( $expiration );

		if ( $is_renew ) {
			$timeout = $this->get_timeout( true, $options );
			if ( 0 !== $timeout && ! $this->get_memcache_used() ) {
				// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				$timeout += current_time( 'timestamp' );
			}
			$expiration = date_i18n( 'Y-m-d H:i:s', $timeout );
		}

		$data    = [
			'expiration' => $expiration,
			'cleaned'    => (int) $cleaned,
		];
		$formats = [ '%s', '%d' ];

		// Only clear `deleted` when renewing the cache (an existing key being re-cached). On an
		// explicit-expiration call from delete_cache(), leave `deleted` alone so a previously
		// hard-flagged row isn't accidentally resurrected.
		if ( $is_renew ) {
			$data['deleted'] = 0;
			$formats[]       = '%d';
		}

		$wpdb->update(
			$this->db_table_caches,
			$data,
			[ 'cache_id' => $cache_id ],
			$formats,
			[ '%d' ]
		);
	}

	/**
	 * Insert a cache relation into the database.
	 *
	 * @param int              $cache_id The ID of the cache row.
	 * @param int|string|mixed $object_id The ID of the related object.
	 * @param string|mixed     $object_type The object type of the relation.
	 *
	 * @return void
	 */
	public function insert_cache_relation( $cache_id, $object_id, $object_type ) {
		global $wpdb;

		// Prevent notice when recursively processing a OPTIONS call.
		if ( is_array( $object_id ) || is_array( $object_type ) ) {
			return;
		}

		/**
		 * Insert cache relation.
		 *
		 * Allows external action upon inserting a cache relation.
		 *
		 * @since 2025.2.0
		 *
		 * @param int $cache_id The row id of the current cache.
		 * @param int $object_id The id of the object the cache has a relation with.
		 * @param string $object_type The object type of the object the cache has a relation with.
		 */
		do_action( 'wp_rest_cache/insert_cache_relation', $cache_id, $object_id, $object_type );

		$wpdb->replace(
			$this->db_table_relations,
			[
				'cache_id'    => $cache_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			],
			[ '%d', '%s', '%s' ]
		);
	}

	/**
	 * Register a cache hit in the database.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return int|boolean Number of rows affected. Boolean false on error
	 */
	private function register_cache_hit( $cache_key ) {
		global $wpdb;

		$sql =
			"UPDATE `{$this->db_table_caches}`
                SET `cache_hits` = `cache_hits` + 1
                WHERE `cache_key` = %s";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $wpdb->prepare( $sql, $cache_key ) );
	}

	/**
	 * Register an endpoint cache in the database.
	 *
	 * @param string               $cache_key The cache key.
	 * @param mixed                $data The cached data.
	 * @param string               $uri The requested URI.
	 * @param array<string,string> $request_headers An array of cacheable request headers.
	 * @param string               $request_method The request method for this call.
	 *
	 * @return void
	 */
	private function register_endpoint_cache( $cache_key, $data, $uri, $request_headers, $request_method ) {
		$cache_id = $this->get_cache_row_id( $cache_key );

		/**
		 * Determine object type of cache.
		 *
		 * Allows external determination of object type of current cache.
		 *
		 * @since 2018.4.2
		 *
		 * @param string $object_type Object type
		 * @param string $cache_key Cache key
		 * @param mixed $data The data that is to be cached
		 * @param string $uri The requested URI
		 */
		$object_type = apply_filters( 'wp_rest_cache/determine_object_type', $this->determine_object_type( $data ), $cache_key, $data, $uri );

		/**
		 * Determine if the cache contains a single item or is a collection of items.
		 *
		 * Allows external determination of single item status of current cache.
		 *
		 * @since 2019.4.3
		 *
		 * @param boolean $is_single Whether the cache contains a single item (true) or a collection of items (false)
		 * @param mixed $data The data that is to be cached
		 * @param string $uri The requested URI
		 */
		$this->is_single = apply_filters( 'wp_rest_cache/is_single_item', $this->is_single, $data, $uri );

		if ( is_null( $cache_id ) ) {
			$cache_id = $this->insert_cache_row( $cache_key, 'endpoint', $uri, $object_type, $this->is_single, $request_headers, $request_method );
		} else {
			$this->update_cache_expiration(
				$cache_id,
				null,
				false,
				[
					'uri'             => $uri,
					'object_type'     => $object_type,
					'request_headers' => $request_headers,
					'request_method'  => $request_method,
				]
			);
		}

		// Force data to be an array.
		$data['data'] = json_decode( wp_json_encode( $data['data'] ), true );

		$this->process_recursive_cache_relations( $cache_id, $data['data'] );

		/**
		 * Process cache relations.
		 *
		 * Allows external processing of cache relations.
		 *
		 * @since 2018.4.2
		 *
		 * @param int $cache_id The row id of the current cache.
		 * @param mixed $data The data that is to be cached.
		 * @param string $object_type Object type.
		 * @param string $uri The requested URI.
		 */
		do_action( 'wp_rest_cache/process_cache_relations', $cache_id, $data, $object_type, $uri );
	}

	/**
	 * Loop through the cached data to determine all cache relations recursively.
	 *
	 * @param int                 $cache_id The ID of the cache row.
	 * @param array<string,mixed> $record An array of data to be checked for relations.
	 *
	 * @return void
	 */
	private function process_recursive_cache_relations( $cache_id, $record ) {
		if ( ! is_array( $record ) ) {
			return;
		}
		$record = array_change_key_case( $record, CASE_LOWER );
		if ( array_key_exists( 'id', $record ) && array_key_exists( 'post_type', $record ) ) {
			$this->insert_cache_relation( $cache_id, $record['id'], $record['post_type'] );
		} elseif ( array_key_exists( 'taxonomy', $record ) ) {
			$this->process_taxonomy_relations( $cache_id, $record );
		} elseif ( array_key_exists( 'id', $record )
					&& array_key_exists( 'type', $record )
					&& ( array_key_exists( 'slug', $record ) || array_key_exists( 'status', $record ) )
		) {
			$this->insert_cache_relation( $cache_id, $record['id'], $record['type'] );
		} elseif ( array_key_exists( 'id', $record )
					&& array_key_exists( 'slug', $record )
					&& array_key_exists( '_links', $record )
		) {
			if ( isset( $record['_links']['collection'][0]['href'] ) ) {
				if ( substr( $record['_links']['collection'][0]['href'], - 12 ) === '/wp/v2/users' ) {
					$this->insert_cache_relation( $cache_id, $record['id'], 'user' );
				}
			}
		}

		if ( array_key_exists( 'type', $record )
					&& array_key_exists( 'post', $record )
					&& 'comment' === $record['type'] ) {
			$object_type = get_post_type( $record['post'] );
			if ( $object_type ) {
				$this->insert_cache_relation( $cache_id, $record['post'], $object_type );
			}
		}

		foreach ( $record as $field => $value ) {
			if ( is_array( $value ) ) {
				$this->process_recursive_cache_relations( $cache_id, $value );
			}
		}
	}

	/**
	 * Current record is a taxonomy, process its relations.
	 *
	 * @param int                 $cache_id The ID of the cache row.
	 * @param array<string,mixed> $record An array of data to be checked for relations.
	 *
	 * @return void
	 */
	private function process_taxonomy_relations( $cache_id, $record ) {
		if ( array_key_exists( 'id', $record )
			&& array_key_exists( 'name', $record )
			&& array_key_exists( 'slug', $record )
		) {
			$this->insert_cache_relation( $cache_id, $record['id'], $record['taxonomy'] );
		} elseif ( array_key_exists( 'term_id', $record ) ) {
			$this->insert_cache_relation( $cache_id, $record['term_id'], $record['taxonomy'] );
		}
	}

	/**
	 * Determine the cache object type, based upon the cached data.
	 *
	 * @param array<string,mixed> $data The cached data.
	 *
	 * @return string The object type, or 'unknown' if it could not be determined.
	 */
	private function determine_object_type( $data ) {
		// Force data to be an array.
		$data['data'] = json_decode( wp_json_encode( $data['data'] ), true );

		if ( array_key_exists( 'id', $data['data'] ) ) {
			$this->is_single = true;
			if ( array_key_exists( 'type', $data['data'] ) ) {
				return $data['data']['type'];
			} elseif ( array_key_exists( 'taxonomy', $data['data'] ) ) {
				return $data['data']['taxonomy'];
			}
		} else {
			$this->is_single = false;
			if ( count( $data['data'] ) && isset( $data['data'][0] ) && is_array( $data['data'][0] ) ) {
				if ( array_key_exists( 'type', $data['data'][0] ) ) {
					return $data['data'][0]['type'];
				} elseif ( array_key_exists( 'taxonomy', $data['data'][0] ) ) {
					return $data['data'][0]['taxonomy'];
				}
			}
		}

		return 'unknown';
	}

	/**
	 * Get an array of cache data for a specific API type.
	 *
	 * @param string $api_type The type of the API for which the data is retrieved (endpoint).
	 * @param int    $per_page Number of items to return per page.
	 * @param int    $page_number The requested page.
	 *
	 * @return array<int,array<string,mixed>> An array containing the requested cache data.
	 */
	public function get_api_data( $api_type, $per_page, $page_number ) {
		global $wpdb;

		$page         = $page_number - 1;
		$prepare_args = [];

		$where = $this->get_where_clause( $api_type, $prepare_args );

		$order = $this->get_orderby_clause();

		$prepare_args[] = ( $page * $per_page );
		$prepare_args[] = $per_page;

		$sql =
			"SELECT * 
            FROM `{$this->db_table_caches}`
            WHERE {$where}
            ORDER BY {$order}
            LIMIT %d, %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_args ), ARRAY_A );
		foreach ( $results as &$result ) {
			$result['is_active'] = ( false !== get_transient( $this->transient_key( $result['cache_key'] ) ) && 1 !== strtotime( $result['expiration'] ) );
			if ( ! $result['is_active'] ) {
				if ( 1 === strtotime( $result['expiration'] ) ) {
					$result['expiration'] = __( 'Flushed', 'wp-rest-cache' );
				} else {
					$result['expiration'] = __( 'Expired', 'wp-rest-cache' );
				}
			} elseif ( 0 === strtotime( $result['expiration'] ) ) {
				$result['expiration'] = __( 'Unlimited', 'wp-rest-cache' );
			}
		}

		/**
		 * Filter the cache data returned for the API Caches table.
		 *
		 * Allows adding custom data to each cache item for display in the admin table.
		 *
		 * @since 2026.2.0
		 *
		 * @param array<int,array<string,mixed>> $results  The cache data.
		 * @param string                         $api_type The API type (endpoint).
		 */
		return apply_filters( 'wp_rest_cache/api_caches_table_data', $results, $api_type );
	}

	/**
	 * Get the number of records for the requested API type.
	 *
	 * @param string $api_type The type of the API for which the data is retrieved (endpoint).
	 *
	 * @return int The number of records.
	 */
	public function get_record_count( $api_type ) {
		global $wpdb;

		$prepare_args = [];
		$where        = $this->get_where_clause( $api_type, $prepare_args );

		$sql =
			"SELECT COUNT(*)
            FROM `{$this->db_table_caches}`
            WHERE {$where}";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $prepare_args ) );
	}

	/**
	 * Build the where clause for the query that retrieves the cache data for a specific API type.
	 *
	 * @param string           $api_type The type of the API for which the data is retrieved (endpoint).
	 * @param array<int,mixed> $prepare_args A reference to an array containing the arguments for the prepare statement.
	 *
	 * @return string The where clause.
	 */
	private function get_where_clause( $api_type, &$prepare_args ) {
		global $wpdb;

		$where          = '`cache_type` = %s AND `deleted` = %d';
		$prepare_args[] = $api_type;
		$prepare_args[] = false;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing -- read-only search filter from the list table form.
		$search = isset( $_POST['s'] ) ? filter_var( $_POST['s'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		if ( ! $search ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing
			$search = isset( $_GET['s'] ) ? filter_var( $_GET['s'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		}

		if ( ! empty( $search ) ) {
			$where         .= ' AND ( `request_uri` LIKE %s OR `object_type` LIKE %s )';
			$search_escaped = '%' . $wpdb->esc_like( $search ) . '%';
			$prepare_args[] = $search_escaped;
			$prepare_args[] = $search_escaped;
		}

		return $where;
	}

	/**
	 * Build the order by clause for the query.
	 *
	 * @return string The order by clause.
	 */
	private function get_orderby_clause() {
		$order = '`cache_id` DESC';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing
		$orderby = isset( $_GET['orderby'] ) ? filter_var( $_GET['orderby'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;

		if ( in_array(
			$orderby,
			[
				'request_uri',
				'object_type',
				'cache_hits',
				'cache_key',
				'expiration',
			],
			true
		)
		) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_param = isset( $_GET['order'] ) ? filter_var( $_GET['order'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
			$order       = '`' . $orderby . '` ' . ( 'desc' === $order_param ? 'DESC' : 'ASC' );
		}

		return $order;
	}

	/**
	 * Get the cache data for a specific cache. Used for the details-page in the backend, so no cache hit is triggered.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return array<string,mixed>|null An array of cache data, or null if the cache row could not be found.
	 */
	public function get_cache_data( $cache_key ) {
		$cache        = [];
		$cache['row'] = $this->get_cache_row( $cache_key );
		if ( ! $cache['row'] ) {
			return null;
		}

		if ( $cache['row']['is_active'] ) {
			$cache['data'] = get_transient( $this->transient_key( $cache_key ) );
		} else {
			$cache['data'] = false;
		}

		// Force data to be an array.
		$cache['data'] = json_decode( wp_json_encode( $cache['data'] ), true );

		return $cache;
	}

	/**
	 * Get the cache timeout as set in the plugin Settings.
	 *
	 * @param boolean             $calculated If the returned value should be calculated using the interval.
	 * @param array<string,mixed> $options An array of options for the wp_rest_cache/timeout filter.
	 *
	 * @return int Timeout (in seconds if calculated).
	 */
	public function get_timeout( $calculated = true, $options = [] ) {
		$timeout = defined( 'WP_REST_CACHE_TIMEOUT' ) ? WP_REST_CACHE_TIMEOUT : get_option( 'wp_rest_cache_timeout', 1 );
		if ( $calculated ) {
			$timeout_interval = $this->get_timeout_interval();
			$timeout          = $timeout * $timeout_interval;
			if ( $this->get_memcache_used() ) {
				$timeout += time();
			}
		}

		if ( $options ) {
			/**
			 * What timeout should be used for the current cache record?
			 *
			 * Allows to change the timeout for a specific cache record.
			 *
			 * @since 2020.3.0
			 *
			 * @param int $timeout The timeout as set in the settings.
			 * @param array $options An array of options, containing the current uri, the object type, the request headers and the request method.
			 */
			$timeout = apply_filters( 'wp_rest_cache/timeout', $timeout, $options );
		}

		return $timeout;
	}

	/**
	 * Get the cache timeout interval, default is 1 year.
	 *
	 * @return int Timeout interval in seconds.
	 */
	public function get_timeout_interval() {
		return defined( 'WP_REST_CACHE_TIMEOUT_INTERVAL' ) ? WP_REST_CACHE_TIMEOUT_INTERVAL : get_option( 'wp_rest_cache_timeout_interval', YEAR_IN_SECONDS );
	}

	/**
	 * Should a cron be activated to regenerate the expired caches?
	 *
	 * @return bool Whether or not a cron should be activated.
	 */
	public function should_regenerate() {
		return defined( 'WP_REST_CACHE_REGENERATE' ) ? WP_REST_CACHE_REGENERATE : get_option( 'wp_rest_cache_regenerate', false ) === '1';
	}

	/**
	 * Get the regeneration interval, default is twice daily.
	 *
	 * @return string The interval key.
	 */
	public function get_regenerate_interval() {
		return defined( 'WP_REST_CACHE_REGENERATE_INTERVAL' ) ? WP_REST_CACHE_REGENERATE_INTERVAL : get_option( 'wp_rest_cache_regenerate_interval', 'twicedaily' );
	}

	/**
	 * Get the maximum number of regenerated caches per cronjob, default is 10.
	 *
	 * @return int Maximum nuber of caches.
	 */
	public function get_regenerate_number() {
		return defined( 'WP_REST_CACHE_REGENERATE_NUMBER' ) ? WP_REST_CACHE_REGENERATE_NUMBER : get_option( 'wp_rest_cache_regenerate_number', 10 );
	}

	/**
	 * Cronjob to automatically regenerate expired caches.
	 *
	 * @return void
	 */
	public function regenerate_expired_caches() {
		global $wpdb;

		$regenerate_number = (int) $this->get_regenerate_number();

		$sql =
			"SELECT * 
            FROM `{$this->db_table_caches}`
            WHERE `cache_type` = 'endpoint'
            ORDER BY `cache_hits` DESC";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( $results as &$result ) {
			if ( 1 === strtotime( $result['expiration'] ) || false === get_transient( $this->transient_key( $result['cache_key'] ) ) ) {
				/**
				 * Filter whether to skip regeneration for a specific cache.
				 *
				 * @since 2026.2.0
				 *
				 * @param bool                $skip   Whether to skip regeneration. Default false.
				 * @param array<string,mixed> $result The cache data.
				 */
				if ( apply_filters( 'wp_rest_cache/skip_cache_regeneration', false, $result ) ) {
					continue;
				}

				// Regenerate.
				$url    = Util::get_home_url() . $result['request_uri'];
				$return = wp_remote_get(
					$url,
					[
						'timeout'   => 10,
						'sslverify' => false,
						'headers'   => json_decode( $result['request_headers'], true ),
					]
				);

				--$regenerate_number;
				if ( $regenerate_number <= 0 ) {
					break;
				}
			}
		}
	}

	/**
	 * Get whether Memcache(d) is being used as external object cache.
	 *
	 * @return bool Whether Memcache(d) is being used.
	 */
	public function get_memcache_used() {
		return defined( 'WP_REST_CACHE_MEMCACHE_USED' ) ? true === WP_REST_CACHE_MEMCACHE_USED : '1' === get_option( 'wp_rest_cache_memcache_used', false );
	}

	/**
	 * Get a list of global cacheable request headers, the list is comma separated.
	 *
	 * @return string The list of global cacheable request headers.
	 */
	public function get_global_cacheable_request_headers() {
		return defined( 'WP_REST_CACHE_GLOBAL_CACHEABLE_REQUEST_HEADERS' ) ? WP_REST_CACHE_GLOBAL_CACHEABLE_REQUEST_HEADERS : get_option( 'wp_rest_cache_global_cacheable_request_headers', '' );
	}

	/**
	 * Get the cache key for the current ID.
	 *
	 * @param string|int $id The ID used for the cache key.
	 *
	 * @return string The cache key.
	 */
	public function transient_key( $id ) {
		return 'wp_rest_cache_' . $id;
	}

	/**
	 * Schedule a cron job to delete flushed or deleted caches from the transients API. This is done via a cronjob to
	 * have a better performance experience when updating / adding a new post / taxonomy item.
	 *
	 * @return void
	 */
	private function schedule_cleanup() {
		/**
		 * Should caches be deleted immediately instead of via a cron?
		 *
		 * This filter can be used to delete caches immediately instead of via a cron job, which is there for
		 * performance reasons.
		 *
		 * @since 2023.1.0
		 *
		 * @param boolean $immediate_deletion Whether the caches should be deleted immediately (true) or with a cron (false, default).
		 */
		$immediate_deletion = apply_filters( 'wp_rest_cache/delete_caches_immediately', false );

		if ( $immediate_deletion ) {
			$this->cleanup_deleted_caches();
		} elseif ( ! wp_next_scheduled( 'wp_rest_cache_cleanup_deleted_caches' ) ) {
			wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, 'wp_rest_cache_cleanup_deleted_caches' );
		}
	}

	/**
	 * Function called by a cron job to delete flushed or deleted caches from the transients API.
	 *
	 * @return void
	 */
	public function cleanup_deleted_caches() {
		global $wpdb;

		/**
		 * How many caches should be cleanup in each run?
		 *
		 * Allows to change the number of cleaned up caches per cron run.
		 *
		 * @since 2020.2.0
		 *
		 * @param int $limit The maximum number of cleaned up caches per cron run.
		 */
		$limit = (int) apply_filters( 'wp_rest_cache/max_cleanup_caches', 1000 );

		$sql = "SELECT  `cache_key`, `deleted`, `request_uri`, `request_headers`
                FROM    {$this->db_table_caches}
                WHERE   `expiration` = %s
                AND     `cleaned` = %d
                LIMIT   %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$caches = $wpdb->get_results( $wpdb->prepare( $sql, date_i18n( 'Y-m-d H:i:s', 1 ), 0, $limit ) );

		/**
		 * Fires when caches are deleted.
		 *
		 * @since 2023.1.0
		 *
		 * @param array $caches An array of caches that are deleted.
		 */
		do_action( 'wp_rest_cache/deleted_caches', $caches );

		if ( $caches ) {
			foreach ( $caches as $cache ) {
				$this->delete_cache( $cache->cache_key, $cache->deleted );
			}
		}

		$sql = "SELECT  COUNT( `cache_id` ) AS `number_of_caches`
                FROM    {$this->db_table_caches}
                WHERE   `expiration` = %s
                AND     `cleaned` = %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$count = $wpdb->get_var( $wpdb->prepare( $sql, date_i18n( 'Y-m-d H:i:s', 1 ), 0 ) );

		if ( $count > 0 ) {
			$this->schedule_cleanup();
		}
	}

	/**
	 * Update the database structure needed for saving caches and their relations and statistics.
	 *
	 * @return void
	 */
	public function update_database_structure() {
		global $wpdb;

		$version = get_option( 'wp_rest_cache_database_version' );

		if ( version_compare( '2026.1.0', $version, '>' ) ) {
			$this->upgrade_2026_1_0();
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->db_table_caches ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( self::DB_VERSION !== $version || $this->db_table_caches !== $wpdb->get_var( $query ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql_caches =
				"CREATE TABLE `{$this->db_table_caches}` (
					`cache_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
					`cache_key` VARCHAR(181) NOT NULL,
					`cache_type` VARCHAR(10) NOT NULL,
					`request_uri` LONGTEXT NOT NULL,
					`request_headers` LONGTEXT NOT NULL,
					`request_method` VARCHAR(10) NOT NULL,
					`object_type` VARCHAR(50) NOT NULL,
					`cache_hits` BIGINT(20) NOT NULL,
					`is_single` TINYINT(1) NOT NULL,
					`expiration` DATETIME NOT NULL,
					`deleted` TINYINT(1) DEFAULT 0,
					`cleaned` TINYINT(1) DEFAULT 0,
					PRIMARY KEY (`cache_id`),
					UNIQUE INDEX `cache_key` (`cache_key`),
					KEY `cache_type` (`cache_type`),
					KEY `non_single_caches` (`cache_type`, `object_type`, `is_single`)
				)";

			dbDelta( $sql_caches );

			update_option( 'wp_rest_cache_database_version', self::DB_VERSION, false );
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->db_table_relations ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$current_db_version = $wpdb->get_var( $query );

		if ( self::DB_VERSION !== $version || $this->db_table_relations !== $current_db_version ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql_relations =
				"CREATE TABLE `{$this->db_table_relations}` (
					`cache_id` BIGINT(20) NOT NULL,
					`object_id` VARCHAR(100) NOT NULL,
					`object_type` VARCHAR(50) NOT NULL,
					PRIMARY KEY (`cache_id`, `object_id`, `object_type`),
					KEY `cache_id` (`cache_id`),
					KEY `object` (`object_id`, `object_type`)
				)";

			dbDelta( $sql_relations );

			update_option( 'wp_rest_cache_database_version', self::DB_VERSION, false );
		}

		if ( version_compare( '2019.4.0', $version, '>' ) ) {
			$this->upgrade_2019_4_0();
		}
	}

	/**
	 * Delete deprecated item api caches.
	 *
	 * @return void
	 */
	private function upgrade_2019_4_0() {
		$nr_of_item_caches = $this->get_record_count( 'item' );
		for ( $count = 0; $count * 100 < $nr_of_item_caches; $count++ ) {
			$item_caches = $this->get_api_data( 'item', 100, $count + 1 );
			foreach ( $item_caches as $item_cache ) {
				$this->delete_cache( $item_cache['cache_key'], true );
			}
		}
	}

	/**
	 * Drop indexes that prevent shrinking `object_id` and `object_type` columns.
	 *
	 * The composite PRIMARY KEY added in 2025.1.0 combined with VARCHAR(191) columns produced a key
	 * larger than the 1000-byte MyISAM / 767-byte InnoDB-without-large-prefix limit. dbDelta cannot
	 * alter a column that is part of an index, so the affected indexes are dropped here; dbDelta
	 * then recreates them with the shrunk column sizes.
	 *
	 * @return void
	 */
	private function upgrade_2026_1_0() {
		global $wpdb;

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->db_table_caches ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( $this->db_table_caches === $wpdb->get_var( $query ) ) {
			$check_index_query = "SHOW KEYS FROM `{$this->db_table_caches}` WHERE Key_name = 'non_single_caches'";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $wpdb->get_results( $check_index_query ) ) {
				$drop_query = "ALTER TABLE `{$this->db_table_caches}` DROP INDEX `non_single_caches`;";
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( $drop_query );
			}
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->db_table_relations ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( $this->db_table_relations === $wpdb->get_var( $query ) ) {
			$check_primary_query = "SHOW KEYS FROM `{$this->db_table_relations}` WHERE Key_name = 'PRIMARY'";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $wpdb->get_results( $check_primary_query ) ) {
				$drop_query = "ALTER TABLE `{$this->db_table_relations}` DROP PRIMARY KEY;";
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( $drop_query );
			}

			$check_object_query = "SHOW KEYS FROM `{$this->db_table_relations}` WHERE Key_name = 'object'";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $wpdb->get_results( $check_object_query ) ) {
				$drop_query = "ALTER TABLE `{$this->db_table_relations}` DROP INDEX `object`;";
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( $drop_query );
			}
		}
	}
}
