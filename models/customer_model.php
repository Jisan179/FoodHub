<?php
/**
 * FoodHub - Procedural Customer Model
 * Pure procedural functions for Customer operations:
 * Browsing, Favorites, Cart, Orders, and Reviews
 */

// =========================================================================
// 1. RESTAURANT & FOOD CATALOG FUNCTIONS
// =========================================================================

/**
 * Get all approved restaurants with optional search filter and rating averages
 */
function get_customer_approved_restaurants($conn, $search = null, $customer_id = null) {
    $where_clauses = ["r.status = 'Approved'"];

    if (!empty($search)) {
        $safe_search = mysqli_real_escape_string($conn, trim($search));
        $where_clauses[] = "(r.name LIKE '%$safe_search%' OR r.description LIKE '%$safe_search%' OR r.address LIKE '%$safe_search%')";
    }

    $where_sql = implode(' AND ', $where_clauses);
    $safe_cust_id = ($customer_id !== null) ? intval($customer_id) : 0;

    $sql = "
        SELECT 
            r.restaurant_id,
            r.name AS restaurant_name,
            r.description,
            r.address,
            r.phone,
            r.created_at,
            (SELECT COUNT(*) FROM food_items f WHERE f.restaurant_id = r.restaurant_id AND f.status = 'Available') AS total_items,
            (SELECT COUNT(*) FROM favorites fav WHERE fav.restaurant_id = r.restaurant_id AND fav.customer_id = $safe_cust_id) AS is_favorite,
            (SELECT AVG(rev.rating) 
             FROM reviews rev 
             JOIN food_items fi ON rev.item_id = fi.item_id 
             WHERE fi.restaurant_id = r.restaurant_id) AS avg_rating,
            (SELECT COUNT(rev.review_id) 
             FROM reviews rev 
             JOIN food_items fi ON rev.item_id = fi.item_id 
             WHERE fi.restaurant_id = r.restaurant_id) AS total_reviews
        FROM restaurants r
        WHERE $where_sql
        ORDER BY r.restaurant_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $restaurants = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $restaurants[] = $row;
        }
    }

    return $restaurants;
}

/**
 * Get a single approved restaurant by ID
 */
function get_customer_restaurant_by_id($conn, $restaurant_id, $customer_id = null) {
    $safe_id = intval($restaurant_id);
    $safe_cust_id = ($customer_id !== null) ? intval($customer_id) : 0;

    $sql = "
        SELECT 
            r.restaurant_id,
            r.name AS restaurant_name,
            r.description,
            r.address,
            r.phone,
            r.created_at,
            (SELECT COUNT(*) FROM favorites fav WHERE fav.restaurant_id = r.restaurant_id AND fav.customer_id = $safe_cust_id) AS is_favorite,
            (SELECT AVG(rev.rating) 
             FROM reviews rev 
             JOIN food_items fi ON rev.item_id = fi.item_id 
             WHERE fi.restaurant_id = r.restaurant_id) AS avg_rating,
            (SELECT COUNT(rev.review_id) 
             FROM reviews rev 
             JOIN food_items fi ON rev.item_id = fi.item_id 
             WHERE fi.restaurant_id = r.restaurant_id) AS total_reviews
        FROM restaurants r
        WHERE r.restaurant_id = $safe_id AND r.status = 'Approved'
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Get all available food items for a restaurant with category filter & ratings
 */
function get_customer_menu_items($conn, $restaurant_id, $category = null) {
    $safe_rest_id = intval($restaurant_id);
    $where = "f.restaurant_id = $safe_rest_id AND f.status = 'Available'";

    if (!empty($category) && $category !== 'All') {
        $safe_cat = mysqli_real_escape_string($conn, trim($category));
        $where .= " AND f.category = '$safe_cat'";
    }

    $sql = "
        SELECT 
            f.item_id,
            f.restaurant_id,
            f.name AS item_name,
            f.description,
            f.price,
            f.category,
            f.status,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.review_id) AS review_count
        FROM food_items f
        LEFT JOIN reviews r ON f.item_id = r.item_id
        WHERE $where
        GROUP BY f.item_id
        ORDER BY f.category ASC, f.name ASC
    ";

    $result = mysqli_query($conn, $sql);
    $items = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }

    return $items;
}

/**
 * Get distinct categories for a restaurant's menu
 */
function get_restaurant_categories($conn, $restaurant_id) {
    $safe_rest_id = intval($restaurant_id);
    $sql = "
        SELECT DISTINCT category 
        FROM food_items 
        WHERE restaurant_id = $safe_rest_id AND status = 'Available'
        ORDER BY category ASC
    ";

    $result = mysqli_query($conn, $sql);
    $categories = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['category'];
        }
    }

    return $categories;
}

/**
 * Global search across restaurants and available food items
 */
function search_food_and_restaurants($conn, $query) {
    $safe_query = mysqli_real_escape_string($conn, trim($query));

    $sql = "
        SELECT 
            f.item_id,
            f.name AS item_name,
            f.description AS item_description,
            f.price,
            f.category,
            r.restaurant_id,
            r.name AS restaurant_name,
            r.address AS restaurant_address,
            COALESCE(AVG(rev.rating), 0) AS avg_rating,
            COUNT(rev.review_id) AS review_count
        FROM food_items f
        JOIN restaurants r ON f.restaurant_id = r.restaurant_id
        LEFT JOIN reviews rev ON f.item_id = rev.item_id
        WHERE r.status = 'Approved' 
          AND f.status = 'Available'
          AND (f.name LIKE '%$safe_query%' OR f.description LIKE '%$safe_query%' OR f.category LIKE '%$safe_query%' OR r.name LIKE '%$safe_query%')
        GROUP BY f.item_id
        ORDER BY f.name ASC
    ";

    $result = mysqli_query($conn, $sql);
    $results = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    }

    return $results;
}

/**
 * Get a single food item with restaurant details
 */
function get_food_item_by_id($conn, $item_id) {
    $safe_id = intval($item_id);

    $sql = "
        SELECT 
            f.item_id,
            f.restaurant_id,
            f.name AS item_name,
            f.description,
            f.price,
            f.category,
            f.status,
            r.name AS restaurant_name,
            r.status AS restaurant_status,
            COALESCE(AVG(rev.rating), 0) AS avg_rating,
            COUNT(rev.review_id) AS review_count
        FROM food_items f
        JOIN restaurants r ON f.restaurant_id = r.restaurant_id
        LEFT JOIN reviews rev ON f.item_id = rev.item_id
        WHERE f.item_id = $safe_id
        GROUP BY f.item_id
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// =========================================================================
// 2. FAVORITES FUNCTIONS
// =========================================================================

/**
 * Get customer favorite restaurants
 */
function get_customer_favorites($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            fav.favorite_id,
            fav.created_at AS favorited_at,
            r.restaurant_id,
            r.name AS restaurant_name,
            r.description,
            r.address,
            r.phone,
            (SELECT COUNT(*) FROM food_items f WHERE f.restaurant_id = r.restaurant_id AND f.status = 'Available') AS total_items,
            (SELECT AVG(rev.rating) 
             FROM reviews rev 
             JOIN food_items fi ON rev.item_id = fi.item_id 
             WHERE fi.restaurant_id = r.restaurant_id) AS avg_rating,
            (SELECT COUNT(rev.review_id) 
             FROM reviews rev 
             JOIN food_items fi ON rev.item_id = fi.item_id 
             WHERE fi.restaurant_id = r.restaurant_id) AS total_reviews
        FROM favorites fav
        JOIN restaurants r ON fav.restaurant_id = r.restaurant_id
        WHERE fav.customer_id = $safe_cust_id AND r.status = 'Approved'
        ORDER BY fav.favorite_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $favorites = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $favorites[] = $row;
        }
    }

    return $favorites;
}

/**
 * Check if a restaurant is in customer's favorites
 */
function is_restaurant_favorited($conn, $customer_id, $restaurant_id) {
    $safe_cust_id = intval($customer_id);
    $safe_rest_id = intval($restaurant_id);

    $sql = "SELECT favorite_id FROM favorites WHERE customer_id = $safe_cust_id AND restaurant_id = $safe_rest_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    return ($result && mysqli_num_rows($result) > 0);
}

/**
 * Add a restaurant to customer favorites
 */
function add_customer_favorite($conn, $customer_id, $restaurant_id) {
    $safe_cust_id = intval($customer_id);
    $safe_rest_id = intval($restaurant_id);

    $sql = "INSERT IGNORE INTO favorites (customer_id, restaurant_id) VALUES ($safe_cust_id, $safe_rest_id)";
    return (bool)mysqli_query($conn, $sql);
}

/**
 * Remove a restaurant from customer favorites
 */
function remove_customer_favorite($conn, $customer_id, $restaurant_id) {
    $safe_cust_id = intval($customer_id);
    $safe_rest_id = intval($restaurant_id);

    $sql = "DELETE FROM favorites WHERE customer_id = $safe_cust_id AND restaurant_id = $safe_rest_id";
    return (bool)mysqli_query($conn, $sql);
}

// =========================================================================
// 3. CART FUNCTIONS (Single-Restaurant Enforced)
// =========================================================================

/**
 * Get the current restaurant_id associated with items in the customer's cart
 */
function get_cart_restaurant_id($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT f.restaurant_id, r.name AS restaurant_name
        FROM cart c
        JOIN food_items f ON c.item_id = f.item_id
        JOIN restaurants r ON f.restaurant_id = r.restaurant_id
        WHERE c.customer_id = $safe_cust_id
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Get all cart items for a customer
 */
function get_customer_cart($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            c.cart_id,
            c.quantity,
            c.created_at AS added_at,
            f.item_id,
            f.name AS item_name,
            f.description AS item_description,
            f.price,
            f.category,
            f.status AS item_status,
            (c.quantity * f.price) AS subtotal,
            r.restaurant_id,
            r.name AS restaurant_name
        FROM cart c
        JOIN food_items f ON c.item_id = f.item_id
        JOIN restaurants r ON f.restaurant_id = r.restaurant_id
        WHERE c.customer_id = $safe_cust_id
        ORDER BY c.cart_id ASC
    ";

    $result = mysqli_query($conn, $sql);
    $cart_items = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cart_items[] = $row;
        }
    }

    return $cart_items;
}

/**
 * Get cart summary calculations
 */
function get_cart_summary($conn, $customer_id) {
    $items = get_customer_cart($conn, $customer_id);
    $total_items = 0;
    $subtotal = 0.0;
    $restaurant_id = null;
    $restaurant_name = '';

    foreach ($items as $item) {
        $total_items += intval($item['quantity']);
        $subtotal += floatval($item['subtotal']);
        $restaurant_id = $item['restaurant_id'];
        $restaurant_name = $item['restaurant_name'];
    }

    $delivery_fee = ($total_items > 0) ? 50.00 : 0.00; // Flat ৳50 delivery fee
    $grand_total = $subtotal + $delivery_fee;

    return [
        'items'           => $items,
        'total_items'     => $total_items,
        'subtotal'        => $subtotal,
        'delivery_fee'    => $delivery_fee,
        'grand_total'     => $grand_total,
        'restaurant_id'   => $restaurant_id,
        'restaurant_name' => $restaurant_name
    ];
}

/**
 * Count total individual items in customer's cart
 */
function count_cart_items($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);
    $sql = "SELECT SUM(quantity) AS total_qty FROM cart WHERE customer_id = $safe_cust_id";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['total_qty'] ?? 0);
    }
    return 0;
}

/**
 * Add item to cart with single-restaurant enforcement
 * Returns: ['status' => 'success'] | ['status' => 'conflict', 'current_restaurant' => '...'] | ['status' => 'error', 'message' => '...']
 */
function add_to_cart($conn, $customer_id, $item_id, $quantity = 1, $clear_if_conflict = false) {
    $safe_cust_id = intval($customer_id);
    $safe_item_id = intval($item_id);
    $qty = max(1, intval($quantity));

    // Verify item exists and is Available
    $item = get_food_item_by_id($conn, $safe_item_id);
    if (!$item || $item['status'] !== 'Available' || $item['restaurant_status'] !== 'Approved') {
        return ['status' => 'error', 'message' => 'This food item is currently unavailable.'];
    }

    // Check single-restaurant constraint
    $existing_cart_rest = get_cart_restaurant_id($conn, $safe_cust_id);
    if ($existing_cart_rest && intval($existing_cart_rest['restaurant_id']) !== intval($item['restaurant_id'])) {
        if (!$clear_if_conflict) {
            return [
                'status'                 => 'conflict',
                'cart_restaurant_id'     => $existing_cart_rest['restaurant_id'],
                'cart_restaurant_name'   => $existing_cart_rest['restaurant_name'],
                'new_restaurant_id'      => $item['restaurant_id'],
                'new_restaurant_name'    => $item['restaurant_name']
            ];
        } else {
            // User confirmed reset cart from new restaurant
            clear_customer_cart($conn, $safe_cust_id);
        }
    }

    $sql = "
        INSERT INTO cart (customer_id, item_id, quantity)
        VALUES ($safe_cust_id, $safe_item_id, $qty)
        ON DUPLICATE KEY UPDATE quantity = quantity + $qty
    ";

    if (mysqli_query($conn, $sql)) {
        return ['status' => 'success', 'message' => "Added {$item['item_name']} to cart."];
    } else {
        return ['status' => 'error', 'message' => 'Database error adding to cart: ' . mysqli_error($conn)];
    }
}

/**
 * Update cart item quantity
 */
function update_cart_quantity($conn, $customer_id, $item_id, $quantity) {
    $safe_cust_id = intval($customer_id);
    $safe_item_id = intval($item_id);
    $qty = intval($quantity);

    if ($qty <= 0) {
        return remove_from_cart($conn, $safe_cust_id, $safe_item_id);
    }

    $sql = "UPDATE cart SET quantity = $qty WHERE customer_id = $safe_cust_id AND item_id = $safe_item_id";
    return (bool)mysqli_query($conn, $sql);
}

/**
 * Remove an item from cart
 */
function remove_from_cart($conn, $customer_id, $item_id) {
    $safe_cust_id = intval($customer_id);
    $safe_item_id = intval($item_id);

    $sql = "DELETE FROM cart WHERE customer_id = $safe_cust_id AND item_id = $safe_item_id";
    return (bool)mysqli_query($conn, $sql);
}

/**
 * Clear customer cart completely
 */
function clear_customer_cart($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);
    $sql = "DELETE FROM cart WHERE customer_id = $safe_cust_id";
    return (bool)mysqli_query($conn, $sql);
}

// =========================================================================
// 4. ORDER PLACEMENT & TRANSACTIONS
// =========================================================================

/**
 * Place order with server-side recalculated price in a DB transaction
 */
function place_customer_order($conn, $customer_id, $delivery_address, $payment_method = 'Cash on Delivery') {
    $safe_cust_id = intval($customer_id);
    $safe_address = mysqli_real_escape_string($conn, trim($delivery_address));
    $safe_payment = mysqli_real_escape_string($conn, trim($payment_method));

    if (empty($safe_address)) {
        return ['success' => false, 'error' => 'Please provide a valid delivery address.'];
    }

    // Get current cart
    $cart_summary = get_cart_summary($conn, $safe_cust_id);
    $cart_items   = $cart_summary['items'];

    if (empty($cart_items)) {
        return ['success' => false, 'error' => 'Your cart is empty. Please add food items before checking out.'];
    }

    $restaurant_id = intval($cart_summary['restaurant_id']);

    // Begin atomic transaction
    mysqli_begin_transaction($conn);

    // 1. Recalculate total directly from database food_items prices to prevent price spoofing
    $computed_total = 0.0;
    $validated_items = [];

    foreach ($cart_items as $cart_entry) {
        $item_id = intval($cart_entry['item_id']);
        $qty     = intval($cart_entry['quantity']);

        // Lock & fetch fresh price
        $fetch_sql = "SELECT item_id, price, status FROM food_items WHERE item_id = $item_id FOR UPDATE";
        $res = mysqli_query($conn, $fetch_sql);
        if (!$res || mysqli_num_rows($res) === 0) {
            mysqli_rollback($conn);
            return ['success' => false, 'error' => 'One or more food items in your cart could not be found.'];
        }

        $food = mysqli_fetch_assoc($res);
        if ($food['status'] !== 'Available') {
            mysqli_rollback($conn);
            return ['success' => false, 'error' => "Item #{$item_id} is currently unavailable."];
        }

        $unit_price = floatval($food['price']);
        $item_subtotal = $unit_price * $qty;
        $computed_total += $item_subtotal;

        $validated_items[] = [
            'item_id'  => $item_id,
            'quantity' => $qty,
            'price'    => $unit_price,
            'subtotal' => $item_subtotal
        ];
    }

    // Add flat delivery fee
    $grand_order_total = $computed_total + 50.00;

    // 2. Insert into orders table
    $order_sql = "
        INSERT INTO orders (customer_id, restaurant_id, total_amount, order_status, delivery_address, payment_method, payment_status)
        VALUES ($safe_cust_id, $restaurant_id, $grand_order_total, 'Pending', '$safe_address', '$safe_payment', 'Unpaid')
    ";

    if (!mysqli_query($conn, $order_sql)) {
        mysqli_rollback($conn);
        return ['success' => false, 'error' => 'Error creating order header: ' . mysqli_error($conn)];
    }

    $new_order_id = mysqli_insert_id($conn);

    // 3. Insert each order item
    foreach ($validated_items as $val_item) {
        $item_id_val  = $val_item['item_id'];
        $qty_val      = $val_item['quantity'];
        $price_val    = $val_item['price'];
        $subtotal_val = $val_item['subtotal'];

        $item_sql = "
            INSERT INTO order_items (order_id, item_id, quantity, price, subtotal)
            VALUES ($new_order_id, $item_id_val, $qty_val, $price_val, $subtotal_val)
        ";

        if (!mysqli_query($conn, $item_sql)) {
            mysqli_rollback($conn);
            return ['success' => false, 'error' => 'Error creating order line items: ' . mysqli_error($conn)];
        }
    }

    // 4. Create initial delivery record
    $deliv_sql = "
        INSERT INTO deliveries (order_id, rider_id, delivery_status, assigned_at)
        VALUES ($new_order_id, NULL, 'Pending Assignment', NOW())
    ";

    if (!mysqli_query($conn, $deliv_sql)) {
        mysqli_rollback($conn);
        return ['success' => false, 'error' => 'Error initializing delivery record: ' . mysqli_error($conn)];
    }

    // 5. Clear cart
    $clear_sql = "DELETE FROM cart WHERE customer_id = $safe_cust_id";
    if (!mysqli_query($conn, $clear_sql)) {
        mysqli_rollback($conn);
        return ['success' => false, 'error' => 'Error clearing cart: ' . mysqli_error($conn)];
    }

    // Commit transaction
    mysqli_commit($conn);

    return [
        'success'  => true,
        'order_id' => $new_order_id,
        'total'    => $grand_order_total
    ];
}

// =========================================================================
// 5. ORDER TRACKING & HISTORY
// =========================================================================

/**
 * Get all orders for customer
 */
function get_customer_orders($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            o.order_id,
            o.restaurant_id,
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            o.created_at,
            r.name AS restaurant_name,
            r.phone AS restaurant_phone,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS total_items,
            d.delivery_status,
            rider.name AS rider_name,
            rider.phone AS rider_phone
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        LEFT JOIN users rider ON d.rider_id = rider.user_id
        WHERE o.customer_id = $safe_cust_id
        ORDER BY o.order_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $orders = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }

    return $orders;
}

/**
 * Get single order details with items and review eligibility
 */
function get_customer_order_details($conn, $order_id, $customer_id) {
    $safe_ord_id  = intval($order_id);
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            o.order_id,
            o.customer_id,
            o.restaurant_id,
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            o.created_at,
            r.name AS restaurant_name,
            r.phone AS restaurant_phone,
            r.address AS restaurant_address,
            d.delivery_id,
            d.delivery_status,
            d.assigned_at,
            d.delivered_at,
            rider.name AS rider_name,
            rider.phone AS rider_phone
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        LEFT JOIN users rider ON d.rider_id = rider.user_id
        WHERE o.order_id = $safe_ord_id AND o.customer_id = $safe_cust_id
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) === 0) {
        return null;
    }

    $order = mysqli_fetch_assoc($result);

    // Fetch order items with review status
    $items_sql = "
        SELECT 
            oi.order_item_id,
            oi.item_id,
            oi.quantity,
            oi.price,
            oi.subtotal,
            f.name AS item_name,
            f.category,
            f.description,
            rev.review_id,
            rev.rating AS user_rating,
            rev.comment AS user_comment
        FROM order_items oi
        JOIN food_items f ON oi.item_id = f.item_id
        LEFT JOIN reviews rev ON rev.order_id = oi.order_id 
                             AND rev.item_id = oi.item_id 
                             AND rev.customer_id = $safe_cust_id
        WHERE oi.order_id = $safe_ord_id
        ORDER BY oi.order_item_id ASC
    ";

    $items_res = mysqli_query($conn, $items_sql);
    $order['items'] = [];

    if ($items_res) {
        while ($item = mysqli_fetch_assoc($items_res)) {
            $order['items'][] = $item;
        }
    }

    return $order;
}

/**
 * Cancel an order (only allowed if status is 'Pending' or 'Preparing')
 */
function cancel_customer_order($conn, $order_id, $customer_id) {
    $safe_ord_id  = intval($order_id);
    $safe_cust_id = intval($customer_id);

    // Verify order eligibility
    $check_sql = "
        SELECT order_id, order_status 
        FROM orders 
        WHERE order_id = $safe_ord_id AND customer_id = $safe_cust_id
        LIMIT 1
    ";
    $res = mysqli_query($conn, $check_sql);

    if (!$res || mysqli_num_rows($res) === 0) {
        return ['success' => false, 'message' => 'Order not found.'];
    }

    $order = mysqli_fetch_assoc($res);
    $status = $order['order_status'];

    if ($status !== 'Pending' && $status !== 'Preparing') {
        return ['success' => false, 'message' => "Cannot cancel order because it is already '{$status}'."];
    }

    mysqli_begin_transaction($conn);

    // Update order status
    $update_ord = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = $safe_ord_id";
    if (!mysqli_query($conn, $update_ord)) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Failed to update order status: ' . mysqli_error($conn)];
    }

    // Update delivery status
    $update_del = "UPDATE deliveries SET delivery_status = 'Cancelled' WHERE order_id = $safe_ord_id";
    if (!mysqli_query($conn, $update_del)) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Failed to update delivery status: ' . mysqli_error($conn)];
    }

    mysqli_commit($conn);
    return ['success' => true, 'message' => "Order #$safe_ord_id has been successfully cancelled."];
}

// =========================================================================
// 6. REVIEWS MANAGEMENT
// =========================================================================

/**
 * Check if a customer can review a food item from a specific order
 */
function can_review_food_item($conn, $customer_id, $order_id, $item_id) {
    $safe_cust_id = intval($customer_id);
    $safe_ord_id  = intval($order_id);
    $safe_item_id = intval($item_id);

    // 1. Order must exist, belong to customer, and be Delivered
    $order_sql = "
        SELECT o.order_id 
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.order_id = $safe_ord_id 
          AND o.customer_id = $safe_cust_id 
          AND o.order_status = 'Delivered'
          AND oi.item_id = $safe_item_id
        LIMIT 1
    ";
    $ord_res = mysqli_query($conn, $order_sql);
    if (!$ord_res || mysqli_num_rows($ord_res) === 0) {
        return false;
    }

    // 2. Must not already have a review
    $rev_sql = "
        SELECT review_id 
        FROM reviews 
        WHERE customer_id = $safe_cust_id 
          AND order_id = $safe_ord_id 
          AND item_id = $safe_item_id 
        LIMIT 1
    ";
    $rev_res = mysqli_query($conn, $rev_sql);
    return ($rev_res && mysqli_num_rows($rev_res) === 0);
}

/**
 * Submit review for a food item
 */
function submit_food_review($conn, $customer_id, $order_id, $item_id, $rating, $comment) {
    $safe_cust_id = intval($customer_id);
    $safe_ord_id  = intval($order_id);
    $safe_item_id = intval($item_id);
    $rate_val     = max(1, min(5, intval($rating)));
    $safe_comment = mysqli_real_escape_string($conn, trim($comment));

    if (!can_review_food_item($conn, $safe_cust_id, $safe_ord_id, $safe_item_id)) {
        return ['success' => false, 'message' => 'You can only review food items from delivered orders that have not been reviewed yet.'];
    }

    $sql = "
        INSERT INTO reviews (customer_id, order_id, item_id, rating, comment)
        VALUES ($safe_cust_id, $safe_ord_id, $safe_item_id, $rate_val, '$safe_comment')
    ";

    if (mysqli_query($conn, $sql)) {
        return ['success' => true, 'review_id' => mysqli_insert_id($conn)];
    } else {
        return ['success' => false, 'message' => 'Database error submitting review: ' . mysqli_error($conn)];
    }
}

/**
 * Get all reviews written by a customer
 */
function get_customer_reviews($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            rev.review_id,
            rev.order_id,
            rev.item_id,
            rev.rating,
            rev.comment,
            rev.created_at,
            f.name AS item_name,
            f.category AS item_category,
            r.restaurant_id,
            r.name AS restaurant_name
        FROM reviews rev
        JOIN food_items f ON rev.item_id = f.item_id
        JOIN restaurants r ON f.restaurant_id = r.restaurant_id
        WHERE rev.customer_id = $safe_cust_id
        ORDER BY rev.review_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $reviews = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
    }

    return $reviews;
}

/**
 * Get single review by review_id owned by customer
 */
function get_review_by_id($conn, $review_id, $customer_id) {
    $safe_rev_id  = intval($review_id);
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            rev.review_id,
            rev.order_id,
            rev.item_id,
            rev.rating,
            rev.comment,
            rev.created_at,
            f.name AS item_name,
            r.name AS restaurant_name
        FROM reviews rev
        JOIN food_items f ON rev.item_id = f.item_id
        JOIN restaurants r ON f.restaurant_id = r.restaurant_id
        WHERE rev.review_id = $safe_rev_id AND rev.customer_id = $safe_cust_id
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Update existing review owned by customer
 */
function update_food_review($conn, $review_id, $customer_id, $rating, $comment) {
    $safe_rev_id  = intval($review_id);
    $safe_cust_id = intval($customer_id);
    $rate_val     = max(1, min(5, intval($rating)));
    $safe_comment = mysqli_real_escape_string($conn, trim($comment));

    $sql = "
        UPDATE reviews 
        SET rating = $rate_val, comment = '$safe_comment' 
        WHERE review_id = $safe_rev_id AND customer_id = $safe_cust_id
    ";

    return (bool)mysqli_query($conn, $sql);
}

/**
 * Delete review owned by customer
 */
function delete_food_review($conn, $review_id, $customer_id) {
    $safe_rev_id  = intval($review_id);
    $safe_cust_id = intval($customer_id);

    $sql = "DELETE FROM reviews WHERE review_id = $safe_rev_id AND customer_id = $safe_cust_id";
    return (bool)mysqli_query($conn, $sql);
}

/**
 * Get items from delivered orders waiting for customer review
 */
function get_unreviewed_delivered_items($conn, $customer_id) {
    $safe_cust_id = intval($customer_id);

    $sql = "
        SELECT 
            oi.order_id,
            oi.item_id,
            f.name AS item_name,
            f.category,
            r.name AS restaurant_name,
            o.created_at AS order_date
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN food_items f ON oi.item_id = f.item_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        LEFT JOIN reviews rev ON rev.order_id = o.order_id 
                             AND rev.item_id = oi.item_id 
                             AND rev.customer_id = $safe_cust_id
        WHERE o.customer_id = $safe_cust_id 
          AND o.order_status = 'Delivered' 
          AND rev.review_id IS NULL
        ORDER BY o.order_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $unreviewed = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $unreviewed[] = $row;
        }
    }

    return $unreviewed;
}

// =========================================================================
// 7. UTILITY & REDIRECTION HELPERS
// =========================================================================

/**
 * Resolve redirection URL safely from customer action endpoints
 */
function resolve_customer_redirect($url, $default = '../browse_restaurants.php') {
    if (empty($url)) {
        return $default;
    }
    // Full URL or root-relative path
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0) {
        return $url;
    }
    // Already has relative parent prefix
    if (strpos($url, '../') === 0) {
        return $url;
    }
    // Plain filename (e.g. 'favorites.php', 'browse_restaurants.php')
    return '../' . $url;
}

