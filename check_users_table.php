<?php
/**
 * Check users table structure
 */

require_once 'src/config/database.php';

try {
    $db = Database::getInstance();
    
    echo "=== Users Table Structure ===\n";
    $columns = $db->fetchAll("DESCRIBE users");
    
    foreach ($columns as $column) {
        echo sprintf("Column: %-20s | Type: %-30s | Key: %-5s | Default: %s\n",
            $column['Field'],
            $column['Type'],
            $column['Key'],
            $column['Default'] ?? 'NULL'
        );
    }
    
    echo "\n=== Comments Table Structure ===\n";
    $commentsColumns = $db->fetchAll("DESCRIBE comments");
    
    foreach ($commentsColumns as $column) {
        echo sprintf("Column: %-20s | Type: %-30s | Key: %-5s | Default: %s\n",
            $column['Field'],
            $column['Type'],
            $column['Key'],
            $column['Default'] ?? 'NULL'
        );
    }
    
    echo "\n=== Foreign Key Relationships ===\n";
    $fks = $db->fetchAll("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_SCHEMA = 'TOPMKT' 
        AND TABLE_NAME IN ('users', 'comments', 'posts')
        ORDER BY TABLE_NAME, COLUMN_NAME
    ");
    
    foreach ($fks as $fk) {
        echo sprintf("%s.%s -> %s.%s (constraint: %s)\n",
            $fk['TABLE_NAME'],
            $fk['COLUMN_NAME'],
            $fk['REFERENCED_TABLE_NAME'],
            $fk['REFERENCED_COLUMN_NAME'],
            $fk['CONSTRAINT_NAME']
        );
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>