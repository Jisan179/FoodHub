<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;

/** Everything a rider can do with a delivery. */
final class DeliveryService extends Service
{
    /** Locks the order first and then the delivery, the same order used everywhere, to avoid deadlocks. */
    private function locked(int $deliveryId, callable $callback)
    {
        return $this->db->transaction(function () use ($deliveryId, $callback) {
            $orderId = (int) $this->db->value('SELECT order_id FROM deliveries WHERE delivery_id = ?', [$deliveryId]);
            $order = $this->db->one('SELECT * FROM orders WHERE order_id = ? FOR UPDATE', [$orderId]);
            $delivery = $this->db->one('SELECT * FROM deliveries WHERE delivery_id = ? FOR UPDATE', [$deliveryId]);
            if (!$order || !$delivery) {
                throw new DomainError('This delivery is not available.');
            }

            return $callback($delivery, $order);
        });
    }

    private function assertMine(array $delivery, int $riderId, array $statuses): void
    {
        if ((int) $delivery['rider_id'] !== $riderId || !in_array($delivery['status'], $statuses, true)) {
            throw new DomainError('This delivery is not available to you for that action.');
        }
    }

    public function accept(int $deliveryId, int $riderId): void
    {
        $this->locked($deliveryId, function (array $delivery, array $order) use ($riderId) {
            if ($delivery['status'] !== 'waiting' || $delivery['rider_id'] !== null || !in_array($order['status'], ['pending', 'preparing', 'ready'], true)) {
                throw new DomainError('This delivery has already been taken or is closed.');
            }
            $this->db->update('deliveries', ['rider_id' => $riderId, 'status' => 'assigned', 'assigned_at' => $this->now()], 'delivery_id', (int) $delivery['delivery_id']);
            $this->record((int) $order['order_id'], 'rider_assigned', $riderId);
        });
    }

    public function pickUp(int $deliveryId, int $riderId): void
    {
        $this->locked($deliveryId, function (array $delivery, array $order) use ($riderId) {
            $this->assertMine($delivery, $riderId, ['assigned']);
            if (!OrderRules::can($order['status'], 'out_for_delivery', 'rider')) {
                throw new DomainError('The restaurant has not marked this order Ready for Delivery yet.');
            }
            $this->db->update('deliveries', ['status' => 'picked_up', 'picked_up_at' => $this->now()], 'delivery_id', (int) $delivery['delivery_id']);
            $this->db->update('orders', ['status' => 'out_for_delivery'], 'order_id', (int) $order['order_id']);
            $this->record((int) $order['order_id'], 'out_for_delivery', $riderId);
        });
    }

    public function complete(int $deliveryId, int $riderId): void
    {
        $this->locked($deliveryId, function (array $delivery, array $order) use ($riderId) {
            $this->assertMine($delivery, $riderId, ['picked_up']);
            if (!OrderRules::can($order['status'], 'delivered', 'rider')) {
                throw new DomainError('This order cannot be marked as delivered.');
            }
            $earning = round((float) $order['total_amount'] * $this->app->config['rider_commission_percent'] / 100, 2);
            $this->db->update('deliveries', [
                'status' => 'delivered', 'delivered_at' => $this->now(), 'rider_earning' => (string) $earning,
            ], 'delivery_id', (int) $delivery['delivery_id']);
            // Payment is collected on delivery
            $this->db->update('orders', ['status' => 'delivered', 'payment_status' => 'paid'], 'order_id', (int) $order['order_id']);
            $this->record((int) $order['order_id'], 'delivered', $riderId);
        });
    }

    /** The rider gives the trip back. The customer's order stays alive. */
    public function release(int $deliveryId, int $riderId, string $reason): void
    {
        $this->locked($deliveryId, function (array $delivery, array $order) use ($riderId, $reason) {
            $this->assertMine($delivery, $riderId, ['assigned', 'picked_up']);
            $this->db->run(
                "UPDATE deliveries SET rider_id = NULL, status = 'waiting', assigned_at = NULL, picked_up_at = NULL WHERE delivery_id = ?",
                [(int) $delivery['delivery_id']]
            );
            if ($order['status'] === 'out_for_delivery') {
                $this->db->update('orders', ['status' => 'ready'], 'order_id', (int) $order['order_id']);
            }
            $this->record((int) $order['order_id'], 'rider_released', $riderId, $reason);
        });
    }
}
