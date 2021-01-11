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

    public function __construct()
    {
    }

    protected function giveIssueInfo()
    {
        $message_type = $this->message->getType();

        if($message_type == 'text') {
            return $this->sys->replyToChat("new text not found.. :(");
        }

        return null;
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
