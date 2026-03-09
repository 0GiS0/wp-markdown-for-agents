<?php
/**
 * Injects a "View as Markdown" button into single posts and pages.
 *
 * @package MD_For_Agents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Injects the markdown button into singular content.
 */
class MD_For_Agents_Button_Injector {

	/**
	 * Default SVG icon markup for the button.
	 *
	 * @var string
	 */
	const ICON_SVG = '<svg class="md-agent-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true">' .
		'<path d="M14.85 3c.63 0 1.15.52 1.14 1.15v7.7c0 .63-.51 1.15-1.15 1.15H1.15C.52 13 0 12.48 0 11.84V4.15C0 3.52.52 3 1.15 3zM9 11V5H7L5.5 7 4 5H2v6h2V8l1.5 1.92L7 8v3zm2.99.5L14.5 8H13V5h-2v3H9.5z"/>' .
		'</svg>';

	/**
	 * Register button-related hooks.
	 */
	public function __construct() {
		add_filter( 'the_content', array( $this, 'inject_button' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Insert the markdown button into single post/page content.
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public function inject_button( $content ) {
		if ( ! $this->should_inject_button() ) {
			return $content;
		}

		$settings = MD_For_Agents_Admin_Settings::get_settings();

		/**
		 * Filter the button position relative to the post content.
		 *
		 * Accepted values: 'before', 'after', 'both', 'none'.
		 *
		 * @param string $position Current position setting.
		 */
		$position = apply_filters( 'md_for_agents_button_position', $settings['button_position'] );

		if ( 'none' === $position ) {
			return $content;
		}

		$button = $this->build_button_html( $settings );

		switch ( $position ) {
			case 'after':
				return $content . $button;
			case 'both':
				return $button . $content . $button;
			default:
				return $button . $content;
		}
	}

	/**
	 * Build the full button HTML applying settings and filters.
	 *
	 * @param array $settings Plugin settings.
	 * @return string
	 */
	private function build_button_html( $settings ) {
		$url = add_query_arg( 'format', 'markdown', get_permalink() );

		$default_text = esc_html__( 'View as Markdown', 'markdown-view-for-ai-agents' );

		/**
		 * Filter the button text.
		 *
		 * @param string $text Button text.
		 */
		$text = apply_filters(
			'md_for_agents_button_text',
			! empty( $settings['button_text'] ) ? $settings['button_text'] : $default_text
		);

		/**
		 * Filter whether to show the SVG icon.
		 *
		 * @param bool $show_icon Whether the icon is displayed.
		 */
		$show_icon = apply_filters( 'md_for_agents_button_icon', '1' === $settings['show_icon'] );

		$icon_html = $show_icon ? self::ICON_SVG . ' ' : '';

		$css_classes = 'md-agent-button';

		if ( ! empty( $settings['custom_css_classes'] ) ) {
			$css_classes .= ' ' . $settings['custom_css_classes'];
		}

		/**
		 * Filter the CSS classes applied to the button element.
		 *
		 * @param string $css_classes Space-separated CSS classes.
		 */
		$css_classes = apply_filters( 'md_for_agents_button_classes', $css_classes );

		$button = sprintf(
			'<div class="md-agent-button-wrapper">' .
				'<a href="%s" class="%s" rel="nofollow" title="%s">' .
					'%s%s' .
				'</a>' .
			'</div>',
			esc_url( $url ),
			esc_attr( $css_classes ),
			esc_attr( $text ),
			$icon_html,
			esc_html( $text )
		);

		/**
		 * Filter the complete button HTML.
		 *
		 * @param string $button The full button markup.
		 * @param string $url    The markdown URL.
		 * @param string $text   The button text.
		 */
		return apply_filters( 'md_for_agents_button_html', $button, $url, $text );
	}

	/**
	 * Only inject on the main content of the currently queried singular post.
	 */
	private function should_inject_button() {
		if ( is_admin() || ! is_singular( array( 'post', 'page' ) ) ) {
			return false;
		}

		if ( ! is_main_query() || ! in_the_loop() ) {
			return false;
		}

		$current_post_id = get_the_ID();
		$queried_post_id = get_queried_object_id();

		if ( ! $current_post_id || ! $queried_post_id ) {
			return false;
		}

		return (int) $current_post_id === (int) $queried_post_id;
	}

	/**
	 * Enqueue the button styles on singular views.
	 */
	public function enqueue_styles() {
		if ( is_singular( array( 'post', 'page' ) ) ) {
			wp_enqueue_style(
				'md-agent-button',
				MD_FOR_AGENTS_PLUGIN_URL . 'assets/css/md-agent-button.css',
				array(),
				MD_FOR_AGENTS_VERSION
			);
		}
	}
}
