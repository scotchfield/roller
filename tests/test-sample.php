<?php

class Test_Roller extends WP_UnitTestCase {

	/**
	 * @covers WP_Roller::__construct
	 */
	public function test_new() {
		$class = new WP_Roller();

		$this->assertNotNull( $class );
	}

}

