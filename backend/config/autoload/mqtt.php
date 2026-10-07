<?php

declare(strict_types=1);

return [
    'host' => $_ENV['MQTT_HOST'] ?? getenv('MQTT_HOST') ?: 'localhost',
    'user' => $_ENV['MQTT_USER'] ?? getenv('MQTT_USER') ?: '',
    'password' => $_ENV['MQTT_PASSWORD'] ?? getenv('MQTT_PASSWORD') ?: '',
];