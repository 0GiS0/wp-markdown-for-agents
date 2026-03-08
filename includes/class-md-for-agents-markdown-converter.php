<?php
/**
 * Converts WordPress post HTML content to Markdown.
 *
 * @package MD_For_Agents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts WordPress post HTML content to Markdown.
 */
class MD_For_Agents_Markdown_Converter {


	/**
	 * Generate full markdown for a post, including frontmatter metadata.
	 *
	 * @param WP_Post $post The post object.
	 * @return string The complete markdown with frontmatter.
	 */
	public function convert_post( $post ) {
		$cached = get_transient( 'md_for_agents_' . $post->ID );
		if ( false !== $cached ) {
			return $cached;
		}

		$frontmatter = $this->build_frontmatter( $post );
		$content     = $this->render_post_content( $post );
		$markdown    = $this->html_to_markdown( $content );

		$result = $frontmatter . "\n# " . get_the_title( $post ) . "\n\n" . $markdown;

		set_transient( 'md_for_agents_' . $post->ID, $result, HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Build YAML frontmatter block from post metadata.
	 *
	 * @param WP_Post $post The post object.
	 * @return string
	 */
	private function build_frontmatter( $post ) {
		$categories = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
		$tags       = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );

		$lines   = array();
		$lines[] = '---';
		$lines[] = 'title: "' . $this->escape_yaml( get_the_title( $post ) ) . '"';
		$lines[] = 'author: "' . $this->escape_yaml( get_the_author_meta( 'display_name', $post->post_author ) ) . '"';
		$lines[] = 'date: "' . get_the_date( 'Y-m-d', $post ) . '"';

		if ( ! empty( $categories ) ) {
			$lines[] = 'categories: ' . $this->yaml_array( $categories );
		}
		if ( ! empty( $tags ) ) {
			$lines[] = 'tags: ' . $this->yaml_array( $tags );
		}

		$lines[] = 'url: "' . get_permalink( $post ) . '"';
		$lines[] = '---';
		$lines[] = '';

		return implode( "\n", $lines );
	}

	/**
	 * Convert an HTML string to Markdown.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	public function html_to_markdown( $html ) {
		// Remove WordPress comments and shortcodes leftovers.
		$html = preg_replace( '/<!--.*?-->/s', '', $html );

		$md = $html;

		// Process block-level elements first.
		$md = $this->convert_headings( $md );
		$md = $this->convert_blockquotes( $md );
		$md = $this->convert_code_blocks( $md );

		$code_blocks = array();
		$md          = $this->protect_fenced_code_blocks( $md, $code_blocks );

		$md = $this->convert_lists( $md );
		$md = $this->convert_tables( $md );
		$md = $this->convert_horizontal_rules( $md );
		$md = $this->convert_paragraphs( $md );

		// Then inline elements.
		$md = $this->convert_images( $md );
		$md = $this->convert_links( $md );
		$md = $this->convert_bold( $md );
		$md = $this->convert_italic( $md );
		$md = $this->convert_inline_code( $md );
		$md = $this->convert_line_breaks( $md );

		// Strip remaining HTML tags.
		$md = wp_strip_all_tags( $md );

		// Decode HTML entities.
		$md = html_entity_decode( $md, ENT_QUOTES, 'UTF-8' );

		$md = $this->restore_protected_blocks( $md, $code_blocks );

		// Normalize whitespace: collapse 3+ newlines to 2.
		$md = preg_replace( '/\n{3,}/', "\n\n", $md );

		return trim( $md ) . "\n";
	}

	/**
	 * Render post content while avoiding front-end-only the_content injections.
	 *
	 * @param WP_Post $post The post object.
	 * @return string
	 */
	private function render_post_content( $post ) {
		$content = $post->post_content;

		if ( function_exists( 'do_blocks' ) ) {
			$content = do_blocks( $content );
		}

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wpautop( $content );

		return $content;
	}

	/**
	 * Convert HTML headings to Markdown headings.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_headings( $html ) {
		for ( $i = 6; $i >= 1; $i-- ) {
			$prefix = str_repeat( '#', $i );
			$html   = preg_replace(
				'/<h' . $i . '[^>]*>(.*?)<\/h' . $i . '>/si',
				"\n" . $prefix . ' $1' . "\n",
				$html
			);
		}
		return $html;
	}

	/**
	 * Get the inline HTML tags allowed during intermediate conversion steps.
	 *
	 * @return array<string, array>
	 */
	private function get_allowed_inline_tags() {
		return array(
			'p'      => array(),
			'br'     => array(),
			'strong' => array(),
			'em'     => array(),
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
				'rel'    => array(),
			),
			'code'   => array(),
		);
	}

	/**
	 * Convert blockquotes to Markdown blockquote lines.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_blockquotes( $html ) {
		return preg_replace_callback(
			'/<blockquote[^>]*>(.*?)<\/blockquote>/si',
			function ( $matches ) {
				$text   = wp_kses( $matches[1], $this->get_allowed_inline_tags() );
				$text   = $this->html_to_markdown( $text );
				$lines  = explode( "\n", trim( $text ) );
				$quoted = array_map(
					function ( $line ) {
						return '> ' . $line;
					},
					$lines
				);
				return "\n" . implode( "\n", $quoted ) . "\n";
			},
			$html
		);
	}

	/**
	 * Convert HTML preformatted blocks to fenced Markdown code blocks.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_code_blocks( $html ) {
		// <pre><code class="language-xxx">...</code></pre>
		$html = preg_replace_callback(
			'/<pre[^>]*>\s*<code[^>]*(?:class=["\'](?:language-)?(\w+)["\'])?[^>]*>(.*?)<\/code>\s*<\/pre>/si',
			function ( $matches ) {
				$lang = ! empty( $matches[1] ) ? $matches[1] : '';
				$code = html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' );
				return "\n```" . $lang . "\n" . trim( $code ) . "\n```\n";
			},
			$html
		);

		// Plain <pre>...</pre> without <code>.
		$html = preg_replace_callback(
			'/<pre[^>]*>(.*?)<\/pre>/si',
			function ( $matches ) {
				$code = html_entity_decode( wp_strip_all_tags( $matches[1] ), ENT_QUOTES, 'UTF-8' );
				return "\n```\n" . trim( $code ) . "\n```\n";
			},
			$html
		);

		return $html;
	}

	/**
	 * Convert HTML lists to Markdown list items.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_lists( $html ) {
		// Unordered lists.
		$html = preg_replace_callback(
			'/<ul[^>]*>(.*?)<\/ul>/si',
			function ( $matches ) {
				return $this->process_list_items( $matches[1], '-' );
			},
			$html
		);

		// Ordered lists.
		$html = preg_replace_callback(
			'/<ol[^>]*>(.*?)<\/ol>/si',
			function ( $matches ) {
				return $this->process_list_items( $matches[1], '1.' );
			},
			$html
		);

		return $html;
	}

	/**
	 * Convert list item HTML into Markdown list text.
	 *
	 * @param string $html HTML markup.
	 * @param string $marker Markdown list marker.
	 * @return string
	 */
	private function process_list_items( $html, $marker ) {
		$counter = 0;
		$result  = preg_replace_callback(
			'/<li[^>]*>(.*?)<\/li>/si',
			function ( $matches ) use ( $marker, &$counter ) {
				$counter++;
				$text   = trim( wp_kses( $matches[1], $this->get_allowed_inline_tags() ) );
				$text   = $this->convert_inline_elements( $text );
				$prefix = ( '1.' === $marker ) ? $counter . '.' : $marker;
				return $prefix . ' ' . $text;
			},
			$html
		);
		return "\n" . trim( $result ) . "\n";
	}

	/**
	 * Convert HTML tables into Markdown tables.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_tables( $html ) {
		return preg_replace_callback(
			'/<table[^>]*>(.*?)<\/table>/si',
			function ( $matches ) {
				$table_html = $matches[1];
				$rows       = array();

				// Extract header and body rows.
				preg_match_all( '/<tr[^>]*>(.*?)<\/tr>/si', $table_html, $tr_matches );

				foreach ( $tr_matches[1] as $index => $row_html ) {
					$cells = array();
					preg_match_all( '/<(?:td|th)[^>]*>(.*?)<\/(?:td|th)>/si', $row_html, $cell_matches );
					foreach ( $cell_matches[1] as $cell ) {
						$cells[] = trim( wp_strip_all_tags( $cell ) );
					}
					$rows[] = $cells;
				}

				if ( empty( $rows ) ) {
					return '';
				}

				$md_lines  = array();
				$col_count = count( $rows[0] );

				// Header row.
				$md_lines[] = '| ' . implode( ' | ', $rows[0] ) . ' |';
				$md_lines[] = '| ' . implode( ' | ', array_fill( 0, $col_count, '---' ) ) . ' |';

				// Data rows.
				$row_count = count( $rows );
				for ( $i = 1; $i < $row_count; $i++ ) {
					// Pad row to have the same number of columns.
					$row        = array_pad( $rows[ $i ], $col_count, '' );
					$md_lines[] = '| ' . implode( ' | ', $row ) . ' |';
				}

				return "\n" . implode( "\n", $md_lines ) . "\n";
			},
			$html
		);
	}

	/**
	 * Convert horizontal rules to Markdown separators.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_horizontal_rules( $html ) {
		return preg_replace( '/<hr\s*\/?>/i', "\n---\n", $html );
	}

	/**
	 * Convert paragraphs to Markdown paragraph spacing.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_paragraphs( $html ) {
		$html = preg_replace( '/<p[^>]*>(.*?)<\/p>/si', "\n$1\n", $html );
		return $html;
	}

	/**
	 * Convert HTML images to Markdown image syntax.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_images( $html ) {
		return preg_replace(
			'/<img[^>]*alt=["\']([^"\']*)["\'][^>]*src=["\']([^"\']+)["\'][^>]*\/?>/si',
			'![$1]($2)',
			preg_replace(
				'/<img[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'][^>]*\/?>/si',
				'![$2]($1)',
				$html
			)
		);
	}

	/**
	 * Convert HTML links to Markdown link syntax.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_links( $html ) {
		return preg_replace(
			'/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si',
			'[$2]($1)',
			$html
		);
	}

	/**
	 * Convert strong tags to Markdown bold syntax.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_bold( $html ) {
		$html = preg_replace( '/<(?:strong|b)>(.*?)<\/(?:strong|b)>/si', '**$1**', $html );
		return $html;
	}

	/**
	 * Convert emphasis tags to Markdown italic syntax.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_italic( $html ) {
		$html = preg_replace( '/<(?:em|i)>(.*?)<\/(?:em|i)>/si', '*$1*', $html );
		return $html;
	}

	/**
	 * Convert inline code tags to Markdown code spans.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_inline_code( $html ) {
		return preg_replace( '/<code>(.*?)<\/code>/si', '`$1`', $html );
	}

	/**
	 * Convert HTML line breaks to Markdown hard breaks.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	private function convert_line_breaks( $html ) {
		return preg_replace( '/<br\s*\/?>/i', "  \n", $html );
	}

	/**
	 * Protect fenced code blocks from later inline transformations.
	 *
	 * @param string               $markdown Markdown text.
	 * @param array<string,string> $blocks Protected block map.
	 * @return string
	 */
	private function protect_fenced_code_blocks( $markdown, &$blocks ) {
		return preg_replace_callback(
			'/```[\s\S]*?```/',
			function ( $matches ) use ( &$blocks ) {
				$placeholder            = '[[MD_FOR_AGENTS_CODE_BLOCK_' . count( $blocks ) . ']]';
				$blocks[ $placeholder ] = $matches[0];
				return $placeholder;
			},
			$markdown
		);
	}

	/**
	 * Restore protected fenced code blocks.
	 *
	 * @param string               $markdown Markdown text.
	 * @param array<string,string> $blocks Protected block map.
	 * @return string
	 */
	private function restore_protected_blocks( $markdown, $blocks ) {
		if ( empty( $blocks ) ) {
			return $markdown;
		}

		return strtr( $markdown, $blocks );
	}

	/**
	 * Quick inline-only conversion (used inside list items).
	 *
	 * @param string $text HTML fragment.
	 * @return string
	 */
	private function convert_inline_elements( $text ) {
		$text = $this->convert_links( $text );
		$text = $this->convert_bold( $text );
		$text = $this->convert_italic( $text );
		$text = $this->convert_inline_code( $text );
		$text = wp_strip_all_tags( $text );
		return $text;
	}

	/**
	 * Escape a string for YAML double-quoted output.
	 *
	 * @param string $str Raw string.
	 * @return string
	 */
	private function escape_yaml( $str ) {
		return str_replace( '"', '\\"', $str );
	}

	/**
	 * Render a scalar list as a YAML array.
	 *
	 * @param array<int,string> $items List items.
	 * @return string
	 */
	private function yaml_array( $items ) {
		$escaped = array_map(
			function ( $item ) {
				return '"' . $this->escape_yaml( $item ) . '"';
			},
			$items
		);
		return '[' . implode( ', ', $escaped ) . ']';
	}
}
