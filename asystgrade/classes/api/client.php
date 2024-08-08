<?php

namespace local_asystgrade\api;

use Exception;
use \local_asystgrade\api\http_client_interface;

defined('MOODLE_INTERNAL') || die();

class client {
    private string                $endpoint;
    private http_client_interface $httpClient;
    private static ?client        $instance = null;

    /**
     * @param string $endpoint
     * @param http_client_interface $httpClient
     */
    private function __construct(string $endpoint, http_client_interface $httpClient) {
        $this->endpoint = $endpoint;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $endpoint
     * @param http_client_interface $httpClient
     * @return client
     */
    public static function getInstance(string $endpoint, http_client_interface $httpClient): client
    {
        if (self::$instance === null) {
            self::$instance = new client($endpoint, $httpClient);
        }
        return self::$instance;
    }

    /**
     * @param array $data
     * @return bool|string
     * @throws Exception
     */
    public function send_data(array $data): bool|string
    {
        try {
            return $this->httpClient->post($this->endpoint, $data);
        } catch (Exception $e) {
            throw new Exception('HTTP request error: ' . $e->getMessage());
        }
    }
}