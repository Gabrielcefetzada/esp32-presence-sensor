<?php

declare(strict_types=1);

namespace App\Process;

use App\Service\MovementService;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Throwable;

class PresenceSensorConsumer extends AbstractProcess
{
    public int $nums = 1;
    public string $name = 'presence-sensor-consumer';
    public bool $redirectStdinStdout = false;
    public string $mqttHost;
    public string $mqttPassword;
    public string $mqttUser;

    private StdoutLoggerInterface $logger;
    private MovementService $movementService;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        parent::__construct($container);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->movementService = $container->get(MovementService::class);
        
        $this->mqttHost = $config->get('mqtt.host', 'localhost');
        $this->mqttUser = $config->get('mqtt.user', '');
        $this->mqttPassword = $config->get('mqtt.password', '');
    }

    public function handle(): void
    {
        $command = sprintf(
            'mosquitto_sub -h %s -p 8883 -u %s -P %s -t sensors/test --cafile /etc/ssl/certs/ca-certificates.crt -v',
            $this->mqttHost,
            $this->mqttUser,
            $this->mqttPassword
        );

        while (true) {
            try {
                $process = popen($command, 'r');

                if ($process === false) {
                    throw new \RuntimeException('Falha ao iniciar o processo mosquitto_sub');
                }

                while (!feof($process)) {
                    $message = fgets($process);

                    if ($message !== false && trim($message) !== '') {
                        $trimmedMessage = trim($message);
                        
                        $this->logger->info(sprintf('[Nova mensagem] %s', $trimmedMessage));                        
                        $this->movementService->handleMovement($trimmedMessage);
                    }
                }

                pclose($process);

                throw new \RuntimeException('O processo mosquitto_sub foi encerrado inesperadamente.');
            } catch (Throwable $e) {
                $this->logger->error(sprintf(
                    '[MQTT Error] %s: %s. Reiniciando em 3s...',
                    get_class($e),
                    $e->getMessage()
                ));
                sleep(3);
            }
        }
    }
}
