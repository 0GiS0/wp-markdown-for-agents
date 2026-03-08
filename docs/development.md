# Development

## Local checks

When you open the repository in the dev container, it installs the git hooks automatically.

The dev container also starts a local WordPress stack for plugin testing:

- WordPress: `http://localhost:8080`
- Admin user: `admin`
- Admin password: `admin`
- Database service: `db`

On container start, the workspace runs a small bootstrap script that waits for WordPress, installs the site if needed, and activates this plugin automatically.

If you need to inspect or manage the local site from the workspace container, WP-CLI is available and the shared WordPress root is mounted at `/srv/wordpress`:

```bash
wp plugin list --path=/srv/wordpress
wp option get siteurl --path=/srv/wordpress
```

PHP auto-format on save is disabled in the dev container because Intelephense formatting conflicts with the repository WPCS ruleset. Use the `fix php style` VS Code task or run `vendor/bin/phpcbf --standard=phpcs.xml.dist` when you want to apply style fixes.

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
