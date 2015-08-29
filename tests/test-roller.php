<?php

class Test_Roller extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->class = WP_Roller::get_instance();

		$this->wp_die = false;
		add_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ), 1, 1 );
	}

	public function tearDown() {
		remove_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ) );
		unset( $this->wp_die );

		$this->class->reset();
		$this->class->clear();

		unset( $this->class );

		parent::tearDown();
	}

	public function get_wp_die_handler( $handler ) {
		return array( $this, 'wp_die_handler' );
	}

	public function wp_die_handler( $message ) {
		$this->wp_die = true;

		throw new WPDieException( $message );
	}

	/**
	 * @covers WP_Roller::init
	 * @covers WP_Roller::reset
	 */
	public function test_init() {
		$this->class->init();

		$this->assertInternalType( 'array', $this->class->state );
	}

	/**
	 * @covers WP_Roller::get_instance
	 */
	public function test_get_instance() {
		$this->assertNotNull( $this->class );
	}

	/**
	 * @covers WP_Roller::clear
	 */
	public function test_clear() {
		$list_id = 'test_list';

		$this->class->update_list( $list_id, 'content' );

		$this->class->reset();
		$this->class->clear();

		$this->assertFalse( $this->class->get_list( $list_id ) );
	}

	/**
	 * @covers WP_Roller::admin_menu
	 */
	public function test_admin_menu() {
		global $admin_page_hooks;

		$this->class->admin_menu();

		$this->assertArrayHasKey( 'roller_menu', $admin_page_hooks );
	}

	/**
	 * @covers WP_Roller::get_list
	 */
	public function test_get_list_empty() {
		$this->assertFalse( $this->class->get_list( 'does_not_exist' ) );
	}

	/**
	 * @covers WP_Roller::update_list
	 * @covers WP_Roller::get_list
	 */
	public function test_update_list_and_get_list() {
		$list_id = 'test_list';
		$list = "test\nlist";

		$this->class->update_list( $list_id, $list );

		$this->assertEquals( $list, $this->class->get_list( $list_id ) );
	}

	/**
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page_empty() {
		try {
			$this->class->roller_page();
		} catch ( WPDieException $e ) {}

		$this->assertTrue( $this->wp_die );
	}

	/**
	 * @covers WP_Roller::ensure_lists
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page() {
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$old_user_id = get_current_user_id();
		wp_set_current_user( $user->ID );

		ob_start();
		$this->class->roller_page();
		ob_end_clean();

		$this->assertInternalType( 'array', $this->class->lists );

		wp_set_current_user( $old_user_id );
	}

	/**
	 * @covers WP_Roller::ensure_lists
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page_post_new_list() {
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$old_user_id = get_current_user_id();
		wp_set_current_user( $user->ID );

		$list_id = 'test_list';
		$_POST[ 'new_list' ] = true;
		$_POST[ 'list_id' ] = $list_id;

		ob_start();
		$this->class->roller_page();
		ob_end_clean();

		$this->assertNotFalse( $this->class->get_list( $list_id ) );

		unset( $_POST[ 'list_id' ] );
		unset( $_POST[ 'new_list' ] );

		wp_set_current_user( $old_user_id );
	}

	/**
	 * @covers WP_Roller::ensure_lists
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page_post_update_list_does_not_exist() {
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$old_user_id = get_current_user_id();
		wp_set_current_user( $user->ID );

		$list_id = 'test_list';
		$list_value = "test\nlist";
		$_POST[ 'update_list' ] = true;
		$_POST[ 'list_id' ] = $list_id;
		$_POST[ 'list_value' ] = $list_value;

		ob_start();
		$this->class->roller_page();
		ob_end_clean();

		$this->assertFalse( $this->class->get_list( $list_id ) );

		unset( $_POST[ 'list_value' ] );
		unset( $_POST[ 'list_id' ] );
		unset( $_POST[ 'update_list' ] );

		wp_set_current_user( $old_user_id );
	}

	/**
	 * @covers WP_Roller::ensure_lists
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page_post_update_list_does_exist() {
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$old_user_id = get_current_user_id();
		wp_set_current_user( $user->ID );

		$list_id = 'test_list';

		$this->class->update_list( $list_id, 'starting_value' );

		$list_value = "new\ntest\nlist";
		$_POST[ 'update_list' ] = true;
		$_POST[ 'list_id' ] = $list_id;
		$_POST[ 'list_value' ] = $list_value;

		ob_start();
		$this->class->roller_page();
		ob_end_clean();

		$this->assertEquals( $list_value, $this->class->get_list( $list_id ) );

		unset( $_POST[ 'list_value' ] );
		unset( $_POST[ 'list_id' ] );
		unset( $_POST[ 'update_list' ] );

		wp_set_current_user( $old_user_id );
	}

	/**
	 * @covers WP_Roller::expression
	 */
	public function test_expression_empty() {
		$this->assertEquals( 0, $this->class->expression( '' ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_empty() {
		$this->assertEmpty( $this->class->shortcode_roller( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_invalid() {
		$this->assertEmpty( $this->class->shortcode_roller( array( 'bad_content' ) ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll() {
		$this->assertEquals( '1', $this->class->shortcode_roller( array( '1d1' ) ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_dice_roll_in_range() {
		$result = intval( $this->class->shortcode_roller( array( '3d6' ) ) );

		$this->assertTrue( $result >= 3 && $result <= 18 );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll_plus() {
		$this->assertEquals( '2', $this->class->shortcode_roller( array( '1d1+1' ) ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll_minus() {
		$this->assertEquals( '0', $this->class->shortcode_roller( array( '1d1-1' ) ) );
	}

	/**
	 * @covers WP_Roller::get_var
	 */
	public function test_get_var_empty() {
		$this->assertEmpty( $this->class->get_var( 'does_not_exist' ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::get_var
	 */
	public function test_shortcode_roller_dice_roll_stored() {
		$var = 'test_var';

		$result = $this->class->shortcode_roller( array( '1d1', 'var' => $var ) );

		$this->assertEmpty( $result );
		$this->assertEquals( '1', $this->class->get_var( $var ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_empty() {
		$this->assertEmpty( $this->class->shortcode_roller_var( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_does_not_exist() {
		$var = 'test_var';

		$this->assertEmpty( $this->class->shortcode_roller_var( array( 'var' => $var ) ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_stored() {
		$var = 'test_var';

		$result = $this->class->shortcode_roller( array( '1d1', 'var' => $var ) );

		$this->assertEquals( '1', $this->class->shortcode_roller_var( array( $var ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_empty() {
		$this->assertEmpty( $this->class->shortcode_roller_choose( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_list_does_not_exist() {
		$this->assertEmpty( $this->class->shortcode_roller_choose( array( 'list' => 'does_not_exist' ) ) );
	}

	/**
	 * @covers WP_Roller::update_list
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_list_exists() {
		$list_id = 'test_list';
		$list = "test\nlist";

		$this->class->update_list( $list_id, $list );

		$result = $this->class->shortcode_roller_choose( array( 'list' => $list_id ) );

		$this->assertTrue( in_array( $result, explode( "\n", $list ) ) );
	}

	/**
	 * @covers WP_Roller::get_var
	 * @covers WP_Roller::update_list
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_list_exists_set_var() {
		$var_id = 'test_var';
		$list_id = 'test_list';
		$list = "test\nlist";

		$this->class->update_list( $list_id, $list );

		$result = $this->class->shortcode_roller_choose( array( 'list' => $list_id, 'var' => $var_id ) );

		$this->assertNull( $result );
		$this->assertTrue( in_array( $this->class->get_var( $var_id ), explode( "\n", $list ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_if
	 */
	public function test_shortcode_roller_if_empty() {
		$content = 'success';

		$this->assertEquals( $content, $this->class->shortcode_roller_if( array(), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_if
	 */
	public function test_shortcode_roller_if_false() {
		$content = 'success';

		$this->assertEmpty( '', $this->class->shortcode_roller_if( array( 'bad_key' => 'bad_value' ), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_empty() {
		$content = 'success';

		$this->assertEmpty( $this->class->shortcode_roller_loop( array(), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_invalid() {
		$content = 'success';

		$this->assertEmpty( $this->class->shortcode_roller_loop( array( 'bad_value' ), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_single() {
		$content = 'success';

		$this->assertEquals( $content, $this->class->shortcode_roller_loop( array( '1' ), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_multiple() {
		$content = 'success';

		$this->assertEquals(
			$content . $content . $content,
			$this->class->shortcode_roller_loop( array( '3' ), $content )
		);
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_invalid() {
		$this->assertEmpty( $this->class->shortcode_roller_exp( false ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_empty() {
		$this->assertEmpty( $this->class->shortcode_roller_exp( array( '' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_var_not_set() {
		$this->assertEmpty( $this->class->shortcode_roller_exp( array( 'testnotexist+1' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_var_add() {
		$this->class->shortcode_roller( array( '10d1', 'var' => 'test' ) );

		$this->assertEquals( 11, $this->class->shortcode_roller_exp( array( 'test+1' ) ) );
		$this->assertEquals( 11, $this->class->shortcode_roller_exp( array( '1+test' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_var_subtract() {
		$this->class->shortcode_roller( array( '10d1', 'var' => 'test' ) );

		$this->assertEquals( 9, $this->class->shortcode_roller_exp( array( 'test-1' ) ) );
		$this->assertEquals( -9, $this->class->shortcode_roller_exp( array( '1-test' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_var_multiply() {
		$this->class->shortcode_roller( array( '10d1', 'var' => 'test' ) );

		$this->assertEquals( 100, $this->class->shortcode_roller_exp( array( 'test*10' ) ) );
		$this->assertEquals( 100, $this->class->shortcode_roller_exp( array( '10*test' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_var_divide() {
		$this->class->shortcode_roller( array( '10d1', 'var' => 'test' ) );

		$this->assertEquals( 1, $this->class->shortcode_roller_exp( array( '10/test' ) ) );
		$this->assertEquals( 1, $this->class->shortcode_roller_exp( array( 'test/10' ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_exp
	 */
	public function test_shortcode_roller_exp_test_var_divide_by_zero() {
		$this->class->shortcode_roller( array( '10d1', 'var' => 'test' ) );

		$this->assertEmpty( $this->class->shortcode_roller_exp( array( 'test/0' ) ) );
	}

}
