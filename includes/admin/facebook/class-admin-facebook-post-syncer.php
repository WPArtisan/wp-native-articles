<?php
/**
 * Facebook Post Syncer admin class.
 * Shoutout to Pippin for writing the code this was absed off.
 *
 * @link https://pippinsplugins.com/batch-processing-for-big-data/
 *
 * @since 1.4.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up the Facebook Instant Article Post Syncer class.
 *
 * Registers a settings tab in the admin Facebook IA Page.
 *
 * @since  1.0.0
 */
class WPNA_Admin_Facebook_Post_Syncer extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_facebook';

	/**
	 * The current batch of post being acted upon.
	 *
	 * @access public
	 * @var int
	 */
	public $step = 0;

	/**
	 * The action to perform on all the articles.
	 *
	 * @access public
	 * @var string
	 */
	public $action;

	/**
	 * Environment to import the posts to.
	 *
	 * @access public
	 * @var string
	 */
	public $environment;

	/**
	 * Status to import the post as.
	 *
	 * @access public
	 * @var string
	 */
	public $status;

	/**
	 * Number of posts to porcess per batch.
	 *
	 * @access public
	 * @var int
	 */
	public $posts_per_batch = 10;

	/**
	 * Total number of posts found to to sync.
	 *
	 * @access public
	 * @var int
	 */
	public $total = 0;

	/**
	 * Restrict posts to these categories.
	 *
	 * @access public
	 * @var null
	 */
	public $categories = null;

	/**
	 * Restrict posts to these authors.
	 *
	 * @access public
	 * @var null
	 */
	public $authors = null;

	/**
	 * Restrict posts newer than this start date.
	 *
	 * @access public
	 * @var null
	 */
	public $start_date = null;

	/**
	 * Restrict posts older than this end date.
	 *
	 * @access public
	 * @var null
	 */
	public $end_date = null;

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'wpna_admin_facebook_tabs', array( $this, 'setup_tabs' ), 10, 1 );
	}

	/**
	 * Registers a tab in the Facebook admin.
	 *
	 * Uses the tabs helper class.
	 *
	 * @access public
	 * @param object $tabs Tab helper class.
	 * @return void
	 */
	public function setup_tabs( $tabs ) {
		$tabs->register_tab(
			'post_syncer',
			esc_html__( 'Post Syncer', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'post_syncer_tab_callback' )
		);
	}

	/**
	 * Output the HTML for the post_syncer tab.
	 *
	 * Uses the settings API and outputs the fields registered.
	 * settings_fields() requries the name of the group of settings to ouput.
	 * do_settings_sections() requires the unique page slug for this settings form.
	 *
	 * @access public
	 * @return void
	 */
	public function post_syncer_tab_callback() {
		?>
		<form class="wpna-sync-posts-form" action="" method="post">
			<?php
				wpna_premium_feature_notice();
			?>
			<h4>
				<?php esc_html_e( 'Mass sync posts to Facebook Instant Articles through the API.', 'wp-native-articles' ); ?>
			</h4>
			<p>
				<?php esc_html_e( 'A quick and easy way to convert all your old posts to Instant Articles. Also useful if you’ve made changes and want to mass update all your current Instant Articles.', 'wp-native-articles' ); ?>
			</p>

			<h3><?php esc_html_e( 'Instant Article Options', 'wp-native-articles' ); ?></h3>

			<ul>

				<?php do_action( 'wpna_post_syncer_form_before_action' ); ?>
				<li>
					<label>
						<span class="label-responsive"><?php esc_html_e( 'Action', 'wp-native-articles' ); ?>:</span>
						<select name="action" id="action" class="postform" disabled="disabled">
							<option value="update"><?php esc_html_e( 'Update', 'wp-native-articles' ); ?></option>
							<option value="delete"><?php esc_html_e( 'Delete', 'wp-native-articles' ); ?></option>
						</select>
					</label>
				</li>

				<?php do_action( 'wpna_post_syncer_form_before_environment' ); ?>
				<li>
					<label>
						<span class="label-responsive"><?php esc_html_e( 'Environment', 'wp-native-articles' ); ?>:</span>
						<select name="environment" id="environment" class="postform" disabled="disabled">
							<option value="development"><?php esc_html_e( 'Development', 'wp-native-articles' ); ?></option>
							<option value="production"><?php esc_html_e( 'Production', 'wp-native-articles' ); ?></option>
						</select>
					</label>
				</li>

				<?php do_action( 'wpna_post_syncer_form_before_status' ); ?>
				<li style="display:none;">
					<label>
						<span class="label-responsive"><?php esc_html_e( 'Status', 'wp-native-articles' ); ?>:</span>
						<select name="draft" id="draft" class="postform" disabled="disabled">
							<option value="false"><?php esc_html_e( 'Draft', 'wp-native-articles' ); ?></option>
							<option value="true"><?php esc_html_e( 'Live', 'wp-native-articles' ); ?></option>
						</select>
					</label>
				</li>

			</ul>

			<h3><?php esc_html_e( 'Post filters', 'wp-native-articles' ); ?></h3>

			<ul>
				<?php do_action( 'wpna_post_syncer_form_before_categories' ); ?>
				<li>
					<label>
						<span class="label-responsive"><?php esc_html_e( 'Categories', 'wp-native-articles' ); ?>:</span>
						<select name="categories[]" id="categories" class="postform" disabled="disabled">
							<option value="0" selected="selected"><?php esc_html_e( 'All', 'wp-native-articles' ); ?></option>
							<?php $categories = get_categories( array( 'hide_empty' => false ) ); ?>
							<?php foreach ( $categories as $category ) : ?>
								<option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?> (<?php echo esc_html( $category->count ); ?>)</option>
							<?php endforeach; ?>
						</select>
					</label>
				</li>

				<?php do_action( 'wpna_post_syncer_form_before_authors' ); ?>
				<li>
					<span class="label-responsive"><?php esc_html_e( 'Authors', 'wp-native-articles' ); ?>:</span>
					<select name="authors[]" id="authors" class="postform" disabled="disabled">
						<option value="0" selected="selected"><?php esc_html_e( 'All', 'wp-native-articles' ); ?></option>
						<?php
							$users = get_users(
								array(
									'orderby' => 'nicename',
									'fields'  => array( 'ID', 'display_name' ),
								)
							);
						?>
						<?php foreach ( $users as $user ) : ?>
							<option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</li>

				<?php do_action( 'wpna_post_syncer_form_before_date' ); ?>
				<li>
					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e( 'Date range:', 'wp-native-articles' ); ?></legend>
						<label for="post-start-date" class="label-responsive"><?php esc_html_e( 'Start date', 'wp-native-articles' ); ?>:</label>
						<input type="date" id="post-start-date" name="post_start_date" value="" disabled="disabled" />
						<label for="post-end-date" class="label-responsive"><?php esc_html_e( 'End date', 'wp-native-articles' ); ?>:</label>
						<input type="date" id="post-end-date" name="post_end_date" value="" disabled="disabled" />
					</fieldset>
				</li>

			</ul>

			<?php submit_button( esc_html__( 'Sync Posts', 'wp-native-articles' ), 'secondary', null, null, array( 'disabled' => 'true' ) ); ?>

		</form>
		<?php
	}

}
