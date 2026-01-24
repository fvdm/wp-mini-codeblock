<?php
/**
 * Standalone test for shell string pattern bug
 */

// Test the regex pattern directly
$pattern_single = "'" . '(?:\\\\.|[^' . "'" . '\\\\])*' . "'";
$pattern_double = '"(?:\\\\.|[^"\\\\])*"';
$pattern = '/(' . $pattern_single . '|' . $pattern_double . ')/s';

$test_cases = [
    // Test case 1: Empty string
    "sed -i '' -E 's/test//' file.txt",
    
    // Test case 2: The problematic line from the bug report
    "sed -i '' -E 's/^#[[:blank:]]*dnssec:\$/dnssec:/' recursor.yml",
    
    // Test case 3: Another line from the bug report  
    "sed -i '' -E 's/^# *validation: process\$/  validation: validate/' recursor.yml",
];

echo "Testing shell string pattern\n";
echo "=============================\n\n";

echo "Pattern: " . $pattern . "\n\n";

foreach ($test_cases as $i => $test) {
    echo "Test case " . ($i + 1) . ": " . $test . "\n";
    
    preg_match_all($pattern, $test, $matches, PREG_OFFSET_CAPTURE);
    
    if (count($matches[0]) > 0) {
        echo "  Found " . count($matches[0]) . " string(s):\n";
        foreach ($matches[0] as $match) {
            echo "    - '" . $match[0] . "' at position " . $match[1] . "\n";
        }
    } else {
        echo "  No strings found\n";
    }
    echo "\n";
}

// Now test what happens when we replace
echo "\n\nTesting replacement behavior:\n";
echo "================================\n\n";

$test = "sed -i '' -E 's/^#[[:blank:]]*dnssec:\$/dnssec:/' recursor.yml";
echo "Input: " . $test . "\n\n";

$counter = 0;
$result = preg_replace_callback($pattern, function($matches) use (&$counter) {
    $token = '___TOKEN_' . $counter . '___';
    echo "Match $counter: '" . $matches[0] . "' -> $token\n";
    $counter++;
    return $token;
}, $test);

echo "\nResult: " . $result . "\n";
