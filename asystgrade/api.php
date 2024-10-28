<?php

require_once('../../config.php');
require_once('lib.php');

use local_asystgrade\api\client;
use local_asystgrade\api\http_client;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        // Preparing Flask API
        $apiendpoint = get_config('local_asystgrade', 'apiendpoint') ?: 'http://flask:5000/api/autograde';
        $httpClient = new http_client();
        $apiClient = client::getInstance($apiendpoint, $httpClient);

        try {
            // Sending data on Flask and obtaining an answer
            $response = $apiClient->send_data($data);
            $grades = json_decode($response, true);

            // Check JSON validity
            if (json_last_error() === JSON_ERROR_NONE) {
                echo json_encode(['success' => true, 'grades' => $grades]);
            } else {
                echo json_encode(['error' => 'Invalid JSON from Flask API']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'No data received']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}