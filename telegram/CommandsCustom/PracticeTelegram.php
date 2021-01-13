<?php

/**
 * Practice brain
 *
 * Controla o processamento, interpretação e afins de todas as mensagens
 * tratadas pelo bot como sendo do practice.
 *
 */

use Longman\TelegramBot\ChatAction;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class PracticeTelegram
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
        try {
            $issue = $this->gh->api('issue')->show($org, $repo, $number);
    
            $message = sprintf(
                '📃***%s/#%d*** %s' . "\n\n" .
                '***%s***' . "\n\n" .
                '🔖 `%s`' . "\n\n" .
                '🤗 ***Quem criou:*** %s' . "\n" .
                '🧐 ***Responsáveis:*** %s' . "\n" .
                '📅 ***%s*** (%s)' . "\n" .
                "\n" .
                '%s',

                basename($issue['repository_url']),
                $issue['number'],
                ($issue['state'] == 'closed' ? '🟢' : '🔴') . ' ' . $issue['state'],
                $issue['title'],
                implode(', ', $this->mapping($issue['labels'], 'name')),
                $issue['user']['login'],
                implode(', ', $this->mapping($issue['assignees'], 'login')),
                $issue['milestone']['title'],
                (new DateTime($issue['milestone']['due_on']))->format('Y-m-d H:i:s'),
                $issue['html_url']
            );

            return $message;

        } catch(\Exception $e) {
            return sprintf('🤖💔 Não consegui infos de https://github.com/%s/%s/issues/%s #tisti', $org, $repo, $number);
        }
    }

    /**
     * Informs the bot is typing
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function informIsTyping(): ServerResponse
    {
        $chat_id = $this->message->getChat()->getId();

        return Request::sendChatAction([
            'chat_id' => $chat_id,
            'action'  => Longman\TelegramBot\ChatAction::TYPING,
        ]);
    }

    public function giveIssueInfo()
    {
        $message_type = $this->message->getType();

        if($message_type != 'text') {
            return null;
        }

        $issues = PracticeBot::parseDriveFolderNames($this->message->getText());
        $total_issues = count($issues);

        if($total_issues == 0) {
            return null;
        }

        $this->informIsTyping();
        $ret = null;

        for($i = 0; $i < $total_issues; $i++) {
            $repo = $issues[$i]['repo'];
            $number = $issues[$i]['issue'];

            $ret = $this->sys->replyToChat(
                $this->getIssueAsString('practice-uffs', $repo, $number),
                ['parse_mode' => 'markdown']
            );
        }

        return $ret;
    }

    protected function handleCommand()
    {
        $user_id = $this->message->getFrom()->getId();
        $command = $this->message->getCommand();
    }

    public function run(SystemCommand $cmd): ServerResponse
    {
        try {
            $this->sys = $cmd;
            $this->message = $cmd->getMessage();
            $this->user = $this->message->getFrom();

            // Any of the following methods will return a result if they want to
            // stop the chaining of other methods, otherwise everything wi be checked.

            if($result = $this->handleCommand()) { return $result; }
            if($result = $this->giveIssueInfo()) { return $result; }

            // If we got here, we have no action to reply...
            return Request::emptyResponse();

        } catch(\Exception $e) {
            return $cmd->replyToChat("🤖💀 Deu ruim: " . $e->getMessage() . "\n" . '`'.$e->getTraceAsString().'`',
                                ['parse_mode' => 'markdown']);
        }
    }
}
