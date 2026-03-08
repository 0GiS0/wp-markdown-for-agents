<?php
/**
 * Converts WordPress post HTML content to Markdown.
 *
 * @package MD_For_Agents
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        $content     = apply_filters( 'the_content', $post->post_content );
        $markdown    = $this->html_to_markdown( $content );

        $result = $frontmatter . "\n# " . get_the_title( $post ) . "\n\n" . $markdown;

        set_transient( 'md_for_agents_' . $post->ID, $result, HOUR_IN_SECONDS );

        return $result;
    }

    /**
     * Build YAML frontmatter block from post metadata.
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
     */
    public function html_to_markdown( $html ) {
        // Remove WordPress comments and shortcodes leftovers.
        $html = preg_replace( '/<!--.*?-->/s', '', $html );

        $md = $html;

        // Process block-level elements first.
        $md = $this->convert_headings( $md );
        $md = $this->convert_blockquotes( $md );
        $md = $this->convert_code_blocks( $md );
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
        $md = strip_tags( $md );

        // Decode HTML entities.
        $md = html_entity_decode( $md, ENT_QUOTES, 'UTF-8' );

        // Normalize whitespace: collapse 3+ newlines to 2.
        $md = preg_replace( '/\n{3,}/', "\n\n", $md );

        return trim( $md ) . "\n";
    }

    // ──────────────────────────────────────────
    // Block-level conversions
    // ──────────────────────────────────────────

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

    private function convert_blockquotes( $html ) {
        return preg_replace_callback(
            '/<blockquote[^>]*>(.*?)<\/blockquote>/si',
            function ( $matches ) {
                $text  = strip_tags( $matches[1], '<p><br><strong><em><a><code>' );
                $text  = $this->html_to_markdown( $text );
                $lines = explode( "\n", trim( $text ) );
                $quoted = array_map( function ( $line ) {
                    return '> ' . $line;
                }, $lines );
                return "\n" . implode( "\n", $quoted ) . "\n";
            },
            $html
        );
    }

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

        // Plain <pre>...</pre> without <code>
        $html = preg_replace_callback(
            '/<pre[^>]*>(.*?)<\/pre>/si',
            function ( $matches ) {
                $code = html_entity_decode( strip_tags( $matches[1] ), ENT_QUOTES, 'UTF-8' );
                return "\n```\n" . trim( $code ) . "\n```\n";
            },
            $html
        );

        return $html;
    }

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

    private function process_list_items( $html, $marker ) {
        $counter = 0;
        $result  = preg_replace_callback(
            '/<li[^>]*>(.*?)<\/li>/si',
            function ( $matches ) use ( $marker, &$counter ) {
                $counter++;
                $text   = trim( strip_tags( $matches[1], '<strong><em><a><code>' ) );
                $text   = $this->convert_inline_elements( $text );
                $prefix = ( '1.' === $marker ) ? $counter . '.' : $marker;
                return $prefix . ' ' . $text;
            },
            $html
        );
        return "\n" . trim( $result ) . "\n";
    }

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
                        $cells[] = trim( strip_tags( $cell ) );
                    }
                    $rows[] = $cells;
                }

                if ( empty( $rows ) ) {
                    return '';
                }

                $md_lines   = array();
                $col_count  = count( $rows[0] );

                // Header row.
                $md_lines[] = '| ' . implode( ' | ', $rows[0] ) . ' |';
                $md_lines[] = '| ' . implode( ' | ', array_fill( 0, $col_count, '---' ) ) . ' |';

                // Data rows.
                for ( $i = 1; $i < count( $rows ); $i++ ) {
                    // Pad row to have the same number of columns.
                    $row        = array_pad( $rows[ $i ], $col_count, '' );
                    $md_lines[] = '| ' . implode( ' | ', $row ) . ' |';
                }

                return "\n" . implode( "\n", $md_lines ) . "\n";
            },
            $html
        );
    }

    private function convert_horizontal_rules( $html ) {
        return preg_replace( '/<hr\s*\/?>/i', "\n---\n", $html );
    }

    private function convert_paragraphs( $html ) {
        $html = preg_replace( '/<p[^>]*>(.*?)<\/p>/si', "\n$1\n", $html );
        return $html;
    }

    // ──────────────────────────────────────────
    // Inline conversions
    // ──────────────────────────────────────────

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

    private function convert_links( $html ) {
        return preg_replace(
            '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si',
            '[$2]($1)',
            $html
        );
    }

    private function convert_bold( $html ) {
        $html = preg_replace( '/<(?:strong|b)>(.*?)<\/(?:strong|b)>/si', '**$1**', $html );
        return $html;
    }

    private function convert_italic( $html ) {
        $html = preg_replace( '/<(?:em|i)>(.*?)<\/(?:em|i)>/si', '*$1*', $html );
        return $html;
    }

    private function convert_inline_code( $html ) {
        return preg_replace( '/<code>(.*?)<\/code>/si', '`$1`', $html );
    }

    private function convert_line_breaks( $html ) {
        return preg_replace( '/<br\s*\/?>/i', "  \n", $html );
    }

    /**
     * Quick inline-only conversion (used inside list items).
     */
    private function convert_inline_elements( $text ) {
        $text = $this->convert_links( $text );
        $text = $this->convert_bold( $text );
        $text = $this->convert_italic( $text );
        $text = $this->convert_inline_code( $text );
        $text = strip_tags( $text );
        return $text;
    }

    // ──────────────────────────────────────────
    // YAML helpers
    // ──────────────────────────────────────────

    private function escape_yaml( $str ) {
        return str_replace( '"', '\\"', $str );
    }

    private function yaml_array( $items ) {
        $escaped = array_map( function ( $item ) {
            return '"' . $this->escape_yaml( $item ) . '"';
        }, $items );
        return '[' . implode( ', ', $escaped ) . ']';
    }
}
