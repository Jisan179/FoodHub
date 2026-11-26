<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DomainError;
use App\Core\HttpException;

final class ReviewRepository extends Repository
{
    public function forCustomer(int $customerId): array
    {
        return $this->db->all(
            'SELECT v.*, f.name AS item_name, f.image_path, r.name AS restaurant_name FROM reviews v
             JOIN food_items f ON f.item_id = v.item_id JOIN restaurants r ON r.restaurant_id = f.restaurant_id
             WHERE v.customer_id = ? ORDER BY v.review_id DESC',
            [$customerId]
        );
    }

    /** Items from delivered orders that have no review yet. */
    public function pendingFor(int $customerId): array
    {
        return $this->db->all(
            "SELECT DISTINCT o.order_id, i.item_id, i.item_name, f.image_path, r.name AS restaurant_name
             FROM orders o JOIN order_items i ON i.order_id = o.order_id JOIN restaurants r ON r.restaurant_id = o.restaurant_id
             JOIN food_items f ON f.item_id = i.item_id
             LEFT JOIN reviews v ON v.order_id = o.order_id AND v.item_id = i.item_id AND v.customer_id = o.customer_id
             WHERE o.customer_id = ? AND o.status = 'delivered' AND v.review_id IS NULL
             ORDER BY o.order_id DESC",
            [$customerId]
        );
    }

    public function create(int $customerId, int $orderId, int $itemId, int $rating, string $comment): int
    {
        $eligible = $this->db->value(
            "SELECT 1 FROM orders o JOIN order_items i ON i.order_id = o.order_id
             WHERE o.order_id = ? AND o.customer_id = ? AND o.status = 'delivered' AND i.item_id = ? LIMIT 1",
            [$orderId, $customerId, $itemId]
        );
        $exists = $this->db->value('SELECT 1 FROM reviews WHERE customer_id = ? AND order_id = ? AND item_id = ?', [$customerId, $orderId, $itemId]);
        if (!$eligible || $exists) {
            throw new DomainError('You can review each item from a delivered order once.');
        }

        return $this->db->insert('reviews', [
            'customer_id' => $customerId, 'order_id' => $orderId, 'item_id' => $itemId,
            'rating' => $rating, 'comment' => $comment,
        ]);
    }

    private function ownedOrFail(int $reviewId, int $customerId): void
    {
        if (!$this->db->value('SELECT 1 FROM reviews WHERE review_id = ? AND customer_id = ?', [$reviewId, $customerId])) {
            throw new HttpException(404, 'Review not found.');
        }
    }

    public function update(int $reviewId, int $customerId, int $rating, string $comment): void
    {
        $this->ownedOrFail($reviewId, $customerId);
        $this->db->update('reviews', ['rating' => $rating, 'comment' => $comment], 'review_id', $reviewId);
    }

    public function delete(int $reviewId, int $customerId): void
    {
        $this->ownedOrFail($reviewId, $customerId);
        $this->db->run('DELETE FROM reviews WHERE review_id = ?', [$reviewId]);
    }
}
