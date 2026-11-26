<?php

declare(strict_types=1);

namespace App\Core;

/** Database-backed limiter, so clearing cookies does not reset the count. */
final class RateLimiter
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function tooMany(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $count = (int) $this->db->value(
            'SELECT COUNT(*) FROM rate_limits WHERE bucket = ? AND attempted_at > ?',
            [hash('sha256', $key), date('Y-m-d H:i:s', time() - $windowSeconds)]
        );

        return $count >= $maxAttempts;
    }

    public function hit(string $key): void
    {
        $this->db->insert('rate_limits', ['bucket' => hash('sha256', $key), 'attempted_at' => date('Y-m-d H:i:s')]);
        // Occasionally remove old rows so the table stays small
        if (random_int(1, 50) === 1) {
            $this->db->run('DELETE FROM rate_limits WHERE attempted_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);
        }
    }

    public function clear(string $key): void
    {
        $this->db->run('DELETE FROM rate_limits WHERE bucket = ?', [hash('sha256', $key)]);
    }
}
