# Franklin Mini Codeblock

A lightweight, dependency-free WordPress syntax highlighting plugin.

## Features
✓ Zero dependencies (production & development)
✓ 11 languages: JS, TS, CSS, HTML, PHP, Python, JSON, XML, Bash, INI, C
✓ Dark theme with flat colors
✓ Copy-to-clipboard button
✓ Fully responsive
✓ Gutenberg block editor support
✓ Tiny filesize (~14KB total)
✓ Optimized for fast page loading

## Installation

1. Download and extract the plugin files
2. Upload the `franklin-mini-codeblock` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin panel
4. Add a "Mini Code Block" in the block editor

## File Structure

```
franklin-mini-codeblock/
├── franklin-mini-codeblock.php   (Main plugin file)
└── build/
    ├── block.json                (Block configuration)
    ├── editor.js                 (Gutenberg editor)
    ├── editor.css                (Editor styles)
    ├── frontend.js               (Syntax highlighter + copy button)
    └── style.css                 (Frontend styles)
```

## Usage

1. In the WordPress editor, add a new block
2. Search for "Mini Code Block"
3. Select your language from the dropdown
4. Paste or type your code
5. Publish!

The code block will appear with:
- Dark background (#1a1a1a)
- Language label
- Copy button
- Syntax highlighting
- Responsive design
- Smooth rounded corners

## Technical Details

- **Total size**: ~14KB (uncompressed)
- **Frontend CSS**: 2.8KB
- **Frontend JS**: 6.5KB (custom syntax highlighter)
- **Editor assets**: Only load when editing
- **Frontend assets**: Only load when block is present on page
- **Font**: System monospace stack
- **Padding**: 1rem (adjusts responsively)
- **Border radius**: 4px

## Browser Support

- All modern browsers (Chrome, Firefox, Safari, Edge)
- Copy button requires Clipboard API (all modern browsers)

## Author

Franklin  
https://frankl.in

## Version

1.0.0

## License

Free to use and modify.
