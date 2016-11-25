<?php
/**
 * Admin setup for Premium page.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends the Admin Base and adds the Premium page.
 *
 * @since 1.0.0
 */
class WPNA_Admin_Premium extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_premium';

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function hooks() {
		add_action( 'wpna_admin_menu_items', array( $this, 'add_menu_items' ), 15, 0 );
	}

	/**
	 * Setups up menu items.
	 *
	 * This adds the sub level menu page for the Support page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function add_menu_items() {
		$page_hook = add_submenu_page(
			'wpna_facebook', // Parent page slug
			esc_html__( 'WP Native Article Premium', 'wp-native-articles' ),
			'<span style="color:#f18500">' . esc_html__( 'Premium', 'wp-native-articles' ) . '</span>',
			'manage_options', // Debug cotains potentially sensitive information
			$this->page_slug,
			array( $this, 'output_callback' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'setup_meta_boxes' ) );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.0.0
		 * @param string $page_hook The Unique hook of the newly registered page
		 */
		do_action( 'wpna_admin_premium_menu_items', $page_hook );
	}

	/**
	 * Outputs HTML for Premium page.
	 *
	 * The Support page is a tabbed interface. It uses
	 * the WPNA_Helper_Tabs class to setup and register the tabbed interface.
	 * The WPNA_Helper_Tabs class is initiated in the setup_tabs method.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return nul
	 */
	public function output_callback() {
		?>
		<style>
			.wpna-pro-box {
				background: #ffffff;
				border: #cdcdcd;
				padding: 30px;
				max-width: 100%;
			}
			.row {
				display: flex;
				justify-content: space-between;
				margin-bottom: 30px;
			}
			.row .child {
				width: 48%;
			}
			img {
				width: 100%;
			}
			.lead-text {
				font-size: 18px;
				line-height: 24px;
			}
		</style>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h1><?php esc_html_e( 'WP Native Articles Premium', 'wp-native-articles' ); ?></h1>
			<div class="wrap">

				<div class="wpna-pro-box">

					<section class="features features-4">
						<div class="container">
							<div class="row">

								<div>
									<p class="lead-text">The Premium version of WP Native Articles comes with full API support</p>
									<ul>
										<li> * <b>Manage Articles</b> Publish, unpublish and manage Instant Articles directly from the WP post page.</li>
										<li> * <b>Real Time Sync</b> Articles synced instantly from WordPress, no waiting for FB to scrape the RSS feed.</li>
										<li> * <b>Article Import Status</b> Instant Articles Errors &amp; import status display live in every article.</li>
										<li> * <b>Live Analytics</b> Individual and aggregated site overview.</li>
										<li> * <b>Premium support</b></li>
									</ul>
								</div>

								<a class="button button-primary" target="_blank" href="https://wp-native-articles.com/?utm_source=fplugin&utm_medium=premium_page--top">Get Premium &#187;</a>

							</div>
						</div>
					</section>

					<section class="features features-4">
						<div class="container">
							<div class="row">

								<div class="child">
									<img alt="Feature Image" src="<?php echo esc_url( plugins_url( '/assets/img/wordpress-instant-articles-api-stats.png', dirname( __FILE__ ) ) ); ?>">
								</div>

								<div class="child">
									<h4>Real Time Analytics.</h4>
									<p>Live analytics straight from Facebook for each article + aggregated overview analytics for your entire site.</p>
								</div>

							</div>
						</div>
					</section>

					<section class="features features-5">
						<div class="container">
							<div class="row">

								<div class="child">
									<h4>Live Import Status.</h4>
									<p>When a post is published or updated the Instant Article status is retrieved live from Facebook and displayed in the Admin. Any errors with the article can be seen immediately.</p>
								</div>

								<div class="child">
									<img alt="Feature Image" src="<?php echo esc_url( plugins_url( '/assets/img/wordpress-instant-articles-import-status.png', dirname( __FILE__ ) ) ); ?>">
								</div>

							</div>
						</div>
					</section>

					<section class="features features-4">
						<div class="container">
							<div class="row">
								<div class="child">
									<img alt="Feature Image" src="<?php echo esc_url( plugins_url( '/assets/img/wp-instant-articles-comments-filters.png', dirname( __FILE__ ) ) ); ?>">
								</div>

								<div class="child">
									<h4>Readable Code.</h4>
									<p>
										Coded to WordPress standards, fully commented and contains as many filters and actions as we could possibly fit. The full documentation can be found at <a target="_blank" href="http://docs.wp-native-articles.com">docs.wp-native-articles.com</a>.
									</p>
								</div>
							</div>
						</div>
					</section>


					<section class="features features-4">
						<div class="container">
							<div class="row">

								<p></p>

								<a class="button button-primary" target="_blank" href="https://wp-native-articles.com/?utm_source=fplugin&utm_medium=premium_page--top">Get Premium &#187;</a>

							</div>
						</div>
					</section>

				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Setup the screen columns.
	 *
	 * Do actions for registering meta boxes for this screen.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function setup_meta_boxes() {
		$screen = get_current_screen();

		/**
		 * Trigger the add_meta_boxes_{$screen_id} hook to allow meta boxes
		 * to be added to this screen.
		 *
		 * @since 1.0.0
		 */
		do_action( 'add_meta_boxes_' . $screen->id );

		/**
		* Trigger the add_meta_boxes hook to allow meta boxes to be added.
		 *
		 * @since 1.0.0
		 * @param string $screen->id The ID of the screen for the admin page.
		 */
		do_action( 'add_meta_boxes', $screen->id );

		// Add screen option: user can choose between 1 or 2 columns (default 2)
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
	}

}
