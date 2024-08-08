<?php

namespace local_asystgrade\api;

use Exception;

defined('MOODLE_INTERNAL') || die();

class client {
    private $endpoint;
    private $httpClient;

    /**
     * @param string $endpoint
     * @param http_client|null $httpClient
     */
    public function __construct(string $endpoint, http_client $httpClient = null) {
        $this->endpoint = $endpoint;
        $this->httpClient = $httpClient ?: new http_client();
    }

    /**
     * @param array $data
     * @return bool|string
     * @throws Exception
     */
    public function send_data(array $data) {
        $response = $this->httpClient->post($this->endpoint, $data);

        return $response;
    }
}