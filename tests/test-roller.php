<?php

class Test_Roller extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		$class = WP_Roller::get_instance();

		$class->reset();
		$class->clear();

		parent::tearDown();
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
	 * @covers WP_Roller::clear
	 */
	public function test_clear() {
		$class = WP_Roller::get_instance();

		$list_id = 'test_list';

		$class->update_list( $list_id, 'content' );

		$class->reset();
		$class->clear();

		$this->assertFalse( $class->get_list( $list_id ) );
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
	 * @covers WP_Roller::get_list
	 */
	public function test_get_list_empty() {
		$class = WP_Roller::get_instance();

		$this->assertFalse( $class->get_list( 'does_not_exist' ) );
	}

	/**
	 * @covers WP_Roller::update_list
	 * @covers WP_Roller::get_list
	 */
	public function test_update_list_and_get_list() {
		$class = WP_Roller::get_instance();

		$list_id = 'test_list';
		$list = "test\nlist";

		$class->update_list( $list_id, $list );

		$this->assertEquals( $list, $class->get_list( $list_id ) );
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
	 * @covers WP_Roller::ensure_lists
	 * @covers WP_Roller::roller_page
	 */
	public function test_roller_page_post_new_list() {
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$old_user_id = get_current_user_id();
		wp_set_current_user( $user->ID );

		$class = WP_Roller::get_instance();

		$list_id = 'test_list';
		$_POST[ 'new_list' ] = true;
		$_POST[ 'list_id' ] = $list_id;

		ob_start();
		$class->roller_page();
		ob_end_clean();

		$this->assertNotFalse( $class->get_list( $list_id ) );

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

		$class = WP_Roller::get_instance();

		$list_id = 'test_list';
		$list_value = "test\nlist";
		$_POST[ 'update_list' ] = true;
		$_POST[ 'list_id' ] = $list_id;
		$_POST[ 'list_value' ] = $list_value;

		ob_start();
		$class->roller_page();
		ob_end_clean();

		$this->assertFalse( $class->get_list( $list_id ) );

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

		$class = WP_Roller::get_instance();

		$list_id = 'test_list';

		$class->update_list( $list_id, 'starting_value' );

		$list_value = "new\ntest\nlist";
		$_POST[ 'update_list' ] = true;
		$_POST[ 'list_id' ] = $list_id;
		$_POST[ 'list_value' ] = $list_value;

		ob_start();
		$class->roller_page();
		ob_end_clean();

		$this->assertEquals( $list_value, $class->get_list( $list_id ) );

		unset( $_POST[ 'list_value' ] );
		unset( $_POST[ 'list_id' ] );
		unset( $_POST[ 'update_list' ] );

		wp_set_current_user( $old_user_id );
	}

	/**
	 * @covers WP_Roller::expression
	 */
	public function test_expression_empty() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( 0, $class->expression( '' ) );
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
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( '1', $class->shortcode_roller( array( '1d1' ) ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_dice_roll_in_range() {
		$class = WP_Roller::get_instance();

		$result = intval( $class->shortcode_roller( array( '3d6' ) ) );

		$this->assertTrue( $result >= 3 && $result <= 18 );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll_plus() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( '2', $class->shortcode_roller( array( '1d1+1' ) ) );
	}

	/**
	 * @covers WP_Roller::expression
	 * @covers WP_Roller::shortcode_roller
	 */
	public function test_shortcode_roller_simple_die_roll_minus() {
		$class = WP_Roller::get_instance();

		$this->assertEquals( '0', $class->shortcode_roller( array( '1d1-1' ) ) );
	}

	/**
	 * @covers WP_Roller::get_var
	 */
	public function test_get_var_empty() {
		$class = WP_Roller::get_instance();

		$this->assertEmpty( $class->get_var( 'does_not_exist' ) );
	}

	/**
	 * @covers WP_Roller::expression
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

		$this->assertEmpty( $class->shortcode_roller_var( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller
	 * @covers WP_Roller::shortcode_roller_var
	 */
	public function test_shortcode_roller_var_does_not_exist() {
		$class = WP_Roller::get_instance();

		$var = 'test_var';

		$this->assertEmpty( $class->shortcode_roller_var( array( 'var' => $var ) ) );
	}

	/**
	 * @covers WP_Roller::expression
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
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_empty() {
		$class = WP_Roller::get_instance();

		$this->assertEmpty( $class->shortcode_roller_choose( array() ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_list_does_not_exist() {
		$class = WP_Roller::get_instance();

		$this->assertEmpty( $class->shortcode_roller_choose( array( 'list' => 'does_not_exist' ) ) );
	}

	/**
	 * @covers WP_Roller::update_list
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_list_exists() {
		$class = WP_Roller::get_instance();

		$list_id = 'test_list';
		$list = "test\nlist";

		$class->update_list( $list_id, $list );

		$result = $class->shortcode_roller_choose( array( 'list' => $list_id ) );

		$this->assertTrue( in_array( $result, explode( "\n", $list ) ) );
	}

	/**
	 * @covers WP_Roller::get_var
	 * @covers WP_Roller::update_list
	 * @covers WP_Roller::shortcode_roller_choose
	 */
	public function test_shortcode_roller_choose_list_exists_set_var() {
		$class = WP_Roller::get_instance();

		$var_id = 'test_var';
		$list_id = 'test_list';
		$list = "test\nlist";

		$class->update_list( $list_id, $list );

		$result = $class->shortcode_roller_choose( array( 'list' => $list_id, 'var' => $var_id ) );

		$this->assertNull( $result );
		$this->assertTrue( in_array( $class->get_var( $var_id ), explode( "\n", $list ) ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_if
	 */
	public function test_shortcode_roller_if_empty() {
		$class = WP_Roller::get_instance();

		$content = 'success';

		$this->assertEquals( $content, $class->shortcode_roller_if( array(), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_if
	 */
	public function test_shortcode_roller_if_false() {
		$class = WP_Roller::get_instance();

		$content = 'success';

		$this->assertEmpty( '', $class->shortcode_roller_if( array( 'bad_key' => 'bad_value' ), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_empty() {
		$class = WP_Roller::get_instance();

		$content = 'success';

		$this->assertEmpty( $class->shortcode_roller_loop( array(), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_invalid() {
		$class = WP_Roller::get_instance();

		$content = 'success';

		$this->assertEmpty( $class->shortcode_roller_loop( array( 'bad_value' ), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_single() {
		$class = WP_Roller::get_instance();

		$content = 'success';

		$this->assertEquals( $content, $class->shortcode_roller_loop( array( '1' ), $content ) );
	}

	/**
	 * @covers WP_Roller::shortcode_roller_loop
	 */
	public function test_shortcode_roller_loop_multiple() {
		$class = WP_Roller::get_instance();

		$content = 'success';

		$this->assertEquals(
			$content . $content . $content,
			$class->shortcode_roller_loop( array( '3' ), $content )
		);
	}

}
