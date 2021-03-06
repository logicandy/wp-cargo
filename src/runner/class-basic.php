<?php

namespace Isotop\Cargo\Runner;

use Isotop\Cargo\Content\Menus;
use Isotop\Cargo\Content\Options;
use Isotop\Cargo\Content\Post;
use Isotop\Cargo\Content\Term;

class Basic extends Abstract_Runner {

	/**
	 * Run all content task.
	 */
	public function run_all() {
		$pusher = $this->cargo->make( 'pusher' );

		// Push all posts.
		foreach ( $this->posts() as $post ) {
			$pusher->send( new Post( $post ) );
			$this->log_info( sprintf( 'Pushed post with id %d to pusher', $post->ID ) );
		}

		// Push all terms.
		foreach ( $this->terms() as $term ) {
			$pusher->send( new Term( $term ) );
			$this->log_info( sprintf( 'Pushed term with id %d to pusher', $term->term_id ) );
		}

		// Push all menus.
		$pusher->send( new Menus() );
		$this->log_info( 'Pushed all menus' );

		// Push all options.
		$pusher->send( new Options() );
		$this->log_info( 'Pushed all options' );

		$this->log_success( 'Done!' );
	}

	/**
	 * Run queued content task.
	 */
	public function run_queue() {
		$database = $this->cargo->make( 'database' );
		$pusher   = $this->cargo->make( 'pusher' );

		foreach ( $database->all() as $item ) {
			if ( ! empty( $item->data ) && cargo_is_json( $item->data ) ) {
				// Bail if we don't get a true when we run through all items.
				if ( $res = $pusher->send( $item->data ) ) {
					if ( is_wp_error( $res ) ) {
						$this->log_error( $res );
					} else {
						$this->log_info( sprintf( 'Pushed item with id: %d', $item->id ) );
					}

					if ( ! $res ) {
						break;
					}
				}
			} else {
				$this->log_info( sprintf( 'Item with id %d is not pushed since data string is empty', $item->id ) );
			}

			// If it can be send delete it from the database.
			if ( ! $database->delete( $item->id ) ) {
				$this->log_error( sprintf( 'Failed to delete item with id: %d', $item->id ) );
			}
		}

		$this->log_success( 'Done!' );
	}
}
