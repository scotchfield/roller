<?php
/**
 * Plugin Name: Roller
 * Plugin URI: http://npc.today/
 * Description: Dice rolling and list selection for NPC generation.
 * Version: 1.0
 * Author: Scott Grant
 * Author URI: http://npc.today/
 */
class WP_Roller {

	/**
	 * Store reference to singleton object.
	 */
	private static $instance = null;

	/**
	 * The domain for localization.
	 */
	const DOMAIN = 'wp-roller';

	/**
	 * Instantiate, if necessary, and add hooks.
	 */
	public function __construct() {
		if ( isset( self::$instance ) ) {
			return;
		}

		self::$instance = $this;

		mt_srand();

		$this->state = array();

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_shortcode( 'roller', array( $this, 'shortcode_roller' ) );
		add_shortcode( 'roller_var', array( $this, 'shortcode_roller_var' ) );
	}

	/**
	 * Add a link to a settings page.
	 */
	public function admin_menu() {
		$page = add_options_page(
			'Roller',
			'Roller',
			'manage_options',
			self::DOMAIN,
			array( $this, 'roller_page' )
		);
	}

	public function roller_page() {
?>
<h1>Roller</h1>
<?php
	}

	public function shortcode_roller( $atts ) {
		if ( isset( $atts[ 0 ] ) ) {
			$pattern = '/(\d*)d(\d*)/';
			if ( preg_match( $pattern, $atts[ 0 ], $match ) ) {
				$result = 0;
				$n = intval( $match[ 1 ] );
				$d = intval( $match[ 2 ] );
				for ( $i = 0; $i < $n; $i += 1 ) {
					$result += mt_rand( 1, $d );
				}

				if ( isset( $atts[ 'var' ] ) ) {
					$this->state[ $atts[ 'var' ] ] = $result;
				}

				return $result;
			}
		}
	}

	public function shortcode_roller_var( $atts ) {
		if ( isset( $atts[ 0 ] ) && isset( $this->state[ $atts[ 0 ] ] ) ) {
			return $this->state[ $atts[ 0 ] ];
		}
	}
}

$wp_roller = new WP_Roller();
