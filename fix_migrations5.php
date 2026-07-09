<?php
$dir = __DIR__ . '/database/migrations/';
$files = glob($dir . '2026_07_08_1*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, "foreign('created_by')") === false && strpos($c, "->foreign('created_by')") === false) continue;
    
    // Normalize line endings
    $c = str_replace("\r\n", "\n", $c);
    
    // Just remove the dangling lines and old foreign() calls, replace everything with correct foreignId+constrained
    // First remove the orphan "$table" line and the injected foreignId lines
    $c = preg_replace('/[ \t]*\$table\n[ \t]*\$table->foreignId\(\'created_by\'\)->nullable\(\);\n/', '', $c);
    $c = preg_replace('/[ \t]*\$table->foreignId\(\'updated_by\'\)->nullable\(\);\n/', '', $c);
    $c = preg_replace('/[ \t]*\$table->foreignId\(\'deleted_by\'\)->nullable\(\);\n/', '', $c);
    
    // Now replace the old foreign() calls with proper foreignId()->constrained()
    $c = str_replace(
        "->foreign('created_by')->references('id')->on('users')->nullOnDelete();",
        "->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();",
        $c
    );
    $c = str_replace(
        "->foreign('updated_by')->references('id')->on('users')->nullOnDelete();",
        "->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();",
        $c
    );
    $c = str_replace(
        "->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();",
        "->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();",
        $c
    );
    
    file_put_contents($f, $c);
    echo "Fixed: " . basename($f) . "\n";
}
echo "Done!\n";
