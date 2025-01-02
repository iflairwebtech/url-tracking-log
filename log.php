<?php 
exit('d');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$logFile = 'log.json';

// Initialize log file if it doesn't exist
if (!file_exists($logFile)) {
    file_put_contents($logFile, '[]');
    chmod($logFile, 0666);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($input['url']) || !isset($input['timestamp']) || !isset($input['title'])) {
            throw new Exception('Missing required fields');
        }
        
        // Read existing logs
        $currentContent = file_get_contents($logFile);
        $logs = json_decode($currentContent, true) ?: [];
        
        if (!is_array($logs)) {
            $logs = [];
        }
        
        // Add new log at the beginning
        array_unshift($logs, [
            'url' => $input['url'],
            'timestamp' => $input['timestamp'],
            'title' => $input['title']
        ]);
        
        // Write back to file
        if (file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to write log');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $logs = json_decode(file_get_contents($logFile), true) ?: [];
    echo json_encode($logs);
}
?>
