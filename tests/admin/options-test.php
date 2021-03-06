<?php

namespace Isotop\Tests\Cargo\Admin;

use Closure;
use Isotop\Cargo\Cargo;
use WP_Screen;

class Options_Test extends \WP_UnitTestCase {

	public function test_push_options() {
		$cargo = Cargo::instance();
		$fn    = Closure::fromCallable( '\\Isotop\\Cargo\\Admin\\push_options' )->bindTo( $cargo );

		$this->assertFalse( $fn() );

		$_POST = ['abc' => 'def'];
		$this->assertFalse( $fn() );

		add_filter( 'pre_http_request', function () {
			return ['body' => '{"success":true}'];
		} );

		$this->assertFalse( $fn() );

		cargo()->set_config( ['content' => ['options' => ['siteurl', 'home']]] );

		global $current_screen;
		$current_screen = WP_Screen::get( 'admin_init' );

		$this->assertTrue( $fn() );

		$current_screen = null;
	}
}
