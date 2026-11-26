<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Paginator;

final class DeliveryRepository extends Repository
{
    private const SELECT = '
        SELECT d.*, o.status AS order_status, o.total_amount, o.delivery_address, o.contact_phone,
               o.payment_method, o.payment_status, o.created_at AS ordered_at,
               r.name AS restaurant_name, r.address AS restaurant_address, r.phone AS restaurant_phone,
               c.name AS customer_name
        FROM deliveries d
        JOIN orders o ON o.order_id = d.order_id
        JOIN restaurants r ON r.restaurant_id = o.restaurant_id
        JOIN users c ON c.user_id = o.customer_id';

    /**
     * Open delivery requests. Customer address and phone are only shown after a rider accepts.
     */
    public function available(): array
    {
        return $this->db->all(
            "SELECT d.delivery_id, d.order_id, o.status AS order_status, o.total_amount,
                    r.name AS restaurant_name, r.address AS restaurant_address
             FROM deliveries d JOIN orders o ON o.order_id = d.order_id JOIN restaurants r ON r.restaurant_id = o.restaurant_id
             WHERE d.status = 'waiting' AND d.rider_id IS NULL AND o.status IN ('pending', 'preparing', 'ready')
             ORDER BY (o.status = 'ready') DESC, d.delivery_id LIMIT 50"
        );
    }

    public function activeFor(int $riderId): array
    {
        return $this->db->all(self::SELECT . " WHERE d.rider_id = ? AND d.status IN ('assigned', 'picked_up') ORDER BY d.delivery_id", [$riderId]);
    }

    public function countDelivered(int $riderId): int
    {
        return (int) $this->db->value("SELECT COUNT(*) FROM deliveries WHERE rider_id = ? AND status = 'delivered'", [$riderId]);
    }

    public function history(int $riderId, Paginator $page): array
    {
        return $this->db->all(
            self::SELECT . " WHERE d.rider_id = ? AND d.status = 'delivered' ORDER BY d.delivered_at DESC, d.delivery_id DESC LIMIT ? OFFSET ?",
            [$riderId, $page->perPage, $page->offset()]
        );
    }

    public function earnings(int $riderId): float
    {
        return (float) $this->db->value("SELECT COALESCE(SUM(rider_earning), 0) FROM deliveries WHERE rider_id = ? AND status = 'delivered'", [$riderId]);
    }
}
