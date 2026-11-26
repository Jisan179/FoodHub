<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database;

abstract class Service
{
    protected App $app;
    protected Database $db;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->db();
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    protected function record(int $orderId, string $event, ?int $userId, string $note = ''): void
    {
        $this->db->insert('order_status_history', [
            'order_id' => $orderId, 'event' => $event, 'changed_by' => $userId, 'note' => mb_substr($note, 0, 255),
        ]);
    }
}
