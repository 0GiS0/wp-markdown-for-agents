# Markdown for AI Agents

<p align="center">
  <img src=".wordpress-org/source/wp-markdown-for-agents-banner-retina.svg" alt="Markdown for AI Agents banner" />
</p>

<p align="center">
  <a href="https://github.com/0GiS0/wp-markdown-for-agents/releases/latest"><img src="https://img.shields.io/github/v/release/0GiS0/wp-markdown-for-agents?display_name=tag&amp;label=latest%20release" alt="Latest release" /></a>
  <a href="https://github.com/0GiS0/wp-markdown-for-agents/releases/latest/download/markdown-for-ai-agents.zip"><img src="https://img.shields.io/badge/download-latest%20ZIP-2ea44f?logo=github" alt="Download ZIP" /></a>
  <a href="https://github.com/0GiS0/wp-markdown-for-agents/actions/workflows/ci.yml"><img src="https://img.shields.io/github/actions/workflow/status/0GiS0/wp-markdown-for-agents/ci.yml?branch=main&amp;label=ci" alt="CI" /></a>
  <a href="https://wordpress.org/"><img src="https://img.shields.io/badge/WordPress-5.0%2B-21759B?logo=wordpress&amp;logoColor=white" alt="WordPress 5.0+" /></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&amp;logoColor=white" alt="PHP 7.4+" /></a>
  <a href="https://github.com/0GiS0/wp-markdown-for-agents/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-GPL--2.0%2B-blue" alt="License GPL-2.0+" /></a>
</p>

Turn WordPress posts and pages into clean Markdown for AI agents, automation tools, and humans. Add `?format=markdown` to any singular post or page URL and the plugin returns a Markdown response with YAML frontmatter and Markdown-friendly content.

> [!TIP]
> Want the fastest install path? Download the latest plugin package here: [markdown-for-ai-agents.zip](https://github.com/0GiS0/wp-markdown-for-agents/releases/latest/download/markdown-for-ai-agents.zip)

This README is the GitHub landing page for releases, development, and creator links. The WordPress.org-specific plugin description stays in [readme.txt](https://github.com/0GiS0/wp-markdown-for-agents/blob/main/readme.txt).

## Why use it?

- Exposes published posts and pages as `text/markdown`
- Adds a visible "View as Markdown" button on the front end
- Includes YAML frontmatter with title, author, date, taxonomy terms, and canonical URL
- Converts headings, lists, links, images, blockquotes, code blocks, and tables into Markdown-friendly output
- Caches generated output with WordPress transients for faster repeated requests
- Keeps your normal theme rendering unchanged outside the optional Markdown endpoint

## Install from GitHub release

This is the recommended path if you just want to use the plugin in WordPress.

1. Download the latest release asset: [markdown-for-ai-agents.zip](https://github.com/0GiS0/wp-markdown-for-agents/releases/latest/download/markdown-for-ai-agents.zip)
2. In WordPress admin, go to Plugins > Add New Plugin > Upload Plugin.
3. Upload the ZIP file and activate the plugin.
4. Open any published post or page and click "View as Markdown", or append `?format=markdown` to the URL.

## Install from source

This path is better if you want to inspect, modify, or contribute to the project.

```bash
cd wp-content/plugins/
git clone https://github.com/0GiS0/wp-markdown-for-agents.git
cd wp-markdown-for-agents
composer install
```

Then activate the plugin from the WordPress admin panel.

If you want the packaged ZIP locally, run:

```bash
chmod +x package-plugin.sh
./package-plugin.sh
```

That creates `dist/markdown-for-ai-agents.zip`.

## Usage

### In the browser

Visit any published post or page and use the "View as Markdown" button injected by the plugin.

### For agents and automation

Append `?format=markdown` to any supported URL:

```text
https://yourblog.com/my-awesome-post/?format=markdown
```

The response is served as `text/markdown; charset=utf-8` and includes YAML frontmatter.

```markdown
---
title: "My Awesome Post"
author: "Gisela Torres"
date: "2026-03-08"
categories: ["Tech", "AI"]
tags: ["wordpress", "markdown"]
url: "https://yourblog.com/my-awesome-post/"
---

# My Awesome Post

Article content in clean Markdown...
```

## Supported output

| HTML element | Markdown output |
| --- | --- |
| `<h1>` to `<h6>` | `#` to `######` |
| `<p>` | Paragraphs with blank-line separation |
| `<strong>`, `<b>` | `**bold**` |
| `<em>`, `<i>` | `*italic*` |
| `<a href="...">` | `[text](url)` |
| `<img>` | `![alt](src)` |
| `<ul>`, `<ol>` | `- item` and `1. item` |
| `<pre><code>` | Fenced code block |
| `<blockquote>` | `> quote` |
| `<table>` | Markdown table |
| `<hr>` | `---` |
| `<code>` | Inline code |

## Requirements

- WordPress 5.0 or newer
- PHP 7.4 or newer

## Project links

- [Latest release](https://github.com/0GiS0/wp-markdown-for-agents/releases/latest)
- [Download latest ZIP](https://github.com/0GiS0/wp-markdown-for-agents/releases/latest/download/markdown-for-ai-agents.zip)
- [Development guide](https://github.com/0GiS0/wp-markdown-for-agents/blob/main/docs/development.md)
- [Release workflow notes](https://github.com/0GiS0/wp-markdown-for-agents/blob/main/docs/releasing.md)
- [WordPress.org readme](https://github.com/0GiS0/wp-markdown-for-agents/blob/main/readme.txt)

## Follow the creator

Built by [Gisela Torres](https://github.com/0GiS0). If you want more developer content around AI, GitHub, and tooling, you can follow these channels:

<div align="center">

[![YouTube Channel Subscribers](https://img.shields.io/youtube/channel/subscribers/UC140iBrEZbOtvxWsJ-Tb0lQ?style=for-the-badge&logo=youtube&logoColor=white&color=red)](https://www.youtube.com/c/GiselaTorres?sub_confirmation=1)
[![GitHub Followers](https://img.shields.io/github/followers/0GiS0?style=for-the-badge&logo=github&logoColor=white)](https://github.com/0GiS0)
[![LinkedIn Follow](https://img.shields.io/badge/LinkedIn-Follow-blue?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/giselatorresbuitrago/)
[![X Follow](https://img.shields.io/badge/X-Follow-black?style=for-the-badge&logo=x&logoColor=white)](https://twitter.com/0GiS0)

</div>


## License

GPL v2 or later. See [LICENSE](https://github.com/0GiS0/wp-markdown-for-agents/blob/main/LICENSE).
