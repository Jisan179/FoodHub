<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;

final class CheckoutService extends Service
{
    /**
     * Places an order atomically. Prices come only from the database, never from the browser.
     *
     * @return int the new order id
     */
    public function place(int $customerId, string $address, string $phone, string $method): int
    {
        if (!in_array($method, ['cod', 'wallet'], true)) {
            throw new DomainError('Choose a valid payment method.');
        }

        return $this->db->transaction(function () use ($customerId, $address, $phone, $method) {
            $lines = $this->db->all(
                'SELECT c.item_id, c.quantity, f.name, f.price, f.restaurant_id, f.is_available, f.deleted_at,
                        r.status AS restaurant_status, r.is_open
                 FROM cart_items c JOIN food_items f ON f.item_id = c.item_id
                 JOIN restaurants r ON r.restaurant_id = f.restaurant_id
                 WHERE c.customer_id = ? FOR UPDATE',
                [$customerId]
            );
            if (!$lines) {
                throw new DomainError('Your cart is empty.');
            }
            $restaurantId = (int) $lines[0]['restaurant_id'];
            $subtotal = 0.0;
            foreach ($lines as $line) {
                if ((int) $line['restaurant_id'] !== $restaurantId) {
                    throw new DomainError('A cart can hold items from one restaurant only.');
                }
                if ($line['deleted_at'] !== null || !$line['is_available']) {
                    throw new DomainError($line['name'] . ' is no longer available. Please remove it from your cart.');
                }
                if ($line['restaurant_status'] !== 'approved' || !$line['is_open']) {
                    throw new DomainError('This restaurant is not accepting orders right now.');
                }
                $subtotal += (float) $line['price'] * (int) $line['quantity'];
            }

            $fee = (float) $this->app->config['delivery_fee'];
            $orderId = $this->db->insert('orders', [
                'customer_id' => $customerId, 'restaurant_id' => $restaurantId,
                'subtotal' => (string) round($subtotal, 2), 'delivery_fee' => (string) $fee,
                'total_amount' => (string) round($subtotal + $fee, 2),
                'delivery_address' => $address, 'contact_phone' => $phone, 'payment_method' => $method,
            ]);
            foreach ($lines as $line) {
                $this->db->insert('order_items', [
                    'order_id' => $orderId, 'item_id' => (int) $line['item_id'], 'item_name' => $line['name'],
                    'unit_price' => $line['price'], 'quantity' => (int) $line['quantity'],
                ]);
            }
            $this->db->insert('deliveries', ['order_id' => $orderId]);
            $this->record($orderId, 'placed', $customerId);
            $this->app->make(CartService::class)->clear($customerId);

            return $orderId;
        });
    }
}
