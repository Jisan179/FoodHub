<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;
use App\Models\FoodRepository;

final class CartService extends Service
{
    public function items(int $customerId): array
    {
        return $this->db->all(
            "SELECT c.item_id, c.quantity, f.name, f.image_path, f.price, f.restaurant_id, r.name AS restaurant_name,
                    (f.deleted_at IS NULL AND f.is_available = 1 AND r.status = 'approved' AND r.is_open = 1) AS can_order
             FROM cart_items c JOIN food_items f ON f.item_id = c.item_id JOIN restaurants r ON r.restaurant_id = f.restaurant_id
             WHERE c.customer_id = ? ORDER BY c.created_at, c.item_id",
            [$customerId]
        );
    }

    public function summary(int $customerId): array
    {
        $items = $this->items($customerId);
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }
        $fee = $items ? (float) $this->app->config['delivery_fee'] : 0.0;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_fee' => $fee,
            'total' => $subtotal + $fee,
            'restaurant_name' => $items[0]['restaurant_name'] ?? '',
            'restaurant_id' => (int) ($items[0]['restaurant_id'] ?? 0),
            'can_checkout' => $items && !in_array(0, array_map('intval', array_column($items, 'can_order')), true),
        ];
    }

    private function limit(int $quantity): int
    {
        return max(1, min((int) $this->app->config['max_item_quantity'], $quantity));
    }

    public function add(int $customerId, int $itemId, int $quantity, bool $replaceCart = false): string
    {
        $item = $this->app->make(FoodRepository::class)->findForOrder($itemId);
        if (!$item || $item['deleted_at'] !== null || !$item['is_available'] || $item['restaurant_status'] !== 'approved') {
            throw new DomainError('This item is not available.');
        }
        if (!$item['is_open']) {
            throw new DomainError('This restaurant is closed right now.');
        }

        $this->db->transaction(function () use ($customerId, $itemId, $quantity, $replaceCart, $item) {
            $other = $this->db->value(
                'SELECT r.name FROM cart_items c JOIN food_items f ON f.item_id = c.item_id
                 JOIN restaurants r ON r.restaurant_id = f.restaurant_id
                 WHERE c.customer_id = ? AND f.restaurant_id <> ? LIMIT 1',
                [$customerId, (int) $item['restaurant_id']]
            );
            if ($other !== null) {
                if (!$replaceCart) {
                    throw new CartConflict("Your cart has items from $other. A cart can hold one restaurant at a time.");
                }
                $this->clear($customerId);
            }
            $max = (int) $this->app->config['max_item_quantity'];
            $this->db->run(
                'INSERT INTO cart_items (customer_id, item_id, quantity) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = LEAST(?, quantity + ?)',
                [$customerId, $itemId, $this->limit($quantity), $max, $this->limit($quantity)]
            );
        });

        return $item['name'];
    }

    public function setQuantity(int $customerId, int $itemId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($customerId, $itemId);

            return;
        }
        $this->db->run('UPDATE cart_items SET quantity = ? WHERE customer_id = ? AND item_id = ?', [$this->limit($quantity), $customerId, $itemId]);
    }

    public function remove(int $customerId, int $itemId): void
    {
        $this->db->run('DELETE FROM cart_items WHERE customer_id = ? AND item_id = ?', [$customerId, $itemId]);
    }

    public function clear(int $customerId): void
    {
        $this->db->run('DELETE FROM cart_items WHERE customer_id = ?', [$customerId]);
    }
}
