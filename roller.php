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
		$this->lists = FALSE;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_shortcode( 'roller', array( $this, 'shortcode_roller' ) );
		add_shortcode( 'roller_var', array( $this, 'shortcode_roller_var' ) );
		add_shortcode( 'roller_choose', array( $this, 'shortcode_roller_choose' ) );
		add_shortcode( 'roller_if', array( $this, 'shortcode_roller_if' ) );
		add_shortcode( 'roller_loop', array( $this, 'shortcode_roller_loop' ) );
	}

	/**
	 * Add a link to a settings page.
	 */
	public function admin_menu() {
		add_menu_page(
			'Roller',
			'Roller',
			'manage_options',
			'roller_menu',
			array( $this, 'roller_page' )
		);
	}

	/**
	 * Make sure the custom lists are loaded inside the class.
	 */
	private function ensure_lists() {
		if ( FALSE == $this->lists ) {
			$this->lists = get_option( 'roller_lists', array() );
		}
	}

	/**
	 * Show the admin page, which is essentially the collection of custom lists.
	 */
	public function roller_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', self::DOMAIN ) );
		}

		$this->ensure_lists();

		if ( isset( $_POST[ 'new_var' ] ) ) {
			$var = $_POST[ 'var' ];
			if ( ! isset( $this->lists[ $var ] ) ) {
				$this->lists[ $var ] = '';
				update_option( 'roller_lists', $this->lists );
			}
		}

		if ( isset( $_POST[ 'update_var' ] ) ) {
			$var = $_POST[ 'var' ];
			$this->lists[ $var ] = $_POST[ 'var_text' ];
			update_option( 'roller_lists', $this->lists );
		}

?>
<h1>Roller</h1>

<form method="post">
<p><b>New list:</b> <input type="text" name="var" /> <input type="submit" name="new_var" value="Create List" /></p>
</form>

<h2>Lists</h2>
<?php

		if ( false != $this->lists ) {
			foreach ( $this->lists as $list_key => $list ) {
?>
<form method="post">
<input type="hidden" name="var" value="<?php echo( $list_key ); ?>" />
<h3><?php echo( $list_key ); ?></h3>
<textarea name="var_text"><?php echo( $list ); ?></textarea>
<input name="update_var" type="submit" value="Update List" />
</form>
<?php
			}
		}

	}

	/**
	 * Perform a dice roll, and either output it immediately or store it in a variable.
	 */
	public function shortcode_roller( $atts ) {
		if ( isset( $atts[ 0 ] ) ) {
			$pattern = '/(\d*)d(\d*)([+-]*)(\d*)/';
			if ( preg_match( $pattern, $atts[ 0 ], $match ) ) {
				$result = 0;
				$n = intval( $match[ 1 ] );
				$d = intval( $match[ 2 ] );
				for ( $i = 0; $i < $n; $i += 1 ) {
					$result += mt_rand( 1, $d );
				}

				if ( $match[ 3 ] == '+' ) {
					$result += intval( $match[ 4 ] );
				} else if ( $match[ 4 ] == '-' ) {
					$result += intval( $match[ 4 ] );
				}

				if ( isset( $atts[ 'var' ] ) ) {
					$this->state[ $atts[ 'var' ] ] = $result;
					return '';
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

	public function shortcode_roller_choose( $atts ) {
		$this->ensure_lists();

		if ( ! isset( $atts[ 'list' ] ) ) {
			return '';
		}

		$list = $atts[ 'list' ];

		if ( ! isset( $this->lists[ $list ] ) ) {
			return '';
		}

		$obj = explode( "\n", $this->lists[ $list ] );
		$val = array_rand( $obj );

		if ( isset( $atts[ 'var' ] ) ) {
			$this->state[ $atts[ 'var' ] ] = trim( $obj[ $val ] );

			return '';
		}

		return $obj[ $val ];
	}

	public function shortcode_roller_if( $atts, $content ) {
		$state = true;

		foreach ( $atts as $k => $v ) {
			if ( ! isset( $this->state[ $k ] ) || $this->state[ $k ] != $v ) {
				$state = false;
			}
		}

		if ( $state ) {
			return do_shortcode( $content );
		}

		return '';
	}

	public function shortcode_roller_loop( $atts, $content ) {
		if ( ! isset( $atts[ 0 ] ) ) {
			return '';
		}

		$i = intval( $atts[ 0 ] );
		$st = '';

		while ( $i > 0 ) {
			$st .= do_shortcode( $content );

			$i -= 1;
		}

		return $st;
	}
}

$wp_roller = new WP_Roller();
