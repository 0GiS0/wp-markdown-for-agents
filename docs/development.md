# Development

## Local checks

When you open the repository in the dev container, it installs the git hooks automatically.

The dev container also starts a local WordPress stack for plugin testing:

- WordPress: `http://localhost:8080`
- Admin user: `admin`
- Admin password: `admin`
- Database service: `db`

On container start, the workspace runs a small bootstrap script that waits for WordPress, installs the site if needed, and activates this plugin automatically.

That bootstrap now runs fully through WP-CLI from inside the dev container. You do not need to leave the container or complete the browser installer manually. If the local database is empty, the startup script will:

- create `wp-config.php` when needed
- install WordPress with the local admin account
- activate Twenty Twenty-Three for a simpler local posts index
- show the posts index on the site root with seeded demo examples
- activate this plugin
- seed a small set of markdown-heavy demo content once

If you change `.devcontainer/Dockerfile`, rebuild the dev container so the PHP extensions used by WP-CLI are available.

If you need to inspect or manage the local site from the workspace container, WP-CLI is available and the shared WordPress root is mounted at `/srv/wordpress`:

```bash
wp plugin list --path=/srv/wordpress
wp option get siteurl --path=/srv/wordpress
wp post list --post_type=post,page --fields=ID,post_type,post_title,post_status --path=/srv/wordpress
```

PHP auto-format on save is disabled in the dev container because Intelephense formatting conflicts with the repository WPCS ruleset. Use the `fix php style` VS Code task or run `vendor/bin/phpcbf --standard=phpcs.xml.dist` when you want to apply style fixes.

Bash scripts can be checked locally with ShellCheck:

```bash
shellcheck .devcontainer/post-start.sh install-git-hooks.sh package-plugin.sh .githooks/pre-commit
```

There is also a `lint bash` VS Code task for the same check.

Install the PHP development tools:

```bash
composer install
```

Install the git hooks for this repository:

```bash
./install-git-hooks.sh
```

The pre-commit hook will:

- Run `php -l` on staged PHP files
- Run `vendor/bin/phpcs --standard=phpcs.xml.dist` when PHPCS is installed

## GitHub Actions

- `🐚 Bash quality` runs `bash -n` and `shellcheck` on repository shell scripts
- `🧪 CI` runs PHP syntax checks, PHPCS quality checks, and packaging
- `🔐 Dependency Review` reviews dependency changes in pull requests

## Security note

GitHub CodeQL currently does not support PHP. For this repository, PHP security coverage comes from:

- `WordPress.Security` sniffs in PHPCS
- `PHPCompatibilityWP` checks for compatibility-related risk
- dependency review in pull requests

## Standards

PHPCS uses:

- `WordPress-Core`
- `WordPress-Docs`
- `WordPress-Extra`
- `WordPress.Security`
- `PHPCompatibilityWP`
