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

}

