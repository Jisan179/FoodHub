<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;
use App\Core\Mailer;
use App\Models\UserRepository;

/** Email-link password reset with single-use, expiring tokens. Only token hashes are stored. */
final class PasswordResetService extends Service
{
    private const LIFETIME_MINUTES = 30;

    /** Silently does nothing for unknown addresses, so accounts cannot be discovered. */
    public function request(string $email): void
    {
        $user = $this->app->make(UserRepository::class)->findByEmail($email);
        if (!$user || !in_array($user['status'], ['active', 'pending'], true)) {
            return;
        }
        $token = bin2hex(random_bytes(32));
        $this->db->run('DELETE FROM password_resets WHERE user_id = ?', [(int) $user['user_id']]);
        $this->db->insert('password_resets', [
            'user_id' => (int) $user['user_id'],
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + self::LIFETIME_MINUTES * 60),
        ]);
        $link = $this->app->config['url'] . '/reset-password/' . $token;
        (new Mailer($this->app->config['mail']))->send(
            $user['email'],
            'Reset your FoodHub password',
            "Hello {$user['name']},\n\nOpen this link within " . self::LIFETIME_MINUTES . " minutes to choose a new password:\n$link\n\n"
            . 'If you did not ask for this, you can ignore this message.'
        );
    }

    public function find(string $token): ?array
    {
        return $this->db->one(
            'SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > ?',
            [hash('sha256', $token), $this->now()]
        );
    }

    public function reset(string $token, string $password): void
    {
        $this->db->transaction(function () use ($token, $password) {
            $reset = $this->db->one(
                'SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > ? FOR UPDATE',
                [hash('sha256', $token), $this->now()]
            );
            if (!$reset) {
                throw new DomainError('This reset link is invalid or has expired. Request a new one.');
            }
            $this->app->make(UserRepository::class)->setPassword((int) $reset['user_id'], $password);
            $this->db->update('password_resets', ['used_at' => $this->now()], 'reset_id', (int) $reset['reset_id']);
        });
    }
}
