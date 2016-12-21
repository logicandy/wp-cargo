<?php

namespace Isotop\Tests\Cargo\Content;

use Isotop\Cargo\Content\Term;

class Term_Test extends \WP_UnitTestCase {

	public function test_fake_term() {
		$term_id = 0;
		$term    = new Term( $term_id );

		$this->assertFalse( $term->get_json() );
	}

	public function test_real_term() {
		$term_id = $this->factory->category->create();
		$term    = new Term( $term_id );

		$this->assertTrue( cargo_is_json( $term->get_json() ) );
	}

	public function test_fake_get_data() {
		$term_id = 0;
		$term    = new Term( $term_id );
		$data    = $term->get_data();

		$this->assertEmpty( $data );
	}

	public function test_real_get_data() {
		$term_id = $this->factory->category->create();
		$term    = new Term( $term_id );
		$data    = $term->get_data();

		$this->assertSame( get_current_blog_id(), $data['extra']['site_id'] );
	}
}
