<?php

/**
 * GitHub webhook handler template.
 * 
 * @see     https://docs.github.com/webhooks/
 * @author  Miloslav Hůla (https://github.com/milo)
 * @author  Fernando Bevilacqua (https://github.com)
 */
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

    protected function handlePayload($event, $payload)
    {
        $this->logRequest($payload);
    }

    public function run()
    {
        $hookSecret = null;//$this->config['github']['webhook_secret']; // null to disable check

        set_error_handler(function($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        $rawPost = null;

        if ($hookSecret !== null) {
            if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
                throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
            } elseif (!extension_loaded('hash')) {
                throw new \Exception("Missing 'hash' extension to check the secret code validity.");
            }

            list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
            if (!in_array($algo, hash_algos(), true)) {
                throw new \Exception("Hash algorithm '$algo' is not supported.");
            }

            $rawPost = file_get_contents('php://input');
            if (!hash_equals($hash, hash_hmac($algo, $rawPost, $hookSecret))) {
                throw new \Exception('Hook secret does not match.');
            }
        }

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            throw new \Exception("Missing HTTP 'Content-Type' header.");
        } elseif (!isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
            throw new \Exception("Missing HTTP 'X-Github-Event' header.");
        }

        switch ($_SERVER['CONTENT_TYPE']) {
            case 'application/json':
                $json = $rawPost ?: file_get_contents('php://input');
                break;

            case 'application/x-www-form-urlencoded':
                $json = $_POST['payload'];
                break;

            default:
                throw new \Exception("Unsupported content type: $_SERVER[CONTENT_TYPE]");
        }

        # Payload structure depends on triggered event
        # https://developer.github.com/v3/activity/events/types/
        $payload = json_decode($json);
        $event = strtolower($_SERVER['HTTP_X_GITHUB_EVENT']);

        $this->handlePayload($event, $payload);
    }
}