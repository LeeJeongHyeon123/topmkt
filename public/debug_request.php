<?php
// 🔥 Ultra Think Mode: 실제 웹 요청 디버깅
header('Content-Type: application/json');

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'UNKNOWN',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'UNKNOWN',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'UNKNOWN',
    'query_string' => $_SERVER['QUERY_STRING'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'headers' => getallheaders(),
    'current_file' => __FILE__,
    'working_directory' => getcwd(),
    'php_self' => $_SERVER['PHP_SELF'] ?? 'UNKNOWN'
], JSON_PRETTY_PRINT);
?>