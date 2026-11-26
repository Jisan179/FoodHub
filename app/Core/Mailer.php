<?php

declare(strict_types=1);

namespace App\Core;

final class Mailer
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $body): bool
    {
        // Strip line breaks so user data can never add mail headers
        $to = str_replace(["\r", "\n"], '', $to);
        $subject = str_replace(["\r", "\n"], '', $subject);

        if ($this->config['driver'] === 'mail') {
            return mail($to, $subject, $body, 'From: ' . $this->config['from'] . "\r\nContent-Type: text/plain; charset=UTF-8");
        }

        $entry = sprintf("[%s]\nTo: %s\nSubject: %s\n\n%s\n%s\n", date('c'), $to, $subject, $body, str_repeat('-', 60));

        return file_put_contents($this->config['log_file'], $entry, FILE_APPEND | LOCK_EX) !== false;
    }
}
