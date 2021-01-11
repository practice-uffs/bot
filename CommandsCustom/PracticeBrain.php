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

class PracticerBrain
{
    public function run(SystemCommand $cmd): ServerResponse
    {
        $message = $cmd->getMessage();
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();

        return $cmd->replyToChat("new text not found.. :(");
    }
}
