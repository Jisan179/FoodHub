<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\HttpException;
use App\Core\Paginator;
use App\Services\OrderRules;

final class OrderRepository extends Repository
{
    private const SELECT = '
        SELECT o.*, r.name AS restaurant_name, r.owner_id, r.phone AS restaurant_phone, r.address AS restaurant_address,
               c.name AS customer_name, c.phone AS customer_phone,
               d.delivery_id, d.status AS delivery_status, d.rider_id, d.rider_earning,
               u.name AS rider_name, u.phone AS rider_phone
        FROM orders o
        JOIN restaurants r ON r.restaurant_id = o.restaurant_id
        JOIN users c ON c.user_id = o.customer_id
        JOIN deliveries d ON d.order_id = o.order_id
        LEFT JOIN users u ON u.user_id = d.rider_id';

    /** @param array{customer_id?: int, owner_id?: int, status?: string, statuses?: string[]} $filters */
    private function filterSql(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (isset($filters['customer_id'])) {
            $where[] = 'o.customer_id = ?';
            $params[] = $filters['customer_id'];
        }
        if (isset($filters['owner_id'])) {
            $where[] = 'r.owner_id = ?';
            $params[] = $filters['owner_id'];
        }
        if (in_array($filters['status'] ?? '', OrderRules::STATUSES, true)) {
            $where[] = 'o.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['statuses'])) {
            $where[] = 'o.status IN (' . implode(',', array_fill(0, count($filters['statuses']), '?')) . ')';
            array_push($params, ...array_values($filters['statuses']));
        }

        return [implode(' AND ', $where), $params];
    }

    public function count(array $filters = []): int
    {
        [$where, $params] = $this->filterSql($filters);

        return (int) $this->db->value("SELECT COUNT(*) FROM orders o JOIN restaurants r ON r.restaurant_id = o.restaurant_id WHERE $where", $params);
    }

    public function paginate(array $filters, Paginator $page): array
    {
        [$where, $params] = $this->filterSql($filters);
        array_push($params, $page->perPage, $page->offset());

        return $this->db->all(self::SELECT . " WHERE $where ORDER BY o.order_id DESC LIMIT ? OFFSET ?", $params);
    }

    public function list(array $filters, int $limit = 50): array
    {
        [$where, $params] = $this->filterSql($filters);
        $params[] = $limit;

        return $this->db->all(self::SELECT . " WHERE $where ORDER BY o.order_id DESC LIMIT ?", $params);
    }

    /**
     * One order, restricted to what the viewer may see.
     * Customers see their own orders, managers see their restaurants' orders, admins see all.
     */
    public function visibleOrFail(int $orderId, array $viewer): array
    {
        $where = 'o.order_id = ?';
        $params = [$orderId];
        if ($viewer['role'] === 'customer') {
            $where .= ' AND o.customer_id = ?';
            $params[] = (int) $viewer['user_id'];
        } elseif ($viewer['role'] === 'manager') {
            $where .= ' AND r.owner_id = ?';
            $params[] = (int) $viewer['user_id'];
        } elseif ($viewer['role'] !== 'admin') {
            throw new HttpException(403);
        }
        $order = $this->db->one(self::SELECT . " WHERE $where", $params);
        if (!$order) {
            throw new HttpException(404, 'Order not found.');
        }
        $order['items'] = $this->items($orderId);
        $order['history'] = $this->db->all(
            'SELECT h.*, u.name AS actor_name FROM order_status_history h LEFT JOIN users u ON u.user_id = h.changed_by
             WHERE h.order_id = ? ORDER BY h.history_id',
            [$orderId]
        );

        return $order;
    }

    public function items(int $orderId): array
    {
        return $this->db->all('SELECT * FROM order_items WHERE order_id = ? ORDER BY order_item_id', [$orderId]);
    }

    public function revenue(?int $ownerId = null): float
    {
        $sql = "SELECT COALESCE(SUM(o.total_amount), 0) FROM orders o JOIN restaurants r ON r.restaurant_id = o.restaurant_id WHERE o.status = 'delivered'";

        return (float) ($ownerId === null ? $this->db->value($sql) : $this->db->value($sql . ' AND r.owner_id = ?', [$ownerId]));
    }

    /** Orders placed on each of the last N days, including empty days. Cancelled orders are left out. */
    public function trendByDay(int $ownerId, int $days = 7): array
    {
        $report = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $report[date('Y-m-d', strtotime("-$i days"))] = ['orders' => 0, 'items' => 0, 'value' => 0.0];
        }
        $rows = $this->db->all(
            "SELECT DATE(o.created_at) AS day, COUNT(*) AS orders, SUM(o.subtotal) AS value,
                    SUM((SELECT SUM(i.quantity) FROM order_items i WHERE i.order_id = o.order_id)) AS items
             FROM orders o JOIN restaurants r ON r.restaurant_id = o.restaurant_id
             WHERE r.owner_id = ? AND o.status <> 'cancelled' AND o.created_at >= ? GROUP BY DATE(o.created_at)",
            [$ownerId, array_key_first($report) . ' 00:00:00']
        );
        foreach ($rows as $row) {
            if (isset($report[$row['day']])) {
                $report[$row['day']] = ['orders' => (int) $row['orders'], 'items' => (int) $row['items'], 'value' => (float) $row['value']];
            }
        }

        return $report;
    }

    /**
     * Dishes ordered since a given time, most ordered first.
     * When two dishes are level, the one ordered most recently ranks higher.
     */
    public function trendingItems(int $ownerId, string $since, int $limit = 5): array
    {
        return $this->db->all(
            "SELECT i.item_id, i.item_name, f.image_path, SUM(i.quantity) AS sold, SUM(i.quantity * i.unit_price) AS value
             FROM order_items i
             JOIN orders o ON o.order_id = i.order_id
             JOIN restaurants r ON r.restaurant_id = o.restaurant_id
             JOIN food_items f ON f.item_id = i.item_id
             WHERE r.owner_id = ? AND o.status <> 'cancelled' AND o.created_at >= ?
             GROUP BY i.item_id, i.item_name, f.image_path
             ORDER BY sold DESC, MAX(o.created_at) DESC, MAX(o.order_id) DESC
             LIMIT ?",
            [$ownerId, $since, $limit]
        );
    }

    public function customerStats(int $customerId): array
    {
        $row = $this->db->one(
            "SELECT COUNT(*) AS total,
                COALESCE(SUM(status NOT IN ('delivered', 'cancelled')), 0) AS active,
                COALESCE(SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END), 0) AS spent
             FROM orders WHERE customer_id = ?",
            [$customerId]
        );

        return ['total' => (int) $row['total'], 'active' => (int) $row['active'], 'spent' => (float) $row['spent']];
    }
}
