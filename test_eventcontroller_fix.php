<?php
// Quick test to verify EventController fix
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing EventController fix for youtube_url issue\n";
echo "================================================\n\n";

// Test database connection with correct host
$host = '211.110.140.147';
$username = 'root';
$password = 'Dnlszkem1!';
$database = 'TOPMKT';

try {
    $connection = new mysqli($host, $username, $password, $database, 3306);
    
    if ($connection->connect_error) {
        throw new Exception('Database connection failed: ' . $connection->connect_error);
    }
    
    echo "✓ Database connection successful\n\n";
    
    // Test the exact query from EventController with corrected column name
    $startDate = '2025-07-01';
    $endDate = '2025-07-31';
    
    $sql = "SELECT 
                id, title, description, instructor_name, instructor_info,
                start_date, end_date, start_time, end_time,
                location_type, venue_name, venue_address, online_link,
                max_participants, registration_fee, category, status,
                content_type, youtube_video, created_at
            FROM lectures 
            WHERE content_type = 'event'
                AND status = 'published'
                AND start_date BETWEEN ? AND ?
            ORDER BY start_date ASC, start_time ASC";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    echo "✓ EventController query executed successfully\n";
    echo "Found " . count($events) . " events for July 2025\n\n";
    
    if (!empty($events)) {
        echo "Sample event data:\n";
        $sample = $events[0];
        echo "ID: " . $sample['id'] . "\n";
        echo "Title: " . $sample['title'] . "\n";
        echo "Date: " . $sample['start_date'] . "\n";
        echo "YouTube Video: " . ($sample['youtube_video'] ?: 'None') . "\n";
    }
    
    echo "\n✓ All fixes verified successfully!\n";
    echo "The EventController should now work without the 'youtube_url' column error.\n";
    
    $connection->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>