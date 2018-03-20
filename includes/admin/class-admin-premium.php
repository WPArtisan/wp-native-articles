<?php
/**
 * Admin setup for Premium page.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @return void
	 */
	public function hooks() {
		add_action( 'wpna_admin_menu_items', array( $this, 'add_menu_items' ), 15, 2 );
	}

	/**
	 * Setups up menu items.
	 *
	 * This adds the sub level menu page for the Support page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $parent_page_id   The unique id of the parent page.
	 * @param string $parent_page_slug The unique slug of the parent page.
	 * @return void
	 */
	public function add_menu_items( $parent_page_id, $parent_page_slug ) {
		$page_hook = add_submenu_page(
			$parent_page_slug, // Parent page slug.
			'WP Native Articles Premium',
			'<span style="color:#f18500">' . esc_html__( 'Premium', 'wp-native-articles' ) . '</span>',
			'manage_options',
			$this->page_slug,
			array( $this, 'output_callback' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'setup_meta_boxes' ) );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.0.0
		 * @param string $page_hook The unique ID for the menu page.
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_premium_menu_items', $page_hook, $this->page_slug );
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
	 * @return void
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
			.child.image-wrap {
				border: 2px solid white;
			}
			img {
				margin: 0;
				width: 100%;
				height: auto;
				vertical-align: middle;
			}
			.lead-text {
				font-size: 24px;
				line-height: 1.5;
			}
			.child h4 {
				margin-top: 0;
				line-height: 1.5;
				font-size: 24px;
			}
			.child p {
				line-height: 1.5;
				font-size: 15px;
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
									<p class="lead-text">Power up your Instant Articles. Go Premium.</p>

									<ul>
										<li> * <b>Control your Instant Articles</b> Publish, unpublish and manage Instant Articles directly from the WP post page.</li>
										<li> * <b>Connect via the API</b> The API method converts articles instantly, no more waiting for FaceBook to scrape the RSS feed.</li>
										<li> * <b>Mass Post Syncer</b> Quickly convert all your WordPress posts to Instant Articles.</li>
										<li> * <b>Article Import Status</b> Instant Articles Errors &amp; import status display live in every article.</li>
										<li> * <b>Live Analytics</b> Individual and aggregated site overview.</li>
										<li> * <b>Placement Manager</b> Automatically add custom code, embeds, ads, images to all or some of your Instant Articles.</li>
										<li> * <b>Crawler Ingestion</b> Facebook's new method for importing Posts. A super quick way to enable Instant Articles for all your posts.</li>
										<li> * <b>Premium support</b> We're fanatical about support. Any problems we're always here to help.</li>
									</ul>
								</div>

								<a class="button button-primary" target="_blank" href="https://wp-native-articles.com/?utm_source=fplugin&utm_medium=premium_page--top">Get Premium &#187;</a>

							</div>
						</div>
					</section>

					<section class="features features-5">
						<div class="container">
							<div class="row">

								<div class="child">
									<h4>Control which posts get converted.</h4>
									<p>Sometimes you may not want your post to be converted to Instant Articles. With the Premium version you can easily disable or enable Instant Article for any supported post right from the post publish box.</p>
								</div>

								<div class="child">
									<img alt="<?php esc_html_e( 'Instant Articles Post Status', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/updates-ia-status.png', WPNA_BASE_FILE ) ); ?>">
								</div>

							</div>
						</div>
					</section>

					<section class="features features-4">
						<div class="container">
							<div class="row">

								<div class="child">
									<img alt="<?php esc_html_e( 'Mass Post Syncer', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/wordpress-mass-post-syncer.png', WPNA_BASE_FILE ) ); ?>">
								</div>

								<div class="child">
									<h4>Convert ALL your WordPress Posts.</h4>
									<p>Want to convert all your old WordPress posts to Instant Articles? Added a Placement? Made a change to the styling, ads or analytics? With the Mass Post syncer you can easily update Instant Articles version for all or some of your old WordPress posts.</p>
								</div>

							</div>
						</div>
					</section>

					<section class="features features-4">
						<div class="container">
							<div class="row">

								<div class="child">
									<h4>Real Time Analytics.</h4>
									<p>Live analytics straight from Facebook for each article + aggregated overview analytics for your entire site.</p>
								</div>

								<div class="child">
									<img alt="<?php esc_html_e( 'Instant Articles Analytics', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/wordpress-instant-articles-api-stats.png', WPNA_BASE_FILE ) ); ?>">
								</div>

							</div>
						</div>
					</section>

					<section class="features features-4">
						<div class="container">
							<div class="row">
								<div class="child">
									<img alt="<?php esc_html_e( 'Placement Manager', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/placement-manager-overview.png', WPNA_BASE_FILE ) ); ?>">
								</div>

								<div class="child">
									<h4>Placement Manager.</h4>
									<p>
										Easily add any custom code you like to some or all of your articles using the <a target="_blank" href="https://wp-native-articles.com/facebook-instant-articles/placement-manager/">Placement Manager</a>.
									</p>
									<p>Great for adding Share Buttons, Videos, Custom ads, Related articles etc etc. Anything that is valid in Instant Articles can be added.</p>
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
									<img alt="<?php esc_html_e( 'Instant Article Status', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/wordpress-instant-articles-import-status.png', WPNA_BASE_FILE ) ); ?>">
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
	 * @return void
	 */
	public function setup_meta_boxes() {
		$screen = get_current_screen();

		/**
		 * Trigger the add_meta_boxes_{$screen_id} hook to allow meta boxes
		 * to be added to this screen.
		 *
		 * @since 1.0.0
		 * @param string $screen->id The ID of the screen for the admin page.
		 * @param null For compatibility.
		 */
		do_action( 'add_meta_boxes_' . $screen->id, null );

		/**
		* Trigger the add_meta_boxes hook to allow meta boxes to be added.
		 *
		 * @since 1.0.0
		 * @param string $screen->id The ID of the screen for the admin page.
		 * @param null For compatibility.
		 */
		do_action( 'add_meta_boxes', $screen->id, null );

		// Add screen option: user can choose between 1 or 2 columns (default 2).
		add_screen_option( 'layout_columns',
			array(
				'max'     => 2,
				'default' => 2,
			)
		);
	}

}
