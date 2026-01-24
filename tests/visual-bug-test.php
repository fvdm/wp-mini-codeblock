<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Shell String Bug Visual Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f0f0; }
        .test-case { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .fmc-string { color: #a5d6a7; }
        .fmc-comment { color: #6a7b8a; font-style: italic; }
        .fmc-number { color: #ffca80; }
        .fmc-operator { color: #ff6b9d; }
        .fmc-flag { color: #7fdbca; }
        .fmc-function { color: #82aaff; }
        .fmc-variable { color: #82aaff; }
        .fmc-keyword { color: #ff6b9d; }
        .fmc-builtin { color: #ffca80; }
        .fmc-preprocessor { color: #c792ea; }
        pre { background: #1a1a1a; color: #e8e8e8; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

<?php
// Minimal WordPress functions simulation
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }

// Include just the highlighting code
class Test_Highlighter {
    private $common_patterns = [];
    private $language_patterns;
    
    public function __construct() {
        $this->init_common_patterns();
        $this->language_patterns = $this->init_language_patterns();
    }
    
    private function init_common_patterns() {
        $this->common_patterns['comment_line_hash'] = '/(#.*$)/m';
        $this->common_patterns['string_single'] = "'" . '(?:\\\\.|[^' . "'" . '\\\\])*' . "'";
        $this->common_patterns['string_double'] = '"(?:\\\\.|[^"\\\\])*"';
        $this->common_patterns['strings_single_double'] = '/(' 
            . $this->common_patterns['string_single'] . '|' 
            . $this->common_patterns['string_double'] 
            . ')/s';
    }
    
    private function init_language_patterns() {
        return [
            'shell' => [
                // Process shebang first (specific pattern)
                ['r' => '/^(#!.*)$/m', 'c' => 'preprocessor'],
                // Process strings before comments to avoid matching # inside quotes
                ['r' => $this->common_patterns['strings_single_double'], 'c' => 'string'],
                ['r' => $this->common_patterns['comment_line_hash'], 'c' => 'comment'],
                ['r' => '/\b(' . implode('|', ['if', 'then', 'else', 'elif', 'fi', 'for', 'do', 'done', 'while']) . ')\b/', 'c' => 'keyword'],
                ['r' => '/(^|\n|;|&&|\|\||\|)[ \t]*(?:sudo|env|time|nice|nohup|watch|xargs)\s+\K(?!___FMC)([a-zA-Z_][a-zA-Z0-9_\-]+)(?!\w)/m', 'c' => 'function'],
                ['r' => '/(^|\n|;|&&|\|\||\|)[ \t]*\K(?!___FMC)([a-zA-Z_][a-zA-Z0-9_\-]+)(?!\w)/m', 'c' => 'number'],
                ['r' => '/([\[\]]{1,2})/', 'c' => 'builtin'],
                ['r' => '/(\$[a-zA-Z_][a-zA-Z0-9_]*|\${[^}]+})/', 'c' => 'variable'],
                ['r' => '/\s(--[a-zA-Z0-9\-]+|\-[a-zA-Z]+)/', 'c' => 'flag'],
                ['r' => '/(\|\||&&|2>>|>>|&>|2>|;|\||>|<)/', 'c' => 'operator'],
                ['r' => '/(\$\(|\(|\)|`)/', 'c' => 'operator'],
                ['r' => '/\b(\d+)\b/', 'c' => 'number']
            ]
        ];
    }
    
    public function highlight_code($code, $language) {
        $patterns = $this->language_patterns;
        
        if (!isset($patterns[$language])) {
            return esc_html($code);
        }
        
        $html = $code;
        $tokens = [];
        
        foreach ($patterns[$language] as $pattern) {
            $regex = $pattern['r'];
            $class = $pattern['c'];
            
            $result = preg_replace_callback($regex, function($matches) use (&$tokens, $class) {
                $token_id = '___FMC_HIGHLIGHT_TOKEN_' . count($tokens) . '___';
                $tokens[] = ['match' => $matches[0], 'className' => $class];
                return $token_id;
            }, $html);
            
            if ($result !== null) {
                $html = $result;
            }
        }
        
        $parts = preg_split('/(___FMC_HIGHLIGHT_TOKEN_\d+___)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';
        
        foreach ($parts as $part) {
            if (preg_match('/^___FMC_HIGHLIGHT_TOKEN_(\d+)___$/', $part, $m)) {
                $html .= $part;
            } else {
                $html .= esc_html($part);
            }
        }
        
        foreach ($tokens as $index => $token) {
            $placeholder = '___FMC_HIGHLIGHT_TOKEN_' . $index . '___';
            $inner = esc_html($token['match']);
            $html = str_replace(
                $placeholder,
                '<span class="fmc-' . esc_attr($token['className']) . '">' . $inner . '</span>',
                $html
            );
        }
        
        return $html;
    }
}

$highlighter = new Test_Highlighter();

$test_code = "cd /usr/local/etc/powerdns \\
&& cp -n recursor.yml-dist recursor.yml \\
&& sed -i '' -E 's/^#[[:blank:]]*dnssec:\$/dnssec:/' recursor.yml \\
&& sed -i '' -E 's/^# *validation: process\$/  validation: validate/' recursor.yml";

echo '<div class="test-case">';
echo '<h2>Bug Report: Shell String Pattern Issue</h2>';
echo '<h3>Input Code:</h3>';
echo '<pre>' . esc_html($test_code) . '</pre>';
echo '<h3>Highlighted Output:</h3>';
echo '<pre>' . $highlighter->highlight_code($test_code, 'shell') . '</pre>';
echo '</div>';

// Additional test cases
$test_cases = [
    "sed -i '' file.txt",
    "sed -i '' -E 's/test//' file.txt",
    "echo '' && echo 'hello'",
];

foreach ($test_cases as $code) {
    echo '<div class="test-case">';
    echo '<h3>Test: ' . esc_html($code) . '</h3>';
    echo '<pre>' . $highlighter->highlight_code($code, 'shell') . '</pre>';
    echo '</div>';
}
?>

</body>
</html>
