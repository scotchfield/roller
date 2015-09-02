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
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Return the single instance of our class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WP_Roller();
		}

		return self::$instance;
	}

	/**
	 * Add hooks, shortcodes, and reset our state.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_shortcode( 'roller', array( $this, 'shortcode_roller' ) );
		add_shortcode( 'roller_var', array( $this, 'shortcode_roller_var' ) );
		add_shortcode( 'roller_choose', array( $this, 'shortcode_roller_choose' ) );
		add_shortcode( 'roller_if', array( $this, 'shortcode_roller_if' ) );
		add_shortcode( 'roller_loop', array( $this, 'shortcode_roller_loop' ) );
		add_shortcode( 'roller_exp', array( $this, 'shortcode_roller_exp' ) );

		$this->reset();
	}

	/**
	 * Reset the random seed and clear the state and list caches.
	 * Does _not_ reload the list state from the options table.
	 */
	public function reset() {
		mt_srand();

		$this->state = array();
		$this->lists = false;
	}

	/**
	 * Delete the list option.
	 */
	public function clear() {
		delete_option( 'roller_lists' );
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
		if ( false === $this->lists ) {
			$this->lists = get_option( 'roller_lists', array() );
		}
	}

	/**
	 * Return the value of a variable, if it is set.
	 */
	public function get_var( $var ) {
		if ( isset( $this->state[ $var ] ) ) {
			return $this->state[ $var ];
		}
	}

	/**
	 * Get the value of a stored list.
	 */
	public function get_list( $id ) {
		if ( isset( $this->lists[ $id ] ) ) {
			return $this->lists[ $id ];
		}

		return false;
	}

	/**
	 * Update the value of a stored list.
	 */
	public function update_list( $id, $val ) {
		$this->lists[ $id ] = $val;

		update_option( 'roller_lists', $this->lists[ $id ] );
	}

	/**
	 * Show the admin page, which is essentially the collection of custom lists.
	 */
	public function roller_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', self::DOMAIN ) );
		}

		$this->ensure_lists();

		if ( isset( $_POST[ 'new_list' ] ) ) {
			if ( false === $this->get_list( $_POST[ 'list_id' ] ) ) {
				$this->update_list( $_POST[ 'list_id' ], '' );
			}
		}

		if ( isset( $_POST[ 'update_list' ] ) ) {
			if ( false !== $this->get_list( $_POST[ 'list_id' ] ) ) {
				$this->update_list( $_POST[ 'list_id' ], $_POST[ 'list_value' ] );
			}
		}

?>
<h1>Roller</h1>

<hr>

<h2>Shortcodes:</h2>
<ul>
	<li>Roll some dice: <b>[roller 3d6]</b></li>
	<li>Save dice rolls as variables: <b>[roller 3d6 var=str]</b></li>
	<li>Display a variable's value: <b>[roller_var str]</b></li>
	<li>Equations: <b>[roller 3d6 var=pow] [roller_exp pow*5 var=san]</b></li>
	<li>Random list elements: <b>[roller_choose var=gender list=gender]</b> <i>(Lists are defined at the bottom of this page)</i></li>
	<li>Conditionals: <b>[roller_if gender=Female][roller_choose var=first_name list=first_name_female][/roller_if]</b></li>
</ul>

<hr>

<form method="post">
<p>
<b>New list:</b>
<input type="text" name="list_id" />
<input type="submit" name="new_list" value="Create List" />
</p>
</form>

<h2>Lists</h2>
<?php

		if ( ! empty( $this->lists ) ) {
			foreach ( $this->lists as $list_key => $list ) {
?>
<form method="post">
<input type="hidden" name="list_id" value="<?php echo( $list_key ); ?>" />
<h3><?php echo( $list_key ); ?></h3>
<textarea name="list_value"><?php echo( $list ); ?></textarea>
<input name="update_list" type="submit" value="Update List" />
</form>
<?php
			}
		}

	}

	/**
	 * Evaluate an expression passed to the roller class.
	 */
	public function expression( $st ) {
		$result = 0;
		$pattern = '/(\d*)d(\d*)([+-]*)(\d*)/';

		if ( preg_match( $pattern, $st, $match ) ) {
			$n = intval( $match[ 1 ] );
			$d = intval( $match[ 2 ] );
			for ( $i = 0; $i < $n; $i += 1 ) {
				$result += mt_rand( 1, $d );
			}

			if ( $match[ 3 ] == '+' ) {
				$result += intval( $match[ 4 ] );
			} else if ( $match[ 3 ] == '-' ) {
				$result -= intval( $match[ 4 ] );
			}
		}

		return $result;
	}

	/**
	 * Perform a dice roll, and either output it immediately or store it in a variable.
	 */
	public function shortcode_roller( $atts ) {
		if ( isset( $atts[ 0 ] ) ) {
			$result = $this->expression( $atts[ 0 ] );

			if ( isset( $atts[ 'var' ] ) ) {
				$this->state[ $atts[ 'var' ] ] = $result;
			} else {
				return $result;
			}
		}
	}

	/**
	 * Return the value of a stored variable, if it is set.
	 */
	public function shortcode_roller_var( $atts ) {
		if ( isset( $atts[ 0 ] ) ) {
			return $this->get_var( $atts[ 0 ] );
		}
	}

	/**
	 * Choose a random element from a stored and named list of values.
	 */
	public function shortcode_roller_choose( $atts ) {
		$this->ensure_lists();

		if ( isset( $atts[ 'list' ] ) && isset( $this->lists[ $atts[ 'list' ] ] ) ) {
			$list = $atts[ 'list' ];

			$obj = explode( "\n", $this->lists[ $list ] );
			$val = array_rand( $obj );

			if ( isset( $atts[ 'var' ] ) ) {
				$this->state[ $atts[ 'var' ] ] = trim( $obj[ $val ] );
			} else {
				return $obj[ $val ];
			}
		}
	}

	/**
	 * Iterate through a list of conditionals; if none are false,
	 * return the contents of the shortcode block.
	 */
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

	/**
	 * Repeat the contents of the shortcode block multiple times.
	 */
	public function shortcode_roller_loop( $atts, $content ) {
		if ( ! isset( $atts[ 0 ] ) ) {
			return;
		}

		$i = intval( $atts[ 0 ] );
		$st = '';

		while ( $i > 0 ) {
			$st .= do_shortcode( $content );

			$i -= 1;
		}

		return $st;
	}

	/**
	 * Evaluate a simple expression in the form var*3.
	 * TODO: Build this out into a real parser. For now, variable operation integer.
	 */
	public function shortcode_roller_exp( $atts ) {
		if ( ! isset( $atts[ 0 ] ) ) {
			return '';
		}

		$exp = preg_split( "/([+*\/-])/", $atts[ 0 ], -1, PREG_SPLIT_DELIM_CAPTURE );

		if ( count( $exp ) != 3 ) {
			return '';
		}

		$result = '';
		$op = $exp[ 1 ];

		$left = $this->get_var( $exp[ 0 ] );
		if ( null == $left && is_numeric( $exp[ 0 ] ) ) {
			$left = intval( $exp[ 0 ] );
		}
		$right = $this->get_var( $exp[ 2 ] );
		if ( null == $right && is_numeric( $exp[ 2 ] ) ) {
			$right = intval( $exp[ 2 ] );
		}

		if ( null == $left || null == $right ) {
			return '';
		}

		if ( $op == '+' ) {
			$result = $left + $right;
		} else if ( $exp[ 1 ] == '-' ) {
			$result = $left - $right;
		} else if ( $exp[ 1 ] == '*' ) {
			$result = $left * $right;
		} else if ( $exp[ 1 ] == '/' && $right != 0 ) {
			$result = $left / $right;
		}

		if ( in_array( 'round', $atts ) ) {
			$result = round( $result );
		} else if ( in_array( 'ceil', $atts ) ) {
			$result = ceil( $result );
		} else if ( in_array( 'floor', $atts ) ) {
			$result = floor( $result );
		}

		if ( isset( $atts[ 'var' ] ) ) {
			$this->state[ $atts[ 'var' ] ] = $result;
		} else {
			return $result;
		}
	}

}

$wp_roller = WP_Roller::get_instance();
