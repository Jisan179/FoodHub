<?php
/*
 * An illustrated route from the restaurant to the customer. It is drawn locally, so no address is sent
 * to a map provider unless the rider chooses "Open in Google Maps".
 */
$directions = 'https://www.google.com/maps/dir/?' . http_build_query([
    'api' => 1,
    'origin' => $trip['restaurant_address'],
    'destination' => $trip['delivery_address'],
]);
$onTheWay = $trip['status'] === 'picked_up';
?>
<figure class="route-map">
    <svg viewBox="0 0 400 190" role="img" aria-label="Illustrated route from <?= e($trip['restaurant_name']) ?> to the customer">
        <rect width="400" height="190" fill="#eaf0e4"/>
        <path d="M0 150 C60 128 96 172 160 158 S270 118 400 140 V190 H0Z" fill="#cfe6f5"/>
        <g fill="#d8e6cb"><rect x="18" y="16" width="70" height="38" rx="6"/><rect x="236" y="22" width="52" height="46" rx="6"/><rect x="150" y="96" width="62" height="34" rx="6"/></g>
        <g stroke="#ffffff" stroke-linecap="round" fill="none">
            <path d="M0 72H400" stroke-width="13"/><path d="M0 128H400" stroke-width="7"/>
            <path d="M104 0V190" stroke-width="11"/><path d="M222 0V190" stroke-width="7"/><path d="M308 0V190" stroke-width="11"/>
            <path d="M0 24L190 190" stroke-width="5"/><path d="M250 0L400 106" stroke-width="5"/>
        </g>
        <path d="M58 128H104V72H222V50H308H344" fill="none" stroke="#ff4757" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="<?= $onTheWay ? '1 0' : '2 10' ?>"/>
        <g><circle cx="58" cy="128" r="17" fill="#ffffff" stroke="#2f3542" stroke-width="3"/><text x="58" y="134" font-size="17" text-anchor="middle">🏪</text></g>
        <g><circle cx="344" cy="50" r="17" fill="#ffffff" stroke="#ff4757" stroke-width="3"/><text x="344" y="56" font-size="17" text-anchor="middle">🏠</text></g>
        <g><circle cx="<?= $onTheWay ? 222 : 58 ?>" cy="<?= $onTheWay ? 72 : 100 ?>" r="15" fill="#2f3542"/><text x="<?= $onTheWay ? 222 : 58 ?>" y="<?= $onTheWay ? 78 : 106 ?>" font-size="15" text-anchor="middle">🛵</text></g>
    </svg>
    <figcaption>
        <span class="muted small">Route illustration, not to scale</span>
        <a class="btn btn-secondary btn-sm" href="<?= e($directions) ?>" target="_blank" rel="noopener noreferrer">📍 Open in Google Maps</a>
    </figcaption>
</figure>
