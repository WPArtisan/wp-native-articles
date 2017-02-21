<?php
/**
 * WordPress Admin tab helper class.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A helper class for creating tabbed interfaces in the WP admin.
 *
 * @since 1.0.0
 */
class WPNA_Helper_Tabs {

	/**
	 * All the currently registered tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $tabs = array();

	/**
	 * The currently active tab.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $active_tab;

	/**
	 * Constructor.
	 *
	 * Does nothing currently.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function __construct() {
	}

	/**
	 * Registers new tabs.
	 *
	 * Takes all the params and registers a new tab ready for ouput.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string  $key      Unique key for the tab.
	 * @param  string  $title    Title for the tab.
	 * @param  string  $url      The URL for the tab.
	 * @param  mixed   $callback Function to call to output the tab content.
	 * @param  boolean $default  Optional. The default tab for when none is selected
	 *                           Default false.
	 * @param  array   $attrs    Optional. Any other attrs to output on the <a> elemet
	 *                           Default empty array.
	 * @return void
	 */
	public function register_tab( $key, $title, $url, $callback, $default = false, $attrs = array() ) {
		$this->tabs[ $key ] = array(
			'title'    => $title,
			'url'      => $url,
			'callback' => $callback,
			'default'  => (boolean) $default,
			'attrs'    => $attrs,
		);
	}

	/**
	 * The currently active tab.
	 *
	 * Checks for the active tab in this order:
	 *     $_GET, $_POST, default, first tab
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string The unique key of the active tab.
	 */
	public function active_tab() {
		// Check $_GET first.
		$tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );

		// If it exists and is valid, return it.
		if ( $tab && ! empty( $this->tabs[ $tab ] ) ) {
			return $tab;
		}

		// Check $_POST next.
		$tab = filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING );

		// If it exists and is valid, return it.
		if ( $tab && ! empty( $this->tabs[ $tab ] ) ) {
			return $tab;
		}

		// Work out if a default has been set.
		foreach ( $this->tabs as $key => $params ) {
			if ( $params['default'] ) {
				return $key;
			}
		}

		// If one hasn't been set default to the first tab.
		if ( ! $this->active_tab ) {
			return key( $this->tabs );
		}
	}

	/**
	 * Outputs the HTML for the tabs navigation.
	 *
	 * Cycles through all the registered tabs and constructs and outputs
	 * the HTML for the nav.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function tabs_nav() {
		?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( (array) $this->tabs as $key => $tab ) : ?>
				<a class="nav-tab<?php echo ( $this->active_tab() === $key ? ' nav-tab-active' : '' ); ?>"
					id="<?php echo esc_attr( $key ); ?>-tab"
					href="<?php echo esc_url( add_query_arg( 'tab', $key, $tab['url'] ) ); ?>">
						<?php echo esc_html( $tab['title'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	/**
	 * Outputs the current tab content.
	 *
	 * Calls the callback registered for the current tab and outputs the content.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function tabs_content() {
		?>
		<div id="tab_container">
		<?php
		if ( isset( $this->tabs[ $this->active_tab() ] ) ) {
			call_user_func( $this->tabs[ $this->active_tab() ]['callback'] );
		}
		?>
		</div>
		<?php
	}

}
