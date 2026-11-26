<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\HttpException;

final class FoodRepository extends Repository
{
    public function forManager(int $restaurantId): array
    {
        return $this->db->all('SELECT * FROM food_items WHERE restaurant_id = ? AND deleted_at IS NULL ORDER BY category, name', [$restaurantId]);
    }

    public function ownedOrFail(int $itemId, int $ownerId): array
    {
        $item = $this->db->one(
            'SELECT f.* FROM food_items f JOIN restaurants r ON r.restaurant_id = f.restaurant_id
             WHERE f.item_id = ? AND r.owner_id = ? AND f.deleted_at IS NULL',
            [$itemId, $ownerId]
        );
        if (!$item) {
            throw new HttpException(404, 'Menu item not found.');
        }

        return $item;
    }

    /** Items a customer can see, with ratings. */
    public function menu(int $restaurantId, string $category = ''): array
    {
        $where = 'f.restaurant_id = ? AND f.is_available = 1 AND f.deleted_at IS NULL';
        $params = [$restaurantId];
        if ($category !== '') {
            $where .= ' AND f.category = ?';
            $params[] = $category;
        }

        return $this->db->all(
            "SELECT f.*,
                (SELECT ROUND(AVG(rating), 1) FROM reviews v WHERE v.item_id = f.item_id) AS avg_rating,
                (SELECT COUNT(*) FROM reviews v WHERE v.item_id = f.item_id) AS review_count
             FROM food_items f WHERE $where ORDER BY f.category, f.name",
            $params
        );
    }

    public function categories(int $restaurantId): array
    {
        return array_column($this->db->all(
            'SELECT DISTINCT category FROM food_items WHERE restaurant_id = ? AND is_available = 1 AND deleted_at IS NULL ORDER BY category',
            [$restaurantId]
        ), 'category');
    }

    public function search(string $term): array
    {
        $like = $this->like($term);

        return $this->db->all(
            "SELECT f.*, r.name AS restaurant_name, r.is_open FROM food_items f
             JOIN restaurants r ON r.restaurant_id = f.restaurant_id
             WHERE r.status = 'approved' AND f.is_available = 1 AND f.deleted_at IS NULL
               AND (f.name LIKE ? OR f.category LIKE ?)
             ORDER BY f.name LIMIT 50",
            [$like, $like]
        );
    }

    /** An item with the facts needed to decide whether it can be ordered. */
    public function findForOrder(int $itemId): ?array
    {
        return $this->db->one(
            'SELECT f.*, r.name AS restaurant_name, r.status AS restaurant_status, r.is_open
             FROM food_items f JOIN restaurants r ON r.restaurant_id = f.restaurant_id WHERE f.item_id = ?',
            [$itemId]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert('food_items', $data);
    }

    public function update(int $itemId, array $data): void
    {
        $this->db->update('food_items', $data, 'item_id', $itemId);
    }

    /** Soft delete keeps past orders and reviews intact, and removes the item from carts. */
    public function softDelete(int $itemId): void
    {
        $this->db->transaction(function () use ($itemId) {
            $this->db->run('UPDATE food_items SET deleted_at = ?, is_available = 0 WHERE item_id = ?', [date('Y-m-d H:i:s'), $itemId]);
            $this->db->run('DELETE FROM cart_items WHERE item_id = ?', [$itemId]);
        });
    }
}
