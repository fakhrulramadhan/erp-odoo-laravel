<?php
$dir = __DIR__ . '/database/migrations/';
$files = glob($dir . '2026_07_08_1*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, "foreign('created_by')") === false) continue;
    
    // Debug: show bytes around the problem area
    $idx = strpos($c, "\$table\n");
    if ($idx === false) $idx = strpos($c, "\$table\r\n");
    
    // Use \r\n aware pattern
    $pattern = '/[ \t]*\$table\r?\n[ \t]*\$table->foreignId\(\'created_by\'\)->nullable\(\);\r?\n[ \t]*\$table->foreignId\(\'updated_by\'\)->nullable\(\);\r?\n[ \t]*\$table->foreignId\(\'deleted_by\'\)->nullable\(\);\r?\n->foreign\(\'created_by\'\)->references\(\'id\'\)->on\(\'users\'\)->nullOnDelete\(\);\r?\n[ \t]*\$table->foreign\(\'updated_by\'\)->references\(\'id\'\)->on\(\'users\'\)->nullOnDelete\(\);\r?\n[ \t]*\$table->foreign\(\'deleted_by\'\)->references\(\'id\'\)->on\(\'users\'\)->nullOnDelete\(\);/';
    
    $replacement = "            \$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();\r\n            \$table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();\r\n            \$table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();";
    
    $new = preg_replace($pattern, $replacement, $c);
    if ($new !== $c) {
        file_put_contents($f, $new);
        echo "Fixed: " . basename($f) . "\n";
    } else {
        // Show the raw bytes for debugging
        $around = substr($c, max(0, strpos($c, "softDeletes") - 5), 500);
        echo "No match in: " . basename($f) . "\n";
        echo "Raw: " . bin2hex(substr($around, 0, 20)) . "\n";
        echo "Text: " . addcslashes(substr($around, 0, 200), "\r\n\t") . "\n";
    }
}
echo "Done!\n";
