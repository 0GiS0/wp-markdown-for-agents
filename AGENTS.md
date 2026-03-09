# Repository Working Rules

## Workflow

- Follow GitHub Flow.
- Start each change from an up-to-date branch off `main`.
- Implement the change on a dedicated feature branch.
- Open a pull request early when useful, and merge through the pull request back into `main`.
- Do not create release tags from feature branches.

## Local Validation

- Run the relevant local checks whenever you implement or modify behavior.
- At minimum, run `composer lint:php` before opening or updating a pull request.
- Run the local Plugin Check validation before pushing changes or updating a pull request. Use `wp --path=/srv/wordpress plugin check markdown-for-ai-agents --require=/srv/wordpress/wp-content/plugins/plugin-check/cli.php --exclude-directories=.devcontainer,.github,vendor,dist --exclude-files=.gitignore,install-git-hooks.sh,package-plugin.sh,phpcs.xml.dist,AGENTS.md` or the equivalent `plugin check` VS Code task.
- Run `composer lint` when you want the repository-standard PHPCS checks.
- If packaging, release automation, or distributable contents changed, also verify `./package-plugin.sh` or the equivalent packaging task.
- Do not merge changes that have not been validated locally unless there is a documented reason.

## Pull Requests

- Every pull request must have at least one label before merge.
- Every pull request intended to appear in release notes must have exactly one changelog category label.
- Do not add multiple changelog category labels to the same pull request, or the release notes will duplicate the entry across sections.

Use one of these changelog category labels:

- `✨ enhancement`
- `🐞 bug`
- `📝 markdown`
- `🤖 ai-agents`
- `🧱 wordpress`
- `📦 packaging`
- `🧪 ci`
- `🏷️ release`
- `📚 documentation`
- `🎨 ux`
- `⚡ performance`

If a pull request should not appear in the changelog, use `skip-changelog`.

## Versioning

- Use SemVer for plugin versions and git tags.
- Keep the plugin header version and the `MD_FOR_AGENTS_VERSION` constant in `markdown-for-ai-agents.php` aligned.
- Decide the version bump in every release-facing pull request.

SemVer rules for this repository:

- Patch: backward-compatible fixes, small WordPress integration fixes, packaging fixes, documentation-only corrections tied to a release, or CI/release adjustments that should ship in the next release.
- Minor: backward-compatible new functionality, meaningful UX improvements, new markdown conversion capabilities, or new agent-facing features.
- Major: breaking changes in public behavior, URL format, markdown output expectations, or compatibility guarantees.

Practical rule:

- If a pull request changes the shipped plugin behavior or release artifact, bump the version in that pull request according to SemVer.
- If a pull request is purely internal and is not intended to ship on its own, explicitly decide whether the version bump should happen there or in the next release-preparation pull request.

## Releases

- Release tags must use the format `vX.Y.Z`.
- Create release tags from `main`, not from a feature branch.
- The `🚀 Release` workflow is triggered by pushing a tag that starts with `v`.
- The release workflow builds the ZIP, generates the changelog from merged pull requests, and publishes the GitHub release.

Recommended release flow:

1. Merge the release-ready pull request into `main`.
2. Confirm the plugin version in `markdown-for-ai-agents.php` matches the intended release version.
3. Confirm merged pull requests included in the release have exactly one changelog category label, or `skip-changelog`.
4. Review the current WordPress.org Detailed Plugin Guidelines before generating a new release: `https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/`.
5. Create and push the tag from `main`, for example `v1.0.1`.
6. Verify the generated GitHub release body and attached ZIP.

## Changelog Notes

- The release changelog builder groups pull requests by labels defined in `release-changelog-builder-config.json`.
- If release notes are empty or incomplete, check merged pull request labels first.
- If labels are fixed after a release is already published, re-running the existing release workflow can regenerate the release notes without creating a new tag.
