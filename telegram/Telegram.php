<?php

/**
 * Practice brain
 *
 * Controla o processamento, interpretação e afins de todas as mensagens
 * tratadas pelo bot como sendo do practice.
 *
 */

class Telegram
{
    protected $client;
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;

        $this->client = new \GuzzleHttp\Client([
            \GuzzleHttp\RequestOptions::VERIFY => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
        ]);
    }

    protected function getBotToken()
    {
        return $this->config['api_key'];
    }

    protected function getTelegramApiUrl()
    {
        return 'https://api.telegram.org/bot'.$this->getBotToken();
    }

    public function sendMessage($chat_id, $message)
    {
        return $this->client->post($this->getTelegramApiUrl().'/sendMessage', [
            'json' => [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'markdown',
                'disable_notification' => true,
                'disable_web_page_preview' => true
            ]
        ]);
    }

    // A function that receives two images and returns a new imagem that is composed of the first image overlayed with the second image
    public function compositeImage($output_path, $image1, $image2)
    {
        $image1 = imagecreatefrompng($image1);
        $image2 = imagecreatefrompng($image2);
        $new_image = imagecreatetruecolor(imagesx($image1), imagesy($image1));
        imagecopy($new_image, $image1, 0, 0, 0, 0, imagesx($image1), imagesy($image1));
        imagecopy($new_image, $image2, 0, 0, 0, 0, imagesx($image2), imagesy($image2));

        imagepng($new_image, $output_path);
    }

    public function sendPhoto($chat_id, $photo, $caption = null)
    {
        return $this->client->post($this->getTelegramApiUrl()."/sendPhoto?chat_id=$chat_id", [
            'multipart' => [
                [
                    'name' => 'photo',
                    'contents' => $photo,
                ],
                [
                    'name' => 'caption',
                    'contents' => $caption,
                ]
            ]
        ]);
    }
}
