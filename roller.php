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

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
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

	}

}

$wp_roller = new WP_Roller();