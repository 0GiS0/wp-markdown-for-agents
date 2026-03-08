<?php
/**
 * Injects a "View as Markdown" button at the top of single posts and pages.
 *
 * @package MD_For_Agents
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MD_For_Agents_Button_Injector {

    public function __construct() {
        add_filter( 'the_content', array( $this, 'inject_button' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Prepend the markdown button to single post/page content.
     */
    public function inject_button( $content ) {
        if ( ! is_singular( array( 'post', 'page' ) ) || ! is_main_query() ) {
            return $content;
        }

        $url  = add_query_arg( 'format', 'markdown', get_permalink() );
        $text = esc_html__( 'View as Markdown', 'md-for-agents' );

        $button = sprintf(
            '<div class="md-agent-button-wrapper">' .
                '<a href="%s" class="md-agent-button" rel="nofollow" title="%s">' .
                    '<svg class="md-agent-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true">' .
                        '<path d="M14.85 3c.63 0 1.15.52 1.14 1.15v7.7c0 .63-.51 1.15-1.15 1.15H1.15C.52 13 0 12.48 0 11.84V4.15C0 3.52.52 3 1.15 3zM9 11V5H7L5.5 7 4 5H2v6h2V8l1.5 1.92L7 8v3zm2.99.5L14.5 8H13V5h-2v3H9.5z"/>' .
                    '</svg> %s' .
                '</a>' .
            '</div>',
            esc_url( $url ),
            esc_attr( $text ),
            $text
        );

        return $button . $content;
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
