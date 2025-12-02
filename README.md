# Franklin Mini Codeblock

A lightweight, dependency-free WordPress syntax highlighting plugin.


## Features
- Zero dependencies (production & development)
- 11 languages: JS, TS, CSS, HTML, PHP, Python, JSON, XML, Bash, INI, C
- Dark theme with flat colors
- Copy-to-clipboard button
- Fully responsive
- Gutenberg block editor support
- Tiny filesize (~14KB total)
- Optimized for fast page loading


## Installation

1. Download and extract the plugin files
2. Upload the `franklin-mini-codeblock` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin panel
4. Add a "Mini Code Block" in the block editor


## File Structure

```
franklin-mini-codeblock/
├── franklin-mini-codeblock.php   (Main plugin file)
└── assets/
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


## License

This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <https://unlicense.org/>


## Attribution (non-binding request)

This project is released under the Unlicense (public domain equivalent).
Attribution is not legally required, but the author kindly asks that,
where reasonable, you keep a note such as:

> Mini Codeblock by Franklin – https://frankl.in


## Author

[Franklin](https://frankl.in) | [Buy me a coffee](https://frankl.in/tip)
