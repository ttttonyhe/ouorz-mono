<?php
/**
 * Default sitemap core class
 *
 * @package Sitemap
 * @author  Arne Brachhold
 * @since   4.0
 */

use function Bhittani\StarRating\functions\sanitize;
/**
 * $Id: sitemap-core.php 935247 2014-06-19 17:13:03Z arnee $
 */

// Enable for dev! Good code doesn't generate any notices...
// error_reporting(E_ALL);
// ini_set('display_errors', 1); .


/**
 * Represents the status (successes and failures) of a ping process
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0b5
 */
class GoogleSitemapGeneratorStatus {

	/**
	 * Var start time of building process .
	 *
	 * @var float $_start_time The start time of the building process .
	 */
	private $start_time = 0;

	/**
	 * The end time of the building process.
	 *
	 * @var float $_end_time The end time of the building process
	 */
	private $end_time = 0;

	/**
	 * Holding an array with the results and information of the last ping .
	 *
	 * @var array Holding an array with the results and information of the last ping
	 */
	private $ping_results = array();

	/**
	 * If the status should be saved to the database automatically .
	 *
	 * @var bool If the status should be saved to the database automatically
	 */
	private $auto_save = true;

	/**
	 * Constructs a new status ued for saving the ping results
	 *
	 * @param string $auto_save .
	 */
	public function __construct( $auto_save = true ) {
		$this->start_time = microtime( true );

		$this->auto_save = $auto_save;

		if ( $auto_save ) {

			$exists = get_option( 'sm_status' );

			if ( false === $exists ) {
				add_option( 'sm_status', '', '', 'no' );
			}
			$this->save();
		}
	}

	/**
	 * Saves the status back to the database
	 */
	public function save() {
		update_option( 'sm_status', $this );
	}

	/**
	 * Returns the last saved status object or null
	 *
	 * @return GoogleSitemapGeneratorStatus
	 */
	public static function load() {
		$status = get_option( 'sm_status' );
		if ( is_a( $status, 'GoogleSitemapGeneratorStatus' ) ) {
			return $status;
		} else {
			return null;
		}
	}

	/**
	 * Ends the ping process
	 */
	public function end() {
		$this->end_time = microtime( true );
		if ( $this->auto_save ) {
			$this->save();
		}
	}

	/**
	 * Returns the duration of the ping process
	 *
	 * @return int
	 */
	public function get_duration() {
		return round( $this->end_time - $this->start_time, 2 );
	}

	/**
	 * Returns the time when the pings were started
	 *
	 * @return int
	 */
	public function get_start_time() {
		return round( $this->start_time, 2 );
	}

	/**
	 * Start ping .
	 *
	 * @param string $service string The internal name of the ping service .
	 * @param string $url string The URL to ping .
	 * @param string $name string The display name of the service .
	 * @return void
	 */
	public function start_ping( $service, $url, $name = null ) {
		$this->ping_results[ $service ] = array(
			'start_time' => microtime( true ),
			'end_time'   => 0,
			'success'    => false,
			'url'        => $url,
			'name'       => $name ? $name : $service,
		);

		if ( $this->auto_save ) {
			$this->save();
		}
	}

	/**
	 * End ping .
	 *
	 * @param string $service string The internal name of the ping service .
	 * @param string $success boolean If the ping was successful .
	 * @return void
	 */
	public function end_ping( $service, $success ) {
		$this->ping_results[ $service ]['end_time'] = microtime( true );
		$this->ping_results[ $service ]['success']  = $success;

		if ( $this->auto_save ) {
			$this->save();
		}
	}

	/**
	 * Returns the duration of the last ping of a specific ping service
	 *
	 * @param string $service string The internal name of the ping service .
	 * @return float
	 */
	public function get_ping_duration( $service ) {
		$res = $this->ping_results[ $service ];
		return round( $res['end_time'] - $res['start_time'], 2 );
	}

	/**
	 * Returns the last result for a specific ping service
	 *
	 * @param string $service string The internal name of the ping service .
	 * @return array
	 */
	public function get_ping_result( $service ) {
		return $this->ping_results[ $service ]['success'];
	}

	/**
	 * Returns the URL for a specific ping service
	 *
	 * @param string $service string The internal name of the ping service .
	 * @return array
	 */
	public function get_ping_url( $service ) {
		return $this->ping_results[ $service ]['url'];
	}

	/**
	 * Returns the name for a specific ping service
	 *
	 * @param string $service string The internal name of the ping service .
	 * @return array
	 */
	public function get_service_name( $service ) {
		return $this->ping_results[ $service ]['name'];
	}

	/**
	 * Returns if a service was used in the last ping
	 *
	 * @param string $service string The internal name of the ping service .
	 * @return bool
	 */
	public function used_ping_service( $service ) {
		return array_key_exists( $service, $this->ping_results );
	}

	/**
	 * Returns the services which were used in the last ping
	 *
	 * @return array
	 */
	public function get_used_ping_services() {
		return array_keys( $this->ping_results );
	}
}

/**
 * Represents an item in the page list
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPage {

	/**
	 * Sets the URL or the relative path to the site dir of the page .
	 *
	 * @var string $_url Sets the URL or the relative path to the site dir of the page
	 */
	public $url;

	/**
	 * Sets the priority of this page .
	 *
	 * @var float $_priority Sets the priority of this page
	 */
	public $priority;

	/**
	 * Sets the chanfe frequency of the page. I want Enums! .
	 *
	 * @var string $_change_freq Sets the chanfe frequency of the page. I want Enums!
	 */
	public $change_freq;

	/**
	 * Sets the last_mod date as a UNIX timestamp. .
	 *
	 * @var int $_last_mod Sets the last_mod date as a UNIX timestamp.
	 */
	public $last_mod;

	/**
	 * Sets the post ID in case this item is a WordPress post or page .
	 *
	 * @var int $_post_id Sets the post ID in case this item is a WordPress post or page
	 */
	public $post_id;

	/**
	 * Initialize a new page object
	 *
	 * @since 3.0
	 * @param string $url The URL or path of the file .
	 * @param float  $priority The Priority of the page 0.0 to 1.0 .
	 * @param string $change_freq The change frequency like daily, hourly, weekly .
	 * @param int    $last_mod The last mod date as a unix timestamp .
	 * @param int    $post_id The post ID of this page .
	 */
	public function __construct( $url = '', $priority = 0.0, $change_freq = 'never', $last_mod = 0, $post_id = 0 ) {
		$this->set_url( $url );
		$this->set_priority( $priority );
		$this->set_change_freq( $change_freq );
		$this->set_last_mod( $last_mod );
		$this->set_post_id( $post_id );
	}

	/**
	 * Returns the URL of the page
	 *
	 * @return string The URL
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Sets the URL of the page
	 *
	 * @param string $url The new URL .
	 */
	public function set_url( $url ) {
		$this->url = (string) $url;
	}

	/**
	 * Returns the priority of this page
	 *
	 * @return float the priority, from 0.0 to 1.0
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Sets the priority of the page
	 *
	 * @param float $priority The new priority from 0.1 to 1.0 .
	 */
	public function set_priority( $priority ) {
		$this->priority = floatval( $priority );
	}

	/**
	 * Returns the change frequency of the page
	 *
	 * @return string The change frequncy like hourly, weekly, monthly etc.
	 */
	public function get_change_freq() {
		return $this->change_freq;
	}

	/**
	 * Sets the change frequency of the page
	 *
	 * @param string $change_freq The new change frequency .
	 */
	public function set_change_freq( $change_freq ) {
		$this->change_freq = (string) $change_freq;
	}

	/**
	 * Returns the last mod of the page
	 *
	 * @return int The lastmod value in seconds
	 */
	public function get_last_mod() {
		return $this->last_mod;
	}

	/**
	 * Sets the last mod of the page
	 *
	 * @param int $last_mod The lastmod of the page .
	 */
	public function set_last_mod( $last_mod ) {
		$this->last_mod = intval( $last_mod );
	}

	/**
	 * Returns the ID of the post
	 *
	 * @return int The post ID
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * Sets the ID of the post
	 *
	 * @param int $post_id The new ID .
	 */
	public function set_post_id( $post_id ) {
		$this->post_id = intval( $post_id );
	}

	/**
	 * Render method .
	 */
	public function render() {

		if ( '/' === $this->url || empty( $this->url ) ) {
			return '';
		}

		$r  = '';
		$r .= "\t<url>\n";
		$r .= "\t\t<loc>" . $this->escape_xml( esc_url_raw( $this->url ) ) . "</loc>\n";
		if ( $this->last_mod > 0 ) {
			$r .= "\t\t<lastmod>" . gmdate( 'Y-m-d\TH:i:s+00:00', $this->last_mod ) . "</lastmod>\n";
		}
		if ( ! empty( $this->change_freq ) ) {
			$r .= "\t\t<changefreq>" . $this->change_freq . "</changefreq>\n";
		}
		if ( false !== $this->priority && '' !== $this->priority ) {
			$r .= "\t\t<priority>" . number_format( $this->priority, 1 ) . "</priority>\n";
		}
		$r .= "\t</url>\n";
		return $r;
	}

	/**
	 * Escape xml .
	 *
	 * @param string $string .
	 */
	protected function escape_xml( $string ) {
		return str_replace( array( '&', '"', '\'', '<', '>' ), array( '&amp;', '&quot;', '&apos;', '&lt;', '&gt;' ), $string );
	}
}

/**
 * Represents an XML entry, like definitions
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorXmlEntry {

	/**
	 * Xml
	 *
	 * @var string $_xml .
	 */
	protected $xml;

	/**
	 * Constructor function
	 *
	 * @param string $xml .
	 */
	public function __construct( $xml ) {
		$this->xml = $xml;
	}

	/**
	 * Render function
	 */
	public function render() {
		return $this->xml;
	}
}

/**
 * Represents an comment
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 * @uses GoogleSitemapGeneratorXmlEntry
 */
class GoogleSitemapGeneratorDebugEntry extends GoogleSitemapGeneratorXmlEntry {
	/**
	 * Render function
	 */
	public function render() {
		return '<!-- ' . $this->xml . " -->\n";
	}
}

/**
 * Represents an item in the sitemap
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorSitemapEntry {

	/**
	 * Sets the URL or the relative path to the site dir of the page .
	 *
	 * @var string $_url Sets the URL or the relative path to the site dir of the page
	 */
	protected $url;

	/**
	 * Sets the last_mod date as a UNIX timestamp. .
	 *
	 * @var int $_last_mod Sets the last_mod date as a UNIX timestamp.
	 */
	protected $last_mod;

	/**
	 * Returns the URL of the page
	 *
	 * @return string The URL
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Sets the URL of the page
	 *
	 * @param string $url The new URL .
	 */
	public function set_url( $url ) {
		$this->url = (string) $url;
	}

	/**
	 * Returns the last mod of the page
	 *
	 * @return int The lastmod value in seconds
	 */
	public function get_last_mod() {
		return $this->last_mod;
	}

	/**
	 * Sets the last mod of the page
	 *
	 * @param int $last_mod The lastmod of the page .
	 */
	public function set_last_mod( $last_mod ) {
		$this->last_mod = intval( $last_mod );
	}
	/**
	 * Constructor
	 *
	 * @param string $url .
	 * @param int    $last_mod .
	 */
	public function __construct( $url = '', $last_mod = 0 ) {
		$this->set_url( $url );
		$this->set_last_mod( $last_mod );
	}
	/**
	 * Render function
	 */
	public function render() {

		if ( '/' === $this->url || empty( $this->url ) ) {
			return '';
		}

		$r  = '';
		$r .= "\t<sitemap>\n";
		$r .= "\t\t<loc>" . $this->escape_xml( esc_url_raw( $this->url ) ) . "</loc>\n";
		if ( $this->last_mod > 0 ) {
			$r .= "\t\t<lastmod>" . gmdate( 'Y-m-d\TH:i:s+00:00', $this->last_mod ) . "</lastmod>\n";
		}
		$r .= "\t</sitemap>\n";
		return $r;
	}

	/**
	 * Escape_xml function .
	 *
	 * @param string $string .
	 */
	protected function escape_xml( $string ) {
		return str_replace( array( '&', '\'', '\'', '<', '>' ), array( '&amp;', '&quot;', '&apos;', '&lt;', '&gt;' ), $string );
	}
}

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

/**
 * Priority Provider which calculates the priority based on the number of comments
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByCountProvider implements Google_Sitemap_Generator_Prio_Provider_Base {

	/**
	 * The total number of comments of all posts .
	 *
	 * @var int $total_comments The total number of comments of all posts
	 */
	protected $total_comments = 0;

	/**
	 * The total number of posts .
	 *
	 * @var int $total_posts The total number of posts
	 */
	protected $total_posts = 0;

	/**
	 * Initializes a new priority provider
	 *
	 * @param int $total_comments int The total number of comments of all posts .
	 * @param int $total_posts int The total number of posts .
	 * @since 3.0
	 */
	public function __construct( $total_comments, $total_posts ) {
		$this->total_comments = $total_comments;
		$this->total_posts    = $total_posts;
	}

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public static function get_name() {
		return __( 'Comment Count', 'sitemap' );
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public static function get_description() {
		return __( 'Uses the number of comments of the post to calculate the priority', 'sitemap' );
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param int $post_id int The ID of the post .
	 * @param int $comment_count int The number of comments for this post .
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public function get_post_priority( $post_id, $comment_count ) {
		if ( $this->total_comments > 0 && $comment_count > 0 ) {
			return round( ( $comment_count * 100 / $this->total_comments ) / 100, 1 );
		} else {
			return 0;
		}
	}
}

/**
 * Priority Provider which calculates the priority based on the average number of comments
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByAverageProvider implements Google_Sitemap_Generator_Prio_Provider_Base {


	/**
	 * The total number of comments of all posts .
	 *
	 * @var int $total_comments The total number of comments of all posts
	 */
	protected $total_comments = 0;

	/**
	 * The total number of posts .
	 *
	 * @var int $total_comments The total number of posts
	 */
	protected $total_posts = 0;

	/**
	 * The average number of comments per post .
	 *
	 * @var int $average The average number of comments per post
	 */
	protected $average = 0.0;

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public static function get_name() {
		return __( 'Comment Average', 'sitemap' );
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public static function get_description() {
		return __( 'Uses the average comment count to calculate the priority', 'sitemap' );
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the average number of comments
	 *
	 * @param int $total_comments int The total number of comments of all posts .
	 * @param int $total_posts int The total number of posts .
	 * @since 3.0
	 */
	public function __construct( $total_comments, $total_posts ) {

		$this->total_comments = $total_comments;
		$this->total_posts    = $total_posts;

		if ( $this->total_comments > 0 && $this->total_posts > 0 ) {
			$this->average = (float) $this->total_comments / $this->total_posts;
		}
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param int $post_id int The ID of the post .
	 * @param int $comment_count int The number of comments for this post .
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public function get_post_priority( $post_id, $comment_count ) {

		// Do not divide by zero !
		if ( 0 == $this->average ) {
			if ( $comment_count > 0 ) {
				$priority = 1;
			} else {
				$priority = 0;
			}
		} else {
			$priority = $comment_count / $this->average;
			if ( $priority > 1 ) {
				$priority = 1;
			} elseif ( $priority < 0 ) {
				$priority = 0;
			}
		}

		return round( $priority, 1 );
	}
}

/**
 * Class to generate a sitemaps.org Sitemaps compliant sitemap of a WordPress site.
 *
 * @package sitemap
 * @author Arne Brachhold
 * @since 3.0
 */
final class GoogleSitemapGenerator {
	/**
	 * The unserialized array with the stored options .
	 *
	 * @var array The unserialized array with the stored options
	 */
	private $options = array();

	/**
	 * The saved additional pages .
	 *
	 * @var array The saved additional pages
	 */
	private $pages = array();

	/**
	 * The values and names of the change frequencies .
	 *
	 * @var array The values and names of the change frequencies
	 */
	private $freq_names = array();

	/**
	 * A list of class names which my be called for priority calculation .
	 *
	 * @var array A list of class names which my be called for priority calculation
	 */
	private $prio_providers = array();

	/**
	 * True if init complete (options loaded etc) .
	 *
	 * @var bool True if init complete (options loaded etc)
	 */
	private $is_initiated = false;

	/**
	 * Defines if the sitemap building process is active at the moment .
	 *
	 * @var bool Defines if the sitemap building process is active at the moment
	 */
	private $is_active = false;

	/**
	 * Holds options like output format and compression for the current request .
	 *
	 * @var array Holds options like output format and compression for the current request
	 */
	private $build_options = array();

	/**
	 * Holds the user interface object
	 *
	 * @since 3.1.1
	 * @var GoogleSitemapGeneratorUI
	 */
	private $ui = null;

	/**
	 * Defines if the simulation mode is on. In this case, data is not echoed but saved instead.
	 *
	 * @var boolean
	 */
	private $sim_mode = false;

	/**
	 * Holds the data if simulation mode is on
	 *
	 * @var array
	 */
	private $sim_data = array(
		'sitemaps' => array(),
		'content'  => array(),
	);

	/**
	 * Defines if the options have been loaded.
	 *
	 * @var bool Defines if the options have been loaded
	 */
	private $options_loaded = false;


	/*************************************** CONSTRUCTION AND INITIALIZING ***************************************/

	/**
	 * Initializes a new Google Sitemap Generator
	 *
	 * @since 4.0
	 */
	private function __construct() {
	}

	/**
	 * Returns the instance of the Sitemap Generator
	 *
	 * @since 3.0
	 * @return GoogleSitemapGenerator The instance or null if not available.
	 */
	public static function get_instance() {
		if ( isset( $GLOBALS['sm_instance'] ) ) {
			return $GLOBALS['sm_instance'];
		} else {
			return null;
		}
	}

	/**
	 * Enables the Google Sitemap Generator and registers the WordPress hooks
	 *
	 * @since 3.0
	 */
	public static function enable() {
		if ( ! isset( $GLOBALS['sm_instance'] ) ) {
			$GLOBALS['sm_instance'] = new GoogleSitemapGenerator();
		}
	}

	/**
	 * Loads up the configuration and validates the prioity providers
	 *
	 * This method is only called if the sitemaps needs to be build or the admin page is displayed.
	 *
	 * @since 3.0
	 */
	public function initate() {
		if ( ! $this->is_initiated ) {

			load_plugin_textdomain( 'sitemap', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

			$this->freq_names = array(
				'always'  => __( 'Always', 'sitemap' ),
				'hourly'  => __( 'Hourly', 'sitemap' ),
				'daily'   => __( 'Daily', 'sitemap' ),
				'weekly'  => __( 'Weekly', 'sitemap' ),
				'monthly' => __( 'Monthly', 'sitemap' ),
				'yearly'  => __( 'Yearly', 'sitemap' ),
				'never'   => __( 'Never', 'sitemap' ),
			);

			$this->load_options();
			$this->load_pages();

			// Register our own priority providers.
			add_filter( 'sm_add_prio_provider', array( $this, 'add_default_prio_providers' ) );

			// Let other plugins register their providers.
			$r = apply_filters( 'sm_add_prio_provider', $this->prio_providers );

			// Check if no plugin return null.
			if ( null !== $r ) {
				$this->prio_providers = $r;
			}

			$this->validate_prio_providers();

			$this->is_initiated = true;
		}
	}


	/*************************************** VERSION AND LINK HELPERS ***************************************/

	/**
	 * Returns the version of the generator
	 *
	 * @since 3.0
	 * @return int The version
	 */
	public static function get_version() {
		return GoogleSitemapGeneratorLoader::get_version();
	}

	/**
	 * Returns the SVN version of the generator
	 *
	 * @since 4.0
	 * @return string The SVN version string
	 */
	public static function get_svn_version() {
		return GoogleSitemapGeneratorLoader::get_svn_version();
	}

	/**
	 * Returns a link pointing to a specific page of the authors website
	 *
	 * @since 3.0
	 * @param string $redir string The to link to .
	 * @return string The full url
	 */
	public static function get_redirect_link( $redir ) {
		return trailingslashit( 'http://url.auctollo.com/' . $redir );
	}

	/**
	 * Returns a link pointing back to the plugin page in WordPress
	 *
	 * @since 3.0
	 * @param string $extra .
	 * @return string The full url
	 */
	public static function get_back_link( $extra = '' ) {
		global $wp_version;
		$url = admin_url( 'options-general.php?page=' . GoogleSitemapGeneratorLoader::get_base_name() . $extra );
		return $url;
	}

	/**
	 * Converts a mysql datetime value into a unix timestamp
	 *
	 * @param string $mysql_date_time string The timestamp in the mysql datetime format .
	 * @return int The time in seconds
	 */
	public static function get_timestamp_from_my_sql( $mysql_date_time ) {
		list( $date, $hours)       = explode( ' ', $mysql_date_time );
		list( $year, $month, $day) = explode( '-', $date );
		list( $hour, $min, $sec)   = explode( ':', $hours );
		return mktime( intval( $hour ), intval( $min ), intval( $sec ), intval( $month ), intval( $day ), intval( $year ) );
	}


	/*************************************** SIMPLE GETTERS ***************************************/

	/**
	 * Returns the names for the frequency values
	 *
	 * @return array
	 */
	public function get_freq_names() {
		return $this->freq_names;
	}

	/**
	 * Returns if the site is running in multi site mode
	 *
	 * @since 4.0
	 * @return bool
	 */
	public function is_multi_site() {
		return ( function_exists( 'is_multisite' ) && is_multisite() );
	}

	/**
	 * Returns if the sitemap building process is currently active
	 *
	 * @since 3.0
	 * @return bool true if active
	 */
	public function is_active() {
		$inst = self::get_instance();
		return ( null !== $inst && $inst->is_active );
	}

	/**
	 * Returns if the compressed sitemap was activated
	 *
	 * @since 3.0b8
	 * @return true if compressed
	 */
	public function is_gzip_enabled() {
		return ( function_exists( 'gzwrite' ) && $this->get_option( 'b_autozip' ) );
	}

	/**
	 * Returns if the XML Dom and XSLT functions are enabled
	 *
	 * @since 4.0b1
	 * @return true if compressed
	 */
	public function is_xsl_enabled() {
		return ( class_exists( 'DomDocument' ) && class_exists( 'XSLTProcessor' ) );
	}

	/**
	 * Returns if Nginx is used as the server software
	 *
	 * @since 4.0.3
	 *
	 * @return bool
	 */
	public function is_nginx() {
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'nginx' ) !== false ) {
			return true;
		}
		return false;
	}



	/*************************************** TAXONOMIES AND CUSTOM POST TYPES ***************************************/

	/**
	 * Returns if this version of WordPress supports the new taxonomy system
	 *
	 * @since 3.0b8
	 * @return true if supported
	 */
	public function is_taxonomy_supported() {
		return ( function_exists( 'get_taxonomy' ) && function_exists( 'get_terms' ) && function_exists( 'get_taxonomies' ) );
	}

	/**
	 * Returns the list of custom taxonomies. These are basically all taxonomies without categories and post tags
	 *
	 * @since 3.1.7
	 * @return array Array of names of user-defined taxonomies
	 */
	public function get_custom_taxonomies() {
		$taxonomies = get_taxonomies( array( 'public' => 1 ) );
		return array_diff( $taxonomies, array( 'category', 'product_cat', 'post_tag', 'nav_menu', 'link_category', 'post_format' ) );
	}

	/**
	 * Returns if this version of WordPress supports custom post types
	 *
	 * @since 3.2.5
	 * @return true if supported
	 */
	public function is_custom_post_types_supported() {
		return ( function_exists( 'get_post_types' ) && function_exists( 'register_post_type' ) );
	}

	/**
	 * Returns the list of custom post types. These are all custom post types except post, page and attachment
	 *
	 * @since 3.2.5
	 * @return array Array of custom post types as per get_post_types
	 */
	public function get_custom_post_types() {
		$post_types = get_post_types( array( 'public' => 1 ) );
		$post_types = array_diff( $post_types, array( 'post', 'page', 'attachment' ) );
		return $post_types;
	}


	/**
	 * Returns the list of active post types, built-in and custom ones.
	 *
	 * @since 4.0b5
	 * @return array Array of custom post types as per get_post_types
	 */
	public function get_active_post_types() {

		$cache_key = __CLASS__ . '::get_active_post_types';

		$active_post_types = wp_cache_get( $cache_key, 'sitemap' );

		if ( false === $active_post_types ) {
			$all_post_types     = get_post_types();
			$enabled_post_types = $this->get_option( 'in_customtypes' );
			if ( $this->get_option( 'in_posts' ) ) {
				$enabled_post_types[] = 'post';
			}
			if ( $this->get_option( 'in_pages' ) ) {
				$enabled_post_types[] = 'page';
			}

			$active_post_types = array();
			foreach ( $enabled_post_types as $post_type ) {
				if ( ! empty( $post_type ) && in_array( $post_type, $all_post_types, true ) ) {
					$active_post_types[] = $post_type;
				}
			}

			wp_cache_set( $cache_key, $active_post_types, 'sitemap', 20 );
		}

		return $active_post_types;
	}

	/**
	 * Returns an array with all excluded post IDs
	 *
	 * @since 4.0b11
	 * @return int[] Array with excluded post IDs
	 */
	public function get_excluded_post_i_ds() {

		$excludes = (array) $this->get_option( 'b_exclude' );

		// Exclude front page page if defined .
		if ( get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' ) ) {
			$excludes[] = get_option( 'page_on_front' );
			return $excludes;
		}
		return array_filter( array_map( 'intval', $excludes ), array( $this, 'is_greater_zero' ) );
	}

	/**
	 * Robots disallowed
	 */
	public function robots_disallowed() {

		// parse url to retrieve host and path.
		$parsed = home_url();
		$rules  = array();

		// location of robots.txt file.
		try {
			if ( file_exists( $parsed . '/robots.txt' ) ) {
				$robotstxt = file( $parsed . '/robots.txt' );

			} elseif ( file_exists( ABSPATH . '/robots.txt' ) ) {
				// if there isn't a robots, then we're allowed in.
				$robotstxt = file( ABSPATH . '/robots.txt' );

			}
		} catch ( Exception $e ) {
			return $rules;
		}

		if ( empty( $robotstxt ) ) {
			return $rules;
		}

		foreach ( $robotstxt as $line ) {
			$line = trim( $line );
			// Skip blank lines .
			if ( ! $line ) {
				continue;
			}

			if ( preg_match( '/^\s*Disallow:(.*)/i', $line, $regs ) ) {

				// An empty rule implies full access - no further tests required .
				if ( ! $regs[1] ) {
					continue;
				}

				// Add rules that apply to array for testing  .
				$id = url_to_postid( home_url( trim( $regs[1] ) ) );
				if ( $id > 0 ) {
					$rules[] = $id;
				}
			}
		}

		return $rules;
	}
	/**
	 * Returns an array with all excluded category IDs.
	 *
	 * @since 4.0b11
	 * @return int[] Array with excluded category IDs
	 */
	public function get_excluded_category_i_ds() {
		$excl_cats = (array) $this->get_option( 'b_exclude_cats' );
		return array_filter( array_map( 'intval', $excl_cats ), array( $this, 'is_greater_zero' ) );
	}

	/*************************************** PRIORITY PROVIDERS ***************************************/

	/**
	 * Returns the list of PriorityProviders
	 *
	 * @return array
	 */
	public function get_prio_providers() {
		return $this->prio_providers;
	}

	/**
	 * Adds the default Priority Providers to the provider list
	 *
	 * @since 3.0
	 * @param array $providers .
	 * @return array
	 */
	public function add_default_prio_providers( $providers ) {
		array_push( $providers, 'GoogleSitemapGeneratorPrioByCountProvider' );
		array_push( $providers, 'GoogleSitemapGeneratorPrioByAverageProvider' );
		if ( class_exists( 'ak_popularity_contest' ) ) {
			array_push( $providers, 'GoogleSitemapGeneratorPrioByPopularityContestProvider' );
		}
		return $providers;
	}

	/**
	 * Validates all given Priority Providers by checking them for required methods and existence
	 *
	 * @since 3.0
	 */
	private function validate_prio_providers() {
		$valid_providers = array();
		$len             = count( $this->prio_providers );
		for ( $i = 0; $i < $len; $i++ ) {
			if ( class_exists( $this->prio_providers[ $i ] ) ) {
				if ( class_implements( $this->prio_providers[ $i ], 'Google_Sitemap_Generator_Prio_Provider_Base' ) ) {
					array_push( $valid_providers, $this->prio_providers[ $i ] );
				}
			}
		}
		$this->prio_providers = $valid_providers;

		if ( ! $this->get_option( 'b_prio_provider' ) ) {
			if ( ! in_array( $this->get_option( 'b_prio_provider' ), $this->prio_providers, true ) ) {
				$this->set_option( 'b_prio_provider', '' );
			}
		}
	}


	/*************************************** COMMENT HANDLING FOR PRIO. PROVIDERS ***************************************/

	/**
	 * Retrieves the number of comments of a post in a asso. array
	 * The key is the post_id, the value the number of comments
	 *
	 * @since 3.0
	 * @return array An array with post_ids and their comment count
	 */
	public function get_comments() {
		// @var $wpdb wpdb .
		global $wpdb;
		$comments = array();

		// Query comments and add them into the array .
		$comment_res = $wpdb->get_results( 'SELECT `comment_post_ID` as `post_id`, COUNT( comment_ID ) as `comment_count` FROM `' . $wpdb->comments . '` WHERE `comment_approved`=\'1\' GROUP BY `comment_post_ID`' ); // db call ok; no-cache ok.
		if ( $comment_res ) {
			foreach ( $comment_res as $comment ) {
				$comments[ $comment->post_id ] = $comment->comment_count;
			}
		}
		return $comments;
	}

	/**
	 * Calculates the full number of comments from an sm_getComments() generated array
	 *
	 * @since 3.0
	 * @param object $comments array The Array with posts and c0mment count .
	 * @see sm_getComments
	 * @return int The full number of comments
	 */
	public function get_comment_count( $comments ) {
		$comment_count = 0;
		foreach ( $comments as $k => $v ) {
			$comment_count += $v;
		}
		return $comment_count;
	}


	/*************************************** OPTION HANDLING ***************************************/

	/**
	 * Sets up the default configuration
	 *
	 * @since 3.0
	 */
	public function init_options() {

		$this->options                       = array();
		$this->options['sm_b_prio_provider'] = 'GoogleSitemapGeneratorPrioByCountProvider'; // Provider for automatic priority calculation .
		$this->options['sm_b_ping']          = true; // Auto ping Google .
		$this->options['sm_b_stats']         = false; // Send anonymous stats .
		$this->options['sm_b_autozip']       = true; // Try to gzip the output .
		$this->options['sm_b_memory']        = ''; // Set Memory Limit (e.g. 16M) .
		$this->options['sm_b_time']          = -1; // Set time limit in seconds, 0 for unlimited, -1 for disabled .
		$this->options['sm_b_style_default'] = true; // Use default style .
		$this->options['sm_b_style']         = ''; // Include a stylesheet in the XML .
		$this->options['sm_b_baseurl']       = ''; // The base URL of the sitemap .
		$this->options['sm_b_robots']        = true; // Add sitemap location to WordPress' virtual robots.txt file .
		$this->options['sm_b_html']          = true; // Include a link to a html version of the sitemap in the XML sitemap .
		$this->options['sm_b_exclude']       = array(); // List of post / page IDs to exclude .
		$this->options['sm_b_exclude_cats']  = array(); // List of post / page IDs to exclude .

		$this->options['sm_in_home']        = true; // Include homepage .
		$this->options['sm_in_posts']       = true; // Include posts .
		$this->options['sm_in_posts_sub']   = false; // Include post pages (<!--nextpage--> tag) .
		$this->options['sm_in_pages']       = true; // Include static pages .
		$this->options['sm_in_cats']        = false; // Include categories .
		$this->options['sm_product_tags']   = true; // Hide product tags in sitemap .
		$this->options['sm_in_product_cat'] = true; // Include product categories .
		$this->options['sm_in_arch']        = false; // Include archives .
		$this->options['sm_in_auth']        = false; // Include author pages .
		$this->options['sm_in_tags']        = false; // Include tag pages .
		$this->options['sm_in_tax']         = array(); // Include additional taxonomies .
		$this->options['sm_in_customtypes'] = array(); // Include custom post types .
		$this->options['sm_in_lastmod']     = true; // Include the last modification date .
		$this->options['sm_b_sitemap_name'] = 'sitemap'; // Name of custom sitemap.
		$this->options['sm_cf_home']        = 'daily'; // Change frequency of the homepage .
		$this->options['sm_cf_posts']       = 'monthly'; // Change frequency of posts .
		$this->options['sm_cf_pages']       = 'weekly'; // Change frequency of static pages .
		$this->options['sm_cf_cats']        = 'weekly'; // Change frequency of categories .
		$this->options['sm_cf_product_cat'] = 'weekly'; // Change frequency of categories .
		$this->options['sm_cf_auth']        = 'weekly'; // Change frequency of author pages .
		$this->options['sm_cf_arch_curr']   = 'daily'; // Change frequency of the current archive (this month) .
		$this->options['sm_cf_arch_old']    = 'yearly'; // Change frequency of older archives .
		$this->options['sm_cf_tags']        = 'weekly'; // Change frequency of tags .

		$this->options['sm_pr_home']        = 1.0; // Priority of the homepage .
		$this->options['sm_pr_posts']       = 0.6; // Priority of posts (if auto prio is disabled) .
		$this->options['sm_pr_posts_min']   = 0.2; // Minimum Priority of posts, even if autocalc is enabled .
		$this->options['sm_pr_pages']       = 0.6; // Priority of static pages .
		$this->options['sm_pr_cats']        = 0.3; // Priority of categories .
		$this->options['sm_pr_product_cat'] = 0.3; // Priority of categories .
		$this->options['sm_pr_arch']        = 0.3; // Priority of archives .
		$this->options['sm_pr_auth']        = 0.3; // Priority of author pages .
		$this->options['sm_pr_tags']        = 0.3; // Priority of tags .

		$this->options['sm_i_donated']           = false; // Did you donate? Thank you! :) .
		$this->options['sm_i_hide_donated']      = false; // And hide the thank you.. .
		$this->options['sm_i_install_date']      = time(); // The installation date .
		$this->options['sm_i_hide_survey']       = false; // Hide the survey note .
		$this->options['sm_i_hide_note']         = false; // Hide the note which appears after 30 days .
		$this->options['sm_i_hide_works']        = false; // Hide the 'works?' message which appears after 15 days .
		$this->options['sm_i_hide_donors']       = false; // Hide the list of donations .
		$this->options['sm_i_hash']              = substr( sha1( sha1( get_bloginfo( 'url' ) ) ), 0, 20 ); // Partial hash for GA stats, NOT identifiable! .
		$this->options['sm_i_tid']               = '';
		$this->options['sm_i_lastping']          = 0; // When was the last ping .
		$this->options['sm_i_supportfeed']       = true; // shows the support feed .
		$this->options['sm_i_supportfeed_cache'] = 0; // Last refresh of support feed .
		$this->options['sm_links_page']          = 10; // Link per page support with default value 10. .
	}

	/**
	 * Loads the configuration from the database
	 *
	 * @since 3.0
	 */
	private function load_options() {

		if ( $this->options_loaded ) {
			return;
		}

		$this->init_options();

		// First init default values, then overwrite it with stored values so we can add default
		// values with an update which get stored by the next edit.
		$stored_options = get_option( 'sm_options' );

		if ( $stored_options && is_array( $stored_options ) ) {
			foreach ( $stored_options as $k => $v ) {
				if ( array_key_exists( $k, $this->options ) ) {
					$this->options[ $k ] = $v;
				}
			}
		} else {
			update_option( 'sm_options', $this->options ); // First time use, store default values .
		}

		$this->options_loaded = true;
	}

	/**
	 * Returns the option value for the given key
	 *
	 * @since 3.0
	 * @param string $key string The Configuration Key .
	 * @return mixed The value
	 */
	public function get_option( $key ) {
		$key = 'sm_' . $key;
		if ( array_key_exists( $key, $this->options ) ) {
			return $this->options[ $key ];
		} else {
			return null;
		}
	}
	/**
	 * Get options .
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Sets an option to a new value
	 *
	 * @since 3.0
	 * @param string $key string The configuration key .
	 * @param string $value mixed The new object .
	 */
	public function set_option( $key, $value ) {
		if ( 0 !== strpos( $key, 'sm_' ) ) {
			$key = 'sm_' . $key;
		}

		$this->options[ $key ] = $value;
	}

	/**
	 * Saves the options back to the database
	 *
	 * @since 3.0
	 * @return bool true on success
	 */
	public function save_options() {
		$oldvalue = get_option( 'sm_options' );
		if ( $oldvalue === $this->options ) {
			return true;
		} else {
			return update_option( 'sm_options', $this->options );
		}
	}

	/**
	 * Returns the additional pages
	 *
	 * @since 4.0
	 * @return GoogleSitemapGeneratorPage[]
	 */
	public function get_pages() {
		return $this->pages;
	}

	/**
	 * Returns the additional pages
	 *
	 * @since 4.0
	 * @param array $pages .
	 */
	public function set_pages( array $pages ) {
		$this->pages = $pages;
	}

	/**
	 * Loads the stored pages from the database
	 *
	 * @since 3.0
	 */
	private function load_pages() {
		// @var $wpdb wpdb .
		global $wpdb;

		$needs_update = false;

		$pages_string = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'sm_cpages'" ); // db call ok; no-cache ok.

		// Class sm_page was renamed with 3.0 -> rename it in serialized value for compatibility .
		if ( ! empty( $pages_string ) && strpos( $pages_string, 'sm_page' ) !== false ) {
			$pages_string = str_replace( 'O:7:\'sm_page\'', 'O:26:\'GoogleSitemapGeneratorPage\'', $pages_string );
			$needs_update = true;
		}

		if ( ! empty( $pages_string ) ) {
			$storedpages = unserialize( $pages_string );
			$this->pages = $storedpages;
		} else {
			$this->pages = array();
		}

		if ( $needs_update ) {
			$this->save_pages();
		}
	}

	/**
	 * Saved the additional pages back to the database
	 *
	 * @since 3.0
	 * @return true on success
	 */
	public function save_pages() {
		$oldvalue = get_option( 'sm_cpages' );
		if ( $oldvalue === $this->pages ) {
			return true;
		} else {
			delete_option( 'sm_cpages' );
			// Add the option, Note the autoload=false because when the autoload happens, our class GoogleSitemapGeneratorPage doesn't exist .
			add_option( 'sm_cpages', $this->pages, '', 'no' );
			return true;
		}
	}


	/*************************************** URL AND PATH FUNCTIONS ***************************************/

	/**
	 * Returns the URL to the directory where the plugin file is located
	 *
	 * @since 3.0b5
	 * @return string The URL to the plugin directory
	 */
	public function get_plugin_url() {

		$url = trailingslashit( plugins_url( '', __FILE__ ) );

		return $url;
	}

	/**
	 * Returns the path to the directory where the plugin file is located
	 *
	 * @since 3.0b5
	 * @return string The path to the plugin directory
	 */
	public function get_plugin_path() {
		$path = dirname( __FILE__ );
		return trailingslashit( str_replace( '\\', '/', $path ) );
	}

	/**
	 * Returns the URL to default XSLT style if it exists
	 *
	 * @since 3.0b5
	 * @return string The URL to the default stylesheet, empty string if not available.
	 */
	public function get_default_style() {
		$p = $this->get_plugin_path();
		if ( file_exists( $p . 'sitemap.xsl' ) ) {
			$url = $this->get_plugin_url();
			// If called over the admin area using HTTPS, the stylesheet would also be https url, even if the site frontend is not.
			if ( substr( get_bloginfo( 'url' ), 0, 5 ) !== 'https' && substr( $url, 0, 5 ) === 'https' ) {
				$url = 'http' . substr( $url, 5 );
			}
			if ( isset( $_SERVER['HTTP_HOST'] ) ) {
				$host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			}
			$url = $this->get_xsl_url( $url, $host );
			return $url . 'sitemap.xsl';
		}
		return '';
	}

	/**
	 * Returns of Permalinks are used
	 *
	 * @return bool
	 */
	public function is_using_permalinks() {
		// @var $wp_rewrite WP_Rewrite .
		global $wp_rewrite;

		return $wp_rewrite->using_mod_rewrite_permalinks();
	}

	/**
	 * Registers the plugin specific rewrite rules
	 *
	 * Combined: sitemap(-+([a-zA-Z0-9_-]+))?\.(xml|html)(.gz)?$
	 *
	 * @since 4.0
	 * @param string $wp_rules Array of existing rewrite rules.
	 * @return Array An array containing the new rewrite rules.
	 */
	public static function add_rewrite_rules( $wp_rules ) {
		$sm_sitemap_name = $GLOBALS['sm_instance']->get_option( 'b_sitemap_name' );
		$sm_rules        = array(
			$sm_sitemap_name . '(-+([a-zA-Z0-9_-]+))?\.xml$' => 'index.php?xml_sitemap=params=$matches[2]',
			$sm_sitemap_name . '(-+([a-zA-Z0-9_-]+))?\.xml\.gz$' => 'index.php?xml_sitemap=params=$matches[2];zip=true',
			$sm_sitemap_name . '(-+([a-zA-Z0-9_-]+))?\.html$' => 'index.php?xml_sitemap=params=$matches[2];html=true',
			$sm_sitemap_name . '(-+([a-zA-Z0-9_-]+))?\.html.gz$' => 'index.php?xml_sitemap=params=$matches[2];html=true;zip=true',
		);
		return array_merge( $sm_rules, $wp_rules );
	}

	/**
	 * Adds the filters for wp rewrite rule adding
	 *
	 * @since 4.0
	 * @uses add_filter()
	 */
	public static function setup_rewrite_hooks() {
		add_filter( 'rewrite_rules_array', array( __CLASS__, 'add_rewrite_rules' ), 1, 1 );
	}
	/**
	 * Removes the filters for wp rewrite rule adding
	 *
	 * @since 4.0
	 * @uses remove_filter()
	 */
	public static function remove_rewrite_hooks() {
		add_filter( 'rewrite_rules_array', array( __CLASS__, 'remove_rewrite_rules' ), 1, 1 );
	}

	/**
	 * Deregisters the plugin specific rewrite rules
	 *
	 * Combined: sitemap(-+([a-zA-Z0-9_-]+))?\.(xml|html)(.gz)?$
	 *
	 * @since 4.0
	 * @param array $wp_rules Array of existing rewrite rules.
	 * @return Array An array containing the new rewrite rules
	 */
	public static function remove_rewrite_rules( $wp_rules ) {
		$sm_rules = array(
			'sitemap(-+([a-zA-Z0-9_-]+))?\.xml$'     => 'index.php?xml_sitemap=params=$matches[2]',
			'sitemap(-+([a-zA-Z0-9_-]+))?\.xml\.gz$' => 'index.php?xml_sitemap=params=$matches[2];zip=true',
			'sitemap(-+([a-zA-Z0-9_-]+))?\.html$'    => 'index.php?xml_sitemap=params=$matches[2];html=true',
			'sitemap(-+([a-zA-Z0-9_-]+))?\.html.gz$' => 'index.php?xml_sitemap=params=$matches[2];html=true;zip=true',
		);
		foreach ( $wp_rules as $key => $value ) {
			if ( array_key_exists( $key, $sm_rules ) ) {
				unset( $wp_rules[ $key ] );
			}
		}
		return $wp_rules;
	}

	/**
	 * Returns the URL for the sitemap file
	 *
	 * @since 3.0
	 *
	 * @param string $type .
	 * @param string $params .
	 * @param array  $build_options .
	 * @return string The URL to the Sitemap file
	 */
	public function get_xml_url( $type = '', $params = '', $build_options = array() ) {

		$pl      = $this->is_using_permalinks();
		$options = '';
		if ( ! empty( $type ) ) {
			$options .= $type;
			if ( ! empty( $params ) ) {
				$options .= '-' . $params;
			}
		}

		$build_options = array_merge( $this->build_options, $build_options );

		$html = ( isset( $build_options['html'] ) ? $build_options['html'] : false );
		$zip  = ( isset( $build_options['zip'] ) ? $build_options['zip'] : false );

		$base_url = get_bloginfo( 'url' );

		// Manual override for root URL .
		$base_url_settings = $this->get_option( 'b_baseurl' );
		$sm_sitemap_name   = $this->get_option( 'b_sitemap_name' );
		if ( ! empty( $base_url_settings ) ) {
			$base_url = $base_url_settings;
		} elseif ( defined( 'SM_BASE_URL' ) && SM_BASE_URL ) {
			$base_url = SM_BASE_URL;
		}
		global $wp_rewrite;
		delete_option( 'sm_rewrite_done' );
		wp_clear_scheduled_hook( 'sm_ping_daily' );
		self::remove_rewrite_hooks();
		$wp_rewrite->flush_rules( false );
		self::setup_rewrite_hooks();
		GoogleSitemapGeneratorLoader::activate_rewrite();
		if ( $pl ) {
			return trailingslashit( $base_url ) . ( '' === $sm_sitemap_name ? 'sitemap' : $sm_sitemap_name ) . ( $options ? '-' . $options : '' ) . ( $html
				? '.html' : '.xml' ) . ( $zip ? '.gz' : '' );
		} else {
			return trailingslashit( $base_url ) . 'index.php?xml_sitemap=params=' . $options . ( $html
				? ';html=true' : '' ) . ( $zip ? ';zip=true' : '' );
		}
	}

	/**
	 * Returns if there is still an old sitemap file in the site directory
	 *
	 * @return Boolean True if a sitemap file still exists
	 */
	public function old_file_exists() {
		$sm_sitemap_name = $this->get_option( 'b_sitemap_name' );
		$path            = trailingslashit( get_home_path() );
		return ( file_exists( $path . $sm_sitemap_name . '.xml' ) || file_exists( $path . 'sitemap.xml.gz' ) );
	}

	/**
	 * Renames old sitemap files in the site directory from previous versions of this plugin
	 *
	 * @return bool True on success
	 */
	public function delete_old_files() {
		$path = trailingslashit( get_home_path() );

		$res = true;
		$f   = $path . 'sitemap.xml';
		if ( file_exists( $f ) ) {
			if ( ! rename( $f, $path . 'sitemap.backup.xml' ) ) {
				$res = false;
			}
		}
		$f = $path . 'sitemap.xml.gz';
		if ( file_exists( $f ) ) {
			if ( ! rename( $f, $path . 'sitemap.backup.xml.gz' ) ) {
				$res = false;
			}
		}

		return $res;
	}


	/*************************************** SITEMAP SIMULATION ***************************************/

	/**
	 * Simulates the building of the sitemap index file.
	 *
	 * @see GoogleSitemapGenerator::simulate_sitemap
	 * @since 4.0
	 * @return array The data of the sitemap index file
	 */
	public function simulate_index() {

		$this->sim_mode = true;

		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-googlesitemapgeneratorstandardbuilder.php';
		do_action( 'sm_build_index', $this );

		$this->sim_mode = false;

		$r = $this->sim_data['sitemaps'];

		$this->clear_sim_data( 'sitemaps' );

		return $r;
	}

	/**
	 * Simulates the building of the sitemap file.
	 *
	 * @see GoogleSitemapGenerator::simulate_index
	 * @since 4.0
	 * @param string $type string The type of the sitemap .
	 * @param string $params string Additional parameters for this type .
	 * @return array The data of the sitemap file
	 */
	public function simulate_sitemap( $type, $params ) {
		$this->sim_mode = true;

		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-googlesitemapgeneratorstandardbuilder.php';
		do_action( 'sm_build_content', $this, $type, $params );

		$this->sim_mode = false;

		$r = $this->sim_data['content'];

		$this->clear_sim_data( 'content' );

		return $r;
	}

	/**
	 * Clears the data of the simulation
	 *
	 * @param string $what Defines what to clear, either both, sitemaps or content .
	 * @see GoogleSitemapGenerator::simulate_index
	 * @see GoogleSitemapGenerator::simulate_sitemap
	 * @since 4.0
	 */
	public function clear_sim_data( $what ) {
		if ( 'both' === $what || 'sitemaps' === $what ) {
			$this->sim_data['sitemaps'] = array();
		}

		if ( 'both' === $what || 'content' === $what ) {
			$this->sim_data['content'] = array();
		}
	}

	/**
	 * Returns the first caller outside of this __CLASS__
	 *
	 * @param array $trace The backtrace .
	 * @return array The caller information
	 */
	private function get_external_backtrace( $trace ) {
		$caller = null;
		foreach ( $trace as $b ) {
			if ( __CLASS__ !== $b['class'] ) {
				$caller = $b;
				break;
			}
		}
		return $caller;
	}


	/*************************************** SITEMAP BUILDING ***************************************/

	/**
	 * Shows the sitemap. Main entry point from HTTP
	 *
	 * @param string $options Options for the sitemap. What type, what parameters.
	 * @since 4.0
	 */
	public function show_sitemap( $options ) {

		$start_time    = microtime( true );
		$start_queries = $GLOBALS['wpdb']->num_queries;
		$start_memory  = memory_get_peak_usage( true );

		// Raise memory and time limits .
		if ( $this->get_option( 'b_memory' ) !== '' ) {
			wp_raise_memory_limit( $this->get_option( 'b_memory' ) );

		}

		if ( $this->get_option( 'b_time' ) !== -1 ) {
			set_time_limit( $this->get_option( 'b_time' ) );
		}

		do_action( 'sm_init', $this );

		$this->is_active = true;

		$parsed_options = array();

		$options = explode( ';', $options );
		foreach ( $options as $k ) {
			$kv                       = explode( '=', $k );
			$parsed_options[ $kv[0] ] = $kv[1];
		}

		$options = $parsed_options;

		$this->build_options = $options;

		// Do not index the actual XML pages, only process them.
		// This avoids that the XML sitemaps show up in the search results.
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex', true, 200 );
		}

		$this->initate();

		$html = ( isset( $options['html'] ) ? $options['html'] : false ) && $this->is_xsl_enabled();
		if ( $html && ! $this->get_option( 'b_html' ) ) {
			$GLOBALS['wp_query']->is_404 = true;
			return;
		}

		// Don't zip if anything happened before which could break the output or if the client does not support gzip.
		// If there are already other output filters, there might be some content on another
		// filter level already, which we can't detect. Zipping then would lead to invalid content.
		$pack = ( isset( $options['zip'] ) ? $options['zip'] : $this->get_option( 'b_autozip' ) );
		if (
			empty( $_SERVER['HTTP_ACCEPT_ENCODING'] ) // No encoding support.
			|| strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ), 'gzip' ) === false // or no gzip.
			|| ! $this->is_gzip_enabled() // No PHP gzip support.
			|| headers_sent() // Headers already sent.
			|| ob_get_contents() // there was already some output....
			|| in_array( 'ob_gzhandler', ob_list_handlers(), true ) // Some other plugin (or PHP) is already gzipping.
			|| $this->get_php_ini_boolean( ini_get( 'zlib.output_compression' ) ) // Zlib compression in php.ini enabled.
			|| ob_get_level() > ( ! $this->get_php_ini_boolean( ini_get( 'output_buffering' ) ) ? 0 : 1 ) // Another output buffer (beside of the default one) is already active.
			|| ( isset( $_SERVER['HTTP_X_VARNISH'] ) && is_numeric( $_SERVER['HTTP_X_VARNISH'] ) ) // Behind a Varnish proxy.
		) {
			$pack = false;
		}

		$packed = false;

		if ( $pack ) {
			$packed = ob_start( 'ob_gzhandler' );
		}

		$builders = array( 'class-googlesitemapgeneratorstandardbuilder.php' );
		foreach ( $builders as $b ) {
			$f = trailingslashit( dirname( __FILE__ ) ) . $b;
			if ( file_exists( $f ) ) {
				require_once $f;
			}
		}

		if ( $html ) {
			ob_start();
		} else {
			header( 'Content-Type: text/xml; charset=utf-8' );
		}

		if ( empty( $options['params'] ) || 'index' === $options['params'] ) {

			$this->build_sitemap_header( 'index' );

			do_action( 'sm_build_index', $this );

			$this->build_sitemap_footer( 'index' );
			$this->add_end_commend( $start_time, $start_queries, $start_memory );
		} else {
			$all_params = $options['params'];
			$type       = null;
			$params     = null;
			if ( strpos( $all_params, '-' ) !== false ) {
				$type   = substr( $all_params, 0, strpos( $all_params, '-' ) );
				$params = substr( $all_params, strpos( $all_params, '-' ) + 1 );
			} else {
				$type = $all_params;
			}

			$this->build_sitemap_header( 'sitemap' );

			do_action( 'sm_build_content', $this, $type, $params );

			$this->build_sitemap_footer( 'sitemap' );

			$this->add_end_commend( $start_time, $start_queries, $start_memory );
		}

		if ( $html ) {
			$xml_source = ob_get_clean();

			// Load the XML source.
			$xml = new DOMDocument();
			$xml->loadXML( $xml_source );

			$xsl = new DOMDocument();
			$xsl->load( $this->get_plugin_path() . 'sitemap.xsl' );

			// Configure the transformer.
			$proc = new XSLTProcessor();
			$proc->importStyleSheet( $xsl ); // Attach the xsl rules.

			$dom_tran_obj = $proc->transformToDoc( $xml );

			// This will also output doctype and comments at top level.
			// phpcs:disable
			global $allowedposttags;
			$allowed_atts = array(
				'align'      => array(),
				'class'      => array(),
				'type'       => array(),
				'id'         => array(),
				'dir'        => array(),
				'lang'       => array(),
				'style'      => array(),
				'xml:lang'   => array(),
				'src'        => array(),
				'alt'        => array(),
				'href'       => array(),
				'rel'        => array(),
				'rev'        => array(),
				'target'     => array(),
				'novalidate' => array(),
				'type'       => array(),
				'value'      => array(),
				'name'       => array(),
				'tabindex'   => array(),
				'action'     => array(),
				'method'     => array(),
				'for'        => array(),
				'width'      => array(),
				'height'     => array(),
				'data'       => array(),
				'title'      => array(),
			);
			$allowedposttags['form']     = $allowed_atts;
			$allowedposttags['label']    = $allowed_atts;
			$allowedposttags['input']    = $allowed_atts;
			$allowedposttags['textarea'] = $allowed_atts;
			$allowedposttags['iframe']   = $allowed_atts;
			$allowedposttags['script']   = $allowed_atts;
			$allowedposttags['style']    = $allowed_atts;
			$allowedposttags['strong']   = $allowed_atts;
			$allowedposttags['small']    = $allowed_atts;
			$allowedposttags['table']    = $allowed_atts;
			$allowedposttags['span']     = $allowed_atts;
			$allowedposttags['abbr']     = $allowed_atts;
			$allowedposttags['code']     = $allowed_atts;
			$allowedposttags['pre']      = $allowed_atts;
			$allowedposttags['div']      = $allowed_atts;
			$allowedposttags['img']      = $allowed_atts;
			$allowedposttags['h1']       = $allowed_atts;
			$allowedposttags['h2']       = $allowed_atts;
			$allowedposttags['h3']       = $allowed_atts;
			$allowedposttags['h4']       = $allowed_atts;
			$allowedposttags['h5']       = $allowed_atts;
			$allowedposttags['h6']       = $allowed_atts;
			$allowedposttags['ol']       = $allowed_atts;
			$allowedposttags['ul']       = $allowed_atts;
			$allowedposttags['li']       = $allowed_atts;
			$allowedposttags['em']       = $allowed_atts;
			$allowedposttags['hr']       = $allowed_atts;
			$allowedposttags['br']       = $allowed_atts;
			$allowedposttags['tr']       = $allowed_atts;
			$allowedposttags['td']       = $allowed_atts;
			$allowedposttags['p']        = $allowed_atts;
			$allowedposttags['a']        = $allowed_atts;
			$allowedposttags['b']        = $allowed_atts;
			$allowedposttags['i']        = $allowed_atts;
			foreach ( $dom_tran_obj->childNodes as $node ) {
			// phpcs:enable
				echo wp_kses( $dom_tran_obj->saveXML( $node ), $allowedposttags ) . "\n";
			}
		}

		if ( $packed ) {
			ob_end_flush();
		}
		$this->is_active = false;
		exit;
	}

	/**
	 * Generates the header for the sitemap with XML declarations, stylesheet and so on.
	 *
	 * @since 4.0
	 * @param string $format The format, either sitemap for a sitemap or index for the sitemap index .
	 */
	private function build_sitemap_header( $format ) {

		if ( ! in_array( $format, array( 'sitemap', 'index' ), true ) ) {
			$format = 'sitemap';
		}
		$this->add_element( new GoogleSitemapGeneratorXmlEntry( '<?xml version=\'1.0\' encoding=\'UTF-8\'?>' ) );
		$style_sheet = ( $this->get_default_style() && $this->get_option( 'b_style_default' ) === true
				? $this->get_default_style() : $this->get_option( 'b_style' ) );

		if ( ! empty( $style_sheet ) ) {
			$this->add_element( new GoogleSitemapGeneratorXmlEntry( '<' . '?xml-stylesheet type=\'text/xsl\' href=\'' . esc_url( $style_sheet ) . '\'?>' ) );
		}
		$this->add_element( new GoogleSitemapGeneratorDebugEntry( 'sitemap-generator-url=\'https://auctollo.com\' sitemap-generator-version=\'' . $this->get_version() . '\'' ) );
		$this->add_element( new GoogleSitemapGeneratorDebugEntry( 'generated-on=\'' . gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) . '\'' ) );

		switch ( $format ) {
			case 'sitemap':
				$this->add_element( new GoogleSitemapGeneratorXmlEntry( '<urlset xmlns:xsi=\'http://www.w3.org/2001/XMLSchema-instance\' xsi:schemaLocation=\'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\' xmlns=\'http://www.sitemaps.org/schemas/sitemap/0.9\'>' ) );
				break;
			case 'index':
				$this->add_element( new GoogleSitemapGeneratorXmlEntry( '<sitemapindex xmlns:xsi=\'http://www.w3.org/2001/XMLSchema-instance\' xsi:schemaLocation=\'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd\' xmlns=\'http://www.sitemaps.org/schemas/sitemap/0.9\'>' ) );
				break;
		}
	}

	/**
	 * Generates the footer for the sitemap with XML ending tag
	 *
	 * @since 4.0
	 * @param string $format The format, either sitemap for a sitemap or index for the sitemap index.
	 */
	private function build_sitemap_footer( $format ) {
		if ( ! in_array( $format, array( 'sitemap', 'index' ), true ) ) {
			$format = 'sitemap';
		}
		switch ( $format ) {
			case 'sitemap':
				$this->add_element( new GoogleSitemapGeneratorXmlEntry( '</urlset>' ) );
				break;
			case 'index':
				$this->add_element( new GoogleSitemapGeneratorXmlEntry( '</sitemapindex>' ) );
				break;
		}
	}

	/**
	 * Adds information about time and memory usage to the sitemap
	 *
	 * @since 4.0
	 * @param float $start_time The microtime of the start .
	 * @param int   $start_queries .
	 * @param int   $start_memory .
	 */
	private function add_end_commend( $start_time, $start_queries = 0, $start_memory = 0 ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			echo '<!-- ';
			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
				echo '<pre>';
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
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
			echo ' --> ';
		}
		$end_time = microtime( true );
		$end_time = round( $end_time - $start_time, 2 );
		$this->add_element( new GoogleSitemapGeneratorDebugEntry( 'Request ID: ' . md5( microtime() ) . '; Queries for sitemap: ' . ( $GLOBALS['wpdb']->num_queries - $start_queries ) . '; Total queries: ' . $GLOBALS['wpdb']->num_queries . '; Seconds: $end_time; Memory for sitemap: ' . ( ( memory_get_peak_usage( true ) - $start_memory ) / 1024 / 1024 ) . 'MB; Total memory: ' . ( memory_get_peak_usage( true ) / 1024 / 1024 ) . 'MB' ) );
	}

	/**
	 * Adds the sitemap to the virtual robots.txt file
	 * This function is executed by WordPress with the do_robots hook
	 *
	 * @since 3.1.2
	 */
	public function do_robots() {
		$this->initate();
		if ( $this->get_option( 'b_robots' ) === true ) {

			$sm_url = $this->get_xml_url();

			echo "\nSitemap: " . esc_url( $sm_url ) . "\n";
		}
	}


	/*************************************** SITEMAP CONTENT BUILDING ***************************************/

	/**
	 * Outputs an element in the sitemap
	 *
	 * @since 3.0
	 * @param object $page GoogleSitemapGeneratorXmlEntry The element .
	 */
	public function add_element( $page ) {

		if ( empty( $page ) ) {
			return;
		}
		// phpcs:disable
		echo $page->render();
		// phpcs:enable
	}

	/**
	 * Adds a url to the sitemap. You can use this method or call add_element directly.
	 *
	 * @since 3.0
	 * @param int    $loc string The location (url) of the page .
	 * @param int    $last_mod int The last Modification time as a UNIX timestamp .
	 * @param string $change_freq string The change frequenty of the page, Valid values are 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly' and 'never'. .
	 * @param float  $priority float The priority of the page, between 0.0 and 1.0 .
	 * @param int    $post_id int The post ID in case this is a post or page .
	 * @see add_element
	 */
	public function add_url( $loc, $last_mod = 0, $change_freq = 'monthly', $priority = 0.5, $post_id = 0 ) {
		// Strip out the last modification time if activated .
		if ( $this->get_option( 'in_lastmod' ) === false ) {
			$last_mod = 0;
		}
		$page = new GoogleSitemapGeneratorPage( $loc, $priority, $change_freq, $last_mod, $post_id );

		do_action( 'sm_addurl', $page );

		if ( $this->sim_mode ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			$caller = $this->get_external_backtrace( debug_backtrace() );
			// phpcs:enable
			$this->sim_data['content'][] = array(
				'data'   => $page,
				'caller' => $caller,
			);
		} else {
			$this->add_element( $page );
		}
	}

	/**
	 * Add a sitemap entry to the index file
	 *
	 * @param string $type .
	 * @param string $params .
	 * @param int    $last_mod .
	 */
	public function add_sitemap( $type, $params = '', $last_mod = 0 ) {

		$url = $this->get_xml_url( $type, $params );

		$sitemap = new GoogleSitemapGeneratorSitemapEntry( $url, $last_mod );

		do_action( 'sm_addsitemap', $sitemap );

		if ( $this->sim_mode ) {
			  // phpcs:disable WordPress.PHP.DevelopmentFunctions
			$caller = $this->get_external_backtrace( debug_backtrace() );
			  // phpcs:enable
			$this->sim_data['sitemaps'][] = array(
				'data'   => $sitemap,
				'type'   => $type,
				'params' => $params,
				'caller' => $caller,
			);
		} else {
			$this->add_element( $sitemap );
		}
	}


	/*************************************** PINGS ***************************************/

	/**
	 * Sends the pings to the search engines
	 *
	 * @return GoogleSitemapGeneratorStatus The status object
	 */
	public function send_ping() {

		$this->load_options();

		$ping_url = $this->get_xml_url();

		$result = $this->execute_ping( $ping_url, true );

		$post_id = get_transient( 'sm_ping_post_id' );

		if ( $post_id ) {

			require_once trailingslashit( dirname( __FILE__ ) ) . 'class-googlesitemapgeneratorstandardbuilder.php';

			$urls = array();

			$urls = apply_filters( 'sm_sitemap_for_post', $urls, $this, $post_id );
			if ( is_array( $urls ) && count( $urls ) > 0 ) {
				foreach ( $urls as $url ) {
					$this->execute_ping( $url, false );
				}
			}

			delete_transient( 'sm_ping_post_id' );
		}

		return $result;
	}


	/**
	 * Execute Ping
	 *
	 * @param string $ping_url string The Sitemap URL to ping .
	 * @param bool   $update_status If the global ping status should be updated .
	 *
	 * @return \GoogleSitemapGeneratorStatus
	 */
	protected function execute_ping( $ping_url, $update_status = true ) {

		$status = new GoogleSitemapGeneratorStatus( $update_status );

		if ( $ping_url ) {
			$pings = array();

			if ( $this->get_option( 'b_ping' ) ) {
				$pings['google'] = array(
					'name'  => 'Google',
					'url'   => 'http://www.google.com/webmasters/sitemaps/ping?sitemap=%s',
					'check' => 'successfully',
				);
			}

			foreach ( $pings as $service_id => $service ) {
				$url = str_replace( '%s', rawurlencode( $ping_url ), $service['url'] );
				$status->start_ping( $service_id, $url, $service['name'] );

				$pingres = $this->remote_open( $url );

				if ( null === $pingres || false === $pingres || false === strpos( $pingres, $service['check'] ) ) {
					$status->end_ping( $service_id, false );
					// phpcs:disable WordPress.PHP.DevelopmentFunctions
					trigger_error( 'Failed to ping $service_id: ' . esc_html( htmlspecialchars( wp_strip_all_tags( $pingres ) ) ), E_USER_NOTICE );
					// phpcs:enable
				} else {
					$status->end_ping( $service_id, true );
				}
			}

			$this->set_option( 'i_lastping', time() );
			$this->save_options();
		}

		$status->end();

		return $status;
	}

	/**
	 * Tries to ping a specific service showing as much as debug output as possible
	 *
	 * @since 4.1
	 * @return array
	 */
	public function send_ping_all() {

		$this->load_options();

		$sitemaps = $this->simulate_index();

		$urls = array();

		$urls[] = $this->get_xml_url();

		foreach ( $sitemaps as $sitemap ) {

			// @var $s GoogleSitemapGeneratorSitemapEntry .
			$s = $sitemap['data'];

			$urls[] = $s->get_url();
		}

		$results = array();

		$first = true;

		foreach ( $urls as $url ) {
			$status    = $this->execute_ping( $url, $first );
			$results[] = array(
				'sitemap' => $url,
				'status'  => $status,
			);
			$first     = false;
		}
		return $results;
	}

	/**
	 * Tries to ping a specific service showing as much as debug output as possible
	 *
	 * @since 3.1.9
	 * @return null
	 */
	public function show_ping_result() {

		check_admin_referer( 'sitemap' );

		if ( ! current_user_can( 'administrator' ) ) {
			echo '<p>Please log in as admin</p>';
			return;
		}

		$service = ! empty( $_GET['sm_ping_service'] ) ? sanitize_text_field( wp_unslash( $_GET['sm_ping_service'] ) ) : null;

		$status = GoogleSitemapGeneratorStatus::load();

		if ( ! $status ) {
			die( 'No build status yet. Write something first.' );
		}

		$url = null;

		$services = $status->get_used_ping_services();

		if ( ! in_array( $service, $services, true ) ) {
			die( 'Invalid service' );
		}

		$url = $status->get_ping_url( $service );

		if ( empty( $url ) ) {
			die( 'Invalid ping url' );
		}

		echo '<html><head><title>Ping Test</title>';
		if ( function_exists( 'wp_admin_css' ) ) {
			wp_admin_css( 'css/global', true );
		}
		echo '</head><body><h1>Ping Test</h1>';

		echo '<p>Trying to ping: <a href=\'' . esc_url( $url ) . '\'>' . esc_html( $url ) . '</a>. The sections below should give you an idea whats going on.</p>';

		// Try to get as much as debug / error output as possible .
		$err_level = error_reporting( E_ALL );
		if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
			define( 'WP_DEBUG_DISPLAY', true );
		}

		if ( ! defined( 'WP_DEBUG' ) ) {
			define( 'WP_DEBUG', true );
		}

		echo '<h2>Errors, Warnings, Notices:</h2>';

		if ( WP_DEBUG === false ) {
			echo '<i>WP_DEBUG was set to false somewhere before. You might not see all debug information until you remove this declaration!</i><br />';
		}
		if ( ini_get( 'display_errors' ) !== 1 ) {
			echo '<i>Your display_errors setting currently prevents the plugin from showing errors here. Please check your webserver logfile instead.</i><br />';
		}

		$res = $this->remote_open( $url );

		echo '<h2>Result (text only):</h2>';

		echo wp_kses(
			$res,
			array(
				'a'  => array( 'href' => array() ),
				'p'  => array(),
				'ul' => array(),
				'ol' => array(),
				'li' => array(),
			)
		);

		echo '<h2>Result (HTML):</h2>';

		esc_html( htmlspecialchars( $res ) );

		// Revert back old values .
		// error_reporting( $err_level ); .
		echo '</body></html>';
		exit;
	}

	/**
	 * Opens a remote file using the WordPress API
	 *
	 * @since 3.0
	 * @param string $url string The URL to open .
	 * @param string $method string get or post .
	 * @param object $post_data array An array with key=>value paris .
	 * @param int    $timeout int Timeout for the request, by default 10 .
	 * @return mixed False on error, the body of the response on success
	 */
	public static function remote_open( $url, $method = 'get', $post_data = null, $timeout = 10 ) {
		$options            = array();
		$options['timeout'] = $timeout;

		if ( 'get' === $method ) {
			$response = wp_remote_get( $url, $options );
		} else {
			$response = wp_remote_post(
				$url,
				array_merge(
					$options,
					array(
						'body' => $post_data,
					)
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			$errs = $response->get_error_messages();
			$errs = htmlspecialchars( implode( '; ', $errs ) );
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			trigger_error( 'WP HTTP API Web Request failed: ' . esc_html( $errs ), E_USER_NOTICE );
			  // phpcs:enable
			return false;
		}

		return $response['body'];
	}

	/**
	 * Sends anonymous statistics (disabled by default)
	 */
	private function send_stats() {
		global $wp_version, $wpdb;
		$post_count = $wpdb->get_var( 'SELECT COUNT(*) FROM {$wpdb->posts} p WHERE p.post_status=\'publish\'' ); // db call ok; no-cache ok.

		// Send simple post count statistic to get an idea in which direction this plugin should be optimized .
		// Only a rough number is required, so we are rounding things up .
		if ( $post_count <= 5 ) {
			$post_count = 5;
		} elseif ( $post_count < 25 ) {
			$post_count = 10;
		} elseif ( $post_count < 35 ) {
			$post_count = 25;
		} elseif ( $post_count < 75 ) {
			$post_count = 50;
		} elseif ( $post_count < 125 ) {
			$post_count = 100;
		} elseif ( $post_count < 2000 ) {
			$post_count = round( $post_count / 200 ) * 200;
		} elseif ( $post_count < 10000 ) {
			$post_count = round( $post_count / 1000 ) * 1000;
		} else {
			$post_count = round( $post_count / 10000 ) * 10000;
		}

		$post_data = array(
			'v'   => 1,
			'tid' => $this->get_option( 'i_tid' ),
			'cid' => $this->get_option( 'i_hash' ),
			'aip' => 1, // Anonymize .
			't'   => 'event',
			'ec'  => 'ping',
			'ea'  => 'auto',
			'ev'  => 1,
			'cd1' => $wp_version,
			'cd2' => $this->get_version(),
			'cd3' => PHP_VERSION,
			'cd4' => $post_count,
			'ul'  => get_bloginfo( 'language' ),
		);

		$this->remote_open( 'http://www.google-analytics.com/collect', 'post', $post_data );
	}

	/**
	 * Returns the number of seconds the support feed should be cached (1 week)
	 *
	 * @return int The number of seconds
	 */
	public static function get_support_feed_cache_lifetime() {
		return 60 * 60 * 24 * 7;
	}

	/**
	 * Returns the SimplePie instance of the support feed
	 * The feed is cached for one week
	 *
	 * @return SimplePie|WP_Error
	 */
	public function get_support_feed() {

		$call_back = array( __CLASS__, 'get_support_feed_cache_lifetime' );

		// Extend cache lifetime so we don't request the feed to often .
		add_filter( 'wp_feed_cache_transient_lifetime', $call_back );
		$result = fetch_feed( SM_SUPPORTFEED_URL );
		remove_filter( 'wp_feed_cache_transient_lifetime', $call_back );

		return $result;
	}

	/**
	 * Handles daily ping
	 */
	public function send_ping_daily() {

		$this->load_options();

		$blog_update = strtotime( get_lastpostdate( 'blog' ) );
		$last_ping   = $this->get_option( 'i_lastping' );
		$yesterday   = time() - ( 60 * 60 * 24 );

		if ( $blog_update >= $yesterday && ( 0 === $last_ping || $last_ping <= $yesterday ) ) {
			$this->send_ping();
		}

		// Send statistics if enabled (disabled by default) .
		if ( $this->get_option( 'b_stats' ) ) {
			$this->send_stats();
		}

		// Cache the support feed so there is no delay when loading the user interface .
		if ( $this->get_option( 'i_supportfeed' ) ) {
			$last = $this->get_option( 'i_supportfeed_cache' );
			if ( $last <= ( time() - $this->get_support_feed_cache_lifetime() ) ) {
				$support_feed = $this->get_support_feed();
				if ( ! is_wp_error( $support_feed ) && $support_feed ) {
					$this->set_option( 'i_supportfeed_cache', time() );
					$this->save_options();
				}
			}
		}
	}


	/*************************************** USER INTERFACE ***************************************/

	/**
	 * Includes the user interface class and initializes it
	 *
	 * @since 3.1.1
	 * @see GoogleSitemapGeneratorUI
	 * @return GoogleSitemapGeneratorUI
	 */
	private function get_ui() {

		if ( null === $this->ui ) {

			$class_name = 'GoogleSitemapGeneratorUI';
			$file_name  = 'class-googlesitemapgeneratorui.php';

			if ( ! class_exists( $class_name ) ) {

				$path = trailingslashit( dirname( __FILE__ ) );

				if ( ! file_exists( $path . $file_name ) ) {
					return false;
				}
				require_once $path . $file_name;
			}

			$this->ui = new $class_name( $this );
		}

		return $this->ui;
	}

	/**
	 * Shows the option page of the plugin. Before 3.1.1, this function was basically the UI, afterwards the UI was outsourced to another class
	 *
	 * @see GoogleSitemapGeneratorUI
	 * @since 3.0
	 * @return bool
	 */
	public function html_show_options_page() {

		$ui = $this->get_ui();
		if ( $ui ) {
			$ui->html_show_options_page();
			return true;
		}

		return false;
	}

	/*************************************** HELPERS ***************************************/

	/**
	 * Returns if the given value is greater than zero
	 *
	 * @param int $value int The value to check .
	 * @since 4.0b10
	 * @return bool True if greater than zero
	 */
	public function is_greater_zero( $value ) {
		return ( $value > 0 );
	}

	/**
	 * Converts the various possible php.ini values for true and false to boolean
	 *
	 * @param string $value string The value from ini_get .
	 *
	 * @return bool The converted value
	 */
	public function get_php_ini_boolean( $value ) {
		if ( is_string( $value ) ) {
			switch ( strtolower( $value ) ) {
				case '+':
				case '1':
				case 'y':
				case 'on':
				case 'yes':
				case 'true':
				case 'enabled':
					return true;

				case '-':
				case '0':
				case 'n':
				case 'no':
				case 'off':
				case 'false':
				case 'disabled':
					return false;
			}
		}

		return (bool) $value;
	}


	/**
	 * Show surevey method .
	 */
	public function show_survey() {
		$this->load_options();
		if ( isset( $_REQUEST['sm_survey'] ) ) {
			return ( sanitize_text_field( wp_unslash( $_REQUEST['sm_survey'] ) ) ) || ! $this->get_option( 'i_hide_survey' );
		}
	}

	/**
	 * Html survey method .
	 */
	public function html_survey() {
		?>
		<div class='updated'>
			<strong>
				<p>
					<?php
					esc_html(
						str_replace(
							'%s',
							'https://w3edge.wufoo.com/forms/mex338s1ysw3i0/',
							/* translators: %s: search term */
							__( 'Thank you for using Google XML Sitemaps! <a href=\'%s\' target=\'_blank\'>Please help us improve by taking this short survey!</a>', 'sitemap' )
						)
					);
					?>
					<a href='<?php esc_url( $this->get_back_link() ) . '&amp;sm_hide_survey=true'; ?>' style='float:right; display:block; border:none;'><small style='font-weight:normal; '><?php esc_html_e( 'Don\'t show this anymore', 'sitemap' ); ?></small></a>
				</p>
			</strong>
			<div style='clear:right;'></div>
		</div>
		<?php
	}

	/**
	 * Get xsl url method .
	 *
	 * @param string $url .
	 * @param string $host .
	 */
	public function get_xsl_url( $url, $host ) {
		if ( substr( $host, 0, 4 ) === 'www.' ) {
			if ( substr( get_bloginfo( 'url' ), 0, 5 ) !== 'https' ) {
				if ( strpos( $url, 'www.' ) === false ) {
					$url = str_replace( 'http://', 'http://www.', $url );
				}
			} else {
				if ( strpos( $url, 'www.' ) === false ) {
					$url = str_replace( 'https://', 'https://www.', $url );
				}
			}
		} else {
			if ( strpos( $url, 'www.' ) !== false ) {
				$url = str_replace( '://www.', '://', $url );
			}
		}
		return $url;
	}
}
