<?php

namespace local_asystgrade\api;

defined('MOODLE_INTERNAL') || die();

interface http_client_interface {

    /**
     * @param string $url
     * @param array $data
     * @return bool|string
     */
    public function post(string $url, array $data): bool|string;
}