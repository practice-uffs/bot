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
    protected array $config = [];

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

    protected function getGithubClient($authenticated = false)
    {
        $guzz_client = new \GuzzleHttp\Client([
            \GuzzleHttp\RequestOptions::VERIFY => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
        ]);
        
        $gh = \Github\Client::createWithHttpClient($guzz_client);

        if($authenticated) {
            $user = $this->config['github']['username'];
            $token = $this->config['github']['token'];

            $gh->authenticate($token, null, Github\Client::AUTH_ACCESS_TOKEN);
        }
        
        return $gh;
    }

    protected function getGoogleDriveClient()
    {
        $drive = new PracticeGoogleDrive($this->config);
        return $drive;
    }

    protected function handleIssuesEvent($payload)
    {
        $org = $payload->organization->login;
        $repo = $payload->repository->name;
        $issue = $payload->issue->number;

        switch($payload->action) {
            case 'opened':
                $gh = $this->getGithubClient(true);
                $drive = $this->getGoogleDriveClient();

                $comment = '';
                $folders = $drive->createIssueWorkingFolder($issue, $repo);

                if($folders != null) {
                    var_dump($folders);
                    $drive_link = isset($folders['folder']) ? $folders['folder']->getWebViewLink() : '';
                    $drive_in_link = isset($folders['in']) ? $folders['in']->getWebViewLink() : '';
                    $drive_out_link = isset($folders['out']) ? $folders['out']->getWebViewLink() : '';

                    $comment = 
                        'Criei as pastas dessa issue no Google Drive:' . "\n" .
                        '* <img width="16" height="16" src="https://ssl.gstatic.com/images/branding/product/1x/drive_2020q4_32dp.png" /> [Pasta da issue]('.$drive_link.') ' . "\n" .
                        '* 🔽 [Entrada]('.$drive_in_link.')' . "\n" .
                        '* 🔼 [Saída]('.$drive_out_link.')';
                } else {
                    $comment = 'Humm, não consegui criar as pastas dessa issue no Google Drive. Desculpe 😥';
                }

                $gh->api('issue')->comments()->create($org, $repo, $issue, array('body' => $comment));
                break;
        }
    }

    protected function handlePayload($event, $payload)
    {
        if($event == 'issues') {
            $this->handleIssuesEvent($payload);
        }
        $this->logRequest($payload);
    }

    public function run()
    {
        $hookSecret = $this->config['github']['webhook_secret']; // null to disable check

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