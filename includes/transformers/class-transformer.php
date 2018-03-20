<?php
/**
 * Transformer class.
 *
 * General layout is inspired by EDD's Discount class..
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Transformers
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transformer class. Saves, deletes, applies etc.
 */
class WPNA_Transformer {

	/**
	 * Transformer ID.
	 *
	 * @var int
	 */
	public $ID;

	/**
	 * Transformer name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Transformer status.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Transformer type.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Transformer selector.
	 *
	 * @var string
	 */
	public $selector;

	/**
	 * Transformer rule.
	 *
	 * @var string
	 */
	public $rule;

	/**
	 * Class constructor.
	 *
	 * @param int $id ID of the current transformer.
	 */
	public function __construct( $id = null ) {
		if ( empty( $id ) ) {
			return false;
		}

		$transformer = WP_Post::get_instance( absint( $id ) );

		if ( $transformer ) {
			$this->setup_transformer( $transformer );
		} else {
			return false;
		}
	}

	/**
	 * Gets a transformer.
	 *
	 * @access public
	 * @param  WP_Post $transformer WP_Post obhject to setup.
	 * @return object WPNA_Placement
	 */
	public function setup_transformer( $transformer = null ) {
		if ( null === $transformer ) {
			return false;
		}

		if ( ! is_object( $transformer ) ) {
			return false;
		}

		if ( is_wp_error( $transformer ) ) {
			return false;
		}

		if ( ! is_a( $transformer, 'WP_Post' ) ) {
			return false;
		}

		if ( 'wpna_transformer' !== get_post_type( $transformer ) ) {
			return false;
		}

		/**
		 * Fires before the instance of the WPNA_Transformer object is set up.
		 *
		 * @param object WPNA_Transformer  WPNA_Transformer instance of the transformer object.
		 * @param object WP_Post $transformer WP_Post instance of the transformer object.
		 */
		do_action( 'wpna_pre_setup_transformer', $this, $transformer );

		/**
		 * Setup all object variables
		 */
		$this->ID       = absint( $transformer->ID );
		$this->name     = $this->setup_name();
		$this->status   = $this->setup_status();
		$this->type     = $this->setup_type();
		$this->selector = $this->setup_selector();
		$this->rule     = $this->setup_rule();

		/**
		 * Fires after the instance of the WPNA_Transformer object is set up.
		 *
		 * @param object WPNA_Transformer      WPNA_Transformer instance of the transformer object.
		 * @param object WP_Post $transformer WP_Post instance of the transformer object.
		 */
		do_action( 'wpna_setup_transformer', $this, $transformer );

		return true;
	}

	/**
	 * Setup the name of the transformer.
	 *
	 * @access private
	 *
	 * @return string Name of the transformer.
	 */
	private function setup_name() {
		$title = get_the_title( $this->ID );
		return $title;
	}

	/**
	 * Setup the status of the transformer.
	 *
	 * @access private
	 *
	 * @return string Status of the transformer.
	 */
	private function setup_status() {
		$status = get_post_status( $this->ID );
		return $status;
	}

	/**
	 * Setup the transformer type.
	 *
	 * @access private
	 *
	 * @return string Transformer type.
	 */
	private function setup_type() {
		$type = $this->get_meta( 'type', true );
		return $type;
	}

	/**
	 * Setup the transformer selector.
	 *
	 * @access private
	 *
	 * @return string Transformer selector.
	 */
	private function setup_selector() {
		$selector = $this->get_meta( 'selector', true );
		return $selector;
	}

	/**
	 * Setup the transformer rules.
	 *
	 * @access private
	 *
	 * @return string Transformer rules.
	 */
	private function setup_rule() {
		$rule = $this->get_meta( 'rule', true );
		return $rule;
	}

	/**
	 * Create a transformer.
	 *
	 * @access public
	 * @param  array $args Arguments to save.
	 * @return object WPNA_Placement
	 */
	public function add( $args ) {

		if ( ! empty( $this->ID ) ) {
			return $this->update( $args );
		}

		/**
		 * Add a new transformer to the database.
		 */

		$meta = $this->build_meta( $args );

		/**
		 * Filters the metadata before being inserted into the database.
		 *
		 * @param array $meta Transformer meta.
		 * @param int   $ID   Transformer ID.
		 */
		$meta = apply_filters( 'wpna_insert_transformer', $meta );

		/**
		 * Fires before the transformer has been added to the database.
		 *
		 * @param array $meta Transformer meta.
		 */
		do_action( 'edd_pre_insert_transformer', $meta );

		$this->ID = wp_insert_post( array(
			'post_type'   => 'wpna_transformer',
			'post_title'  => $meta['name'],
			'post_status' => 'active',
		) );

		foreach ( $meta as $key => $value ) {
			$this->update_meta( $key, $value );
		}

		/**
		 * Fires after the transformer code is inserted.
		 *
		 * @param array $meta {
		 *     The transformer details.
		 *
		 *     @type string $name     The name of the transformer.
		 *     @type string $status   The transformer status. Defaults to active.
		 *     @type string $type     The type of ransformer.
		 *     @type string $selector The selector to apply the transformer to.
		 *     @type string $rule     The transformer rule to apply.
		 * }
		 * @param int $ID The ID of the transformer that was inserted.
		 */
		do_action( 'wpna_post_insert_transformer', $meta, $this->ID );

		// Clear the cache.
		wpna_clear_transformer_cache();

		// Setup the transformer again.
		$this->setup_transformer( WP_Post::get_instance( $this->ID ) );

		return $this->ID;
	}

	/**
	 * Update an existing discount in the database.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param array $args Discount details.
	 * @return mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new discount.
	 */
	public function update( $args ) {
		$meta = $this->build_meta( $args );

		/**
		 * Filter the data being updated
		 *
		 * @param array $meta Discount meta.
		 * @param int   $ID   Discount ID.
		 */
		$meta = apply_filters( 'wpna_update_transformer', $meta, $this->ID );

		/**
		 * Fires before the transformer has been updated in the database.
		 *
		 * @param array $meta Transformer meta.
		 * @param int   $ID   Transformer ID.
		 */
		do_action( 'wpna_pre_update_transformer', $meta, $this->ID );

		wp_update_post( array(
			'ID'          => $this->ID,
			'post_title'  => $meta['name'],
			'post_status' => $meta['status'],
		) );

		foreach ( $meta as $key => $value ) {
			$this->update_meta( $key, $value );
		}

		// Clear the cache.
		wpna_clear_transformer_cache();

		// Setup the transformer again.
		$this->setup_transformer( WP_Post::get_instance( $this->ID ) );

		/**
		 * Fires after the transformer has been updated in the database.
		 *
		 * @param array $meta Transformer meta.
		 * @param int   $ID   Transformer ID.
		 */
		do_action( 'wpna_post_update_transformer', $meta, $this->ID );

		return $this->ID;
	}

	/**
	 * Build Transformer Meta Array.
	 *
	 * @access private
	 * @param array $args Transformer meta.
	 * @return array Filtered and sanitized transformer args.
	 */
	private function build_meta( $args ) {
		if ( ! is_array( $args ) || empty( $args ) ) {
			return null;
		}

		$selector_key = 'selector_' . $args['type'];

		$meta = array(
			'name'     => ! empty( $args['name'] ) ? $args['name'] : '',
			'status'   => ! empty( $args['status'] ) ? $args['status'] : 'active',
			'type'     => ! empty( $args['type'] ) ? $args['type'] : '',
			'selector' => ! empty( $args[ $selector_key ] ) ? $args[ $selector_key ] : '',
			'rule'     => ! empty( $args['rule'] ) ? $args['rule'] : '',
		);

		if ( 'pattern_matcher' === $meta['rule'] ) {
			$meta['search_for']   = ! empty( $args['search_for'] ) ? $args['search_for'] : '';
			$meta['replace_with'] = ! empty( $args['replace_with'] ) ? $args['replace_with'] : '';
		}

		if ( 'custom' === $meta['type'] ) {
			$meta['properties'] = ! empty( $args['properties'] ) ? $args['properties'] : '';
		}

		return $meta;
	}

	/**
	 * Helper method to update post meta associated with the transformer.
	 *
	 * @access public
	 * @param string $key        Meta key.
	 * @param string $value      Meta value.
	 * @param string $prev_value Previous meta value.
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $key = '', $value = '', $prev_value = '' ) {
		if ( empty( $key ) || '' === $key ) {
			return false;
		}

		$key = '_wpna_transformer_' . $key;

		$value = apply_filters( 'wpna_update_transformer_meta_' . $key, $value, $this->ID );

		return update_post_meta( $this->ID, $key, $value, $prev_value );
	}

	/**
	 * Helper method to retrieve meta data associated with the transformer.
	 *
	 * @access public
	 * @param string $key    Meta key.
	 * @param bool   $single Return single item or array.
	 */
	public function get_meta( $key = '', $single = true ) {
		$meta = get_post_meta( $this->ID, '_wpna_transformer_' . $key, $single );
		return $meta;
	}
}
