<?php
$files = glob(__DIR__ . '/database/migrations/2026_07_08_1*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, "foreign('created_by')") !== false) {
        // Remove the mangled lines
        $bad = "            \$table\n            \$table->foreignId('created_by')->nullable();\n            \$table->foreignId('updated_by')->nullable();\n            \$table->foreignId('deleted_by')->nullable();\n->foreign('created_by')->references('id')->on('users')->nullOnDelete();\n            \$table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();\n            \$table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();";
        $good = "            \$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();\n            \$table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();\n            \$table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();";
        $c = str_replace($bad, $good, $c);
        file_put_contents($f, $c);
        echo "Fixed: " . basename($f) . PHP_EOL;
    }
}
echo "Done!\n";
