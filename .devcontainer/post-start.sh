#!/usr/bin/env bash

set -eu

wp_path="/srv/wordpress"
site_url="http://localhost:8080"
sample_seed_option="md_for_agents_sample_content_seeded"

export XDEBUG_MODE=off

wp_cli() {
	wp --path="$wp_path" "$@"
}

upsert_post() {
	post_type="$1"
	post_slug="$2"
	post_title="$3"
	post_content="$4"

	post_id="$(wp_cli post list --post_type="$post_type" --name="$post_slug" --field=ID 2>/dev/null | head -n 1 || true)"

	if [ -n "$post_id" ]; then
		wp_cli post update "$post_id" \
			--post_title="$post_title" \
			--post_name="$post_slug" \
			--post_content="$post_content" >/dev/null
		echo "$post_id"
		return 0
	fi

	wp_cli post create \
		--post_type="$post_type" \
		--post_status=publish \
		--post_title="$post_title" \
		--post_name="$post_slug" \
		--post_content="$post_content" \
		--porcelain
}

set_post_terms() {
	post_slug="$1"
	taxonomy="$2"
	terms_csv="$3"

	post_id="$(wp_cli post list --post_type=post --name="$post_slug" --field=ID 2>/dev/null | head -n 1 || true)"

	if [ -z "$post_id" ]; then
		return 0
	fi

	wp_cli post term set "$post_id" "$taxonomy" "$terms_csv" >/dev/null
}

seed_sample_content() {
	getting_started_page_id="$(upsert_post \
		page \
		getting-started-markdown-agents \
		'Getting Started with Markdown for Agents' \
		"<!-- wp:paragraph {\"fontSize\":\"large\"} --><p class=\"has-large-font-size\">This local page exists to test the plugin output quickly from a realistic WordPress page.</p><!-- /wp:paragraph --><!-- wp:heading {\"level\":2} --><h2>Checklist</h2><!-- /wp:heading --><!-- wp:list --><ul><li>WordPress installed through WP-CLI</li><li>Plugin activated automatically</li><li>Sample content seeded idempotently</li></ul><!-- /wp:list --><!-- wp:heading {\"level\":2} --><h2>Example prompt</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Summarize this page for an autonomous coding agent.</p><!-- /wp:paragraph -->")"

	upsert_post \
		post \
		release-notes-draft \
		'Release Notes Draft' \
		"<!-- wp:heading {\"level\":1} --><h1>Release Notes Draft</h1><!-- /wp:heading --><!-- wp:heading {\"level\":2} --><h2>Highlights</h2><!-- /wp:heading --><!-- wp:list --><ul><li>Automated WordPress bootstrap</li><li>Local plugin activation</li><li>Repeatable demo content</li></ul><!-- /wp:list --><!-- wp:heading {\"level\":2} --><h2>Pending</h2><!-- /wp:heading --><!-- wp:list {\"ordered\":true} --><ol><li>Verify markdown export output</li><li>Review button placement in the editor</li><li>Package the release zip</li></ol><!-- /wp:list -->" >/dev/null

	upsert_post \
		post \
		agent-qa-scenario \
		'Agent QA Scenario' \
		"<!-- wp:heading {\"level\":2} --><h2>Scenario</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Use this post to test how the plugin exposes headings, lists, and links for agents.</p><!-- /wp:paragraph --><!-- wp:list --><ul><li>Visit the generated markdown endpoint</li><li>Confirm headings remain stable</li><li>Confirm links remain absolute</li></ul><!-- /wp:list --><!-- wp:paragraph --><p>Related page ID: ${getting_started_page_id}</p><!-- /wp:paragraph -->" >/dev/null

	upsert_post \
		post \
		headings-lists-and-frontmatter \
		'Headings, Lists, and Frontmatter' \
		"<!-- wp:paragraph --><p>This post exists to check heading depth, ordered lists, unordered lists, and YAML frontmatter metadata.</p><!-- /wp:paragraph --><!-- wp:heading {\"level\":2} --><h2>Acceptance checklist</h2><!-- /wp:heading --><!-- wp:list --><ul><li>Heading levels remain stable</li><li>Bullet items stay as bullet items</li><li>Categories and tags appear in the frontmatter</li></ul><!-- /wp:list --><!-- wp:heading {\"level\":3} --><h3>Release steps</h3><!-- /wp:heading --><!-- wp:list {\"ordered\":true} --><ol><li>Run linting</li><li>Package the plugin</li><li>Smoke-test the markdown endpoint</li></ol><!-- /wp:list -->" >/dev/null

	upsert_post \
		post \
		links-quotes-and-inline-code \
		'Links, Quotes, and Inline Code' \
		"<!-- wp:paragraph --><p>Use this example to verify how inline links and inline code are represented in the markdown output.</p><!-- /wp:paragraph --><!-- wp:quote --><blockquote class=\"wp-block-quote\"><p>Agents work best when the source content is explicit, stable, and easy to parse.</p><cite>Local demo note</cite></blockquote><!-- /wp:quote --><!-- wp:paragraph --><p>Open the <a href=\"http://localhost:8080/getting-started-markdown-agents/\">getting started page</a> and compare the endpoint output to <code>wp post get</code>.</p><!-- /wp:paragraph -->" >/dev/null

	upsert_post \
		post \
		code-blocks-and-tables \
		'Code Blocks and Tables' \
		"<!-- wp:paragraph --><p>This sample covers fenced code blocks and HTML tables converted into markdown tables.</p><!-- /wp:paragraph --><!-- wp:code --><pre class=\"wp-block-code\"><code lang=\"bash\">wp plugin activate wp-markdown-for-agents --path=/srv/wordpress
wp option get siteurl --path=/srv/wordpress</code></pre><!-- /wp:code --><!-- wp:table --><figure class=\"wp-block-table\"><table><thead><tr><th>Check</th><th>Expected result</th></tr></thead><tbody><tr><td>Code fence</td><td>Preserved with bash language</td></tr><tr><td>Table headers</td><td>Rendered as markdown table header row</td></tr></tbody></table></figure><!-- /wp:table -->" >/dev/null

	upsert_post \
		post \
		images-and-mixed-formatting \
		'Images and Mixed Formatting' \
		"<!-- wp:paragraph --><p>This example mixes <strong>bold text</strong>, <em>emphasis</em>, and an image with alt text for markdown conversion checks.</p><!-- /wp:paragraph --><!-- wp:image {\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} --><figure class=\"wp-block-image size-large\"><img src=\"https://wordpress.org/style/images/wp-header-logo.png\" alt=\"WordPress logo\" /></figure><!-- /wp:image --><!-- wp:paragraph --><p>Verify that the generated markdown keeps the image URL and preserves inline emphasis correctly.</p><!-- /wp:paragraph -->" >/dev/null

	set_post_terms headings-lists-and-frontmatter category "Demo Content,Frontmatter"
	set_post_terms headings-lists-and-frontmatter post_tag "headings,lists,metadata"
	set_post_terms links-quotes-and-inline-code category "Demo Content,Links"
	set_post_terms links-quotes-and-inline-code post_tag "quotes,links,inline-code"
	set_post_terms code-blocks-and-tables category "Demo Content,Code"
	set_post_terms code-blocks-and-tables post_tag "code-blocks,tables"
	set_post_terms images-and-mixed-formatting category "Demo Content,Media"
	set_post_terms images-and-mixed-formatting post_tag "images,bold,italic"

	hello_world_id="$(wp_cli post list --post_type=post --name=hello-world --field=ID 2>/dev/null | head -n 1 || true)"
	if [ -n "$hello_world_id" ]; then
		wp_cli post delete "$hello_world_id" --force >/dev/null
	fi

	sample_page_id="$(wp_cli post list --post_type=page --name=sample-page --field=ID 2>/dev/null | head -n 1 || true)"
	if [ -n "$sample_page_id" ]; then
		wp_cli post delete "$sample_page_id" --force >/dev/null
	fi

	privacy_policy_id="$(wp_cli post list --post_type=page --name=privacy-policy --field=ID 2>/dev/null | head -n 1 || true)"
	if [ -n "$privacy_policy_id" ]; then
		wp_cli option delete wp_page_for_privacy_policy >/dev/null 2>&1 || true
		wp_cli post delete "$privacy_policy_id" --force >/dev/null
	fi

	wp_cli option update show_on_front posts >/dev/null
	wp_cli option delete page_on_front >/dev/null 2>&1 || true
	wp_cli option delete page_for_posts >/dev/null 2>&1 || true

	if wp_cli option get "$sample_seed_option" >/dev/null 2>&1; then
		wp_cli option update "$sample_seed_option" 1 >/dev/null
	else
		wp_cli option add "$sample_seed_option" 1 >/dev/null
	fi
}

if [ ! -d "$wp_path" ]; then
	exit 0
fi

attempt=1
max_attempts=30

while [ "$attempt" -le "$max_attempts" ]; do
	if [ -f "$wp_path/wp-config.php" ]; then
		break
	fi
	sleep 2
	attempt=$((attempt + 1))
done

if [ ! -f "$wp_path/wp-config.php" ]; then
	if [ -n "${WORDPRESS_DB_HOST:-}" ] && [ -n "${WORDPRESS_DB_NAME:-}" ] && [ -n "${WORDPRESS_DB_USER:-}" ] && [ -n "${WORDPRESS_DB_PASSWORD:-}" ]; then
		wp config create \
			--path="$wp_path" \
			--dbname="$WORDPRESS_DB_NAME" \
			--dbuser="$WORDPRESS_DB_USER" \
			--dbpass="$WORDPRESS_DB_PASSWORD" \
			--dbhost="$WORDPRESS_DB_HOST" \
			--skip-check \
			--force >/dev/null 2>&1 || true
	fi

	if [ ! -f "$wp_path/wp-config.php" ]; then
		echo "WordPress config is not ready yet; skipping local bootstrap."
		exit 0
	fi
fi

installed=0
attempt=1

while [ "$attempt" -le "$max_attempts" ]; do
	if wp_cli core is-installed >/dev/null 2>&1; then
		installed=1
		break
	fi

	if wp_cli core install \
		--url="$site_url" \
		--title="Markdown for AI Agents" \
		--admin_user="admin" \
		--admin_password="admin" \
		--admin_email="admin@example.com" \
		--skip-email >/dev/null 2>&1; then
		installed=1
		break
	fi

	sleep 2
	attempt=$((attempt + 1))
done

if [ "$installed" -eq 0 ]; then
	echo "WordPress database bootstrap is not ready yet; skipping local site installation."
	exit 0
fi

wp_cli option update blog_public 0 >/dev/null 2>&1 || true
wp_cli rewrite structure '/%postname%/' >/dev/null 2>&1 || true
wp_cli rewrite flush --hard >/dev/null 2>&1 || true
wp_cli theme activate twentytwentythree >/dev/null 2>&1 || true
wp_cli plugin activate wp-markdown-for-agents >/dev/null 2>&1 || true
seed_sample_content