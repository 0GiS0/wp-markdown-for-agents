# Markdown for AI Agents 📄

![Markdown for AI Agents banner](.github/assets/wp-markdown-for-agents-banner.svg)

A lightweight WordPress plugin that lets AI agents (and humans) retrieve any post or page as clean Markdown — just append `?format=markdown` to any URL.

## Features

- **Visible button** at the top of every post/page linking to the Markdown version
- **`?format=markdown`** URL parameter serves content as `text/markdown`
- **Frontmatter metadata** includes title, author, date, categories, tags, and original URL
- **HTML → Markdown** converter handles headings, lists, tables, code blocks, images, links, and more
- **Cached output** via WordPress transients for fast responses
- **Dark mode support** — button adapts to light and dark themes
- **Zero dependencies** — no Composer packages required

## Installation

1. Download or clone this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/0GiS0/wp-markdown-for-agents.git
   ```
2. Activate the plugin from the WordPress admin panel (**Plugins → Activate**).

That's it! The button appears automatically on all posts and pages.

### Install via ZIP upload

If you want to install it from the WordPress admin UI:

1. Generate the plugin ZIP from the repository root:
   ```bash
   chmod +x package-plugin.sh
   ./package-plugin.sh
   ```
2. Upload `dist/wp-markdown-for-agents.zip` from **Plugins → Add New Plugin → Upload Plugin**.

If you use VS Code tasks, you can also run the `package plugin zip` task.

## Usage

### For humans 🧑

Click the **"View as Markdown"** button at the top of any article to see the Markdown version in your browser.

### For AI agents 🤖

Append `?format=markdown` to any post URL:

```
https://yourblog.com/my-awesome-post/?format=markdown
```

The response is served with `Content-Type: text/markdown; charset=utf-8` and includes YAML frontmatter:

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

## Supported HTML Elements

| HTML Element | Markdown Output |
|---|---|
| `<h1>` – `<h6>` | `#` – `######` |
| `<p>` | Paragraph with double newline |
| `<strong>`, `<b>` | `**bold**` |
| `<em>`, `<i>` | `*italic*` |
| `<a href="...">` | `[text](url)` |
| `<img>` | `![alt](src)` |
| `<ul>`, `<ol>` | `- item` / `1. item` |
| `<pre><code>` | Fenced code block |
| `<blockquote>` | `> quote` |
| `<table>` | Markdown table |
| `<hr>` | `---` |
| `<code>` | `` `inline code` `` |

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
