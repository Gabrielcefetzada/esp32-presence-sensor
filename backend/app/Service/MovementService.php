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
        $text = "🚨 *Warning! Presence detected!*\n\n" . $this->escapeMarkdown($payload);

        $sent = $this->telegramIntegration->sendMessage($text);

        if ($sent) {
            $this->logger->info('Message sent to Telegram successfully.');
        } else {
            $this->logger->error('Message delivery fail.');
        }
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