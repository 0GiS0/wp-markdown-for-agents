=== Markdown for AI Agents ===
Contributors: inteligenciaartificial
Tags: markdown, ai, agents, content, api
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Serve any WordPress post or page as clean Markdown for AI agents. Just append ?format=markdown to any URL.

== Description ==

**Markdown for AI Agents** adds a small button at the top of every post and page that links to a Markdown version of the content. AI agents can also access the Markdown programmatically by appending `?format=markdown` to any post URL.

The generated Markdown includes YAML frontmatter with metadata (title, author, date, categories, tags) and converts all common HTML elements to their Markdown equivalents.

**Features:**

* Visible "View as Markdown" button on posts and pages
* `?format=markdown` URL parameter support
* YAML frontmatter with post metadata
* Full HTML-to-Markdown conversion (headings, lists, tables, code blocks, images, links, etc.)
* Cached output for fast response times
* Dark mode support
* Zero external dependencies

== Installation ==

1. Upload the `wp-markdown-for-agents` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Done! The button appears automatically on all posts and pages.

== Frequently Asked Questions ==

= How do AI agents use this plugin? =

AI agents append `?format=markdown` to any post URL. The plugin returns the content as `text/markdown` with YAML frontmatter metadata.

= Does this affect my SEO? =

No. The button link uses `rel="nofollow"` and the Markdown response includes a `X-Robots-Tag: noindex` header.

= Is the Markdown cached? =

Yes. The plugin caches the generated Markdown for 1 hour using WordPress transients. The cache is cleared automatically when you update a post.

== Changelog ==

= 1.0.0 =
* Initial release
* HTML-to-Markdown converter
* Button injection on posts and pages
* ?format=markdown URL handler
* YAML frontmatter with metadata
* Transient caching
