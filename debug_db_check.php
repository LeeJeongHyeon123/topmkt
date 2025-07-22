<?php
// Debug script to check database content
require_once '/workspace/src/config/database.php';

try {
    $db = Database::getInstance();
    
    echo "=== Database Connection Test ===\n";
    
    // Check if user 4 has any lectures
    $sql = "SELECT id, user_id, status, content_type, title, LENGTH(lecture_images) as image_data_length, updated_at FROM lectures WHERE user_id = 4 ORDER BY updated_at DESC LIMIT 5";
    $results = $db->fetchAll($sql);
    
    echo "Found " . count($results) . " lectures for user_id 4:\n";
    foreach ($results as $lecture) {
        echo "ID: {$lecture['id']}, Status: {$lecture['status']}, Type: {$lecture['content_type']}, Title: {$lecture['title']}, Image Data Length: {$lecture['image_data_length']}, Updated: {$lecture['updated_at']}\n";
    }
    
    echo "\n=== Draft Lectures Only ===\n";
    $draftSql = "SELECT id, user_id, status, content_type, title, LENGTH(lecture_images) as image_data_length, lecture_images, updated_at FROM lectures WHERE user_id = 4 AND status = 'draft' ORDER BY updated_at DESC LIMIT 3";
    $draftResults = $db->fetchAll($draftSql);
    
    echo "Found " . count($draftResults) . " draft lectures for user_id 4:\n";
    foreach ($draftResults as $draft) {
        echo "ID: {$draft['id']}, Status: {$draft['status']}, Type: {$draft['content_type']}, Title: {$draft['title']}, Image Data Length: {$draft['image_data_length']}, Updated: {$draft['updated_at']}\n";
        echo "Image Data: " . substr($draft['lecture_images'], 0, 200) . "...\n\n";
    }
    
    echo "\n=== Testing getLatestDraftLecture logic ===\n";
    $testSql = "SELECT * FROM lectures WHERE status = 'draft' AND content_type = 'lecture' AND user_id = 4 ORDER BY updated_at DESC, created_at DESC LIMIT 1";
    $testResult = $db->fetch($testSql);
    
    if ($testResult) {
        echo "getLatestDraftLecture would return:\n";
        echo "ID: {$testResult['id']}, Title: {$testResult['title']}, Status: {$testResult['status']}, Type: {$testResult['content_type']}\n";
        echo "lecture_images length: " . strlen($testResult['lecture_images']) . "\n";
        echo "lecture_images content: " . substr($testResult['lecture_images'], 0, 300) . "...\n";
        
        // Test JSON parsing
        if (!empty($testResult['lecture_images'])) {
            try {
                $parsed = json_decode($testResult['lecture_images'], true);
                echo "JSON parsing successful: " . (is_array($parsed) ? count($parsed) . " images" : "NOT_ARRAY") . "\n";
            } catch (Exception $e) {
                echo "JSON parsing failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "lecture_images is empty\n";
        }
    } else {
        echo "getLatestDraftLecture would return NULL (no draft lecture found)\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>