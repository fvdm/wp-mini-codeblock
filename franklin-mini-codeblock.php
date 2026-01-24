<?php
/**
 * Plugin Name:  Franklin Mini Codeblock
 * Description:  Minimal, fast syntax highlighting with no dependencies
 * Version:      1.5.5
 * Author:       Franklin
 * Author URI:   https://frankl.in
 * Text Domain:  franklin-mini-codeblock
 * License:      Unlicense
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Franklin_Mini_Codeblock {
    private $version = '1.5.5';
    private $language_patterns;
    
    // Reusable regex patterns
    private $common_patterns = [];

    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
        
        // Initialize common patterns once
        $this->init_common_patterns();
        
        // Initialize language patterns once to avoid recreating on every render
        $this->language_patterns = $this->init_language_patterns();
    }
    
    private function init_common_patterns() {
        // Single-line comment pattern (for //, #, etc.)
        $this->common_patterns['comment_line_slashes'] = '/(\/\/.*$)/m';
        $this->common_patterns['comment_line_hash'] = '/(#.*$)/m';
        
        // Multi-line comment pattern (/* ... */)
        $this->common_patterns['comment_block'] = '/(\/\*[\s\S]*?\*\/)/s';
        
        // String patterns - reusable across languages
        // Matches single-quoted strings: 'text' with escape sequences
        $this->common_patterns['string_single'] = "'" . '(?:\\\\.|[^' . "'" . '\\\\])*' . "'";
        
        // Matches double-quoted strings: "text" with escape sequences
        $this->common_patterns['string_double'] = '"(?:\\\\.|[^"\\\\])*"';
        
        // Matches backtick strings: `text` (for template literals)
        $this->common_patterns['string_backtick'] = '`(?:\\\\.|[^`\\\\])*`';
        
        // Combined string pattern (single or double quotes)
        $this->common_patterns['strings_single_double'] = '/(' 
            . $this->common_patterns['string_single'] . '|' 
            . $this->common_patterns['string_double'] 
            . ')/s';
        
        // Combined string pattern (all three: single, double, backtick)
        $this->common_patterns['strings_all'] = '/(' 
            . $this->common_patterns['string_single'] . '|' 
            . $this->common_patterns['string_double'] . '|'
            . $this->common_patterns['string_backtick']
            . ')/s';
        
        // Number patterns
        // Basic integers and floats
        $this->common_patterns['number_basic'] = '\b(\d+\.?\d*|\.\d+)\b';
        
        // Numbers with hex support
        $this->common_patterns['number_with_hex'] = '\b(\d+\.?\d*|\.\d+|0x[0-9a-fA-F]+)\b';
    }
    
    public function deactivate() {
        // Clean up transient cache on deactivation
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_fmc_%' OR option_name LIKE '_transient_timeout_fmc_%'" );
    }

    public function register_block() {
        register_block_type( __DIR__ . '/assets', [
            'render_callback' => [ $this, 'render_block' ]
        ] );
    }

    private function init_language_patterns() {
        // Ensure common patterns are initialized
        // (Handles edge case where init_language_patterns is called via reflection)
        if ( empty( $this->common_patterns ) ) {
            $this->init_common_patterns();
        }
        
        // C language keywords
        $c_keywords = [
            // Types
            'int', 'char', 'float', 'double',
            'void', 'long', 'short', 'signed', 'unsigned',
            'struct', 'union', 'enum', 'typedef',
            'sizeof',
            // Control flow
            'if', 'else',
            'switch', 'case', 'default', 'break',
            'for', 'while', 'do',
            'continue', 'return', 'goto',
            // Storage classes
            'const', 'static', 'extern',
            'auto', 'register',
            'volatile', 'inline', 'restrict'
        ];
        
        return [
            'c' => [
                ['r' => $this->common_patterns['comment_line_slashes'], 'c' => 'comment'],
                ['r' => $this->common_patterns['comment_block'], 'c' => 'comment'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string'],
                ['r' => '/\b(' . implode('|', $c_keywords) . ')\b/', 'c' => 'keyword'],
                ['r' => '/#\s*(include|define|ifdef|ifndef|endif|pragma)/', 'c' => 'preprocessor'],
                ['r' => '/' . $this->common_patterns['number_with_hex'] . '/', 'c' => 'number']
            ],
            'css' => [
                ['r' => $this->common_patterns['comment_block'], 'c' => 'comment'],
                ['r' => '/([.#][a-zA-Z][a-zA-Z0-9_-]*)/', 'c' => 'selector'],
                ['r' => '/([a-zA-Z-]+)\s*:/', 'c' => 'property'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string'],
                ['r' => '/#[0-9a-fA-F]{3,8}\b/', 'c' => 'number'],
                ['r' => '/\b(\d+(?:px|em|rem|%|vh|vw|pt)?)/', 'c' => 'number']
            ],
            'html' => [
                ['r' => '/(<!--[\s\S]*?-->)/s', 'c' => 'comment'],
                ['r' => '/(<\/?[a-zA-Z][a-zA-Z0-9]*)/', 'c' => 'tag'],
                ['r' => '/([a-zA-Z-]+)=/', 'c' => 'attr'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string']
            ],
            'ini' => [
                ['r' => '/(;.*$|#.*$)/m', 'c' => 'comment'],
                ['r' => '/(\[[^\]]+\])/', 'c' => 'section'],
                ['r' => '/^([a-zA-Z_][a-zA-Z0-9_.]*)(?=\s*=)/m', 'c' => 'property'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string']
            ],
            'javascript' => [
                ['r' => '/([^:]\/\/.*$)/m', 'c' => 'comment'],
                ['r' => $this->common_patterns['comment_block'], 'c' => 'comment'],
                ['r' => $this->common_patterns['strings_all'], 'c' => 'string'],
                ['r' => '/(?<![\'"])\b([a-zA-Z0-9_]+)\s*:/', 'c' => 'property'],
                // JavaScript keywords
                ['r' => '/\b(' . implode('|', [
                    'const', 'let', 'var',
                    'function', 'return',
                    'class', 'new', 'this', 'super', 'extends', 'static',
                    'if', 'else',
                    'switch', 'case', 'default', 'break',
                    'for', 'while', 'do',
                    'continue',
                    'try', 'catch', 'throw',
                    'import', 'export', 'from',
                    'async', 'await',
                    'typeof', 'instanceof', 'delete', 'void',
                    'yield'
                ]) . ')\b/', 'c' => 'keyword'],
                ['r' => '/\b(true|false|null|undefined|NaN|Infinity)\b/', 'c' => 'literal'],
                ['r' => '/' . $this->common_patterns['number_basic'] . '/', 'c' => 'number'],
                ['r' => '/([a-zA-Z_$][a-zA-Z0-9_$]*)\s*(?=\()/', 'c' => 'function']
            ],
            'json' => [
                ['r' => '/"(?:\\\\.|[^"\\\\])*"/', 'c' => 'string'],
                ['r' => '/\b(true|false|null)\b/', 'c' => 'literal'],
                ['r' => '/\b(-?\d+\.?\d*|\.\d+)\b/', 'c' => 'number']
            ],
            'lua' => [
                ['r' => '/(--.*$)/m', 'c' => 'comment'],
                ['r' => '/(--\[\[[\s\S]*?\]\])/s', 'c' => 'comment'],
                ['r' => '/("([^"\\\\]|\\\\.)*"|' . "'" . '([^' . "'" . '\\\\]|\\\\.)*' . "'" . '|\[\[[\s\S]*?\]\])/s', 'c' => 'string'],
                // Lua keywords
                ['r' => '/\b(' . implode('|', [
                    'local',
                    'function', 'return',
                    'if', 'then', 'else', 'elseif', 'end',
                    'for', 'do',
                    'while', 'repeat', 'until',
                    'break', 'goto',
                    'and', 'or', 'not',
                    'true', 'false', 'nil',
                    'in'
                ]) . ')\b/', 'c' => 'keyword'],
                // Lua built-in functions
                ['r' => '/\b(' . implode('|', [
                    'print',
                    'pairs', 'ipairs', 'next',
                    'type', 'tostring', 'tonumber',
                    'error', 'assert',
                    'pcall', 'xpcall',
                    'require', 'dofile', 'load', 'loadfile',
                    'set'
                ]) . ')\b/', 'c' => 'function'],
                ['r' => '/\b(string|table|math|os|io|coroutine|debug|package)\b/', 'c' => 'type'],
                ['r' => '/' . $this->common_patterns['number_with_hex'] . '/', 'c' => 'number'],
                ['r' => '/\b([a-zA-Z_][a-zA-Z0-9_]*)\b(?=\s*[,)=])/', 'c' => 'variable'],
                ['r' => '/(\.\.\.|\.{2}|[+\-*\/%^#=<>~]+)/', 'c' => 'operator']
            ],
            'php' => [
                ['r' => '/(#.*$|\/\/.*$)/m', 'c' => 'comment'],
                ['r' => $this->common_patterns['comment_block'], 'c' => 'comment'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string'],
                // PHP keywords
                ['r' => '/\b(' . implode('|', [
                    'namespace', 'use',
                    'class', 'trait', 'interface', 'extends', 'implements',
                    'public', 'private', 'protected', 'static', 'const',
                    'function', 'return',
                    'new',
                    'if', 'else', 'elseif',
                    'switch', 'case', 'default', 'break',
                    'foreach', 'while',
                    'echo', 'print',
                    'require', 'include'
                ]) . ')\b/', 'c' => 'keyword'],
                ['r' => '/(\$[a-zA-Z_][a-zA-Z0-9_]*)/', 'c' => 'variable'],
                ['r' => '/' . $this->common_patterns['number_basic'] . '/', 'c' => 'number']
            ],
            'python' => [
                ['r' => $this->common_patterns['comment_line_hash'], 'c' => 'comment'],
                ['r' => "/('''[\\s\\S]*?'''|\"\"\"[\\s\\S]*?\"\"\")/s", 'c' => 'string'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string'],
                // Python keywords
                ['r' => '/\b(' . implode('|', [
                    'import', 'from', 'as',
                    'def', 'class',
                    'return', 'yield',
                    'lambda',
                    'if', 'elif', 'else',
                    'for', 'while', 'in',
                    'break', 'continue', 'pass',
                    'try', 'except', 'finally',
                    'with',
                    'async', 'await',
                    'global', 'nonlocal'
                ]) . ')\b/', 'c' => 'keyword'],
                ['r' => '/\b(True|False|None)\b/', 'c' => 'literal'],
                ['r' => '/' . $this->common_patterns['number_basic'] . '/', 'c' => 'number']
            ],
            'shell' => [
                ['r' => '/^(#!.*)$/m', 'c' => 'preprocessor'],
                ['r' => $this->common_patterns['comment_line_hash'], 'c' => 'comment'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string'],
                // Shell keywords
                ['r' => '/\b(' . implode('|', [
                    'function', 'in',
                    'if', 'then', 'else', 'elif', 'fi',
                    'case', 'esac',
                    'for', 'do', 'done',
                    'while', 'until',
                    'select'
                ]) . ')\b/', 'c' => 'keyword'],
                // Shell built-in commands
                ['r' => '/\b(' . implode('|', [
                    'echo', 'printf', 'read',
                    'test',
                    'cd',
                    'export', 'unset',
                    'alias', 'unalias',
                    'source',
                    'shift', 'getopts',
                    'exit', 'return',
                    'sudo', 'chmod', 'chown',
                    'rm'
                ]) . ')\b/', 'c' => 'builtin'],
                ['r' => '/([\[\]]{1,2})/', 'c' => 'builtin'],
                // Common Unix commands
                ['r' => '/\b(' . implode('|', [
                    'ls', 'cat',
                    'grep', 'awk', 'sed',
                    'find', 'xargs',
                    'head', 'tail',
                    'curl', 'wget',
                    'ssh', 'scp',
                    'tar', 'zip', 'unzip',
                    'make',
                    'screen', 'dtach',
                    'docker', 'kubectl',
                    'defaults', 'tmutil',
                    'pbcopy', 'pbpaste',
                    'node', 'npm'
                ]) . ')\b/', 'c' => 'function'],
                ['r' => '/(\$[a-zA-Z_][a-zA-Z0-9_]*|\${[^}]+})/', 'c' => 'variable'],
                ['r' => '/\s(--[a-zA-Z0-9\-]+|\-[a-zA-Z]+)/', 'c' => 'flag'],
                ['r' => '/(\|\||&&|2>>|>>|&>|2>|;|\||>|<)/', 'c' => 'operator'],
                ['r' => '/(\$\(|\(|\)|`)/', 'c' => 'operator'],
                ['r' => '/\b(\d+)\b/', 'c' => 'number']
            ],
            'text' => [],
            'typescript' => [
                ['r' => $this->common_patterns['comment_line_slashes'], 'c' => 'comment'],
                ['r' => $this->common_patterns['comment_block'], 'c' => 'comment'],
                ['r' => $this->common_patterns['strings_all'], 'c' => 'string'],
                // TypeScript keywords
                ['r' => '/\b(' . implode('|', [
                    'const', 'let', 'var',
                    'function', 'return',
                    'class', 'new', 'this', 'super', 'extends', 'static',
                    'interface', 'type', 'enum', 'implements',
                    'namespace',
                    'public', 'private', 'protected', 'readonly',
                    'declare',
                    'if', 'else',
                    'switch', 'case', 'default', 'break',
                    'for', 'while', 'do',
                    'continue',
                    'try', 'catch', 'throw',
                    'import', 'export', 'from',
                    'async', 'await',
                    'typeof', 'instanceof', 'delete', 'void',
                    'yield'
                ]) . ')\b/', 'c' => 'keyword'],
                ['r' => '/\b(string|number|boolean|any|void|never|unknown)\b/', 'c' => 'type'],
                ['r' => '/\b(true|false|null|undefined|NaN|Infinity)\b/', 'c' => 'literal'],
                ['r' => '/' . $this->common_patterns['number_basic'] . '/', 'c' => 'number']
            ],
            'url' => [
                // Protocol (e.g., http:, https:, ftp:)
                // Matches letter followed by alphanumeric/+/./- then colon, before //
                ['r' => '/\b([a-zA-Z][a-zA-Z0-9+.-]*:)(?=\/\/)/', 'c' => 'url-protocol'],
                
                // Protocol separator (//)
                ['r' => '/\/\//', 'c' => 'url-slash'],
                
                // Hostname or domain (e.g., example.com)
                // Matches characters between // and next delimiter (/, :, ?, #, or end)
                ['r' => '/(?<=\/\/)([^\s\/:?#]+)/', 'c' => 'url-host'],
                
                // Port number (e.g., :8080)
                // Matches colon followed by 2-5 digits, before /, ?, #, space, or end
                ['r' => '/(:\d{2,5})(?=\/|[?#]|\s|$)/', 'c' => 'url-port'],
                
                // Path segments (e.g., /api/users)
                // Matches path components between slashes
                ['r' => '/(?<=\/)([^\/\s?#]+)(?=(\/|\?|#|$))/', 'c' => 'url-path'],
                
                // Trailing slashes in paths
                // Matches / after non-slash/colon before non-slash character or end
                ['r' => '/(?<=[^\/:])\/(?=[^\s\/]|$)/', 'c' => 'url-slash'],
                
                // Query string (e.g., ?foo=bar&baz=qux)
                // Matches ? followed by anything except # or space
                ['r' => '/(\?[^#\s]*)/', 'c' => 'url-query'],
                
                // Fragment/hash (e.g., #section)
                // Matches # followed by anything except space
                ['r' => '/(#[^\s]*)/', 'c' => 'url-hash']
            ],
            'xml' => [
                ['r' => '/(<!--[\s\S]*?-->)/s', 'c' => 'comment'],
                ['r' => '/(<\/?[a-zA-Z][a-zA-Z0-9:]*)/', 'c' => 'tag'],
                ['r' => '/([a-zA-Z:][a-zA-Z0-9:_-]*)=/', 'c' => 'attr'],
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string']
            ]
        ];
    }

    private function highlight_query( $query ) {
        // Note: Input is already HTML-escaped in highlight_code() before tokenization
        if ( ! preg_match( '/^\?(.*)$/', $query, $matches ) ) {
            return $query;
        }

        $rest = $matches[1];
        $output = '<span class="fmc-url-query-mark">?</span>';
        
        if ( ! $rest ) {
            return $output;
        }

        $parts = preg_split( '/(&amp;)/', $rest, -1, PREG_SPLIT_DELIM_CAPTURE );
        foreach ( $parts as $part ) {
            if ( $part === '&amp;' ) {
                $output .= '<span class="fmc-url-query-amp">&amp;</span>';
            } elseif ( $part ) {
                if ( preg_match( '/^([^=]+)(=(.*))?$/', $part, $m ) ) {
                    $output .= '<span class="fmc-url-query-key">' . $m[1] . '</span>';
                    if ( isset( $m[2] ) && $m[2] ) {
                        $output .= '<span class="fmc-url-query-eq">=</span>';
                    }
                    if ( isset( $m[3] ) && $m[3] ) {
                        $output .= '<span class="fmc-url-query-value">' . $m[3] . '</span>';
                    }
                } else {
                    $output .= $part;
                }
            }
        }

        return $output;
    }

    private function highlight_code( $code, $language ) {
        $patterns = $this->language_patterns;
        
        // Add aliases
        $patterns['bash'] = $patterns['shell'];
        $patterns['plain'] = $patterns['text'];

        if ( ! isset( $patterns[ $language ] ) ) {
            $language = 'text';
        }

        // Start with raw code - we'll escape as we build the output
        $html = $code;
        $tokens = [];

        foreach ( $patterns[ $language ] as $pattern ) {
            $regex = $pattern['r'];
            $class = $pattern['c'];
            
            // Token placeholder uses ___FMC_HIGHLIGHT_TOKEN_N___ format
            // Note: Includes plugin shortname to make collisions extremely unlikely
            $result = preg_replace_callback( $regex, function( $matches ) use ( &$tokens, $class ) {
                $token_id = '___FMC_HIGHLIGHT_TOKEN_' . count( $tokens ) . '___';
                $tokens[] = [ 'match' => $matches[0], 'className' => $class ];
                return $token_id;
            }, $html );
            
            // Only update $html if preg_replace_callback succeeded
            if ( $result !== null ) {
                $html = $result;
            }
        }

        // Split by token placeholders to escape unmatched text
        $parts = preg_split( '/(___FMC_HIGHLIGHT_TOKEN_\d+___)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
        $html = '';
        
        foreach ( $parts as $part ) {
            if ( preg_match( '/^___FMC_HIGHLIGHT_TOKEN_(\d+)___$/', $part, $m ) ) {
                // This is a token placeholder - keep it as is
                $html .= $part;
            } else {
                // This is unmatched text - escape it
                $html .= esc_html( $part );
            }
        }

        foreach ( $tokens as $index => $token ) {
            $placeholder = '___FMC_HIGHLIGHT_TOKEN_' . $index . '___';
            $inner = ( $language === 'url' && $token['className'] === 'url-query' )
                ? $this->highlight_query( esc_html( $token['match'] ) )
                : esc_html( $token['match'] );
            $html = str_replace(
                $placeholder,
                '<span class="fmc-' . esc_attr( $token['className'] ) . '">' . $inner . '</span>',
                $html
            );
        }

        // Safety pass for any remaining placeholders
        $html = preg_replace_callback( '/___FMC_HIGHLIGHT_TOKEN_(\d+)___/', function( $matches ) use ( $tokens ) {
            $idx = intval( $matches[1] );
            return isset( $tokens[ $idx ] ) ? esc_html( $tokens[ $idx ]['match'] ) : '';
        }, $html );

        return $html;
    }

    private function get_cache_key( $code, $language ) {
        // Use delimiters between components to avoid ambiguous concatenations
        return 'fmc_' . md5( $code . '|' . $language . '|' . $this->version );
    }

    public function render_block( $attributes, $content ) {
        $code = isset( $attributes['code'] ) ? (string) $attributes['code'] : '';
        if ( $code === '' ) {
            return '';
        }

        $id = isset( $attributes['id'] ) ? (string) $attributes['id'] : '';
        $language = isset( $attributes['language'] ) ? (string) $attributes['language'] : 'javascript';
        $clean_id = $id ? str_replace( ' ', '-', strip_tags( $id ) ) : '';
        $id_attr = $clean_id ? ' data-id="' . esc_attr( $clean_id ) . '"' : '';

        // Check cache
        $cache_key = $this->get_cache_key( $code, $language );
        $highlighted = get_transient( $cache_key );

        if ( $highlighted === false ) {
            $highlighted = $this->highlight_code( $code, $language );
            set_transient( $cache_key, $highlighted, WEEK_IN_SECONDS );
        }

        // Security note: $highlighted contains HTML spans generated by highlight_code()
        // The raw code is escaped via esc_html() before pattern matching, and
        // class names are escaped via esc_attr() during span generation
        return '<div class="fmc-wrapper">'
            . '<div class="fmc-header">'
            . '<span class="fmc-lang">&nbsp;</span>'
            . '<button class="fmc-copy" aria-label="Copy code"' . $id_attr . '>Copy</button>'
            . '</div>'
            . '<pre class="fmc-pre"><code class="fmc-code">'
            . $highlighted
            . '</code></pre>'
            . '</div>';
    }

    public function frontend_assets() {
        if ( ! has_block( 'franklin/mini-codeblock' ) ) {
            return;
        }

        wp_enqueue_style(
            'franklin-mini-codeblock-style',
            plugins_url( 'assets/style.css', __FILE__ ),
            [],
            $this->version
        );

        wp_enqueue_script(
            'franklin-mini-codeblock-frontend',
            plugins_url( 'assets/frontend.js', __FILE__ ),
            [],
            $this->version,
            true
        );
    }
}

new Franklin_Mini_Codeblock();
