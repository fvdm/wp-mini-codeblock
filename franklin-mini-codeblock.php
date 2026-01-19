<?php
/**
 * Plugin Name:  Franklin Mini Codeblock
 * Description:  Minimal, fast syntax highlighting with no dependencies
 * Version:      1.4.0
 * Author:       Franklin
 * Author URI:   https://frankl.in
 * Text Domain:  franklin-mini-codeblock
 * License:      Unlicense
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Franklin_Mini_Codeblock {
    private $version = '1.4.0';
    private $language_patterns;

    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
        
        // Initialize language patterns once to avoid recreating on every render
        $this->language_patterns = $this->init_language_patterns();
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
        return [
            'c' => [
                ['r' => '/(\/\/.*$)/m', 'c' => 'comment'],
                ['r' => '/(\/\*[\s\S]*?\*\/)/s', 'c' => 'comment'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string'],
                ['r' => '/\b(int|char|float|double|void|long|short|signed|unsigned|struct|union|enum|typedef|sizeof|if|else|for|while|do|switch|case|default|break|continue|return|goto|const|static|extern|auto|register|volatile|inline|restrict)\b/', 'c' => 'keyword'],
                ['r' => '/#\s*(include|define|ifdef|ifndef|endif|pragma)/', 'c' => 'preprocessor'],
                ['r' => '/\b(\d+\.?\d*|\.\d+|0x[0-9a-fA-F]+)\b/', 'c' => 'number']
            ],
            'css' => [
                ['r' => '/(\/\*[\s\S]*?\*\/)/s', 'c' => 'comment'],
                ['r' => '/([.#][a-zA-Z][a-zA-Z0-9_-]*)/', 'c' => 'selector'],
                ['r' => '/([a-zA-Z-]+)\s*:/', 'c' => 'property'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string'],
                ['r' => '/#[0-9a-fA-F]{3,8}\b/', 'c' => 'number'],
                ['r' => '/\b(\d+(?:px|em|rem|%|vh|vw|pt)?)/', 'c' => 'number']
            ],
            'html' => [
                ['r' => '/(<!--[\s\S]*?-->)/s', 'c' => 'comment'],
                ['r' => '/(<\/?[a-zA-Z][a-zA-Z0-9]*)/', 'c' => 'tag'],
                ['r' => '/([a-zA-Z-]+)=/', 'c' => 'attr'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string']
            ],
            'ini' => [
                ['r' => '/(;.*$|#.*$)/m', 'c' => 'comment'],
                ['r' => '/(\[[^\]]+\])/', 'c' => 'section'],
                ['r' => '/^([a-zA-Z_][a-zA-Z0-9_.]*)(?=\s*=)/m', 'c' => 'property'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string']
            ],
            'javascript' => [
                ['r' => '/([^:]\/\/.*$)/m', 'c' => 'comment'],
                ['r' => '/(\/\*[\s\S]*?\*\/)/s', 'c' => 'comment'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*"|`(?:\\\\.|[^`\\\\])*`)/s', 'c' => 'string'],
                ['r' => '/(?<![\'"])\b([a-zA-Z0-9_]+)\s*:/', 'c' => 'property'],
                ['r' => '/\b(const|let|var|function|return|if|else|for|while|class|import|export|from|async|await|new|this|super|extends|static|try|catch|throw|typeof|instanceof|delete|void|yield|break|continue|switch|case|default|do)\b/', 'c' => 'keyword'],
                ['r' => '/\b(true|false|null|undefined|NaN|Infinity)\b/', 'c' => 'literal'],
                ['r' => '/\b(\d+\.?\d*|\.\d+)\b/', 'c' => 'number'],
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
                ['r' => '/\b(and|break|do|else|elseif|end|false|for|function|goto|if|in|local|nil|not|or|repeat|return|then|true|until|while)\b/', 'c' => 'keyword'],
                ['r' => '/\b(print|pairs|ipairs|next|type|tostring|tonumber|error|assert|pcall|xpcall|require|dofile|load|loadfile|set)\b/', 'c' => 'function'],
                ['r' => '/\b(string|table|math|os|io|coroutine|debug|package)\b/', 'c' => 'type'],
                ['r' => '/\b(\d+\.?\d*|\.\d+|0x[0-9a-fA-F]+)\b/', 'c' => 'number'],
                ['r' => '/\b([a-zA-Z_][a-zA-Z0-9_]*)\b(?=\s*[,)=])/', 'c' => 'variable'],
                ['r' => '/(\.\.\.|\.{2}|[+\-*\/%^#=<>~]+)/', 'c' => 'operator']
            ],
            'php' => [
                ['r' => '/(#.*$|\/\/.*$)/m', 'c' => 'comment'],
                ['r' => '/(\/\*[\s\S]*?\*\/)/s', 'c' => 'comment'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string'],
                ['r' => '/\b(class|function|public|private|protected|static|const|return|if|else|foreach|while|new|namespace|use|trait|interface|extends|implements|echo|print|require|include)\b/', 'c' => 'keyword'],
                ['r' => '/(\$[a-zA-Z_][a-zA-Z0-9_]*)/', 'c' => 'variable'],
                ['r' => '/\b(\d+\.?\d*|\.\d+)\b/', 'c' => 'number']
            ],
            'python' => [
                ['r' => '/(#.*$)/m', 'c' => 'comment'],
                ['r' => "/('''[\\s\\S]*?'''|\"\"\"[\\s\\S]*?\"\"\")/s", 'c' => 'string'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string'],
                ['r' => '/\b(def|class|import|from|return|if|elif|else|for|while|in|try|except|finally|with|as|lambda|yield|async|await|pass|break|continue|global|nonlocal)\b/', 'c' => 'keyword'],
                ['r' => '/\b(True|False|None)\b/', 'c' => 'literal'],
                ['r' => '/\b(\d+\.?\d*|\.\d+)\b/', 'c' => 'number']
            ],
            'shell' => [
                ['r' => '/(#.*$)/m', 'c' => 'comment'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string'],
                ['r' => '/^(#!.*)$/m', 'c' => 'preprocessor'],
                ['r' => '/\b(if|then|else|elif|fi|for|while|until|do|done|case|esac|select|function|in)\b/', 'c' => 'keyword'],
                ['r' => '/\b(echo|printf|read|cd|rm|exit|return|source|alias|unalias|export|unset|shift|getopts|test|sudo( [A-Za-z0-9_\.\/-]+)?|chmod|chown|\[\])\b/', 'c' => 'builtin'],
                ['r' => '/\b(ls|cat|grep|awk|sed|find|xargs|tail|head|screen|dtach|curl|wget|ssh|scp|tar|zip|unzip|make|defaults|docker|kubectl|tmutil|pbcopy|pbpaste|node|npm)\b/', 'c' => 'function'],
                ['r' => '/(\$[a-zA-Z_][a-zA-Z0-9_]*|\${[^}]+})/', 'c' => 'variable'],
                ['r' => '/\s(--[a-zA-Z0-9\-]+|\-[a-zA-Z]+)/', 'c' => 'flag'],
                ['r' => '/(\|\||&&|2>>|>>|&>|2>|;|\||>|<)/', 'c' => 'operator'],
                ['r' => '/(\$\(|\)|`|\\\()/', 'c' => 'operator'],
                ['r' => '/\b(\d+)\b/', 'c' => 'number']
            ],
            'text' => [],
            'typescript' => [
                ['r' => '/(\/\/.*$)/m', 'c' => 'comment'],
                ['r' => '/(\/\*[\s\S]*?\*\/)/s', 'c' => 'comment'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*"|`(?:\\\\.|[^`\\\\])*`)/s', 'c' => 'string'],
                ['r' => '/\b(const|let|var|function|return|if|else|for|while|class|import|export|from|async|await|new|this|super|extends|static|interface|type|enum|namespace|public|private|protected|readonly|implements|declare)\b/', 'c' => 'keyword'],
                ['r' => '/\b(string|number|boolean|any|void|never|unknown)\b/', 'c' => 'type'],
                ['r' => '/\b(true|false|null|undefined|NaN|Infinity)\b/', 'c' => 'literal'],
                ['r' => '/\b(\d+\.?\d*|\.\d+)\b/', 'c' => 'number']
            ],
            'url' => [
                ['r' => '/\b([a-zA-Z][a-zA-Z0-9+.-]*:)(?=\/\/)/', 'c' => 'url-protocol'],
                ['r' => '/\/\//', 'c' => 'url-slash'],
                ['r' => '/(?<=\/\/)([^\s\/:?#]+)/', 'c' => 'url-host'],
                ['r' => '/(:\d{2,5})(?=\/|[?#]|\s|$)/', 'c' => 'url-port'],
                ['r' => '/(?<=\/)([^\/\s?#]+)(?=(\/|\?|#|$))/', 'c' => 'url-path'],
                ['r' => '/(\?[^#\s]*)/', 'c' => 'url-query'],
                ['r' => '/(#[^\s]*)/', 'c' => 'url-hash']
            ],
            'xml' => [
                ['r' => '/(<!--[\s\S]*?-->)/s', 'c' => 'comment'],
                ['r' => '/(<\/?[a-zA-Z][a-zA-Z0-9:]*)/', 'c' => 'tag'],
                ['r' => '/([a-zA-Z:][a-zA-Z0-9:_-]*)=/', 'c' => 'attr'],
                ['r' => '/(' . "'" . '(?:\\\\.|[' . "'" . '\\\\])*' . "'" . '|"(?:\\\\.|[^"\\\\])*")/s', 'c' => 'string']
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

        $html = esc_html( $code );
        $tokens = [];

        foreach ( $patterns[ $language ] as $pattern ) {
            $regex = $pattern['r'];
            $class = $pattern['c'];
            
            // Token placeholder uses ___TOKEN_N___ format
            // Note: Extremely unlikely to conflict with actual code content
            $html = preg_replace_callback( $regex, function( $matches ) use ( &$tokens, $class ) {
                $token_id = '___TOKEN_' . count( $tokens ) . '___';
                $tokens[] = [ 'match' => $matches[0], 'className' => $class ];
                return $token_id;
            }, $html );
        }

        foreach ( $tokens as $index => $token ) {
            $placeholder = '___TOKEN_' . $index . '___';
            $inner = ( $language === 'url' && $token['className'] === 'url-query' )
                ? $this->highlight_query( $token['match'] )
                : $token['match'];
            $html = str_replace(
                $placeholder,
                '<span class="fmc-' . esc_attr( $token['className'] ) . '">' . $inner . '</span>',
                $html
            );
        }

        // Safety pass for any remaining placeholders
        $html = preg_replace_callback( '/___TOKEN_(\d+)___/', function( $matches ) use ( $tokens ) {
            $idx = intval( $matches[1] );
            return isset( $tokens[ $idx ] ) ? $tokens[ $idx ]['match'] : '';
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
