<?php
$dir = __DIR__ . '/database/migrations/';
$files = glob($dir . '2026_07_08_1*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, "foreign('created_by')") === false) continue;
    
    // Normalize line endings to \n first
    $c = str_replace("\r\n", "\n", $c);
    
    // Pattern: the mangled block with mixed indentation
    $pattern = '/[ \t]*\$table\n[ \t]*\$table->foreignId\(\'created_by\'\)->nullable\(\);\n[ \t]*\$table->foreignId\(\'updated_by\'\)->nullable\(\);\n[ \t]*\$table->foreignId\(\'deleted_by\'\)->nullable\(\);\n->foreign\(\'created_by\'\)->references\(\'id\'\)->on\(\'users\'\)->nullOnDelete\(\);\n[ \t]*\$table->foreign\(\'updated_by\'\)->references\(\'id\'\)->on\(\'users\'\)->nullOnDelete\(\);\n[ \t]*\$table->foreign\(\'deleted_by\'\)->references\(\'id\'\)->on\(\'users\'\)->nullOnDelete\(\);/';
    
    $replacement = "            \$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();\n            \$table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();\n            \$table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();";
    
    $new = preg_replace($pattern, $replacement, $c);
    if ($new !== $c) {
        file_put_contents($f, $new);
        echo "Fixed: " . basename($f) . "\n";
    } else {
        echo "SKIP: " . basename($f) . "\n";
    }
}
echo "Done!\n";
