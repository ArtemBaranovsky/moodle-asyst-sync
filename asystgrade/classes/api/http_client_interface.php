<?php

namespace local_asystgrade\api;

defined('MOODLE_INTERNAL') || die();

interface http_client_interface {
    public function post($url, $data);
}