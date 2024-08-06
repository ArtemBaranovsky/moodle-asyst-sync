<?php

namespace local_asystgrade\api;

defined('MOODLE_INTERNAL') || die();

class http_client implements http_client_interface {
    public function post($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($response === false) {
            throw new \Exception('Error sending data to API');
        }

        return $response;
    }
}
