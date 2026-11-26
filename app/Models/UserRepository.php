<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Paginator;

final class UserRepository extends Repository
{
    public const ROLES = ['admin', 'customer', 'manager', 'rider'];
    public const STATUSES = ['pending', 'active', 'inactive', 'suspended'];

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE user_id = ?', [$id]);
    }

    public function findByLogin(string $identifier): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1', [$identifier, $identifier]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function usernameTaken(string $username): bool
    {
        return (bool) $this->db->value('SELECT 1 FROM users WHERE username = ?', [$username]);
    }

    public function emailTaken(string $email, int $exceptId = 0): bool
    {
        return (bool) $this->db->value('SELECT 1 FROM users WHERE email = ? AND user_id <> ?', [$email, $exceptId]);
    }

    public function create(array $data, string $password): int
    {
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);

        return $this->db->insert('users', $data);
    }

    public function update(int $id, array $data): void
    {
        $this->db->update('users', $data, 'user_id', $id);
    }

    public function setPassword(int $id, string $password): void
    {
        $this->update($id, ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    private function filterSql(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (($filters['search'] ?? '') !== '') {
            $where[] = '(name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ?)';
            array_push($params, ...array_fill(0, 4, $this->like($filters['search'])));
        }
        if (in_array($filters['role'] ?? '', self::ROLES, true)) {
            $where[] = 'role = ?';
            $params[] = $filters['role'];
        }
        if (in_array($filters['status'] ?? '', self::STATUSES, true)) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }

        return [implode(' AND ', $where), $params];
    }

    public function count(array $filters = []): int
    {
        [$where, $params] = $this->filterSql($filters);

        return (int) $this->db->value("SELECT COUNT(*) FROM users WHERE $where", $params);
    }

    public function paginate(array $filters, Paginator $page): array
    {
        [$where, $params] = $this->filterSql($filters);
        array_push($params, $page->perPage, $page->offset());

        return $this->db->all(
            "SELECT user_id, name, username, email, phone, role, status, created_at FROM users
             WHERE $where ORDER BY (status = 'pending') DESC, user_id DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public function countByRole(): array
    {
        $counts = array_fill_keys(self::ROLES, 0);
        foreach ($this->db->all('SELECT role, COUNT(*) AS total FROM users GROUP BY role') as $row) {
            $counts[$row['role']] = (int) $row['total'];
        }

        return $counts;
    }

    public function pending(): array
    {
        return $this->db->all("SELECT user_id, name, username, email, phone, role, created_at FROM users WHERE status = 'pending' ORDER BY user_id");
    }

    public function activeRiders(): array
    {
        return $this->db->all("SELECT user_id, name FROM users WHERE role = 'rider' AND status = 'active' ORDER BY name");
    }

    public function activeAdminCount(): int
    {
        return (int) $this->db->value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'");
    }

    /** True when the account is linked to orders as customer, restaurant owner or rider. */
    public function hasOrderHistory(int $id): bool
    {
        return (bool) $this->db->value(
            'SELECT 1 FROM orders o JOIN restaurants r ON r.restaurant_id = o.restaurant_id
             LEFT JOIN deliveries d ON d.order_id = o.order_id
             WHERE o.customer_id = ? OR r.owner_id = ? OR d.rider_id = ? LIMIT 1',
            [$id, $id, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM users WHERE user_id = ?', [$id]);
    }
}
