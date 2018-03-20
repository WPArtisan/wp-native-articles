<?php
/**
 * Facebook article styling class.
 *
 * @since  1.1.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends the Admin Base and adds support for article styling & layout elements
 * in a new tab on the Facebook admin page.
 *
 * @since 1.0.0
 */
class WPNA_Admin_Facebook_Styling extends WPNA_Admin_Base implements WPNA_Admin_Interface {

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
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init',               array( $this, 'setup_settings' ), 10, 0 );
		add_action( 'wpna_admin_facebook_tabs', array( $this, 'setup_tabs' ), 10, 1 );

		// These actions are only applied if Instant Articles is enabled.
		if ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable' ) ) ) {

			add_filter( 'wpna_post_meta_box_content_tabs', array( $this, 'post_meta_box_styling_settings' ), 10, 1 );
			add_filter( 'wpna_post_meta_box_fields', array( $this, 'post_meta_box_fields' ), 10, 1 );
		}

		// Sanitize the main options.
		add_filter( 'wpna_sanitize_option_fbia_show_subtitle',                      'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_show_authors',                       'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_show_kicker',                        'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_show_media',                         'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_title',                      'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_title_font_size',            'wpna_validate_font_size', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_title_vertical_position',    'wpna_validate_vertical_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_title_horizontal_position',  'wpna_validate_horizontal_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_font_size',                  'wpna_validate_font_size', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_vertical_position',          'wpna_validate_vertical_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_horizontal_position',        'wpna_validate_horizontal_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_credit_vertical_position',   'wpna_validate_vertical_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_caption_credit_horizontal_position', 'wpna_validate_horizontal_alignment', 10, 1 );

		// Sanitize the post meta.
		add_filter( 'wpna_sanitize_post_meta_fbia_show_subtitle',                      'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_show_authors',                       'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_show_kicker',                        'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_show_media',                         'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_title',                      'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_title_font_size',            'wpna_validate_font_size', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_title_vertical_position',    'wpna_validate_vertical_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_title_horizontal_position',  'wpna_validate_horizontal_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_font_size',                  'wpna_validate_font_size', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_vertical_position',          'wpna_validate_vertical_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_horizontal_position',        'wpna_validate_horizontal_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_credit_vertical_position',   'wpna_validate_vertical_alignment', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_caption_credit_horizontal_position', 'wpna_validate_horizontal_alignment', 10, 1 );

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
	 * @return void
	 */
	public function setup_settings() {

		// Unique ID for this section.
		$section_general = 'wpna_facebook-styling-general';

		register_setting( $section_general, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			$section_general,
			esc_html__( 'General', 'wp-native-articles' ),
			array( $this, 'general_section_callback' ),
			$section_general
		);

		add_settings_field(
			'fbia_show_subtitle',
			'<label for="fbia_show_subtitle">' . esc_html__( 'Show Subtitle', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_show_subtitle_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_show_authors',
			'<label for="fbia_show_authors">' . esc_html__( 'Show Authors', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_show_authors_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_show_kicker',
			'<label for="fbia_show_kicker">' . esc_html__( 'Show Kicker', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_show_kicker_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_show_media',
			'<label for="fbia_show_media">' . esc_html__( 'Show Media', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_show_media_callback' ),
			$section_general,
			$section_general
		);

		// Unique ID for this section.
		$section_captions = 'wpna_facebook-styling-captions';

		register_setting( $section_captions, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			$section_captions,
			esc_html__( 'Captions', 'wp-native-articles' ),
			array( $this, 'captions_section_callback' ),
			$section_captions
		);

		add_settings_field(
			'fbia_caption_title',
			'<label for="fbia_caption_title">' . esc_html__( 'Show Title', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_caption_title_callback' ),
			$section_captions,
			$section_captions
		);

		add_settings_field(
			'fbia_caption_title_font_size',
			'<label for="fbia_caption_title_font_size">' . esc_html__( 'Title Font Size', 'wp-native-articles' ) . '</label>',
			array( $this, 'caption_title_font_size_callback' ),
			$section_captions,
			$section_captions
		);

		add_settings_field(
			'fbia_caption_title_vertical_position',
			'<label for="fbia_caption_title_vertical_position">' . esc_html__( 'Title Vertical Position', 'wp-native-articles' ) . '</label>',
			array( $this, 'caption_title_vertical_position_callback' ),
			$section_captions,
			$section_captions
		);

		add_settings_field(
			'fbia_caption_title_horizontal_position',
			'<label for="fbia_caption_title_horizontal_position">' . esc_html__( 'Title Horizontal Position', 'wp-native-articles' ) . '</label>',
			array( $this, 'caption_title_horizontal_position_callback' ),
			$section_captions,
			$section_captions
		);

		add_settings_field(
			'fbia_caption_font_size',
			'<label for="fbia_caption_font_size">' . esc_html__( 'Caption Font Size', 'wp-native-articles' ) . '</label>',
			array( $this, 'caption_font_size_callback' ),
			$section_captions,
			$section_captions
		);

		add_settings_field(
			'fbia_caption_vertical_position',
			'<label for="fbia_caption_vertical_position">' . esc_html__( 'Caption Vertical Position', 'wp-native-articles' ) . '</label>',
			array( $this, 'caption_vertical_position_callback' ),
			$section_captions,
			$section_captions
		);

		add_settings_field(
			'fbia_caption_horizontal_position',
			'<label for="fbia_caption_horizontal_position">' . esc_html__( 'Caption Horizontal Position', 'wp-native-articles' ) . '</label>',
			array( $this, 'caption_horizontal_position_callback' ),
			$section_captions,
			$section_captions
		);

		if ( 1 === 2 ) {
			add_settings_field(
				'fbia_caption_credit_vertical_position',
				'<label for="fbia_caption_credit_vertical_position">' . esc_html__( 'Credit Vertical Position', 'wp-native-articles' ) . '</label>',
				array( $this, 'caption_credit_vertical_position_callback' ),
				$section_captions,
				$section_captions
			);

			add_settings_field(
				'fbia_caption_credit_horizontal_position',
				'<label for="fbia_caption_credit_horizontal_position">' . esc_html__( 'Credit Horizontal Position', 'wp-native-articles' ) . '</label>',
				array( $this, 'caption_credit_horizontal_position_callback' ),
				$section_captions,
				$section_captions
			);
		}

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
			'styling',
			esc_html__( 'Styling', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'styling_tab_callback' )
		);
	}

	/**
	 * Output the HTML for the Styling tab.
	 *
	 * Uses the settings API and outputs the fields registered.
	 * settings_fields() requires the name of the group of settings to ouput.
	 * do_settings_sections() requires the unique page slug for this settings form.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function styling_tab_callback() {
		$section        = 'general';
		$section_fields = 'wpna_facebook-styling-general';

		// @codingStandardsIgnoreLine
		if ( isset( $_GET['section'] ) && 'captions' === $_GET['section'] ) {
			$section        = 'captions';
			$section_fields = 'wpna_facebook-styling-captions';
		}
		?>
		<?php // @codingStandardsIgnoreStart ?>
		<div class="wpna-subsections-wrapper">
			<ul>
				<li class="<?php if ( 'general' === $section ) echo ' active' ;?>">
					<a href="<?php echo esc_url( add_query_arg( 'section', 'general' ) ); ?>"><?php esc_html_e( 'General', 'wp-native-articles' ); ?></a>
					&nbsp;|&nbsp;
				</li>
				<li class="<?php if ( 'captions' === $section ) echo ' active' ;?>">
					<a href="<?php echo esc_url( add_query_arg( 'section', 'captions' ) ); ?>"><?php esc_html_e( 'Captions', 'wp-native-articles' ); ?></a>
				</li>
			</ul>
		</div>
		<?php // @codingStandardsIgnoreEnd ?>

		<form action="options.php" method="post">
			<?php settings_fields( $section_fields ); ?>
			<?php do_settings_sections( $section_fields ); ?>
			<?php submit_button(); ?>
		</form>

		<?php
	}

	/**
	 * Outputs the HTML displayed at the top of the gneral settings section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function general_section_callback() {
		?>
		<p><?php esc_html_e( 'Set default article layout options. These can all be overridden on a per post basis.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'show_subtitle' settings field.
	 *
	 * Whether to show the subtitle in the article header or not. The subtitle
	 * defaults to the post excerpt.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_show_subtitle_callback() {
		$values = (array) wpna_get_switch_values();
		?>
		<label for="fbia_show_subtitle">
			<?php $this->generate_styling_select_element( 'fbia_show_subtitle', $values, wpna_get_option( 'fbia_show_subtitle' ) ); ?>
			<?php esc_html_e( 'The post excerpt', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_show_subtitle' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'show_authors' settings field.
	 *
	 * Whether to show the authors in the article header or not.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_show_authors_callback() {
		$values = (array) wpna_get_switch_values();
		?>
		<label for="fbia_show_authors">
			<?php $this->generate_styling_select_element( 'fbia_show_authors', $values, wpna_get_option( 'fbia_show_authors' ) ); ?>
			<?php esc_html_e( 'The post authors', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_show_authors' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'show_kicker' settings field.
	 *
	 * Whether to show the kicker in the article header or not. The kicker
	 * defaults to a article's categories.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_show_kicker_callback() {
		$values = (array) wpna_get_switch_values();
		?>
		<label for="fbia_show_kicker">
			<?php $this->generate_styling_select_element( 'fbia_show_kicker', $values, wpna_get_option( 'fbia_show_kicker' ) ); ?>
			<?php esc_html_e( 'The post categories', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_show_kicker' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'show_media' settings field.
	 *
	 * Whether to show the media image in the article header or not. Defaults
	 * to the article's featured image.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_show_media_callback() {
		$values = (array) wpna_get_switch_values();
		?>
		<label for="fbia_show_media">
			<?php $this->generate_styling_select_element( 'fbia_show_media', $values, wpna_get_option( 'fbia_show_media' ) ); ?>
			<?php esc_html_e( 'The post featured image (or video if set)', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_show_media' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML displayed at the top of the settings section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function captions_section_callback() {
		?>
		<p>
			<?php esc_html_e( 'Use this section to set default Instant Article settings for captions.', 'wp-native-articles' ); ?>
			<?php esc_html_e( 'They can all be overidden on a per article basis and on a per image basis.', 'wp-native-articles' ); ?>
			<br />
			<?php esc_html_e( 'Facebook guidelines:', 'wp-native-articles' ); ?>
			<a href="https://developers.facebook.com/docs/instant-articles/reference/caption" target="_blank">https://developers.facebook.com/docs/instant-articles/reference/caption</a>
		</p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'caption_title' settings field.
	 *
	 * Whether to use the attachment titles or not.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_caption_title_callback() {
		$values = (array) wpna_get_switch_values();
		?>
		<label for="fbia_caption_title">
			<?php $this->generate_styling_select_element( 'fbia_caption_title', $values, wpna_get_option( 'fbia_caption_title' ) ); ?>
			<?php esc_html_e( 'Show the attachment title.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_title' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'caption_title_font_size' settings field.
	 *
	 * Sets the default font size for caption titles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_title_font_size_callback() {
		$font_sizes = (array) wpna_get_font_sizes();

		// Add an empty first element to show it's optional.
		array_unshift( $font_sizes, '' );
		?>
		<label for="fbia_caption_title_font_size">
			<?php $this->generate_styling_select_element( 'fbia_caption_title_font_size', $font_sizes, wpna_get_option( 'fbia_caption_title_font_size' ) ); ?>
			<?php esc_html_e( 'Default font size to use for caption titles.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_title_font_size' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_caption_title_vertical_position' settings field.
	 *
	 * Sets the default vertical position for caption titles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_title_vertical_position_callback() {
		$positions = (array) wpna_get_vertical_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $positions, '' );
		?>
		<label for="fbia_caption_title_vertical_position">
			<?php $this->generate_styling_select_element( 'fbia_caption_title_vertical_position', $positions, wpna_get_option( 'fbia_caption_title_vertical_position' ) ); ?>
			<?php esc_html_e( 'Default vertical position to use for caption titles.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_title_vertical_position' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_caption_title_horizontal_position' settings field.
	 *
	 * Sets the default horizontal position for caption titles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_title_horizontal_position_callback() {
		$positions = (array) wpna_get_horizontal_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $positions, '' );
		?>
		<label for="fbia_caption_title_horizontal_position">
			<?php $this->generate_styling_select_element( 'fbia_caption_title_horizontal_position', $positions, wpna_get_option( 'fbia_caption_title_horizontal_position' ) ); ?>
			<?php esc_html_e( 'Default horizontal position to use for caption titles.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_title_horizontal_position' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'caption_font_size' settings field.
	 *
	 * Sets the default font size for caption titles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_font_size_callback() {
		$font_sizes = (array) wpna_get_font_sizes();

		// Add an empty first element to show it's optional.
		array_unshift( $font_sizes, '' );
		?>
		<label for="fbia_caption_font_size">
			<?php $this->generate_styling_select_element( 'fbia_caption_font_size', $font_sizes, wpna_get_option( 'fbia_caption_font_size' ) ); ?>
			<?php esc_html_e( 'Default font size to use for caption titles.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_font_size' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_caption_vertical_position' settings field.
	 *
	 * Sets the default vertical position for caption titles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_vertical_position_callback() {
		$positions = (array) wpna_get_vertical_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $positions, '' );
		?>
		<label for="fbia_caption_vertical_position">
			<?php $this->generate_styling_select_element( 'fbia_caption_vertical_position', $positions, wpna_get_option( 'fbia_caption_vertical_position' ) ); ?>
			<?php esc_html_e( 'Default vertical position to use for caption titles.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_vertical_position' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_caption_horizontal_position' settings field.
	 *
	 * Sets the default horizontal position for caption titles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_horizontal_position_callback() {
		$positions = (array) wpna_get_horizontal_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $positions, '' );
		?>
		<label for="fbia_caption_horizontal_position">
			<?php $this->generate_styling_select_element( 'fbia_caption_horizontal_position', $positions, wpna_get_option( 'fbia_caption_horizontal_position' ) ); ?>
			<?php esc_html_e( 'Default horizontal position to use for caption titles.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_horizontal_position' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_caption_credit_vertical_position' settings field.
	 *
	 * Sets the default vertical position for caption credits.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_credit_vertical_position_callback() {
		$positions = (array) wpna_get_vertical_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $positions, '' );
		?>
		<label for="fbia_caption_credit_vertical_position">
			<?php $this->generate_styling_select_element( 'fbia_caption_credit_vertical_position', $positions, wpna_get_option( 'fbia_caption_credit_vertical_position' ) ); ?>
			<?php esc_html_e( 'Default vertical position to use for caption credits.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_credit_vertical_position' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_caption_credit_horizontal_position' settings field.
	 *
	 * Sets the default horizontal position for caption credits.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function caption_credit_horizontal_position_callback() {
		$positions = (array) wpna_get_horizontal_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $positions, '' );
		?>
		<label for="fbia_caption_credit_horizontal_position">
			<?php $this->generate_styling_select_element( 'fbia_caption_credit_horizontal_position', $positions, wpna_get_option( 'fbia_caption_credit_horizontal_position' ) ); ?>
			<?php esc_html_e( 'Default horizontal position to use for caption credits.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_caption_credit_horizontal_position' );
		?>

		<?php
	}

	/**
	 * Register the styling settings tab for use in the post meta box.
	 *
	 * Just a filter that enables modification of the $tabs array.
	 * Would be better switched to a function.
	 *
	 * @since 1.0.0
	 * @todo Refactor. Tabs class?
	 *
	 * @access public
	 * @param  array $tabs Existing tabs.
	 * @return array
	 */
	public function post_meta_box_styling_settings( $tabs ) {

		$tabs[] = array(
			'key'      => 'fbia_styling',
			'title'    => esc_html__( 'Styling', 'wp-native-articles' ),
			'callback' => array( $this, 'post_meta_box_styling_callback' ),
		);

		return $tabs;
	}

	/**
	 * Reigster fields that should be saved in the post meta.
	 *
	 * @since 1.3.5
	 * @access public
	 * @param  array $fields Fields that should be saved.
	 * @return array $fields
	 */
	public function post_meta_box_fields( $fields ) {
		$fields[] = 'fbia_show_subtitle';
		$fields[] = 'fbia_show_authors';
		$fields[] = 'fbia_show_kicker';
		$fields[] = 'fbia_show_media';
		$fields[] = 'fbia_caption_title';
		$fields[] = 'fbia_caption_title_font_size';
		$fields[] = 'fbia_caption_title_vertical_position';
		$fields[] = 'fbia_caption_title_horizontal_position';
		$fields[] = 'fbia_caption_font_size';
		$fields[] = 'fbia_caption_vertical_position';
		$fields[] = 'fbia_caption_horizontal_position';
		$fields[] = 'fbia_caption_credit_vertical_position';
		$fields[] = 'fbia_caption_credit_horizontal_position';

		return $fields;
	}

	/**
	 * Output HTML for the Styling post meta box tab.
	 *
	 * These values are set per article and override global defaults.
	 * Fields are currently hardcoded. The settings API won't work here.
	 * Fields have the same names as their global variables. This allows for
	 * checking if the global variables has been overridden at an article level
	 * or not.
	 *
	 * @since 1.1.0
	 * @todo Publish button
	 * @todo Swtich to hooks for fields
	 *
	 * @access public
	 * @param  WP_Post $post Global post object.
	 * @return void
	 */
	public function post_meta_box_styling_callback( $post ) {
		// Get all the swich values.
		$switch_values = (array) wpna_get_switch_values();

		// Add an empty first element to show it's optional.
		array_unshift( $switch_values, '' );

		// Get all the font sizes.
		$font_sizes = (array) wpna_get_font_sizes();

		// Add an empty first element to show it's optional.
		array_unshift( $font_sizes, '' );

		// Get all the vertical positions.
		$vertical_positions = (array) wpna_get_vertical_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $vertical_positions, '' );

		// Get all the horizontal positions.
		$horizontal_positions = (array) wpna_get_horizontal_alignments();

		// Add an empty first element to show it's optional.
		array_unshift( $horizontal_positions, '' );
		?>
		<h3><?php esc_html_e( 'Override Styling Values', 'wp-native-articles' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use these settings to override global values for this post only', 'wp-native-articles' ); ?></p>

		<div class="pure-form pure-form-aligned">

			<h3><?php esc_html_e( 'General', 'wp-native-articles' ); ?></h3>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_show_subtitle"><?php esc_html_e( 'Show Subtitle', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_show_subtitle', $switch_values, get_post_meta( get_the_ID(), '_wpna_fbia_show_subtitle', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_show_subtitle' );
					?>
				</div>
			</fieldset>
			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_show_authors"><?php esc_html_e( 'Show Authors', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_show_authors', $switch_values, get_post_meta( get_the_ID(), '_wpna_fbia_show_authors', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_show_authors' );
					?>
				</div>
			</fieldset>
			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_show_kicker"><?php esc_html_e( 'Show Kicker', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_show_kicker', $switch_values, get_post_meta( get_the_ID(), '_wpna_fbia_show_kicker', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_show_kicker' );
					?>
				</div>
			</fieldset>
			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_show_media"><?php esc_html_e( 'Show Media', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_show_media', $switch_values, get_post_meta( get_the_ID(), '_wpna_fbia_show_media', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_show_media' );
					?>
				</div>
			</fieldset>

			<h3><?php esc_html_e( 'Captions', 'wp-native-articles' ); ?></h3>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_title"><?php esc_html_e( 'Attachment Title', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_title', $switch_values, get_post_meta( get_the_ID(), '_wpna_fbia_caption_title', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_caption_title' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_title_font_size"><?php esc_html_e( 'Title Font Size', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_title_font_size', $font_sizes, get_post_meta( get_the_ID(), '_wpna_fbia_caption_title_font_size', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'caption_title_font_size' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_title_vertical_position"><?php esc_html_e( 'Title Vertical Position', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_title_vertical_position', $vertical_positions, get_post_meta( get_the_ID(), '_wpna_fbia_caption_title_vertical_position', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_caption_title_vertical_position' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_title_horizontal_position"><?php esc_html_e( 'Title Horizontal Position', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_title_horizontal_position', $horizontal_positions, get_post_meta( get_the_ID(), '_wpna_fbia_caption_title_horizontal_position', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_caption_title_horizontal_position' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_font_size"><?php esc_html_e( 'Caption Font Size', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_font_size', $font_sizes, get_post_meta( get_the_ID(), '_wpna_fbia_caption_font_size', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_caption_font_size' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_vertical_position"><?php esc_html_e( 'Caption Vertical Position', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_vertical_position', $vertical_positions, get_post_meta( get_the_ID(), '_wpna_fbia_caption_vertical_position', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_caption_vertical_position' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_caption_horizontal_position"><?php esc_html_e( 'Caption Horizontal Position', 'wp-native-articles' ); ?></label>
					<?php $this->generate_styling_select_element( 'fbia_caption_horizontal_position', $horizontal_positions, get_post_meta( get_the_ID(), '_wpna_fbia_caption_horizontal_position', true ) ); ?>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_caption_horizontal_position' );
					?>
				</div>
			</fieldset>

			<?php
			// No easy way to add credits it at the moment.
			// Disable for now.
			if ( 1 === 2 ) : ?>

				<fieldset>
					<div class="pure-control-group">
						<label for="fbia_caption_credit_vertical_position"><?php esc_html_e( 'Credit Vertical Position', 'wp-native-articles' ); ?></label>
						<?php $this->generate_styling_select_element( 'fbia_caption_credit_vertical_position', $vertical_positions, get_post_meta( get_the_ID(), '_wpna_fbia_caption_credit_vertical_position', true ) ); ?>
						<?php
						// Show a notice if the option has been overridden.
						wpna_post_option_overridden_notice( 'fbia_caption_credit_vertical_position' );
						?>
					</div>
				</fieldset>

				<fieldset>
					<div class="pure-control-group">
						<label for="fbia_caption_credit_horizontal_position"><?php esc_html_e( 'Credit Horizontal Position', 'wp-native-articles' ); ?></label>
						<?php $this->generate_styling_select_element( 'fbia_caption_credit_horizontal_position', $horizontal_positions, get_post_meta( get_the_ID(), '_wpna_fbia_caption_credit_horizontal_position', true ) ); ?>
						<?php
						// Show a notice if the option has been overridden.
						wpna_post_option_overridden_notice( 'fbia_caption_credit_horizontal_position' );
						?>
					</div>
				</fieldset>

			<?php endif; ?>

			<?php
			/**
			 * Add extra fields using this action. Or deregister this method
			 * altogether and register your own.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpna_post_meta_box_facebook_styling_footer' );
			?>

		</div>

		<?php
	}

	/**
	 * Helper function for generating styling select boxes.
	 *
	 * @todo Move to more generic HTML element helper function.
	 *
	 * @access public
	 * @param  string $id       The option name to use.
	 * @param  array  $options  Options to loop over.
	 * @param  string $selected The selected option.
	 * @return void
	 */
	public function generate_styling_select_element( $id, $options, $selected = null ) {
		// If it's not an associative array then create keys based on values.
		if ( array_values( $options ) === $options ) {
			$options = array_combine( $options, $options );
		}
		?>
		<select name="wpna_options[<?php echo esc_attr( $id ); ?>]" id="<?php echo esc_attr( $id ); ?>">
			<?php foreach ( $options as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $selected, $key ); ?>><?php echo esc_html( $value ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

}
