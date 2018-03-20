<?php
/**
 * Admin setup for Support.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends the Admin Base and adds the Support page and related content.
 *
 * @since 1.0.0
 */
class WPNA_Admin_Support extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_support';

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
			esc_html__( 'Support', 'wp-native-articles' ),
			esc_html__( 'Support', 'wp-native-articles' ),
			'manage_options', // Debug cotains potentially sensitive information.
			$this->page_slug,
			array( $this, 'output_callback' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'setup_tabs' ) );
		add_action( 'load-' . $page_hook, array( $this, 'setup_meta_boxes' ) );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.0.0
		 * @param string $page_hook The unique ID for the menu page.
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_support_menu_items', $page_hook, $this->page_slug );
	}

	/**
	 * Outputs HTML for Support page.
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
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h1><?php esc_html_e( 'Support', 'wp-native-articles' ); ?></h1>
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
	 * Sets up the tab helper for the Admin Support page.
	 *
	 * Creates a new instance of the WPNA_Helper_Tabs class and registers the
	 * 'General' & 'Debug' tabs. Other tabs are added using the
	 * 'wpna_support_admin_tabs' action.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function setup_tabs() {
		$this->tabs = new WPNA_Helper_Tabs();

		$this->tabs->register_tab(
			'general',
			esc_html__( 'General', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'general_tab_callback' ),
			true
		);

		$this->tabs->register_tab(
			'debug',
			esc_html__( 'Debug', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'debug_tab_callback' )
		);

		/**
		 * Called after tabs have been setup for this page.
		 * Passes the tabs in so it can be modified, other tabs added etc.
		 *
		 * @since 1.0.0
		 * @param WPNA_Helper_Tabs $this->tabs Instance of the tabs helper. Used
		 * to register new tabs.
		 */
		do_action( 'wpna_admin_support_tabs', $this->tabs );
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
		 * Trigger the add_meta_boxes hook to allow meta boxes to be added.
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

	/**
	 * Output the HTML for the General tab.
	 *
	 * Nothing fancy here. Just HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function general_tab_callback() {
		$allowed_html = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		);
		?>
		<h1><?php esc_html_e( 'Support', 'wp-native-articles' ); ?></h1>

		<h3><?php esc_html_e( 'Documentation &amp; Knowledge Base', 'wp-native-articles' ); ?></h3>
		<p>
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the docs site.
					__( 'Documentation, tutorials and knowledge base for both the free and pro versions of the plugin can be found at <a target="_blank" href="%s">docs.wp-native-articles.com</a>.', 'wp-native-articles' ),
					$allowed_html
				),
				esc_url( 'http://docs.wp-native-articles.com' )
			); ?>
		</p>

		<h3><?php esc_html_e( 'Bugs and Support', 'wp-native-articles' ); ?></h3>
		<p>
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the support forums.
					__( 'All bugs for the free version of the plugin should be reported via the <a target="_blank" href="%s">WordPress plugin support forum</a>.', 'wp-native-articles' ),
					$allowed_html
				),
				esc_url( 'https://wordpress.org/support/plugin/wp-native-articles' )
			); ?>
		</p>
		<p>
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL account page.
					__( 'If you have the Pro version of the plugin you can access our premium support and email us directly by visiting your account <a target="_blank" href="%s">here</a>.', 'wp-native-articles' ),
					$allowed_html
				),
				esc_url( 'https://wp-native-articles.com/account/' )
			); ?>
		</p>

		<h3><?php esc_html_e( 'FAQs', 'wp-native-articles' ); ?></h3>
		<p>
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the plugin on WordPress.org.
					__( 'Popular FAQs regarding the free version are included in the WordPress readme <a target="_blank" href="%s">here</a>.', 'wp-native-articles' ),
					$allowed_html
				),
				esc_url( 'https://wordpress.org/plugins/wp-native-articles/' )
			); ?>
		</p>
		<p>
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the FAQs on wp-native-articles.com.
					__( 'Further FAQs can be found on our website <a target="_blank" href="%s">here</a>.', 'wp-native-articles' ),
					$allowed_html
				),
				esc_url( 'https://wp-native-articles.com/#faqs' )
			); ?>
		</p>
		<?php
	}

	/**
	 * Output the HTML for the debug tab.
	 *
	 * Has a summary table then a large box with full system debug.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function debug_tab_callback() {
		?>
		<style>
		.param {font-weight: bold;}
		</style>
		<h1><?php esc_html_e( 'Debug Information', 'wp-native-articles' ); ?></h1>
		<h2><?php esc_html_e( 'Summary', 'wp-native-articles' ); ?></h2>
		<table class="widefat fixed" cellspacing="0">
			<tbody>
				<tr>
					<td class="param"><?php esc_html_e( 'WordPress Version', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php bloginfo( 'version', __( 'Unavailable', 'wp-native-articles' ) ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'Site URL', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php bloginfo( 'url', esc_html__( 'Unavailable', 'wp-native-articles' ) ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'Active Theme', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo esc_html( wp_get_theme() ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'User Operating System', 'wp-native-articles' ); ?>:</td>
					<td class="value">
						<?php
						$user_agent = null;
						// Get the user agent. PHP bug with INPUT_SERVER so default to global if not found.
						// @codingStandardsIgnoreStart
						if ( filter_has_var( INPUT_SERVER, 'HTTP_USER_AGENT' ) ) {
							$user_agent = filter_input( INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING );
						} elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) { // Input var okay.
							$user_agent = filter_var( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), FILTER_SANITIZE_STRING ); // Input var okay.
						}
						// @codingStandardsIgnoreEnd

						echo $user_agent ? esc_html( $user_agent ) : '';
						?>
					</td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'PHP Version', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo function_exists( 'phpversion' ) ? esc_html( phpversion() ) : esc_html__( 'Unavailable', 'wp-native-articles' ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'MySql Version', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo esc_html( $this->get_mysql_version() ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'Memory Limit', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo esc_html( WP_MEMORY_LIMIT ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'Upload Max Filesize', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'Post Max Size', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo esc_html( ini_get( 'post_max_size' ) ); ?></td>
				</tr>
				<tr>
					<td class="param"><?php esc_html_e( 'Max Execution Time', 'wp-native-articles' ); ?>:</td>
					<td class="value"><?php echo esc_html( sprintf( '%s %s', ini_get( 'max_execution_time' ), esc_html__( 'seconds', 'wp-native-articles' ) ) ); ?></td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Full Debug Information', 'wp-native-articles' ); ?></h2>
		<pre class="debug_output" style="height: 300px; width: 80%; border: 1px solid #dcdcdc; padding: 15px; overflow: scroll;">
			<?php foreach ( $debug_info = $this->get_debug_information() as $section => $details ) : ?>
				<table border="0" cellpadding="3" width="600">
					<tbody>
						<h2><?php echo esc_html( $section ); ?></h2>
						<?php foreach ( $details as $constant => $values ) : ?>
							<tr>
								<td class="param"><?php echo esc_html( $constant ); ?></td>
								<td class="value">
									<?php
									foreach ( (array) $values as $value ) {
										echo esc_html( $value );
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>
		</pre>
		<?php
	}

	/**
	 * Constructs the debug information.
	 *
	 * Get as much info as we can for debugging purposes.
	 * May seem overkill until you're trying to debug an obscure issue.
	 * Grabs data about:
	 *  - WordPress
	 *  - plugins
	 *  - mu plugins
	 *  - themes
	 *  - wp_native_articles settngs
	 *  - PHP
	 *  - MySql
	 *  - Memcache
	 *  - Apache
	 *  - User Browser
	 *
	 * N.b. We don't want this translated otherwise I wont be able to read it!
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_debug_information() {
		$info = array();

		// Constants.
		$info['wordpress'] = array(
			'versions'                    => get_bloginfo( 'version' ),
			'url'                         => get_bloginfo( 'url' ),
			'WP_MEMORY_LIMIT'             => WP_MEMORY_LIMIT,
			'WP_MAX_MEMORY_LIMIT'         => WP_MAX_MEMORY_LIMIT,
			'WP_CONTENT_DIR'              => WP_CONTENT_DIR,
			'WP_CACHE'                    => WP_CACHE,
			'SCRIPT_DEBUG'                => SCRIPT_DEBUG,
			'MEDIA_TRASH'                 => MEDIA_TRASH,
			'SHORTINIT'                   => SHORTINIT,
			'WP_FEATURE_BETTER_PASSWORDS' => WP_FEATURE_BETTER_PASSWORDS,
			'WP_CONTENT_URL'              => WP_CONTENT_URL,
			'WP_PLUGIN_DIR'               => WP_PLUGIN_DIR,
			'WP_PLUGIN_URL'               => WP_PLUGIN_URL,
			'WPMU_PLUGIN_DIR'             => WPMU_PLUGIN_DIR,
			'WPMU_PLUGIN_URL'             => WPMU_PLUGIN_URL,
			'FORCE_SSL_ADMIN'             => FORCE_SSL_ADMIN,
			'AUTOSAVE_INTERVAL'           => AUTOSAVE_INTERVAL,
			'EMPTY_TRASH_DAYS'            => EMPTY_TRASH_DAYS,
			'WP_POST_REVISIONS'           => WP_POST_REVISIONS,
			'WP_CRON_LOCK_TIMEOUT'        => WP_CRON_LOCK_TIMEOUT,
			'TEMPLATEPATH'                => get_template_directory(),
			'STYLESHEETPATH'              => get_stylesheet_directory(),
		);

		// WordPress Plugin details.
		$info['plugins'] = get_plugins();
		foreach ( $info['plugins'] as $plugin_string => $plugin_data ) {
			$info['plugins'][ $plugin_string ]['active'] = is_plugin_active( $plugin_string );
		}

		// WordPress MU Plugins.
		if ( is_multisite() ) {
			$info['mu_plugins'] = get_mu_plugins();
		}

		// WP Themes.
		// Nothing worth doing about the lack of snake_case.
		// @codingStandardsIgnoreStart
		$all_themes = wp_get_themes();
		foreach ( $all_themes as $theme_string => $theme_data ) {
			$info['themes'][ $theme_string ] = array(
				'string'      => $theme_string,
				'name'        => $theme_data->Name,
				'ThemeURI'    => $theme_data->ThemeURI,
				'Author'      => $theme_data->Author,
				'AuthorURI'   => $theme_data->AuthorURI,
				'Version'     => $theme_data->Version,
				'Template'    => $theme_data->Template,
				'Status'      => $theme_data->Status,
				'TextDomain'  => $theme_data->TextDomain,
				'active'      => wp_get_theme() === $theme_data->Name,
			);
		}
		// @codingStandardsIgnoreEnd

		// WP Security.
		$info['wp_native_articles'] = array(
			'settings' => wpna_get_options(),
		);

		// PHP.
		if (
			! function_exists( 'ob_start' ) ||
			! function_exists( 'phpinfo' ) ||
			! function_exists( 'ob_get_contents' ) ||
			! function_exists( 'ob_end_clean' ) ||
			! function_exists( 'preg_replace' )
		) {
			$info['php'] = array( '' => esc_html__( 'This information is not available.', 'wp-native-articles' ) );
		} else {
			$info['php'] = $this->parse_phpinfo();
		}

		// MySql.
		$info['mysql'] = array(
			'version' => $this->get_mysql_version(),
		);

		// Memcache.
		if ( class_exists( 'Memcache' ) ) {

			// Get the memcached sever. PHP bug with INPUT_SERVER so default to global.
			if ( filter_has_var( INPUT_SERVER, 'server' ) ) {
				$server = filter_input( INPUT_SERVER, 'server', FILTER_SANITIZE_STRING );
			} elseif ( isset( $_SERVER['server'] ) ) { // Input var okay.
				$server = filter_var( wp_unslash( $_SERVER['server'] ), FILTER_SANITIZE_STRING ); // Input var okay.
			} else {
				$server = 'localhost';
			}

			$memcache = new Memcache();
			try {
				$is_memcache_available = $memcache->connect( $server );

				if ( $is_memcache_available ) {
					$info['memcached'] = array(
						'version' => $memcache->getVersion(),
					);
				}
			} catch ( Exception $e ) {
				if ( WP_DEBUG ) {
					// @codingStandardsIgnoreLine
					trigger_error( esc_html( $e->getMessage() ) );
				}
			}
		}

		// Webserver.
		$info['server'] = array(
			'apache' => function_exists( 'apache_get_version' ) ? apache_get_version() : 'false',
		);

		return $info;
	}

	/**
	 * Gets PHP settings.
	 *
	 * Parses the phpinfo() function and returns everything as a handy array.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function parse_phpinfo() {
		ob_start();
		// For the moment ignore this, but we may revisit it and decide if it's needed.
		// @todo. Investigate if this is still needed.
		// @codingStandardsIgnoreLine
		phpinfo( INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES );

		$pi = preg_replace(
			array(
				'#^.*<body>(.*)</body>.*$#ms',
				'#<h2>PHP License</h2>.*$#ms',
				'#<h1>Configuration</h1>#',
				"#\r?\n#",
				'#</(h1|h2|h3|tr)>#',
				'# +<#',
				"#[ \t]+#",
				'#&nbsp;#',
				'#  +#',
				'# class=".*?"#',
				'%&#039;%',
				'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
				'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
				'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
				'# +#',
				'#<tr>#',
				'#</tr>#',
			),
			array(
				'$1',
				'',
				'',
				'',
				'</$1>' . "\n",
				'<',
				' ',
				' ',
				' ',
				'',
				' ',
				'<h2>PHP Configuration</h2>\n<tr><td>PHP Version</td><td>$2</td></tr>\n<tr><td>PHP Egg</td><td>$1</td></tr>',
				'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
				'<tr><td>Zend Engine</td><td>$2</td></tr>\n<tr><td>Zend Egg</td><td>$1</td></tr>',
				' ',
				'%S%',
				'%E%',
			),
			ob_get_clean()
		);

		$sections = explode( '<h2>', strip_tags( $pi, '<h2><th><td>' ) );
		unset( $sections[0] );

		$pi = array();
		foreach ( $sections as $section ) {
			$n = substr( $section, 0, strpos( $section, '</h2>' ) );
			preg_match_all(
				'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
				$section,
				$askapache,
				PREG_SET_ORDER
			);

			foreach ( $askapache as $m ) {
				if ( isset( $m[2] ) ) {
					$pi[ $n ][ $m[1] ] = $m[2];
				}
			}
		}

		return $pi;
	}

	/**
	 * Retrives the current MySql version and returns it.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_mysql_version() {
		global $wpdb;

		if ( ! $mysql_version = wp_cache_get( 'wpna_mysqlversion' ) ) {
			return $mysql_version;
		}

		$mysql_version = $wpdb->get_var( 'select version() as mysqlversion' ); // db call ok.

		wp_cache_set( 'wpna_mysqlversion', $mysql_version );

		return $mysql_version;
	}

}
