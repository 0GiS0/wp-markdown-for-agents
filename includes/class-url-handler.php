<?php
/**
 * Handles requests with ?format=markdown and serves the post as plain Markdown.
 *
 * @package MD_For_Agents
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MD_For_Agents_URL_Handler {

    /** @var MD_For_Agents_Markdown_Converter */
    private $converter;

    public function __construct( MD_For_Agents_Markdown_Converter $converter ) {
        $this->converter = $converter;
        add_action( 'template_redirect', array( $this, 'handle_markdown_request' ) );
    }

    /**
     * Intercept the request if ?format=markdown is present and serve markdown.
     */
    public function handle_markdown_request() {
        if ( ! isset( $_GET['format'] ) || 'markdown' !== $_GET['format'] ) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        if ( ! is_singular( array( 'post', 'page' ) ) ) {
            return;
        }

        $post = get_queried_object();

        if ( ! $post || ! ( $post instanceof WP_Post ) ) {
            status_header( 404 );
            echo '# 404 — Post not found';
            exit;
        }

        $markdown = $this->converter->convert_post( $post );

        // Serve as plain text markdown.
        status_header( 200 );
        header( 'Content-Type: text/markdown; charset=utf-8' );
        header( 'X-Robots-Tag: noindex' );

        echo $markdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }
}
