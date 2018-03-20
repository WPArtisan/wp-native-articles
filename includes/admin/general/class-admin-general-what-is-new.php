<?php
/**
 * Admin setup for the General / What's New tab.
 *
 * @since  1.5.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends the Admin General page and adds the What's New page and related content.
 *
 * @since 1.5.0
 */
class WPNA_Admin_General_What_Is_New extends WPNA_Admin_Base implements WPNA_Admin_Interface {

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
	 * @param object $tabs Tab manager class.
	 * @return void
	 */
	public function setup_tabs( $tabs ) {
		$tabs->register_tab(
			'what_is_new',
			esc_html__( 'What\'s New', 'wp-native-articles' ),
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
		?>
		<div class="wrap wpna-updates">

			<div class="wpna-updates-featured">
				<div class="wpna-updates-featured-image">
					<img alt="Content Transformers Table" src="<?php echo esc_url( plugins_url( '/assets/img/updates-transformers.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<h3><?php esc_html_e( 'Transformers', 'wp-native-articles' ); ?></h3>
				<p><?php esc_html_e( 'The new Transformers feature allows you target and fix even the most troublesome post content that is not being converted correctly. We have added in several different tools enabling you to target everything from shortcodes, to the_content() filters, to individual elements using CSS selectors.', 'wp-native-articles' ); ?></p>
				<ul>
					<li>
						<strong><?php esc_html_e( 'Shortcodes & Filters', 'wp-native-articles' ); ?></strong>
						<?php esc_html_e( 'Select a shortcode or the_content() filter to bypass the parser or completely remove from your Instant Articles.', 'wp-native-articles' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Pattern Matcher', 'wp-native-articles' ); ?></strong>
						<?php esc_html_e( 'Use wildcard placeholders to match patterns in your post content then transform it into a template you define.', 'wp-native-articles' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Facebook Rules', 'wp-native-articles' ); ?></strong>
						<?php esc_html_e( 'Target individual elements using CSS or XPath selectors and apply transformation rules from the official Facebook library. Requires PHP >= 5.4.', 'wp-native-articles' ); ?>
					</li>
				</ul>
				<?php echo wp_kses(
					sprintf(
						// translators: Placement is an external link to an example page.
						__( 'Read more about transformers and see examples <a target="_blank" href="%s">here</a>', 'wp-native-articles' ),
						'https://wp-native-articles.com/features/transformers/'
					),
					array(
						'i' => true,
						'a' => array(
							'href'   => true,
							'target' => true,
						),
					)
				); ?>
				<div class="clear"></div>
			</div>

			<div class="wpna-updates-featured">
				<div class="wpna-updates-featured-image">
					<img alt="New post status option" src="<?php echo esc_url( plugins_url( '/assets/img/updates-ia-status.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<h3><?php esc_html_e( 'Post Sync Option', 'wp-native-articles' ); ?> <span class="wpna-premium-feature"><?php esc_html_e( 'Premium Only', 'wp-native-articles' ); ?></span></h3>
				<p>
					<?php echo wp_kses(
						__( 'For premium users only. The Post Syncing and Import Status options have been moved from the Status tab to the Post Publish box to make it easier to manage your Instant Articles. There is no longer a per post <b>Should Sync</b> option (you can still disable syncing globally though).', 'wp-native-articles' ),
						array(
							'b' => true,
						)
					); ?>
				</p>
				<p>
					<?php echo wp_kses(
						__( 'Now, if you wish for a post not to be published to Instant Articles set it to <b>Draft</b>.', 'wp-native-articles' ),
						array(
							'b' => true,
						)
					); ?>
				</p>
				<div class="clear"></div>
			</div>

			<div class="wpna-updates-featured">
				<div class="wpna-updates-featured-image">
					<img alt="Shortcodes example" src="<?php echo esc_url( plugins_url( '/assets/img/updates-shortcodes.png', WPNA_BASE_FILE ) ); ?>" />
				</div>
				<h3><?php esc_html_e( 'New Shortcodes', 'wp-native-articles' ); ?></h3>
				<p><?php esc_html_e( 'Two new shortcodes have been added. These allow you to manually place an ad or related article block within an individual post.', 'wp-native-articles' ); ?></p>
				<ul>
					<li>
						<code>[wpna_ad placement_id=""]</code>
						<?php echo wp_kses(
							sprintf(
								// translators: Placement is a link to the ads settings page.
								__( 'Manually place an ad block within your post. By default it uses the <a href="%s" target="_blank">ad settings</a> you have defined globally. The <i>optional</i> placement_id field allows you to specify a different audience network ad.', 'wp-native-articles' ),
								esc_url( admin_url( 'admin.php?page=wpna_facebook&section=ads' ) )
							),
							array(
								'i' => true,
								'a' => array(
									'href'   => true,
									'target' => true,
								),
							)
						); ?>
					</li>
					<li>
						<code>[wpna_related_articles title="<?php esc_html_e( 'My Optional Title', 'wp-native-articles' ); ?>" ids="1343,1388,1989"]</code>
						<?php echo wp_kses(
							__( 'Place a related articles block within your post. Specify up to four WordPress post ids to link to. The title parameter is <i>optional</i>.', 'wp-native-articles' ),
							array(
								'i' => true,
							)
						); ?>
					</li>
				</ul>
				<div class="clear"></div>
			</div>

			<h3><?php esc_html_e( 'Other Updates', 'wp-native-articles' ); ?></h3>

			<div class="wpna-other-updates-wrap">
				<div class="wpna-other-update">
					<h5><?php esc_html_e( 'Content Parser', 'wp-native-articles' ); ?></h5>
					<p>
						<?php esc_html_e( '<dd> list type elements are now converted more reliably.', 'wp-native-articles' ); ?>
						<?php esc_html_e( 'Links around images with captions are also removed.', 'wp-native-articles' ); ?>
						<?php esc_html_e( 'Searching for images by path no longer throws a warning.', 'wp-native-articles' ); ?>
					</p>
				</div>

				<div class="wpna-other-update">
					<h5><?php esc_html_e( 'Aesop Story Composer Support', 'wp-native-articles' ); ?></h5>
					<p><?php esc_html_e( 'All elements from the Aesop Story builder plugin are now supported.', 'wp-native-articles' ); ?></p>
				</div>

				<div class="wpna-other-update">
					<h5><?php esc_html_e( 'Jannah Theme Support', 'wp-native-articles' ); ?></h5>
					<p><?php esc_html_e( 'Custom post headers from the Jannah theme are now shown in Instant Articles.', 'wp-native-articles' ); ?></p>
				</div>

				<div class="wpna-other-update">
					<h5><?php esc_html_e( 'Zombify Support', 'wp-native-articles' ); ?></h5>
					<p><?php esc_html_e( 'Many custom elements from the Zombify page builder are now supported.', 'wp-native-articles' ); ?></p>
				</div>

				<div class="wpna-other-update">
					<h5><?php esc_html_e( 'WPBakery Visual Composer', 'wp-native-articles' ); ?></h5>
					<p><?php esc_html_e( 'Fixed an error when syncing posts via the API method.', 'wp-native-articles' ); ?></p>
				</div>

				<div class="wpna-other-update">
					<h5><?php esc_html_e( 'PHP 7.2 Compatibility', 'wp-native-articles' ); ?></h5>
					<p><?php esc_html_e( 'Fixed a small warning thrown when using PHP 7.2. Now fully compliant.', 'wp-native-articles' ); ?></p>
				</div>

			</div>
		</div>
		<?php
	}

}
