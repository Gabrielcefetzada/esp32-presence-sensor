<?php

declare(strict_types=1);

namespace App\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\ConfigInterface;

class TelegramIntegration
{
    private string $botToken;
    private string $chatId;
    private Client $client;

    public function __construct(ConfigInterface $config)
    {
        $this->botToken = $config->get('telegram.bot_token', '');
        $this->chatId = $config->get('telegram.chat_id', '');
        $this->client = new Client();
    }

    public function sendMessage(string $message): bool
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            return false;
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $this->botToken);

        try {
            $response = $this->client->post($url, [
                'json' => [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }
}