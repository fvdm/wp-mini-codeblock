# Franklin Mini Codeblock - Tests

This directory contains test scripts for the Franklin Mini Codeblock plugin.

## Comprehensive Language Test

### Purpose

The `comprehensive-test.php` script generates a complete test suite that validates syntax highlighting for **all supported languages** after the refactoring of the `init_language_patterns` method. This test ensures that:

- Common pattern extraction works correctly
- Modularized keyword arrays function properly
- Reorganized keywords maintain correct highlighting
- All language-specific features are preserved

### Usage

1. **Run the comprehensive test:**
   ```bash
   php tests/comprehensive-test.php
   ```

2. **View the generated output:**
   - Open `examples/comprehensive-test.html` in a web browser
   - Or view it directly from the command line:
     ```bash
     open examples/comprehensive-test.html  # macOS
     xdg-open examples/comprehensive-test.html  # Linux
     start examples/comprehensive-test.html  # Windows
     ```

### Languages Tested

This comprehensive test covers all 7 primary languages with complex keyword patterns:

1. **JavaScript** - Variable declarations, control flow, classes, async/await
2. **TypeScript** - Type annotations, interfaces, access modifiers
3. **PHP** - Namespaces, classes, control structures, output functions
4. **Python** - Imports, functions, classes, async, context managers
5. **C** - Types, preprocessor directives, control structures
6. **Lua** - Functions, control flow, iterators, operators
7. **Shell** - Built-in commands, control structures, Unix utilities

### Expected Results

When viewing `examples/comprehensive-test.html`, verify that:

- ✅ All keyword groups are highlighted correctly (declarations, control flow, etc.)
- ✅ Language-specific features work (e.g., PHP variables, Python decorators)
- ✅ String and comment highlighting is consistent
- ✅ Numbers and operators are properly styled
- ✅ All tests show "✓ PASSED" badge

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

## Running All Tests

To run all tests and verify the refactoring:

```bash
# Run comprehensive test for all languages
php tests/comprehensive-test.php

# Run shell-specific test
php tests/highlight-samples.php

# Open both test results
open examples/comprehensive-test.html examples/shell-test.html  # macOS
```

## Taking Screenshots

For code review purposes, you can take screenshots of the rendered HTML pages to visually confirm that the highlighting is correct. The pages include:

- Color legends showing expected highlighting colors
- Side-by-side comparison of raw code and highlighted output
- Visual inspection checklists
- Success/failure indicators

## How It Works

The test scripts:

1. Load `franklin-mini-codeblock.php` with WordPress function stubs
2. Use reflection to access the private `highlight_code()` method
3. Process each test sample through the syntax highlighter
4. Generate standalone HTML files with inline CSS from `assets/style.css`
5. Include color samples and visual inspection checklists

## Requirements

- PHP 7.0 or higher (same as the plugin)
- No additional dependencies
