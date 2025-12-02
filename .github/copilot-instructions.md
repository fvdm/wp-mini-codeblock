# Copilot Instructions for Franklin Mini Codeblock

## Repository Overview

**Franklin Mini Codeblock** is a lightweight, dependency-free WordPress plugin that provides syntax highlighting for code blocks in the Gutenberg block editor. The plugin supports 13 languages (JavaScript, TypeScript, CSS, HTML, PHP, Python, Lua, JSON, XML, Bash, INI, C, URL) with a dark theme and copy-to-clipboard functionality.

**Key characteristics:**
- Zero dependencies (no npm, composer, or external build tools)
- Pre-built assets committed directly to the repository
- Small size, under 20KB uncompressed
- License: Unlicense (public domain)

## Project Structure

```
franklin-mini-codeblock/
├── franklin-mini-codeblock.php   # Main plugin file (WordPress entry point)
├── assets/                       # Pre-built assets (DO NOT regenerate)
│   ├── block.json               # Gutenberg block configuration
│   ├── editor.js                # Block editor JavaScript
│   ├── editor.css               # Block editor styles
│   ├── frontend.js              # Syntax highlighter + copy button
│   └── style.css                # Frontend styles
├── README.md                     # Project documentation
├── LICENSE.md                    # Unlicense (public domain)
└── .github/
    └── FUNDING.yml              # GitHub Sponsors configuration
```

## File Purposes

| File | Purpose |
|------|---------|
| `franklin-mini-codeblock.php` | Main plugin entry point. Registers the block, enqueues assets, and renders output. Contains the `Franklin_Mini_Codeblock` class. |
| `assets/block.json` | Defines block name (`franklin/mini-codeblock`), attributes (`code`, `language`, `id` for Matomo tracking), and asset references. |
| `assets/editor.js` | Gutenberg editor component with language selector and code textarea. |
| `assets/editor.css` | Styles for the block editor interface. |
| `assets/frontend.js` | Client-side syntax highlighter using regex patterns. Handles copy-to-clipboard. |
| `assets/style.css` | Frontend CSS with dark theme, responsive design, and syntax highlighting colors. |

## Validation Commands

Since this project has no build system or test infrastructure, use PHP syntax checking:

```bash
# Validate PHP syntax (always run after editing PHP files)
php -l franklin-mini-codeblock.php
```

Expected output: `No syntax errors detected in franklin-mini-codeblock.php`

## Development Notes

### No Build Process
The `assets/` directory contains pre-built, hand-written assets. There is no npm, webpack, or other build tool. Edit these files directly.

### No Test Infrastructure
There are no automated tests. Manual testing in a WordPress environment is required.

### No CI/CD Workflows
There are no GitHub Actions workflows. Validation is manual.

### Supported Languages
Languages are defined in `assets/frontend.js` in the `patterns` object and in `assets/editor.js` in the `languages` array. When adding a new language, update both files.

### Block Rendering
The PHP `render_block()` method in `franklin-mini-codeblock.php` generates the HTML output. The frontend JavaScript in `assets/frontend.js` applies syntax highlighting client-side.

### CSS Class Naming
All CSS classes use the `fmc-` prefix (Franklin Mini Codeblock). Syntax highlighting classes follow the pattern `fmc-{token-type}` (e.g., `fmc-keyword`, `fmc-string`).

### Version Updates
When updating the plugin version, change it in both locations in `franklin-mini-codeblock.php`:
1. The plugin header comment (`Version: X.X.X`)
2. The `$this->version` property in the constructor

## Quick Reference

| Task | Action |
|------|--------|
| Validate PHP | `php -l franklin-mini-codeblock.php` |
| Add new language | Edit `assets/frontend.js` (patterns) and `assets/editor.js` (languages array) |
| Modify block output | Edit `render_block()` in `franklin-mini-codeblock.php` |
| Change syntax colors | Edit `.fmc-*` classes in `assets/style.css` |
| Update block config | Edit `assets/block.json` |

## Important Constraints

1. **Do not run npm/composer** - This project has no package manager dependencies
2. **Edit assets/ directly** - Assets are hand-written, not compiled from source files
3. **Always validate PHP syntax** after editing PHP files
4. **Preserve the `fmc-` prefix** for all CSS classes

Trust these instructions. Only search for additional information if something documented here is incomplete or produces unexpected results.
