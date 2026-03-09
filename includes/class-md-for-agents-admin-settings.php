<?php
/**
 * Admin settings page for the Markdown View for AI Agents plugin.
 *
 * @package MD_For_Agents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a settings page under Settings → Markdown View.
 */
class MD_For_Agents_Admin_Settings {

	/**
	 * Option name used in the wp_options table.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'md_for_agents_settings';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'md-for-agents';

	/**
	 * Default settings values.
	 *
	 * @var array
	 */
	const DEFAULTS = array(
		'button_text'        => '',
		'show_icon'          => '1',
		'custom_css_classes' => '',
		'button_position'    => 'before',
	);

	/**
	 * Register admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add the settings page under the Settings menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Markdown View for AI Agents', 'markdown-view-for-ai-agents' ),
			__( 'Markdown View', 'markdown-view-for-ai-agents' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 */
	public function register_settings() {
		register_setting(
			self::PAGE_SLUG,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::DEFAULTS,
			)
		);

		add_settings_section(
			'md_for_agents_button_section',
			__( 'Button settings', 'markdown-view-for-ai-agents' ),
			'__return_null',
			self::PAGE_SLUG
		);

		add_settings_field(
			'button_text',
			__( 'Button text', 'markdown-view-for-ai-agents' ),
			array( $this, 'render_button_text_field' ),
			self::PAGE_SLUG,
			'md_for_agents_button_section'
		);

		add_settings_field(
			'show_icon',
			__( 'Show icon', 'markdown-view-for-ai-agents' ),
			array( $this, 'render_show_icon_field' ),
			self::PAGE_SLUG,
			'md_for_agents_button_section'
		);

		add_settings_field(
			'custom_css_classes',
			__( 'Custom CSS classes', 'markdown-view-for-ai-agents' ),
			array( $this, 'render_custom_css_classes_field' ),
			self::PAGE_SLUG,
			'md_for_agents_button_section'
		);

		add_settings_field(
			'button_position',
			__( 'Button position', 'markdown-view-for-ai-agents' ),
			array( $this, 'render_button_position_field' ),
			self::PAGE_SLUG,
			'md_for_agents_button_section'
		);
	}

	/**
	 * Sanitize the settings before saving.
	 *
	 * @param array $input Raw input from the settings form.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['button_text'] = isset( $input['button_text'] )
			? sanitize_text_field( $input['button_text'] )
			: '';

		$sanitized['show_icon'] = ! empty( $input['show_icon'] ) ? '1' : '0';

		$sanitized['custom_css_classes'] = isset( $input['custom_css_classes'] )
			? sanitize_text_field( $input['custom_css_classes'] )
			: '';

		$valid_positions              = array( 'before', 'after', 'both', 'none' );
		$sanitized['button_position'] = isset( $input['button_position'] ) && in_array( $input['button_position'], $valid_positions, true )
			? $input['button_position']
			: 'before';

		return $sanitized;
	}

	/**
	 * Return the current settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( $saved, self::DEFAULTS );
	}

	/**
	 * Enqueue CSS and JS only on this plugin's settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'md-agent-button',
			MD_FOR_AGENTS_PLUGIN_URL . 'assets/css/md-agent-button.css',
			array(),
			MD_FOR_AGENTS_VERSION
		);

		wp_enqueue_style(
			'md-agent-admin',
			MD_FOR_AGENTS_PLUGIN_URL . 'assets/css/md-agent-admin.css',
			array( 'md-agent-button' ),
			MD_FOR_AGENTS_VERSION
		);

		wp_enqueue_script(
			'md-agent-admin-preview',
			MD_FOR_AGENTS_PLUGIN_URL . 'assets/js/md-agent-admin-preview.js',
			array(),
			MD_FOR_AGENTS_VERSION,
			true
		);

		$settings = self::get_settings();

		wp_localize_script(
			'md-agent-admin-preview',
			'mdForAgentsAdmin',
			array(
				'defaultButtonText' => esc_html__( 'View as Markdown', 'markdown-view-for-ai-agents' ),
				'currentSettings'   => $settings,
			)
		);
	}

	/**
	 * Render the main settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings     = self::get_settings();
		$default_text = esc_html__( 'View as Markdown', 'markdown-view-for-ai-agents' );
		$button_text  = ! empty( $settings['button_text'] ) ? $settings['button_text'] : $default_text;
		$show_icon    = '1' === $settings['show_icon'];
		$css_classes  = $settings['custom_css_classes'];

		$extra_classes = '';
		if ( ! empty( $css_classes ) ) {
			$extra_classes = ' ' . esc_attr( $css_classes );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="md-agent-admin-layout">
				<div class="md-agent-admin-form">
					<form action="options.php" method="post">
						<?php
						settings_fields( self::PAGE_SLUG );
						do_settings_sections( self::PAGE_SLUG );
						submit_button();
						?>
					</form>
				</div>

				<div class="md-agent-admin-preview-panel">
					<h2><?php esc_html_e( 'Preview', 'markdown-view-for-ai-agents' ); ?></h2>
					<div class="md-agent-admin-preview-area">
						<div id="md-agent-preview-container">
							<div class="md-agent-button-wrapper">
								<a href="#" class="md-agent-button<?php echo esc_attr( $extra_classes ); ?>" rel="nofollow" onclick="return false;">
									<?php if ( $show_icon ) : ?>
										<svg class="md-agent-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true">
											<path d="M14.85 3c.63 0 1.15.52 1.14 1.15v7.7c0 .63-.51 1.15-1.15 1.15H1.15C.52 13 0 12.48 0 11.84V4.15C0 3.52.52 3 1.15 3zM9 11V5H7L5.5 7 4 5H2v6h2V8l1.5 1.92L7 8v3zm2.99.5L14.5 8H13V5h-2v3H9.5z"/>
										</svg>
									<?php endif; ?>
									<span class="md-agent-button-label"><?php echo esc_html( $button_text ); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the button text field.
	 */
	public function render_button_text_field() {
		$settings     = self::get_settings();
		$default_text = esc_html__( 'View as Markdown', 'markdown-view-for-ai-agents' );
		?>
		<input
			type="text"
			id="md_for_agents_button_text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_text]"
			value="<?php echo esc_attr( $settings['button_text'] ); ?>"
			placeholder="<?php echo esc_attr( $default_text ); ?>"
			class="regular-text"
		/>
		<p class="description">
			<?php
			printf(
				/* translators: %s: default button text */
				esc_html__( 'Leave empty to use the default text: "%s".', 'markdown-view-for-ai-agents' ),
				esc_html( $default_text )
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render the show icon checkbox field.
	 */
	public function render_show_icon_field() {
		$settings = self::get_settings();
		?>
		<label for="md_for_agents_show_icon">
			<input
				type="checkbox"
				id="md_for_agents_show_icon"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_icon]"
				value="1"
				<?php checked( '1', $settings['show_icon'] ); ?>
			/>
			<?php esc_html_e( 'Display the Markdown icon next to the button text.', 'markdown-view-for-ai-agents' ); ?>
		</label>
		<?php
	}

	/**
	 * Render the custom CSS classes field.
	 */
	public function render_custom_css_classes_field() {
		$settings = self::get_settings();
		?>
		<input
			type="text"
			id="md_for_agents_custom_css_classes"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_css_classes]"
			value="<?php echo esc_attr( $settings['custom_css_classes'] ); ?>"
			placeholder="my-class another-class"
			class="regular-text"
		/>
		<p class="description">
			<?php esc_html_e( 'Space-separated CSS classes to add to the button element.', 'markdown-view-for-ai-agents' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the button position select field.
	 */
	public function render_button_position_field() {
		$settings = self::get_settings();
		$options  = array(
			'before' => __( 'Before the content', 'markdown-view-for-ai-agents' ),
			'after'  => __( 'After the content', 'markdown-view-for-ai-agents' ),
			'both'   => __( 'Before and after the content', 'markdown-view-for-ai-agents' ),
			'none'   => __( 'Hidden (endpoint only)', 'markdown-view-for-ai-agents' ),
		);
		?>
		<select
			id="md_for_agents_button_position"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_position]"
		>
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['button_position'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Choose where the button appears relative to the post content. "Hidden" keeps the ?format=markdown endpoint active without showing any button.', 'markdown-view-for-ai-agents' ); ?>
		</p>
		<?php
	}
}
