<?php

class Test_Roller extends WP_UnitTestCase {

	/**
	 * @covers WP_Roller::__construct
	 */
	public function test_new() {
		$class = new WP_Roller();

		$this->assertNotNull( $class );
	}

	/**
	 * @covers WP_Roller::init
	 */
	public function test_init() {
		$class = WP_Roller::get_instance();
		$class->init();

		$this->assertInternalType( 'array', $class->state );
	}

	/**
	 * @covers WP_Roller::get_instance
	 */
	public function test_get_instance() {
		$class = WP_Roller::get_instance();

		$this->assertNotNull( $class );
	}

	/**
	 * @covers WP_Roller::admin_menu
	 */
	public function test_admin_menu() {
		global $admin_page_hooks;

		$class = WP_Roller::get_instance();
		$class->admin_menu();

		$this->assertArrayHasKey( 'roller_menu', $admin_page_hooks );
	}

	/**
	 * @covers WP_Roller::ensure_lists
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page() {
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$old_user_id = get_current_user_id();
		wp_set_current_user( $user->ID );

		$class = WP_Roller::get_instance();

		ob_start();
		$class->roller_page();
		ob_end_clean();

		$this->assertInternalType( 'array', $class->lists );

		wp_set_current_user( $old_user_id );
	}

}

