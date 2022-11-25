<?php
/**
 * Googlesitemapgeneratorstatus class file.
 *
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0b5
 */

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
