<?php
/**
 * Base admin interface.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for the admin base class.
 *
 * All classes that extend the admin base class should
 * implement this as well.
 *
 * @since 1.0.0
 */
interface WPNA_Admin_Interface {

	/**
	 * Register hooks here
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function hooks();

	/**
	 * Returns the full url to the current page
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function page_url();

}
