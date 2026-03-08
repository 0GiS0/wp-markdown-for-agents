#!/usr/bin/env bash

set -eu

wp_path="/srv/wordpress"
site_url="http://localhost:8080"

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
	echo "WordPress config is not ready yet; skipping local bootstrap."
	exit 0
fi

installed=0
attempt=1

while [ "$attempt" -le "$max_attempts" ]; do
	if wp core is-installed --path="$wp_path" >/dev/null 2>&1; then
		installed=1
		break
	fi

	if wp core install \
		--path="$wp_path" \
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

wp plugin activate wp-markdown-for-agents --path="$wp_path" >/dev/null 2>&1 || true