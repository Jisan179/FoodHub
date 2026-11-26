<?php

declare(strict_types=1);

/** Escape a value for safe HTML output. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format an amount in Bangladeshi taka. */
function money($amount): string
{
    return '৳' . number_format((float) $amount, 2);
}

/** Turn a stored status such as "out_for_delivery" into a readable label. */
function status_label(string $status): string
{
    $labels = [
        'ready' => 'Ready for Delivery',
        'cod' => 'Cash on Delivery',
        'wallet' => 'Mobile Wallet (simulated)',
        'waiting' => 'Waiting for Rider',
        'manager' => 'Restaurant Manager',
        'admin' => 'Administrator',
    ];

    return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}

/** CSS badge class for a status value. */
function status_badge(string $status): string
{
    $tones = [
        'success' => ['active', 'approved', 'delivered', 'paid'],
        'warning' => ['pending', 'waiting', 'unpaid'],
        'info' => ['preparing', 'ready', 'assigned', 'picked_up', 'out_for_delivery'],
        'danger' => ['cancelled', 'rejected', 'suspended', 'inactive'],
    ];
    foreach ($tones as $tone => $values) {
        if (in_array($status, $values, true)) {
            return 'badge badge-' . $tone;
        }
    }

    return 'badge';
}

/** A food emoji used as artwork when there is no photo. */
function food_emoji(string $text): string
{
    $art = ['burger' => '🍔', 'fries' => '🍟', 'biryani' => '🍛', 'kacchi' => '🍛', 'kebab' => '🌯', 'wrap' => '🌯', 'pizza' => '🍕',
        'noodle' => '🍜', 'chicken' => '🍗', 'salad' => '🥗', 'shake' => '🥤', 'drink' => '🥤', 'tea' => '🍵', 'cake' => '🍰', 'dessert' => '🍰', 'grill' => '🍢'];
    foreach ($art as $word => $emoji) {
        if (stripos($text, $word) !== false) {
            return $emoji;
        }
    }

    return '🍽️';
}

function format_datetime(?string $value): string
{
    return $value ? date('d M Y, h:i A', strtotime($value)) : '-';
}
