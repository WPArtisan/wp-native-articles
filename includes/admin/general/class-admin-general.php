<?php
/**
 * Admin setup for General.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends the Admin Base and adds the General page and related content.
 *
 * @since 1.0.0
 */
class WPNA_Admin_General extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_general';

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
		add_action( 'wpna_admin_menu_items', array( $this, 'add_menu_items' ), 8, 2 );
	}

	/**
	 * These actions only fire on this page.
	 *
	 * @since 1.2.6
	 * @access public
	 * @return void
	 */
	public function page_hooks() {
		add_action( current_filter(), array( $this, 'setup_tabs' ), 11 );
	}

	/**
	 * Setups up menu items.
	 *
	 * This adds the sub level menu page for the General page.
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
			esc_html__( 'Dashboard', 'wp-native-articles' ),
			esc_html__( 'Dashboard', 'wp-native-articles' ),
			'manage_options', // Debug contains potentially sensitive information.
			$this->page_slug,
			array( $this, 'output_callback' )
		);

		// Load actions that should only fire on this page.
		add_action( 'load-' . $page_hook, array( $this, 'page_hooks' ) );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.0.0
		 * @param string $page_hook The unique ID for the menu page.
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_general_menu_items', $page_hook, $this->page_slug );
	}

	/**
	 * Outputs HTML for General page.
	 *
	 * The General page is a tabbed interface. It uses
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
		<div class="wrap wpna-dashboard">

			<div class="wpna-header">
				<img class="wpna-plam-icon" src="<?php echo esc_url( plugins_url( '/assets/img/palm-icon.svg', WPNA_BASE_FILE ) ); ?>" />
				<h1>
					<?php echo sprintf(
						// translators: Placeholder is the current plugin version.
						esc_html__( 'Welcome to WP Native Articles %s', 'wp-native-articles' ),
						esc_html( WPNA_VERSION )
					);
					?>
				</h1>
			</div>
			<h2 class="wpna-subtitle">
				<?php esc_html_e( 'You are now ready to get started with Facebook Instant Articles.', 'wp-native-articles' ); ?>
				<br />
				<?php esc_html_e( 'Thank you for using WP Native Articles!', 'wp-native-articles' ); ?>
			</h2>
			<div class="wrap">
				<?php $this->tabs->tabs_nav(); ?>
				<form action="options.php" method="post">
					<?php $this->tabs->tabs_content(); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Sets up the tab helper for the Admin General page.
	 *
	 * Creates a new instance of the WPNA_Helper_Tabs class and registers the
	 * 'General' & 'Debug' tabs. Other tabs are added using the
	 * 'wpna_admin_general_tabs' action.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function setup_tabs() {
		$this->tabs = new WPNA_Helper_Tabs();

		$this->tabs->register_tab(
			'dashboard',
			esc_html__( 'Dashboard', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'tab_callback' ),
			true
		);

		/**
		 * Called after tabs have been setup for this page.
		 * Passes the tabs in so it can be modified, other tabs added etc.
		 *
		 * @since 1.0.0
		 * @param WPNA_Helper_Tabs $this->tabs Instance of the tabs helper. Used
		 * to register new tabs.
		 */
		do_action( 'wpna_admin_general_tabs', $this->tabs );
	}

	/**
	 * Output the HTML for the Dashboard.
	 *
	 * @return void
	 */
	public function tab_callback() {
		$enabled  = '<span class="wpna-status-enabled">' . esc_html__( 'Enabled', 'wp-native-articles' ) . '</span>';
		$disabled = '<span class="wpna-status-disabled">' . esc_html__( 'Disabled', 'wp-native-articles' ) . '</span>';

		$rss_feed_slug  = wpna_get_option( 'fbia_feed_slug' );
		$rss_feed_items = wpna_get_option( 'fbia_posts_per_feed' );
		$ia_enabled     = ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable' ) ) ? $enabled : $disabled );
		$ads            = ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable_ads' ) ) ? $enabled : $disabled );

		// This is aggressively cached so is quick.
		$transformers = (array) wpna_get_transformers(
			array(
				// @codingStandardsIgnoreLine
				'posts_per_page' => 250,
				'post_status'    => array( 'active' ),
			)
		);
		?>

		<h3><?php esc_html_e( 'Facebook Instant Articles:', 'wp-native-articles' ); ?> <?php echo $ia_enabled; // WPCS: XSS ok. ?></h3>

		<style>
		.wpna-overview-status-wrap {
			max-width: 740px;
			background: #ffffff;
			padding: 10px;
			background: #ffffff;
			border: #cdcdcd;
		}
		.wpna-overview-status {
			width: 100%;
			border-collapse: collapse;
			table-layout: fixed;
		}
		.wpna-overview-status td {
			width: auto;
			padding: 10px 15px;
			font-size: 14px;
			line-height: 1.5;
			border-bottom: 1px solid #dcdcdc;
		}
		.wpna-overview-status td.wide {
			width: 300px;
			border-right: 1px solid #dcdcdc;
		}
		.wpna-overview-status td.title {
			font-weight: bold;
		}
		.wpna-overview-status td.title a {
			color: inherit;
			text-decoration: none;
			font-weight: bold;
		}
		.wpna-overview-status td.title a:hover {
			color: #00a0d2;
		}
		.wpna-overview-status td.subtitle {
			padding-left: 30px;
		}
		</style>


		<div class="wpna-overview-status-wrap">
			<table class="wpna-overview-status">
				<thead>
				</thead>

				<tbody>
					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=feed' ) ); ?>">
								<?php esc_html_e( 'RSS Feed:', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td>
							<?php if ( empty( $rss_feed_slug ) ) : ?>

								<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=feed#fbia_feed_slug' ) ); ?>">
									<?php echo esc_html_e( 'No RSS Feed slug set', 'wp-native-articles' ); ?>
								</a>

							<?php elseif ( empty( $rss_feed_items ) ) : ?>

								<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=feed#fbia_feed_slug' ) ); ?>">
									<?php echo esc_html_e( 'Posts Per Feed field not set', 'wp-native-articles' ); ?>
								</a>

							<?php else : ?>
								<?php echo $enabled; // WPCS: XSS ok. ?>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=api' ) ); ?>">
								<?php esc_html_e( 'API Sync', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td>
							<?php if ( defined( 'WPNA_PREMIUM' ) && WPNA_PREMIUM ) : ?>

								<?php
								$facebook_api = new WPNA_Facebook_API();
								if ( ! $facebook_api->is_connected() ) :
								?>
									<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=api' ) ); ?>">
										<?php echo esc_html_e( 'Facebook API is not connected.', 'wp-native-articles' ); ?>
									</a>
								<?php else : ?>
									<?php echo $enabled; // WPCS: XSS ok. ?>
								<?php endif; ?>

							<?php else : ?>
								<span class="wpna-premium-feature"><?php esc_html_e( 'Premium Feature', 'wp-native-articles' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>

					<?php if ( defined( 'WPNA_PREMIUM' ) && WPNA_PREMIUM ) : ?>
						<?php if ( $facebook_api->is_connected() ) : ?>

						<tr>
							<td class="wide subtitle"><?php esc_html_e( '- Enviroment', 'wp-native-articles' ); ?></td>
							<td>
								<?php echo esc_html( ucfirst( wpna_get_option( 'fbia_enviroment' ) ) ); ?>
							</td>
						</tr>

						<tr>
							<td class="wide subtitle"><?php esc_html_e( '- Sync Articles', 'wp-native-articles' ); ?></td>
							<td>
								<?php echo ( wpna_switch_to_boolean( wpna_get_option( 'fbia_sync_articles' ) ) ? $enabled : $disabled ); // WPCS: XSS ok. ?>
							</td>
						</tr>

						<?php endif; ?>
					<?php endif; ?>

					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=crawler_ingestion' ) ); ?>">
								<?php esc_html_e( 'Crawler Ingestion', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td>
							<?php if ( defined( 'WPNA_PREMIUM' ) && WPNA_PREMIUM ) : ?>
								<?php echo ( wpna_switch_to_boolean( wpna_get_option( 'fbia_crawler_ingestion' ) ) ? $enabled : $disabled ); // WPCS: XSS ok. ?>
							<?php else : ?>
								<span class="wpna-premium-feature"><?php esc_html_e( 'Premium Feature', 'wp-native-articles' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td COLSPAN="2">&nbsp;</td>
					</tr>

					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&section=ads' ) ); ?>">
								<?php esc_html_e( 'Ads', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td>
							<?php echo ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable_ads' ) ) ? $enabled : $disabled ); // WPCS: XSS ok. ?>
						</td>
					</tr>

					<?php if ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable_ads' ) ) ) : ?>

						<tr>
							<td class="wide subtitle"><?php esc_html_e( '- Autoplace Ads', 'wp-native-articles' ); ?></td>
							<td class="">
								<?php echo ( wpna_switch_to_boolean( wpna_get_option( 'fbia_auto_ad_placement' ) ) ? $enabled : $disabled ); // WPCS: XSS ok. ?>
							</td>
						</tr>

						<tr>
							<td class="wide subtitle"><?php esc_html_e( '- Ad Code', 'wp-native-articles' ); ?></td>
							<td class="">
								<?php
								$audience_network = wpna_get_option( 'fbia_ad_code_placement_id' );
								$ad_code          = wpna_get_option( 'fbia_ad_code' );

								if ( 'audience_network' === wpna_get_option( 'fbia_ad_code_type' ) && empty( $audience_network ) ) : ?>

									<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&section=ads#fbia_ad_code_placement_id' ) ); ?>">
										<?php echo esc_html_e( 'No Audience Network Placement ID set', 'wp-native-articles' ); ?>
									</a>

								<?php elseif ( 'custom' === wpna_get_option( 'fbia_ad_code_type' ) && empty( $ad_code ) ) : ?>

									<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&section=ads#fbia_ad_code' ) ); ?>">
										<?php echo esc_html_e( 'No Custom Ad Code set', 'wp-native-articles' ); ?>
									</a>

								<?php else : ?>
									<?php echo $enabled; // WPCS: XSS ok. ?>
								<?php endif; ?>
							</td>
						</tr>

					<?php endif; ?>

					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&section=analytics' ) ); ?>">
								<?php esc_html_e( 'Analytics', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td class="">
							<?php
							// Get the active analytics providers.
							$active_analytics_providers = (array) wpna_get_option( 'fbia_analytics_providers' );
							// We have a 'none' analytics field by default so account for that.
							$analytics_enabled = ! ( empty( $active_analytics_providers ) || ( 1 === count( $active_analytics_providers ) && isset( $active_analytics_providers[0] ) && 'none' === $active_analytics_providers[0] ) );
							// See if there is any code actually set.
							$analytics_code = apply_filters( 'wpna_facebook_post_analytics', '' );

							if ( ! $analytics_enabled ) : ?>

								<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&section=analytics' ) ); ?>">
									<?php echo esc_html_e( 'No Active Analytics Integration', 'wp-native-articles' ); ?>
								</a>

							<?php elseif ( empty( $analytics_code ) ) : ?>

								<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_facebook&section=analytics' ) ); ?>">
									<?php echo esc_html_e( 'No Analytics code detected', 'wp-native-articles' ); ?>
								</a>

							<?php else : ?>
								<?php echo $enabled; // WPCS: XSS ok. ?>
							<?php endif; ?>
						</td>
					</tr>

					<?php if ( $analytics_enabled ) : ?>

						<tr>
							<td class="wide subtitle"><?php esc_html_e( '- Active Analytics Integrations', 'wp-native-articles' ); ?></td>
							<td class="">
								<?php
								// Important PHP 5.2 compat. Can only array_flip int & string. Can't nest functions in empty().
								$active_analytics_providers = array_filter( $active_analytics_providers );
								if ( ! empty( $active_analytics_providers ) ) {
									$analytics_nice_names                  = wpna()->admin_facebook_analytics->get_integrations();
									$active_analytics_providers_nice_names = array_intersect_key( $analytics_nice_names, array_flip( $active_analytics_providers ) );
									echo esc_html( implode( ', ', $active_analytics_providers_nice_names ) );
								}
								?>
							</td>
						</tr>

					<?php endif; ?>


					<tr>
						<td COLSPAN="2">&nbsp;</td>
					</tr>

					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_placements' ) ); ?>">
								<?php esc_html_e( 'Placements', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td>
							<?php if ( defined( 'WPNA_PREMIUM' ) && WPNA_PREMIUM ) : ?>
								<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_placements' ) ); ?>">
									<?php
									$active_placements = wpna_get_placements();
									// translators: Placeholder is the number of active placements.
									echo esc_html( sprintf( _n( '%d active placement', '%d active placements', count( $active_placements ), 'wp-native-articles' ), count( $active_placements ) ) );
									?>
								</a>
							<?php else : ?>
								<span class="wpna-premium-feature"><?php esc_html_e( 'Premium Feature', 'wp-native-articles' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td class="wide title">
							<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_transformers' ) ); ?>">
								<?php esc_html_e( 'Transformers', 'wp-native-articles' ); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_transformers' ) ); ?>">
								<?php
								// translators: Placeholder is the number of active transformers.
								echo esc_html( sprintf( _n( '%d active transformer', '%d active transformers', count( $transformers ), 'wp-native-articles' ), count( $transformers ) ) );
								?>
							</a>
						</td>
					</tr>

				</tbody>

			</table>
		</div>


		<?php
	}

}
