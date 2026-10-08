<?php

declare(strict_types=1);

return [
    'bot_token' => $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN') ?: '',
    'chat_id' => $_ENV['TELEGRAM_CHAT_ID'] ?? getenv('TELEGRAM_CHAT_ID') ?: '',
];
