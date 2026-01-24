<?php
/**
 * Test case for bug: Shell patterns break in string matching
 * Issue: https://github.com/fvdm/wp-mini-codeblock/issues/XXX
 */

// Include the plugin
require_once __DIR__ . '/../franklin-mini-codeblock.php';

// Get the global instance
$instance = new Franklin_Mini_Codeblock();

// Get highlight_code method
$plugin = new ReflectionClass('Franklin_Mini_Codeblock');
$method = $plugin->getMethod('highlight_code');
$method->setAccessible(true);

// Test the problematic code from the bug report
$code = "cd /usr/local/etc/powerdns \\
&& cp -n recursor.yml-dist recursor.yml \\
&& sed -i '' -E 's/^#[[:blank:]]*dnssec:\$/dnssec:/' recursor.yml \\
&& sed -i '' -E 's/^# *validation: process\$/  validation: validate/' recursor.yml";

echo "Testing shell string pattern bug\n";
echo "==================================\n\n";
echo "Input code:\n";
echo $code . "\n\n";

echo "Highlighted output:\n";
echo "==================================\n";
$result = $method->invoke($instance, $code, 'shell');
echo $result . "\n\n";

// Check if the output contains incorrect highlighting
$issues = [];

// Check if the empty string '' is properly highlighted
if (strpos($result, "'' -E") !== false && strpos($result, "<span class=\"fmc-string\">''</span> -E") === false) {
    $issues[] = "Empty string '' is not properly highlighted as a string";
}

// Check if the sed regex strings are highlighted
if (strpos($result, "'s/^#") !== false) {
    if (strpos($result, "<span class=\"fmc-string\">'s/^#") === false) {
        $issues[] = "sed regex string is not properly highlighted";
    }
}

if (count($issues) > 0) {
    echo "FAILED: Issues found:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
    exit(1);
} else {
    echo "PASSED: All checks passed\n";
    exit(0);
}
