<?php
/**
 * Test script for Franklin Mini Codeblock syntax highlighting
 * 
 * This script generates examples/shell-test.html with highlighted code samples
 * to verify that shell syntax highlighting works correctly, particularly for
 * opening and closing parentheses.
 * 
 * Usage: php tests/highlight-samples.php
 */

// WordPress functions stubs (needed by the plugin)
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'get_transient' ) ) {
    function get_transient( $key ) {
        return false; // Always return false to force fresh highlighting
    }
}

if ( ! function_exists( 'set_transient' ) ) {
    function set_transient( $key, $value, $expiration ) {
        return true;
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Stub - we don't need to actually register actions for testing
        return true;
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {
        // Stub - we don't need to actually register hooks for testing
        return true;
    }
}

// Define ABSPATH to prevent exit in plugin file
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

// Load the plugin
require_once __DIR__ . '/../franklin-mini-codeblock.php';

// Test samples
$samples = [
    [
        'description' => 'Command with sudo prefix (first=orange, second=blue)',
        'code' => 'sudo mkdir',
        'language' => 'shell'
    ],
    [
        'description' => 'Single command (first=orange)',
        'code' => 'mkdir',
        'language' => 'shell'
    ],
    [
        'description' => 'User-provided sample with parentheses in single-quoted string',
        'code' => "defaults write com.Ubisoft.AssassinsCreedBrotherhood AppleLanguages '(\"en-US\")'",
        'language' => 'shell'
    ],
    [
        'description' => 'Subshell with $(...)',
        'code' => 'echo $(ls)',
        'language' => 'shell'
    ],
    [
        'description' => 'Parentheses in single-quoted string (not a subshell)',
        'code' => "var='(not-subshell)'",
        'language' => 'shell'
    ],
    [
        'description' => 'Backtick command substitution',
        'code' => 'echo `date`',
        'language' => 'shell'
    ],
    [
        'description' => 'Single bracket test operator',
        'code' => 'if [ -f /etc/passwd ]; then echo yes; fi',
        'language' => 'shell'
    ],
    [
        'description' => 'Double bracket test operator',
        'code' => 'if [[ -n "$var" ]]; then',
        'language' => 'shell'
    ],
    [
        'description' => 'Piped commands (each first command=orange)',
        'code' => 'ls -la | grep test',
        'language' => 'shell'
    ],
    [
        'description' => 'Multiple commands with sudo',
        'code' => 'sudo apt-get install package',
        'language' => 'shell'
    ],
    [
        'description' => 'Multiline with backslashes and operators (all commands highlighted)',
        'code' => 'sudo chown $(logname) ~/Library/Preferences/.GlobalPreferences.plist \
&& chmod 0600 $_ \
&& defaults write -g ApplePressAndHoldEnabled -bool true',
        'language' => 'shell'
    ],
    [
        'description' => 'Multiline with semicolon before backslash (defaults highlighted)',
        'code' => 'launchctl unload ~/Library/LaunchAgents/com.fvdm.AccentMenu.plist \
&& rm -vf ~/Library/LaunchAgents/com.fvdm.AccentMenu.plist ; \
defaults write -g ApplePressAndHoldEnabled -bool false',
        'language' => 'shell'
    ],
    [
        'description' => 'Multiline separate commands (each line highlighted)',
        'code' => 'sudo rm -f /etc/postfix/postfix-script /etc/postfix/post-install
sudo postfix upgrade-configuration
sudo service postfix restart',
        'language' => 'shell'
    ]
];

// Create an instance of the plugin class using reflection
$reflection = new ReflectionClass( 'Franklin_Mini_Codeblock' );
$instance = $reflection->newInstanceWithoutConstructor();

// Get the highlight_code method
$highlight_method = $reflection->getMethod( 'highlight_code' );
$highlight_method->setAccessible( true );

// Get the language patterns
$patterns_property = $reflection->getProperty( 'language_patterns' );
$patterns_property->setAccessible( true );

// Initialize patterns
$init_patterns_method = $reflection->getMethod( 'init_language_patterns' );
$init_patterns_method->setAccessible( true );
$patterns = $init_patterns_method->invoke( $instance );
$patterns_property->setValue( $instance, $patterns );

// Read the CSS file
$css_file = __DIR__ . '/../assets/style.css';
if ( ! file_exists( $css_file ) ) {
    die( "Error: CSS file not found at $css_file\n" );
}
$css = file_get_contents( $css_file );
if ( $css === false ) {
    die( "Error: Failed to read CSS file at $css_file\n" );
}

// Generate HTML
$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franklin Mini Codeblock - Shell Syntax Test</title>
    <style>
' . $css . '
    </style>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            background: #f5f5f5;
            color: #333;
        }
        h1 {
            color: #1a1a1a;
            border-bottom: 2px solid #ddd;
            padding-bottom: 0.5rem;
        }
        h2 {
            color: #333;
            margin-top: 2rem;
            font-size: 1.2rem;
        }
        .test-case {
            margin: 2rem 0;
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .test-description {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 1rem;
        }
        .raw-code {
            background: #f8f8f8;
            border-left: 3px solid #ddd;
            padding: 1rem;
            margin: 1rem 0;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
            font-size: 0.9rem;
            color: #333;
        }
        .legend {
            margin: 2rem 0;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .legend h3 {
            margin-top: 0;
        }
        .color-sample {
            display: inline-block;
            margin: 0.25rem 0.5rem 0.25rem 0;
        }
        .color-box {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            margin-right: 0.25rem;
            vertical-align: middle;
            border: 1px solid #333;
        }
    </style>
</head>
<body>
    <h1>Franklin Mini Codeblock - Shell Syntax Highlighting Test</h1>
    
    <div class="legend">
        <h3>Expected Highlighting Colors - Command Coloring Rule</h3>
        <p>This test verifies the new shell command highlighting rule:</p>
        <ul>
            <li><strong>First command word</strong> (e.g., <code>sudo</code>, <code>mkdir</code>, <code>ls</code>) → <strong>Orange</strong></li>
            <li><strong>Second command word</strong> after command wrappers (e.g., <code>mkdir</code> in <code>sudo mkdir</code>) → <strong>Blue</strong></li>
        </ul>
        <div>
            <span class="color-sample"><span class="color-box" style="background: #ffca80;"></span> <strong>First Command:</strong> sudo, mkdir, ls, grep</span>
            <span class="color-sample"><span class="color-box" style="background: #82aaff;"></span> <strong>Second Command:</strong> mkdir (after sudo), apt-get (after sudo)</span>
            <span class="color-sample"><span class="color-box" style="background: #ff6b9d;"></span> <strong>Operator:</strong> ( ) $( ` | ; &&</span>
            <span class="color-sample"><span class="color-box" style="background: #a5d6a7;"></span> <strong>String:</strong> \'...\' "..."</span>
        </div>
    </div>
';

// Generate test cases
foreach ( $samples as $sample ) {
    $highlighted = $highlight_method->invoke( $instance, $sample['code'], $sample['language'] );
    
    $html .= '
    <div class="test-case">
        <h2>' . htmlspecialchars( $sample['description'] ) . '</h2>
        <div class="test-description">Raw code:</div>
        <div class="raw-code">' . htmlspecialchars( $sample['code'] ) . '</div>
        
        <div class="test-description">Highlighted output:</div>
        <div class="fmc-wrapper">
            <div class="fmc-header">
                <span class="fmc-lang">' . htmlspecialchars( strtoupper( $sample['language'] ) ) . '</span>
                <button class="fmc-copy" disabled>Copy</button>
            </div>
            <pre class="fmc-pre"><code class="fmc-code">' . $highlighted . '</code></pre>
        </div>
    </div>
';
}

$html .= '
    <div style="margin: 3rem 0; padding: 1rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3>Visual Inspection Checklist - Command Coloring</h3>
        <ul>
            <li>✓ In <code>sudo mkdir</code>: <code>sudo</code> should be orange, <code>mkdir</code> should be blue</li>
            <li>✓ In <code>mkdir</code>: <code>mkdir</code> should be orange</li>
            <li>✓ In <code>ls -la | grep test</code>: both <code>ls</code> and <code>grep</code> should be orange</li>
            <li>✓ Both <code>(</code> and <code>)</code> should have the same pink/operator color</li>
            <li>✓ The <code>$(</code> in <code>echo $(ls)</code> should be highlighted as an operator</li>
            <li>✓ Parentheses inside strings should be green (string color)</li>
            <li>✓ Square brackets <code>[ ]</code> and <code>[[ ]]</code> should be highlighted</li>
            <li>✓ Backticks <code>`</code> should have the operator color</li>
        </ul>
    </div>
</body>
</html>
';

// Write the output file
$output_dir = __DIR__ . '/../examples';
$output_file = $output_dir . '/shell-test.html';

if ( ! is_dir( $output_dir ) ) {
    if ( ! mkdir( $output_dir, 0755, true ) ) {
        die( "Error: Failed to create examples directory at $output_dir\n" );
    }
}

if ( file_put_contents( $output_file, $html ) === false ) {
    die( "Error: Failed to write output file at $output_file\n" );
}

echo "✓ Generated " . $output_file . "\n";
echo "  Open this file in a browser to verify the syntax highlighting.\n";
