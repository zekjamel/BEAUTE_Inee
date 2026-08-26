<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class QuardlockDiagnosticLogger
{
    public function __construct(
        #[Autowire('%kernel.logs_dir%')]
        private readonly string $logsDirectory,
    ) {
    }

    /** @param array<string, mixed> $context */
    public function log(string $event, array $context = []): void
    {
        if (!is_dir($this->logsDirectory) && !@mkdir($this->logsDirectory, 0750, true) && !is_dir($this->logsDirectory)) {
            return;
        }

        $record = [
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'event' => $event,
            ...$context,
        ];
        $encoded = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            return;
        }

        @file_put_contents(
            $this->logsDirectory . '/quardlock-fido.jsonl',
            $encoded . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}