<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;
use App\Core\HttpException;

/** Status changes made by customers, managers and administrators. */
final class OrderService extends Service
{
    public function changeStatus(int $orderId, string $to, array $actor): void
    {
        $this->db->transaction(function () use ($orderId, $to, $actor) {
            // Row lock: two people cannot change the same order at once
            $order = $this->db->one(
                'SELECT o.order_id, o.status, o.customer_id, r.owner_id FROM orders o
                 JOIN restaurants r ON r.restaurant_id = o.restaurant_id WHERE o.order_id = ? FOR UPDATE',
                [$orderId]
            );
            $role = $actor['role'];
            $ownerColumn = ['customer' => 'customer_id', 'manager' => 'owner_id'][$role] ?? null;
            if (!$order || ($ownerColumn !== null && (int) $order[$ownerColumn] !== (int) $actor['user_id'])) {
                throw new HttpException(404, 'Order not found.');
            }
            if (!OrderRules::can($order['status'], $to, $role)) {
                throw new DomainError(sprintf(
                    'An order that is "%s" cannot be changed to "%s".',
                    status_label($order['status']),
                    status_label($to)
                ));
            }
            $this->db->update('orders', ['status' => $to], 'order_id', $orderId);
            if ($to === 'cancelled') {
                $this->db->run("UPDATE deliveries SET status = 'cancelled' WHERE order_id = ?", [$orderId]);
            }
            $this->record($orderId, $to, (int) $actor['user_id']);
        });
    }

    /** An administrator assigns a rider, or returns the delivery to the queue with null. */
    public function assignRider(int $orderId, ?int $riderId, array $actor): void
    {
        $this->db->transaction(function () use ($orderId, $riderId, $actor) {
            $order = $this->db->one('SELECT status FROM orders WHERE order_id = ? FOR UPDATE', [$orderId]);
            $delivery = $this->db->one('SELECT status FROM deliveries WHERE order_id = ? FOR UPDATE', [$orderId]);
            if (!$order || !$delivery) {
                throw new HttpException(404, 'Order not found.');
            }
            if (OrderRules::isFinal($order['status']) || !in_array($delivery['status'], ['waiting', 'assigned'], true)) {
                throw new DomainError('The rider can only be changed before pickup on an open order.');
            }
            if ($riderId !== null && !$this->db->value("SELECT 1 FROM users WHERE user_id = ? AND role = 'rider' AND status = 'active'", [$riderId])) {
                throw new DomainError('Choose an active rider.');
            }
            $this->db->run(
                'UPDATE deliveries SET rider_id = ?, status = ?, assigned_at = ? WHERE order_id = ?',
                [$riderId, $riderId === null ? 'waiting' : 'assigned', $riderId === null ? null : $this->now(), $orderId]
            );
            $this->record($orderId, $riderId === null ? 'rider_removed' : 'rider_assigned', (int) $actor['user_id']);
        });
    }
}
