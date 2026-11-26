<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Links optional photos in public/assets/img/ to the demo restaurants and dishes.
 * A file may be .jpg, .png or .webp. Missing files are skipped, so the emoji artwork is shown instead.
 *
 * @return int number of photos linked
 */
return static function (Database $db): int {
    $photos = [
        // file name (without extension) => [table, key column, id]
        'spice-grill-house' => ['restaurants', 'restaurant_id', 1],
        'burger-spot' => ['restaurants', 'restaurant_id', 2],
        'chicken-biryani' => ['food_items', 'item_id', 1],
        'beef-kebab-wrap' => ['food_items', 'item_id', 2],
        'mutton-kacchi' => ['food_items', 'item_id', 3],
        'double-cheeseburger' => ['food_items', 'item_id', 4],
        'truffle-fries' => ['food_items', 'item_id', 5],
    ];
    $linked = 0;
    foreach ($photos as $name => [$table, $key, $id]) {
        foreach (['jpg', 'png', 'webp'] as $extension) {
            if (is_file(BASE_PATH . "/public/assets/img/$name.$extension")) {
                $db->update($table, ['image_path' => "assets/img/$name.$extension"], $key, $id);
                $linked++;
                break;
            }
        }
    }

    return $linked;
};
