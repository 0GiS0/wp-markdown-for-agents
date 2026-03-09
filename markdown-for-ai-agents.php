<?php
/**
 * Main plugin bootstrap file.
 *
 * @package MD_For_Agents
 *
 * Plugin Name:       Markdown for AI Agents
 * Plugin URI:        https://github.com/0GiS0/wp-markdown-for-agents
 * Description:       Serve WordPress posts and pages as clean Markdown for AI agents via a button or ?format=markdown.
 * Version:           1.0.1
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Gisela Torres
 * Author URI:        https://www.returngis.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       markdown-for-ai-agents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MD_FOR_AGENTS_VERSION', '1.0.1' );
define( 'MD_FOR_AGENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MD_FOR_AGENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MD_FOR_AGENTS_PLUGIN_DIR . 'includes/class-md-for-agents-markdown-converter.php';
require_once MD_FOR_AGENTS_PLUGIN_DIR . 'includes/class-md-for-agents-button-injector.php';
require_once MD_FOR_AGENTS_PLUGIN_DIR . 'includes/class-md-for-agents-url-handler.php';

/**
 * Initialize the plugin.
 */
function md_for_agents_init() {
	$converter = new MD_For_Agents_Markdown_Converter();
	new MD_For_Agents_Button_Injector();
	new MD_For_Agents_URL_Handler( $converter );
}
add_action( 'init', 'md_for_agents_init' );

/**
 * Clear cached markdown for a post when it is saved.
 *
 * @param int $post_id The post ID.
 */
function md_for_agents_clear_cache( $post_id ) {
	delete_transient( 'md_for_agents_' . $post_id );
}
add_action( 'save_post', 'md_for_agents_clear_cache' );
