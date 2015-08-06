<?php

class Test_Roller extends WP_UnitTestCase {

	public function tearDown() {
		$class = WP_Roller::get_instance();

		$class->reset();
	}

	/**
	 * @covers WP_Roller::__construct
	 */
	public function test_new() {
		$class = new WP_Roller();

		$this->assertNotNull( $class );
	}

	/**
	 * @covers WP_Roller::init
	 * @covers WP_Roller::reset
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

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_empty() {
		$class = WP_Roller::get_instance();

		$this->assertEmpty( $class->shortcode_roller( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_invalid() {
		$class = WP_Roller::get_instance();

		$this->assertEmpty( $class->shortcode_roller( array( 'bad_content' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( '1', $class->shortcode_roller( array( '1d1' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_dice_roll_in_range() {
		$class = WP_Roller::get_instance();

		$result = intval( $class->shortcode_roller( array( '3d6' ) ) );

		$this->assertTrue( $result >= 3 && $result <= 18 );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll_plus() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( '2', $class->shortcode_roller( array( '1d1+1' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll_minus() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( '0', $class->shortcode_roller( array( '1d1-1' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::get_var
	 */
	public function test_shortcode_roller_dice_roll_stored() {
		$class = WP_Roller::get_instance();

		$var = 'test_var';

		$result = $class->shortcode_roller( array( '1d1', 'var' => $var ) );

		$this->assertEmpty( $result );
		$this->assertEquals( '1', $class->get_var( $var ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_empty() {
		$class = WP_Roller::get_instance();

		$this->assertNull( $class->shortcode_roller_var( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_does_not_exist() {
		$class = WP_Roller::get_instance();

		$var = 'test_var';

		$this->assertNull( $class->shortcode_roller_var( array( 'var' => $var ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_stored() {
		$class = WP_Roller::get_instance();

		$var = 'test_var';

		$result = $class->shortcode_roller( array( '1d1', 'var' => $var ) );

		$this->assertEquals( '1', $class->shortcode_roller_var( array( $var ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_empty() {
		$class = WP_Roller::get_instance();

		$this->assertNull( $class->shortcode_roller_choose( array() ) );
	}


}
