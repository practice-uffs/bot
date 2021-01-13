<?php

/**
 * Practice brain
 *
 * Controla o processamento, interpretação e afins de todas as mensagens
 * tratadas pelo bot como sendo do practice.
 *
 */

class PracticeBot {
    public static function parseDriveFolderNames($text, $defaultRepo = 'programa')
    {
        $re = '/([A-Za-z-_0-9]+)?#([0-9]+)/m';
        $matches = [];

        preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
        $has_issue_mention = count($matches) > 0;

        if(!$has_issue_mention) {
            return [];
        }

        $result = [];

        foreach($matches as $match) {
            $repo = $match[1];
            $number = $match[2];

            $repo = $repo == '' ? $defaultRepo : $repo;

            $result[] = ['repo' => $repo, 'issue' => $number];
        }

        return $result;
    }
}