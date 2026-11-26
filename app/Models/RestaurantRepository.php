<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DomainError;
use App\Core\HttpException;
use App\Core\Paginator;

final class RestaurantRepository extends Repository
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'suspended'];

    private const RATING_COLUMNS = '
        (SELECT ROUND(AVG(v.rating), 1) FROM reviews v JOIN food_items f ON f.item_id = v.item_id WHERE f.restaurant_id = r.restaurant_id) AS avg_rating,
        (SELECT COUNT(*) FROM reviews v JOIN food_items f ON f.item_id = v.item_id WHERE f.restaurant_id = r.restaurant_id) AS review_count,
        (SELECT COUNT(*) FROM food_items f WHERE f.restaurant_id = r.restaurant_id AND f.is_available = 1 AND f.deleted_at IS NULL) AS item_count';

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM restaurants WHERE restaurant_id = ?', [$id]);
    }

    /** The restaurant, only if it belongs to this manager. */
    public function ownedOrFail(int $id, int $ownerId): array
    {
        $restaurant = $this->db->one('SELECT * FROM restaurants WHERE restaurant_id = ? AND owner_id = ?', [$id, $ownerId]);
        if (!$restaurant) {
            throw new HttpException(404, 'Restaurant not found.');
        }

        return $restaurant;
    }

    public function forOwner(int $ownerId): array
    {
        return $this->db->all('SELECT * FROM restaurants WHERE owner_id = ? ORDER BY restaurant_id', [$ownerId]);
    }

    public function approved(int $customerId, string $search = '', string $cuisine = '', int $limit = 100): array
    {
        $where = "r.status = 'approved'";
        $params = [$customerId];
        if ($search !== '') {
            $where .= ' AND (r.name LIKE ? OR r.description LIKE ? OR r.address LIKE ?)';
            array_push($params, ...array_fill(0, 3, $this->like($search)));
        }
        if ($cuisine !== '') {
            $where .= ' AND r.cuisine_type = ?';
            $params[] = $cuisine;
        }
        $params[] = $limit;

        return $this->db->all(
            'SELECT r.*, ' . self::RATING_COLUMNS . ',
                EXISTS (SELECT 1 FROM favorites x WHERE x.restaurant_id = r.restaurant_id AND x.customer_id = ?) AS is_favorite
             FROM restaurants r WHERE ' . $where . ' ORDER BY r.is_open DESC, r.name LIMIT ?',
            $params
        );
    }

    public function cuisines(): array
    {
        return array_column($this->db->all("SELECT DISTINCT cuisine_type FROM restaurants WHERE status = 'approved' AND cuisine_type <> '' ORDER BY cuisine_type"), 'cuisine_type');
    }

    public function approvedOrFail(int $id, int $customerId): array
    {
        $restaurant = $this->db->one(
            'SELECT r.*, ' . self::RATING_COLUMNS . ",
                EXISTS (SELECT 1 FROM favorites x WHERE x.restaurant_id = r.restaurant_id AND x.customer_id = ?) AS is_favorite
             FROM restaurants r WHERE r.restaurant_id = ? AND r.status = 'approved'",
            [$customerId, $id]
        );
        if (!$restaurant) {
            throw new HttpException(404, 'Restaurant not found.');
        }

        return $restaurant;
    }

    public function create(array $data): int
    {
        return $this->db->insert('restaurants', $data);
    }

    public function update(int $id, array $data): void
    {
        $this->db->update('restaurants', $data, 'restaurant_id', $id);
    }

    public function setStatus(int $id, string $status, string $reason = ''): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new DomainError('Invalid restaurant status.');
        }
        if ($status === 'rejected' && $reason === '') {
            throw new DomainError('Please give a reason for rejecting this restaurant.');
        }
        if (!$this->find($id)) {
            throw new HttpException(404, 'Restaurant not found.');
        }
        $this->update($id, ['status' => $status, 'rejection_reason' => $status === 'rejected' ? mb_substr($reason, 0, 255) : null]);
    }

    public function count(string $status = ''): int
    {
        return in_array($status, self::STATUSES, true)
            ? (int) $this->db->value('SELECT COUNT(*) FROM restaurants WHERE status = ?', [$status])
            : (int) $this->db->value('SELECT COUNT(*) FROM restaurants');
    }

    public function paginate(string $status, Paginator $page): array
    {
        $where = '1 = 1';
        $params = [];
        if (in_array($status, self::STATUSES, true)) {
            $where = 'r.status = ?';
            $params[] = $status;
        }
        array_push($params, $page->perPage, $page->offset());

        return $this->db->all(
            "SELECT r.*, u.name AS owner_name, u.email AS owner_email,
                (SELECT COUNT(*) FROM food_items f WHERE f.restaurant_id = r.restaurant_id AND f.deleted_at IS NULL) AS item_count
             FROM restaurants r JOIN users u ON u.user_id = r.owner_id
             WHERE $where ORDER BY (r.status = 'pending') DESC, r.restaurant_id DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public function hasOrders(int $id): bool
    {
        return (bool) $this->db->value('SELECT 1 FROM orders WHERE restaurant_id = ? LIMIT 1', [$id]);
    }

    public function delete(int $id): void
    {
        if ($this->hasOrders($id)) {
            throw new DomainError('This restaurant has order history, so it cannot be deleted. Close it for orders instead.');
        }
        $this->db->run('DELETE FROM restaurants WHERE restaurant_id = ?', [$id]);
    }
}
