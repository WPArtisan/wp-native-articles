<?php
/**
 * Facebook feed admin class.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sets up the Facebook Instant Article RSS Feed.
 *
 * Registers a settings tab in the admin Facebook IA Page.
 * Creates the RSS feed to be consumed by Facebook.
 *
 * @since  1.0.0
 */
class WPNA_Admin_Facebook_Feed extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_facebook';

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
		add_action( 'admin_init',                             array( $this, 'setup_settings' ), 10, 0 );
		add_action( 'wpna_admin_facebook_tabs',               array( $this, 'setup_tabs' ), 10, 1 );
		add_action( 'update_option_wpna_options',             array( $this, 'flush_feed_query_cache' ), 10, 0 );
		add_action( 'add_option_wpna_options',                array( $this, 'flush_feed_query_cache' ), 10, 0 );
		add_action( 'save_post',                              array( $this, 'flush_feed_query_cache' ), 10, 0 );

		// A custom endpoint is added with the permalinks API so we need to
		// flush the rewrite rules to clean it up and remove it.
		add_action( 'update_option_wpna_options',             'flush_rewrite_rules', 10, 0 );
		add_action( 'add_option_wpna_options',                'flush_rewrite_rules', 10, 0 );

		// These actions are only applied if Instant Articles is enabled
		if ( wpna_switch_to_boolean( wpna_get_option('fbia_enable') ) ) {
			add_action( 'init',                                array( $this, 'add_feed' ), 10, 0 );

			add_filter( 'feed_content_type',                   array( $this, 'feed_content_type' ), 10, 2 );
			add_filter( 'wp_headers',                          array( $this, 'feed_headers' ), 10, 2 );
			add_filter( 'posts_request',                       array( $this, 'pre_feed_query_cache' ), 10, 2 );
			add_filter( 'posts_results',                       array( $this, 'post_feed_query_cache' ), 10, 2 );
			add_filter( 'pre_get_posts',                       array( $this, 'feed_query' ), 10, 1 );
		}

		// Form sanitization filters
		add_filter( 'wpna_sanitize_option-fbia_posts_per_feed',  'absint', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_article_caching', 'boolval', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_modified_only',   'boolval', 10, 1 );

		// Set a default feed slug value
		add_filter( 'wpna_get_option_fbia_feed_slug',          array( $this, 'default_feed_slug' ), 10, 3 );
	}

	/**
	 * Register Facebook feed settings.
	 *
	 * Uses the settings API to create and register all the settings fields in
	 * the Feed tab of the Facebook admin. Uses the global wpna_sanitize_options()
	 * function to provide validation hooks based on each field name.
	 *
	 * The settings API replaces the entire global settings object with the new
	 * values. wpna_sanitize_options() takes any other fields found in the global
	 * settings array that aren't registered here and merges them in to ensure
	 * they're not lost.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function setup_settings() {

		// Group name. Used for nonces etc
		$option_group = 'wpna_facebook-feed';

		register_setting( $option_group, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			'wpna_facebook-feed_section_1',
			esc_html__( 'RSS Feed', 'wp-native-articles' ),
			array( $this, 'section_1_callback' ),
			$option_group // This needs to be unique to this tab + Match the one called in do_settings_sections
		);

		add_settings_field(
			'fbia_feed_slug',
			'<label for="fbia_feed_slug">' . esc_html__( 'Feed Slug', 'wp-native-articles' ) . '</label>',
			array( $this, 'feed_slug_callback' ),
			$option_group,
			'wpna_facebook-feed_section_1'
		);

		add_settings_field(
			'fbia_posts_per_feed',
			'<label for="fbia_posts_per_feed">' . esc_html__( 'Posts Per Feed', 'wp-native-articles' ) . '</label>',
			array( $this, 'posts_per_feed_callback' ),
			$option_group,
			'wpna_facebook-feed_section_1'
		);

		add_settings_field(
			'fbia_article_caching',
			'<label for="fbia_article_caching">' . esc_html__( 'Cache Article Content', 'wp-native-articles' ) . '</label>',
			array( $this, 'article_caching_callback' ),
			$option_group,
			'wpna_facebook-feed_section_1'
		);

		add_settings_field(
			'fbia_modified_only',
			'<label for="fbia_modified_only">' . esc_html__( 'Modified Only', 'wp-native-articles' ) . '</label>',
			array( $this, 'modified_only_callback' ),
			$option_group,
			'wpna_facebook-feed_section_1'
		);
	}

	/**
	 * Registers a tab in the Facebook admin.
	 *
	 * Uses the tabs helper class.
	 *
	 * @access public
	 * @return null
	 */
	public function setup_tabs( $tabs ) {
		$tabs->register_tab(
			'feed',
			esc_html__( 'Feed', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'feed_tab_callback' )
		);
	}

	/**
	 * Output the HTML for the Feed tab.
	 *
	 * Uses the settings API and outputs the fields registered.
	 * settings_fields() requries the name of the group of settings to ouput.
	 * do_settings_sections() requires the unique page slug for this settings form.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function feed_tab_callback() {
		?>
		<form action="options.php" method="post">
			<?php settings_fields( 'wpna_facebook-feed' ); ?>
			<?php do_settings_sections( 'wpna_facebook-feed' ); ?>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Outputs the HTML displayed at the top of the settings section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function section_1_callback() {
		$feed_slug = wpna_get_option( 'fbia_feed_slug' );;
		?>
		<p>
			<?php esc_html_e( 'These settings apply to the RSS feed of Instant Articles.', 'wp-native-articles' ); ?>
			<br />
			<?php esc_html_e( 'Unlike the API, the RSS feed generates many articles at once. This can be very intensive so it is highly recomended you enable caching.', 'wp-native-articles' ); ?>
			<br />
			<?php esc_html_e( 'Your feed can be found here:', 'wp-native-articles' ); ?>
			<a href="<?php echo esc_url( site_url( '/feed/' . $feed_slug ) ); ?>" target="_blank"><?php echo esc_url( site_url( '/feed/' . $feed_slug ) ); ?></a>
		</p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_feed_slug' settings field.
	 *
	 * Maximum amount of posts to show in the feed. Facebook won't read more
	 * than 50 so let's default to that
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function feed_slug_callback() {
		?>
		<input type="text" name="wpna_options[fbia_feed_slug]" id="fbia_feed_slug" class="regular-text" value="<?php echo esc_html( wpna_get_option('fbia_feed_slug', 'facebook-instant-articles' ) ); ?>" />
		<p class="description"><?php esc_html_e( 'The endpoint of the Instant Articles Feed.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_posts_per_feed' settings field.
	 *
	 * Maximum amount of posts to show in the feed. Facebook won't read more
	 * than 50 so let's default to that
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function posts_per_feed_callback() {
		?>
		<input type="number" min="0" step="1" name="wpna_options[fbia_posts_per_feed]" id="fbia_posts_per_feed" class="regular-text" value="<?php echo intval( wpna_get_option('fbia_posts_per_feed') ); ?>" />
		<p class="description"><?php esc_html_e( 'Limit the maximum amount of articles in the feed.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_article_caching' settings field.
	 *
	 * Whether to cache the article contents in transients or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function article_caching_callback() {
		?>
		<label for="fbia_article_caching">
			<input type="hidden" name="wpna_options[fbia_article_caching]" value="0">
			<input type="checkbox" name="wpna_options[fbia_article_caching]" id="fbia_article_caching" class="" value="true"<?php checked( (bool) wpna_get_option('fbia_article_caching') ); ?> />
			<?php esc_html_e( 'Cache the content of articles in the feed.', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_modified_only' settings field.
	 *
	 * Whether to show only the most recently modified posts.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function modified_only_callback() {
		?>
		<label for="fbia_modified_only">
			<input type="hidden" name="wpna_options[fbia_modified_only]" value="0">
			<input type="checkbox" name="wpna_options[fbia_modified_only]" id="fbia_modified_only" class="" value="true"<?php checked( (bool) wpna_get_option('fbia_modified_only') ); ?> />
			<?php esc_html_e( 'Only show recently modified posts in the feed.', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	/**
	 * Register the feed with WordPress.
	 *
	 * Creates endpoints for the feed with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function add_feed() {

		$feed_slug = wpna_get_option( 'fbia_feed_slug' );

		add_feed( $feed_slug,  array( $this, 'feed_callback' ) );
	}

	/**
	 * Caches the main feed query.
	 *
	 * Our main feed query is very expensive. This runs before the feed query is
	 * called, checks if it has alraedy been cached in transients and if so
	 * makes sure no query runs.
	 *
	 * @since 1.0.0
	 * @link https://www.reddit.com/r/Wordpress/comments/19crcn/best_practice_for_hijacking_main_loop_and_caching/
	 *
	 * @access public
	 * @param  array    $request
	 * @param  WP_Query $query
	 * @return array
	 */
	public function pre_feed_query_cache( $request, $query ){

		$feed_slug = wpna_get_option( 'fbia_feed_slug' );

		if ( $query->is_feed( $feed_slug ) && $query->is_main_query() && wpna_get_option('fbia_article_caching') ) {

			if ( get_transient( 'wpna_facebook_feed_posts' ) )
				$request = null;

		}

		return $request;
	}

	/**
	 * Caches the main feed query.
	 *
	 * This runs after the query has been called. If the query has previously
	 * been cached then it pulls the results from transients and sets them as
	 * the feed results. This will cache it for 1 hour. Everytime a post is
	 * updated it is also flushed.
	 *
	 * @since 1.0.0
	 * @link https://www.reddit.com/r/Wordpress/comments/19crcn/best_practice_for_hijacking_main_loop_and_caching/
	 *
	 * @access public
	 * @param  array    $posts
	 * @param  WP_Query $query
	 * @return array
	 */
	public function post_feed_query_cache( $posts, $query ){

		$feed_slug = wpna_get_option( 'fbia_feed_slug' );

		if ( $query->is_feed( $feed_slug ) && $query->is_main_query() && wpna_get_option('fbia_article_caching') ) {

			if ( $cached_posts = get_transient( 'wpna_facebook_feed_posts' ) ) {
				$posts = $cached_posts;
			} else {
				set_transient( 'wpna_facebook_feed_posts', $posts, HOUR_IN_SECONDS );
			}

		}

		return $posts;
	}

	/**
	 * Flush the feed cache.
	 *
	 * Whenever a post is updated flush the feed query cache.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  int  $post_id
	 * @return null
	 */
	public function flush_feed_query_cache() {
		delete_transient( 'wpna_facebook_feed_posts' );
	}

	/**
	 * Adjusts the query params for the instant articles feed.
	 *
	 * Sets order and number. Adds a fitler to try and remove posts with no content.
	 * Optionally adds a date query to only show modified posts in the last 24 hours.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  WP_Query $query
	 * @return WP_Query
	 */
	public function feed_query( $query ) {

		// Requested option name, / value to default to.
		$feed_slug = wpna_get_option( 'fbia_feed_slug' );

		if ( $query->is_feed( $feed_slug ) && $query->is_main_query() ) {

			// Try and filter out any empty posts
			add_filter( 'posts_where', array( $this, 'filter_empty_posts' ), 10, 1 );

			$query->set( 'orderby', 'modified' );
			$query->set( 'posts_per_page', intval( wpna_get_option('fbia_posts_per_feed', 10 ) ) );
			$query->set( 'posts_per_rss', intval( wpna_get_option('fbia_posts_per_feed', 10 ) ) );

			if ( wpna_get_option('fbia_modified_only' ) ) {
				$query->set( 'date_query', array(
					array(
						'column' => 'post_modified',
						'after'  => '1 day ago',
					),
				) );
			}

		}

		return $query;
	}

	/**
	 * Remove posts with no content from the results.
	 *
	 * Facebook doesn't like empty articles so let's try and remove them
	 * Unhook this filter, we only want it to run once.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $where
	 * @return string
	 */
	public function filter_empty_posts( $where = '' ) {
		remove_filter( current_filter(), array( $this, __FUNCTION__ ) );
		return $where . " AND trim(coalesce(post_content, '')) <>''";
	}

	/**
	 * Endpoint for the feed.
	 *
	 * Adds an action to enable the setting of headers.
	 * Locates the correct template for the feed and loads it in.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function feed_callback() {
		/**
		 * Executed before the RSS feed.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpna_facebook_pre_feed' );

		// Load in the main feed template
		include wpna_locate_template( 'wpna-feed' );

		/**
		 * Executed at the end of the RSS feed.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpna_facebook_post_feed' );
	}

	/**
	 * Set the feed contant type.
	 *
	 * Filter the type, this hook wil set the correct HTTP header for Content-type.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $content_type The current feed content type.
	 * @param  string $type         The feed we're dealing with.
	 * @return string $content_type The new content type.
	 */
	public function feed_content_type( $content_type, $type ) {

		$feed_slug = wpna_get_option( 'fbia_feed_slug' );

		if ( $feed_slug === $type ) {
			$content_type = feed_content_type( 'rss2' );
		}

		return $content_type;
	}

	/**
	 * Set no caching headers for the feed.
	 *
	 * Ensures that when article caching is disabled so is browser caching.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $headers The current feed headers.
	 * @param  WP    $wp      WordPress environment setup class.
	 * @return null
	 */
	public function feed_headers( $headers, $wp ) {

		if ( ! wpna_get_option('fbia_article_caching') ) {
			$headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
			$headers['Pragma'] = 'no-cache';
			$headers['Expires'] = '0';
		}

		return $headers;
	}

	/**
	 * Set a default feed slug value.
	 *
	 * If no feed slug value has been set return a defaut one.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $value   Option value.
	 * @param  string $name    Option name.
	 * @param  string $default Default value passed.
	 * @return string
	 */
	public function default_feed_slug( $value, $name, $default ) {
		if ( empty( $value ) ) {
			$value = 'facebook-instant-articles';
		}

		return $value;
	}

}
