<?php

namespace local_asystgrade\api;

defined('MOODLE_INTERNAL') || die();

class client {
    private $endpoint;
    private $httpClient;

    public function __construct(string $endpoint, http_client $httpClient = null) {
        $this->endpoint = $endpoint;
        $this->httpClient = $httpClient ?: new http_client();
    }

    public function send_data($data) {
        $response = $this->httpClient->post($this->endpoint, $data);

        return $response;
    }
}