=== Markdown for AI Agents ===
Contributors: inteligenciaartificial
Tags: markdown, ai, agents, content, export
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Serve WordPress posts and pages as clean Markdown for AI agents and automation tools.

== Description ==

Markdown for AI Agents adds a small button to singular posts and pages so visitors, crawlers, and AI agents can open a clean Markdown version of the content.

The same Markdown output is also available programmatically by appending `?format=markdown` to a post or page URL.

The generated Markdown includes YAML frontmatter with useful metadata and converts common HTML elements into Markdown-friendly output.

Features include:

* Visible "View as Markdown" button on posts and pages
* `?format=markdown` endpoint for agents and automation tools
* YAML frontmatter with title, author, date, categories, tags, and URL
* HTML-to-Markdown conversion for headings, lists, links, images, code blocks, blockquotes, and tables
* Cached output using WordPress transients for faster repeat requests
* Lightweight front-end integration with no external services required

Typical use cases:

* AI agents that need a stable Markdown representation of a post
* internal automation or export workflows that consume article content
* debugging or reviewing how WordPress content is exposed outside the theme layer

== Installation ==

1. Upload the `markdown-for-ai-agents` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress admin.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Open any published post or page.
4. Click the "View as Markdown" button, or append `?format=markdown` to the URL.

== Frequently Asked Questions ==

= Which content types are supported? =

The plugin supports singular posts and pages on the front end.

= How do agents or automation tools access the Markdown output? =

AI agents append `?format=markdown` to any post URL. The plugin returns the content as `text/markdown` with YAML frontmatter metadata.

= Does this change my regular theme output? =

No. The normal front-end rendering stays the same, except for the added "View as Markdown" button on singular posts and pages.

= Does this affect SEO? =

No. The button link uses `rel="nofollow"` and the Markdown response includes a `X-Robots-Tag: noindex` header.

= Is the Markdown cached? =

Yes. The plugin caches the generated Markdown for 1 hour using WordPress transients. The cache is cleared automatically when you update a post.

== Upgrade Notice ==

= 1.0.2 =

Aligned the distributed plugin slug and WordPress.org assets, and improved local validation for Plugin Check.

= 1.0.1 =

Improved WordPress.org readme content and public plugin metadata.

= 1.0.0 =

Initial public release.

== Changelog ==

= 1.0.2 =
* aligned the distributed plugin slug with `markdown-for-ai-agents`
* removed WordPress.org Plugin Check warnings from the plugin bootstrap
* added Plugin Check support and SVG-to-PNG tooling to the dev container workflow

= 1.0.1 =
* Improved WordPress.org readme formatting and public metadata

= 1.0.0 =
* Initial release
* HTML-to-Markdown converter
* Button injection on posts and pages
* ?format=markdown URL handler
* YAML frontmatter with metadata
* Transient caching
