<?php
/**
 * Admin setup for the General / Getting Started tab.
 *
 * @since  1.5.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends the Admin General page and adds the Getting Start page and related content.
 *
 * @since 1.5.0
 */
class WPNA_Admin_General_Getting_Started extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_general';

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'wpna_admin_general_tabs', array( $this, 'setup_tabs' ), 10, 1 );
	}

	/**
	 * Registers a new tab with the tab helper for the Admin General - Getting Started page.
	 *
	 * @access public
	 * @param object $tabs Tabs manager class.
	 * @return void
	 */
	public function setup_tabs( $tabs ) {
		$tabs->register_tab(
			'getting_started',
			esc_html__( 'Getting Started', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'tab_callback' ),
			true
		);
	}

	/**
	 * Output the HTML for the Getting Start tab.
	 *
	 * Nothing fancy here. Just HTML.
	 *
	 * @access public
	 * @return void
	 */
	public function tab_callback() {
		if ( $page_id = wpna_get_option( 'fbia_authorise_id' ) ) {
			$facebook_configuration = sprintf( '<a target="__blank" href="https://www.facebook.com/%s/publishing_tools/?section=INSTANT_ARTICLES_SETTINGS">' . __( 'Configuration', 'wp-native-articles' ) . '</a>', $page_id );
		} else {
			$facebook_configuration = '<b>' . esc_html__( 'Configuration', 'wp-native-articles' ) . '</b>';
		}
		?>
		<div class="wrap wpna-getting-started">
			<h3 class="wpna-subtitle">
				<?php esc_html_e( 'Follow the steps below to setup Facebook Instant Articles using the RSS Feed method.', 'wp-native-articles' ); ?>
				<br />
				<?php esc_html_e( 'You will be up and running in no time.', 'wp-native-articles' ); ?>
			</h3>
			<div class="wpna-step">
				<div class="wpna-step-media">
					<img alt="<?php esc_html_e( 'Enabling Instant Articles', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/getting-started-one.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( '#1 Signup', 'wp-native-articles' ); ?></h3>
					<p><?php esc_html_e( 'The first step is to enable Instant Articles on your Facebook page (if you have not already done so). This grants you access to all the Instant Articles tools you need to get started.', 'wp-native-articles' ); ?></p>
					<p>
						<?php echo wp_kses(
							// translators: Placement is an external link to an example page.
							__( 'After signing up you can access the Instant Articles tools for your Facebook Page by going to <b>Publishing Tools</b> at the top of your page, then clicking <b>Configuration</b> under the Instant Articles item in the left hand menu.', 'wp-native-articles' ),
							array( 'b' => true )
						); ?>
					</p>

					<?php echo wp_kses(
						sprintf(
							// translators: Placement is an external link to an example page.
							__( '<a target="_blank" href="%s" class="button button-primary">Sign Up</a>', 'wp-native-articles' ),
							'https://www.facebook.com/instant_articles/signup'
						),
						array(
							'a' => array(
								'href'   => true,
								'class'  => true,
								'target' => true,
							),
						)
					); ?>
				</div>
				<div class="clear"></div>
			</div>

			<div class="wpna-step">
				<div class="wpna-step-media">
					<img alt="<?php esc_html_e( 'Connecting your site', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/getting-started-two.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( '#2 Authorize Your Site', 'wp-native-articles' ); ?></h3>
					<p><?php esc_html_e( 'You must register your site URL with your Facebook page before you can begin publishing Instant Articles.', 'wp-native-articles' ); ?></p>
					<p>
					<?php echo wp_kses(
						sprintf(
							// translators: Placeholder is a link to the General Settings page.
							__( 'Go to the Instant Articles Configuration screen on your Facebook page, scroll down to the <b>Connect Your Site</b> section and copy the <b>Page ID</b>. Paste the ID you just copied into the <b>Authorization ID</b> field on the <a target="_blank" href="%s">General Settings page</a> of this plugin and save it.', 'wp-native-articles' ),
							esc_url( admin_url( 'admin.php?page=wpna_facebook#fbia-content-parser' ) )
						),
						array(
							'b' => array(),
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

					<p>
					<?php echo wp_kses(
						sprintf(
							// translators: Placeholder is their Site URL.
							__( 'Copy your site URL (<code>%s</code>) and paste it into the URL input in the <b>Connect Your Site</b> section and click <b>Claim URL</b>.', 'wp-native-articles' ),
							home_url()
						),
						array(
							'b'    => array(),
							'code' => array(),
						)
					); ?>
					</p>
					<p>
					<?php echo wp_kses(
						__( 'If you successfully claimed your URL then it should appear under the <b>Your Registered URLs</b> section.', 'wp-native-articles' ),
						array( 'b' => array() )
					); ?>
					</p>
				</div>
				<div class="clear"></div>
			</div>

			<div class="wpna-step">
				<div class="wpna-step-media">
					<img alt="<?php esc_html_e( 'Style Manager', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/gettong-started-three.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( '#3 Styling', 'wp-native-articles' ); ?></h3>
					<p>
						<?php
						echo wp_kses(
							sprintf(
								// translators: Placeholder is 'configuration', possibly wrapped in a link.
								__( 'To access the style manager, go to the Instant Articles %s screen on your Facebook page and scroll down to <b>Styles</b>. Make changes to the existing default style to begin with.', 'wp-native-articles' ),
								$facebook_configuration
							),
							array(
								'b' => array(),
								'a' => array(
									'href'   => true,
									'target' => true,
								),
							)
						); ?>

					</p>
					<p>
					<?php echo wp_kses(
						__( 'At the very minimum <b>you are required to upload a logo</b>, but you should aim to style your Instant Articles to look as similar to your site as possible.', 'wp-native-articles' ),
						array( 'b' => array() )
					); ?>
					</p>

				</div>
				<div class="clear"></div>
			</div>

			<div class="wpna-step">
				<div class="wpna-step-media">
					<img alt="<?php esc_html_e( 'Ads and Analytics', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/getting-started-analytics.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( '#4 Ads & Analytics (Optional)', 'wp-native-articles' ); ?></h3>

					<p>
					<?php echo wp_kses(
						sprintf(
							// translators: Placeholder is a link to the Analytics page.
							__( 'If you are using one of the compatible WordPress Analytics plugins then your analytics should be automatically configured. If not you can manually enable analytics on the <a target="_blank" href="%s">analytics settings page</a>.', 'wp-native-articles' ),
							esc_url( admin_url( 'admin.php?page=wpna_facebook&section=analytics' ) )
						),
						array(
							'b' => array(),
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

					<p>
					<?php echo wp_kses(
						sprintf(
							// translators: Placeholder is a link to the Ads page.
							__( 'You can enable and configure Audience Network, as well as custom ads, on the <a target="_blank" href="%s">ads settings page</a>.', 'wp-native-articles' ),
							esc_url( admin_url( 'admin.php?page=wpna_facebook&section=ads' ) )
						),
						array(
							'b' => array(),
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

				</div>
				<div class="clear"></div>
			</div>

			<div class="wpna-step">
				<div class="wpna-step-media">
					<img alt="<?php esc_html_e( 'RSS Feed settings', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/getting-started-rss-feed.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( '#5 Import Your Articles', 'wp-native-articles' ); ?></h3>

					<p>
					<?php
					$feed_slug = wpna_get_option( 'fbia_feed_slug' );

					echo wp_kses(
						sprintf(
							// translators: Placeholder 1 is the RSS Feed URL. Placeholder 2 is a link to the FAcebook configuration page.
							__( 'Add your Instant Articles RSS Feed (<code>%1$s</code>) to the <b>Production RSS Feed</b> field on the %2$s page to allow Facebook to start importing your posts. It can take upto an hour for Facebook to ingest posts via the RSS Feed method.', 'wp-native-articles' ),
							esc_url( get_feed_link( $feed_slug ) ),
							$facebook_configuration
						),
						array(
							'code' => array(),
							'b'    => array(),
							'a'    => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

					<p>
					<?php
					echo wp_kses(
						sprintf(
							// translators: Placeholder is a link to the RSS Feed page.
							__( 'Further RSS Feed settings can be found on the <a target="_blank" href="%s">RSS Settings</a> page. Once your Instant Articles have been reviewed you should enabled the Cache and Modified Only options for performance reasons.', 'wp-native-articles' ),
							esc_url( admin_url( 'admin.php?page=wpna_facebook&tab=feed' ) )
						),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

				</div>
				<div class="clear"></div>
			</div>

			<div class="wpna-step">
				<div class="wpna-step-media">
					<img alt="<?php esc_html_e( 'Submit for review', 'wp-native-articles' ); ?>" src="<?php echo esc_url( plugins_url( '/assets/img/getting-started-review.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( '#6 Submit for Review', 'wp-native-articles' ); ?></h3>

					<p>
					<?php echo wp_kses(
						sprintf(
							// translators: Placeholder is a link to the Facebook configuration page.
							__( 'When Facebook has ingested 10 articles you can then submit them for review from the %s page. Reviews normally take 3-5 days and once approved your Instant Articles will be live. Any posts you share to your Facebook page will now automatically use the Instant Article version if it is available.', 'wp-native-articles' ),
							$facebook_configuration
						),
						array(
							'b' => array(),
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

				</div>
				<div class="clear"></div>
			</div>

			<div class="wpna-step">
				<div class="wpna-step-instructions">
					<h3><?php esc_html_e( 'Tips', 'wp-native-articles' ); ?></h3>

					<p>
					<?php echo wp_kses(
						sprintf(
							// translators: Placeholder is a link to the transformers page.
							__( '- Download the Facebook Pages App to (<a target="_blank" href="https://itunes.apple.com/us/app/facebook-pages-manager/id514643583?mt=8">iOS</a> or <a target="_blank" href="https://play.google.com/store/apps/details?id=com.facebook.pages.app">Android</a>) to preview your Instant Articles before you submit them. Create <a target="_blank" href="%s">Transformer</a> rules to deal with troublsome content.', 'wp-native-articles' ),
							esc_url( admin_url( 'admin.php?page=wpna_transformers' ) )
						),
						array(
							'b' => array(),
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					); ?>
					</p>

				</div>
				<div class="clear"></div>
			</div>

		</div>
		<?php
	}

}
