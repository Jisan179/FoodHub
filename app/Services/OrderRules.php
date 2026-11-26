<?php

declare(strict_types=1);

namespace App\Services;

/** The order state machine. Pure logic with no database, so it is easy to test. */
final class OrderRules
{
    public const STATUSES = ['pending', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];

    /** from => [to => roles allowed to make the change] */
    private const TRANSITIONS = [
        'pending' => [
            'preparing' => ['manager', 'admin'],
            'cancelled' => ['customer', 'manager', 'admin'],
        ],
        'preparing' => [
            'ready' => ['manager', 'admin'],
            'cancelled' => ['customer', 'admin'],
        ],
        'ready' => [
            'out_for_delivery' => ['rider'],
            'cancelled' => ['admin'],
        ],
        'out_for_delivery' => [
            'delivered' => ['rider'],
            'ready' => ['rider'],
            'cancelled' => ['admin'],
        ],
        'delivered' => [],
        'cancelled' => [],
    ];

    public static function can(string $from, string $to, string $role): bool
    {
        return in_array($role, self::TRANSITIONS[$from][$to] ?? [], true);
    }

    /** @return string[] the statuses this role can move an order to */
    public static function options(string $from, string $role): array
    {
        return array_values(array_filter(
            array_keys(self::TRANSITIONS[$from] ?? []),
            static fn (string $to) => self::can($from, $to, $role)
        ));
    }

    public static function isFinal(string $status): bool
    {
        return in_array($status, ['delivered', 'cancelled'], true);
    }
}
