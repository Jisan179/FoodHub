<?php

declare(strict_types=1);

namespace App\Models;

final class FavoriteRepository extends Repository
{
    /** @return bool true when the restaurant is now a favorite */
    public function toggle(int $customerId, int $restaurantId): bool
    {
        $removed = $this->db->run('DELETE FROM favorites WHERE customer_id = ? AND restaurant_id = ?', [$customerId, $restaurantId])->rowCount();
        if ($removed > 0) {
            return false;
        }
        $this->db->insert('favorites', ['customer_id' => $customerId, 'restaurant_id' => $restaurantId]);

        return true;
    }

    public function count(int $customerId): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM favorites WHERE customer_id = ?', [$customerId]);
    }
}
