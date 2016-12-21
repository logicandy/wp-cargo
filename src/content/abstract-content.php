<?php

namespace Isotop\Cargo\Content;

use Isotop\Cargo\Contracts\Content_Interface;

abstract class Abstract_Content implements Content_Interface {

	/**
	 * Content type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Content data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Create content data.
	 *
	 * @param string $type
	 * @param mixed  $data
	 */
	public function create( string $type, $data ) {
		$this->type = $type;
		$this->data = (array) $data;
	}

	/**
	 * Add value to content data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function add( string $key, $value ) {
		if ( ! $this->valid_data() ) {
			return;
		}

		$this->data[$key] = $value;
	}

	/**
	 * Get JSON string for content data.
	 *
	 * @return mixed
	 */
	public function get_json() {
		if ( ! $this->valid_data() ) {
			return false;
		}

		return wp_json_encode( [
			'type' => $this->type,
			'data' => $this->data
		] );
	}

	/**
	 * Prepare meta value.
	 *
	 * @param  int   $object_id
	 * @param  array $meta
	 *
	 * @return mixed
	 */
	protected function prepare_meta( $object_id, $meta ) {
		if ( ! is_array( $meta ) ) {
			return [];
		}

		$result = [];

		foreach ( $meta as $slug => $value1 ) {
			if ( is_array( $value1 ) && count( $value1 ) === 1 ) {
				$value1 = $value1[0];
			}

			/**
			 * Modify meta value.
			 *
			 * @param  int    $object_id
			 * @param  string $slug
			 * @param  mixed  $value1
			 * @param  string $type
			 *
			 * @return mixed
			 */
			$value2 = apply_filters( 'cargo_prepare_meta_value', $object_id, $slug, $value1, $this->type );

			if ( is_null( $value2 ) ) {
				continue;
			}

			if ( $value1 === $value2 || ! is_array( $value2 ) ) {
				$value2 = [
					'slug'  => $slug,
					'title' => '',
					'type'  => gettype( $value2 ),
					'value' => $this->cast_string( $value2 )
				];
			}

			$result[] = $value2;
		}

		return $result;
	}

	/**
	 * Cast string value.
	 *
	 * @param  mixed $str
	 *
	 * @return mixed
	 */
	protected function cast_string( $str ) {
		if ( ! is_string( $str ) ) {
			return $str;
		}

		if ( is_numeric( $str ) ) {
			return $str == (int) $str ? (int) $str : (float) $str;
		}

		if ( $str === 'true' || $str === 'false' ) {
			return $str === 'true';
		}

		return maybe_unserialize( $str );
	}

	/**
	 * Is the content data valid?
	 *
	 * @return bool
	 */
	protected function valid_data() {
		return ! is_wp_error( $this->data ) && ! empty( $this->data );
	}
}
