<?php

declare(strict_types=1);

namespace App\Service;

use App\Integration\TelegramIntegration;
use Hyperf\Contract\StdoutLoggerInterface;

class MovementService
{
    private TelegramIntegration $telegramIntegration;
    private StdoutLoggerInterface $logger;

    public function __construct(TelegramIntegration $telegramIntegration, StdoutLoggerInterface $logger)
    {
        $this->telegramIntegration = $telegramIntegration;
        $this->logger = $logger;
    }

    public function handleMovement(string $payload): void
    {
        $message = $this->extractMessage($payload);

        if ($message === null) {
            $this->logger->warning('Invalid or empty MQTT payload: ' . $payload);
            return;
        }

        $text = $this->escapeMarkdown($message);

        $sent = $this->telegramIntegration->sendMessage($text);

        if ($sent) {
            $this->logger->info('Message sent to Telegram successfully.');
        } else {
            $this->logger->error('Message delivery fail.');
        }
    }

    private function extractMessage(string $payload): ?string
    {
        $start = strpos($payload, '{');

        if ($start === false) {
            return null;
        }

        $json = substr($payload, $start);

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if (!is_array($data) || !isset($data['message']) || !is_string($data['message'])) {
            return null;
        }

        $message = trim($data['message']);

        return $message === '' ? null : $message;
    }

    private function escapeMarkdown(string $text): string
    {
        return str_replace(
            ['_', '*', '`', '['],
            ['\_', '\*', '\`', '\['],
            $text
        );
    }
}
