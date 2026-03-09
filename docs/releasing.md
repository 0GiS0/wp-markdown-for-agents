# Releasing

## CI

This repository includes a GitHub Actions workflow called `🧪 CI` that runs on every push to `main` and on every pull request.

It currently does two checks:

- Lints all PHP files against PHP `7.4`, `8.0`, `8.1`, `8.2`, and `8.3`
- Builds the installable plugin ZIP and uploads it as a workflow artifact

## Releases

This repository also includes a release workflow called `🚀 Release`, triggered by Git tags that start with `v`, for example:

```bash
git tag v1.0.1
git push origin v1.0.1
```

When you push a matching tag, GitHub Actions will:

- Build `dist/markdown-for-ai-agents.zip`
- Build a categorized changelog from merged pull requests
- Create a GitHub Release automatically
- Attach the plugin ZIP to that release

For consistency, keep the plugin version in `markdown-for-ai-agents.php` aligned with the tag you publish.

The changelog is configured in `release-changelog-builder-config.json` and groups pull requests according to the repository labels, so the release notes depend on using labels consistently on PRs.
