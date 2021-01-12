<?php

class PracticeGithub
{
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function logRequest($request)
    {
        $log_name = date('Y-m-d_h-i-s') . '_github_webhook-' . rand(0, 9999) . '.log';
        $log_path = dirname(__FILE__) . '/../logs/' . $log_name;
    
        file_put_contents($log_path, print_r($request, true));
    }

    public function run($data, $delivery, $event)
    {
    }
}