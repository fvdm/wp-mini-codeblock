# Franklin Mini Codeblock - Tests

This directory contains test scripts for the Franklin Mini Codeblock plugin.

## Shell Syntax Highlighting Test

### Purpose

The `highlight-samples.php` script generates a static HTML file with test cases to verify that shell syntax highlighting works correctly, particularly for:

- Opening `(` and closing `)` parentheses (should both be highlighted as operators)
- Subshell constructs like `$(...)` and backticks
- Square bracket test operators `[ ]` and `[[ ]]`
- String literals with embedded parentheses

### Usage

1. **Run the test script:**
   ```bash
   php tests/highlight-samples.php
   ```

2. **View the generated output:**
   - Open `examples/shell-test.html` in a web browser
   - Or view it directly from the command line:
     ```bash
     open examples/shell-test.html  # macOS
     xdg-open examples/shell-test.html  # Linux
     start examples/shell-test.html  # Windows
     ```

### Expected Results

When viewing `examples/shell-test.html`, verify that:

- ✓ Both `(` and `)` have the same pink/operator color (#ff6b9d)
- ✓ The `$(` in `echo $(ls)` is highlighted as an operator
- ✓ Parentheses inside strings (like `'("en-US")'`) are green (string color #a5d6a7)
- ✓ Square brackets `[ ]` and `[[ ]]` are highlighted as builtins (peach color #ff9e64)
- ✓ Backticks `` ` `` have the operator color

### Test Cases Included

1. **User-provided sample:** `defaults write com.Ubisoft.AssassinsCreedBrotherhood AppleLanguages '("en-US")'`
   - Tests parentheses in single-quoted strings
   
2. **Subshell:** `echo $(ls)`
   - Tests `$(...)` subshell syntax
   
3. **Not a subshell:** `var='(not-subshell)'`
   - Tests that parentheses in strings are colored as strings, not operators
   
4. **Backticks:** `` echo `date` ``
   - Tests backtick command substitution
   
5. **Single bracket test:** `if [ -f /etc/passwd ]; then echo yes; fi`
   - Tests `[ ]` test operator
   
6. **Double bracket test:** `if [[ -n "$var" ]]; then`
   - Tests `[[ ]]` extended test operator

### Taking Screenshots

For code review purposes, you can take screenshots of the rendered `shell-test.html` page to visually confirm that the highlighting is correct. The page includes a color legend and side-by-side comparison of raw code and highlighted output.

## How It Works

The test script:

1. Loads `franklin-mini-codeblock.php` with WordPress function stubs
2. Uses reflection to access the private `highlight_code()` method
3. Processes each test sample through the syntax highlighter
4. Generates a standalone HTML file with inline CSS from `assets/style.css`
5. Includes color samples and a visual inspection checklist

## Requirements

- PHP 7.0 or higher (same as the plugin)
- No additional dependencies
