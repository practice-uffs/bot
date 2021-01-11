<?php

/**
 * Practice brain
 *
 * Controla o processamento, interpretação e afins de todas as mensagens
 * tratadas pelo bot como sendo do practice.
 *
 */

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class PracticerBrain
{
    protected SystemCommand $sys;
    protected $message;
    protected $sender;
    protected $gh;

    public function __construct()
    {
        $guzz_client = new \GuzzleHttp\Client([
            \GuzzleHttp\RequestOptions::VERIFY => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
        ]);
        
        $this->gh = \Github\Client::createWithHttpClient($guzz_client);
    }

    protected function mapping($arr, $key)
    {
        $ret = [];
    
        if(!isset($arr)) {
            return $ret;
        }
    
        foreach($arr as $entry) {
            if(isset($entry[$key])) {
                $ret[] = $entry[$key];
            }
        }
    
        return $ret;
    }
    
    protected function getIssueAsString($org, $repo, $number)
    {
        $issue = $this->gh->api('issue')->show('practice-uffs', 'programa', 300);
    
        $message =  '***' . basename($issue['repository_url']) . '#' . $issue['number'] . '***: ' . 
                    '__' . $issue['title'] . '__' . "\n" .
                    //substr($issue['body'], 0, 200) . '...' . "\n" .
                    'Labels: ' . implode(', ', $this->mapping($issue['labels'], 'name')) . "\n" .
                    'Quem criou: ' . $issue['user']['login'] . "\n" .
                    'Responsáveis: ' . implode(', ', $this->mapping($issue['assignees'], 'login')) . "\n" .
                    'Status: ' . $issue['state'] . "\n" .
                    $issue['milestone']['title'] . ' (' . (new DateTime($issue['milestone']['due_on']))->format('Y-m-d H:i:s') . ')' . "\n" .
                    "\n" .
                    $issue['url'];

        return $message;
    }

    protected function giveIssueInfo()
    {
        $re = '/([A-Za-z-_0-9]+)?#([0-9]+)/m';
        $matches = [];
        $message_type = $this->message->getType();

        if($message_type != 'text') {
            return null;
        }

        preg_match_all($re, $this->message->getText(), $matches, PREG_SET_ORDER, 0);
        $has_issue_mention = count($matches) > 0;
        
        if(!$has_issue_mention) {
            return null;
        }

        $repo = $matches[0][1];
        $number = $matches[0][2];

        $repo = $repo == '' ? 'programa' : $repo;

        return $this->sys->replyToChat($this->getIssueAsString('practice-uffs', $repo, $number));
    }

    protected function handleCommand()
    {
        $user_id = $this->message->getFrom()->getId();
        $command = $this->message->getCommand();
    }

    public function run(SystemCommand $cmd): ServerResponse
    {
        $this->sys = $cmd;
        $this->message = $cmd->getMessage();
        $this->user = $this->message->getFrom();

        // Any of the following methods will return a result if they want to
        // stop the chaining of other methods, otherwise everything wi be checked.

        if($result = $this->handleCommand()) { return $result; }
        if($result = $this->giveIssueInfo()) { return $result; }

        // If we got here, we have no action to reply...
        return Request::emptyResponse();
    }
}
